<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberPhone;
use App\Models\ProgramAttendance;
use App\Models\ProgramAuditLog;
use App\Models\ProgramRegistration;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Finds people registered more than once and merges them.
 *
 * Duplicates = records sharing a name (first + last, ignoring case and
 * spaces), a phone number (any format, main or extra) or an email. Pairs
 * marked "not the same person" are not suggested again.
 *
 * merge() moves everything from the duplicate onto the record kept -
 * phones, attendance, registrations + payments, pledges, roles, cells,
 * departments, invitees, login - fills in details the kept record lacks,
 * logs a snapshot of the duplicate and deletes it. One transaction.
 */
class MemberMerger
{
    /** Columns holding a users.id, re-pointed when two logins are merged. */
    private const USER_COLUMNS = [
        'head_of_church_units' => ['reg_by'],
        'members' => ['recorded_by'],
        'pledges' => ['recorded_by'],
        'pledge_audit_logs' => ['actor_id'],
        'pledge_campaigns' => ['created_by'],
        'pledge_contributions' => ['recorded_by'],
        'programs' => ['created_by'],
        'program_attendances' => ['recorded_by'],
        'program_audit_logs' => ['actor_id'],
        'program_payments' => ['recorded_by', 'reviewed_by'],
        'program_registrations' => ['registered_by'],
        'program_occurrences' => ['report_closed_by'],
        'program_sms_templates' => ['created_by', 'updated_by'],
        'service_church_times' => ['updated_by'],
        'new_soul_assignments' => ['assigned_by'],
        'member_phones' => ['added_by'],
        'member_duplicate_ignores' => ['ignored_by'],
        'users' => ['created_by', 'authorized_by'],
    ];

    /**
     * @param  array|null  $churchIds  only groups with someone in these churches (null = all)
     * @return Collection<int, array{members: Collection, reasons: array}>
     */
    public function duplicateGroups(?array $churchIds = null): Collection
    {
        $members = Member::with(['church', 'phones', 'title'])
            ->where('is_training', false)
            ->get();

        // Union-find over shared keys.
        $parent = [];
        $find = function ($x) use (&$parent, &$find) {
            return $parent[$x] === $x ? $x : ($parent[$x] = $find($parent[$x]));
        };
        $reasons = [];
        $byKey = [];
        foreach ($members as $m) {
            $parent[$m->id] = $m->id;
            $name = trim(preg_replace('/\s+/', ' ', mb_strtolower($m->first_name . ' ' . $m->last_name)));
            if ($name !== '') {
                $byKey['name:' . $name][] = $m->id;
            }
            foreach ($m->allPhones() as $phone) {
                if (Member::normalisedPhone($phone)) {
                    $byKey['phone:' . $phone][] = $m->id;
                }
            }
            if ($m->email) {
                $byKey['email:' . mb_strtolower(trim($m->email))][] = $m->id;
            }
        }

        $ignored = DB::table('member_duplicate_ignores')->get()
            ->map(fn ($r) => min($r->member_a_id, $r->member_b_id) . '-' . max($r->member_a_id, $r->member_b_id))
            ->flip();

        foreach ($byKey as $key => $ids) {
            $ids = array_values(array_unique($ids));
            if (count($ids) < 2) {
                continue;
            }
            $type = strstr($key, ':', true);
            for ($i = 1; $i < count($ids); $i++) {
                $pair = min($ids[0], $ids[$i]) . '-' . max($ids[0], $ids[$i]);
                if (isset($ignored[$pair])) {
                    continue;
                }
                $parent[$find($ids[$i])] = $find($ids[0]);
                $reasons[$ids[0]][$type] = true;
                $reasons[$ids[$i]][$type] = true;
            }
        }

        $byId = $members->keyBy('id');
        $groups = collect($parent)->keys()->groupBy(fn ($id) => $find($id))->filter(fn ($ids) => $ids->count() > 1);

        return $groups->map(function ($ids) use ($byId, $reasons, $churchIds) {
            $group = $ids->map(fn ($id) => $byId[$id]);
            if ($churchIds !== null && $group->whereIn('church_id', $churchIds)->isEmpty()) {
                return null;
            }
            $why = collect($ids)->flatMap(fn ($id) => array_keys($reasons[$id] ?? []))->unique()->values()->all();

            return ['members' => $this->withActivity($group), 'reasons' => $why];
        })->filter()->sortByDesc(fn ($g) => count($g['reasons']) * 100 + $g['members']->count())->values();
    }

    /** Adds attendance / registration / pledge counts used to choose which record to keep. */
    private function withActivity(Collection $members): Collection
    {
        $ids = $members->pluck('id');
        $att = ProgramAttendance::whereIn('member_id', $ids)->whereIn('attendance_status', ['present', 'late'])
            ->selectRaw('member_id, COUNT(*) as n')->groupBy('member_id')->pluck('n', 'member_id');
        $reg = ProgramRegistration::whereIn('member_id', $ids)->selectRaw('member_id, COUNT(*) as n')->groupBy('member_id')->pluck('n', 'member_id');
        $pledges = DB::table('pledges')->whereIn('member_id', $ids)->selectRaw('member_id, COUNT(*) as n')->groupBy('member_id')->pluck('n', 'member_id');

        return $members->map(function ($m) use ($att, $reg, $pledges) {
            $m->activity = ['attended' => (int) ($att[$m->id] ?? 0), 'registrations' => (int) ($reg[$m->id] ?? 0), 'pledges' => (int) ($pledges[$m->id] ?? 0)];

            return $m;
        })->sortByDesc(fn ($m) => ($m->user_id ? 1000 : 0) + ($m->member_type === 'member' ? 500 : 0) + array_sum($m->activity))->values();
    }

    public function ignore(array $memberIds, ?int $userId): void
    {
        $ids = array_values(array_unique(array_map('intval', $memberIds)));
        for ($i = 0; $i < count($ids); $i++) {
            for ($j = $i + 1; $j < count($ids); $j++) {
                DB::table('member_duplicate_ignores')->updateOrInsert(
                    ['member_a_id' => min($ids[$i], $ids[$j]), 'member_b_id' => max($ids[$i], $ids[$j])],
                    ['ignored_by' => $userId, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    /**
     * Moves everything from $duplicate onto $keep and deletes $duplicate.
     *
     * @return array<string, int> what was moved, for the message and the log
     */
    public function merge(Member $keep, Member $duplicate, ?int $userId): array
    {
        if ($keep->id === $duplicate->id) {
            throw ValidationException::withMessages(['merge' => 'Choose two different records.']);
        }

        return DB::transaction(function () use ($keep, $duplicate, $userId) {
            $keep = Member::with('phones')->lockForUpdate()->findOrFail($keep->id);
            $dup = Member::with(['phones', 'member_roles'])->lockForUpdate()->findOrFail($duplicate->id);
            $snapshot = $dup->toArray();
            $moved = [];

            // 1. Phones: the duplicate's numbers become extra numbers of the kept record.
            $dupPhones = $dup->allPhones();
            MemberPhone::where('member_id', $dup->id)->delete();
            $have = $keep->allPhones();
            $moved['phones'] = 0;
            foreach ($dupPhones as $phone) {
                if (Member::normalisedPhone($phone) && !in_array($phone, $have, true)) {
                    MemberPhone::create(['member_id' => $keep->id, 'phone' => $phone, 'label' => 'Merged', 'added_by' => $userId]);
                    $have[] = $phone;
                    $moved['phones']++;
                }
            }

            // 2. Details the kept record doesn't have yet.
            $this->fillMissing($keep, $dup);

            // 3. Designations, cells, departments (each once).
            $moved['roles'] = $this->movePivot('member_roles', 'designation_id', $dup->id, $keep->id);
            $moved['cells'] = $this->movePivot('cell_group_members', 'cell_group_id', $dup->id, $keep->id);
            $moved['departments'] = $this->movePivot('department_members', 'department_id', $dup->id, $keep->id);

            // 4. Registrations: one per program; payments follow the registration kept.
            $moved['registrations'] = 0;
            foreach (ProgramRegistration::where('member_id', $dup->id)->get() as $reg) {
                $mine = ProgramRegistration::where('member_id', $keep->id)->where('program_id', $reg->program_id)
                    ->where('registration_status', 'registered')->first();
                if ($mine && $reg->registration_status === 'registered') {
                    DB::table('program_payments')->where('registration_id', $reg->id)->update(['registration_id' => $mine->id]);
                    ProgramAttendance::where('registration_id', $reg->id)->update(['registration_id' => $mine->id]);
                    $reg->delete();
                    $mine->unsetRelation('payments');
                    $mine->recalculatePaymentStatus();
                } else {
                    $reg->update(['member_id' => $keep->id]);
                }
                $moved['registrations']++;
            }

            // 5. Attendance: one per service (and session); "present" beats "absent".
            $moved['attendance'] = 0;
            foreach (ProgramAttendance::where('member_id', $dup->id)->get() as $att) {
                $mine = ProgramAttendance::where('member_id', $keep->id)->where('occurrence_id', $att->occurrence_id)
                    ->where(fn ($q) => $att->session_id ? $q->where('session_id', $att->session_id) : $q->whereNull('session_id'))->first();
                if ($mine) {
                    if ($mine->attendance_status === 'absent' && in_array($att->attendance_status, ['present', 'late'], true)) {
                        $mine->delete();
                        $att->update(['member_id' => $keep->id]);
                    } else {
                        $att->delete();
                    }
                } else {
                    $att->update(['member_id' => $keep->id]);
                }
                $moved['attendance']++;
            }

            // 6. Everything else that points at the person.
            $moved['pledges'] = DB::table('pledges')->where('member_id', $dup->id)->update(['member_id' => $keep->id]);
            DB::table('head_of_church_units')->where('head_of_unit', $dup->id)->update(['head_of_unit' => $keep->id]);
            $moved['invitees'] = Member::where('invited_by_member_id', $dup->id)->whereKeyNot($keep->id)->update(['invited_by_member_id' => $keep->id]);
            DB::table('new_soul_assignments')->where('member_id', $dup->id)->update(['member_id' => $keep->id]);
            DB::table('new_soul_followups')->where('member_id', $dup->id)->update(['member_id' => $keep->id]);
            DB::table('member_duplicate_ignores')->where('member_a_id', $dup->id)->orWhere('member_b_id', $dup->id)->delete();
            if ((int) $keep->invited_by_member_id === (int) $dup->id) {
                $keep->invited_by_member_id = null;
            }

            // 7. Login: keep one account.
            $moved['login'] = $this->mergeLogins($keep, $dup);

            $keep->save();
            ProgramAuditLog::record('member.merged', $keep, $snapshot + ['phones_list' => $dupPhones], $moved + ['merged_member_id' => $dup->id]);
            $dup->delete();

            return $moved;
        });
    }

    private function fillMissing(Member $keep, Member $dup): void
    {
        foreach (['email', 'location', 'kingschat_username', 'title_id', 'gender', 'date_of_birth', 'invited_by', 'invited_by_member_id',
                  'foundation_clases_date', 'baptism_date', 'marriage_status', 'marriage_dates', 'first_visit_program_id'] as $field) {
            if (blank($keep->{$field}) && !blank($dup->{$field})) {
                $keep->{$field} = $dup->{$field};
            }
        }
        foreach (['foundation_clases', 'baptism_status'] as $field) {
            if ($dup->{$field} === 'yes' || blank($keep->{$field})) {
                $keep->{$field} = $dup->{$field} ?? $keep->{$field};
            }
        }
        if ($dup->first_visit_date && (!$keep->first_visit_date || $dup->first_visit_date->lt($keep->first_visit_date))) {
            $keep->first_visit_date = $dup->first_visit_date;
            $keep->first_visit_program_id = $dup->first_visit_program_id;
        }
        if ($dup->member_type === 'member' && $keep->member_type !== 'member') {
            $keep->member_type = 'member';
            $keep->follow_up_status = 'became_member';
        }
        if ($dup->notes) {
            $keep->notes = trim(($keep->notes ? $keep->notes . "\n" : '') . $dup->notes);
        }
    }

    /** Rows of a member pivot (unique per member + $column): move what the kept record lacks. */
    private function movePivot(string $table, string $column, int $from, int $to): int
    {
        $existing = DB::table($table)->where('member_id', $to)->pluck($column)->all();
        $moved = DB::table($table)->where('member_id', $from)->whereNotIn($column, $existing ?: [0])->update(['member_id' => $to]);
        DB::table($table)->where('member_id', $from)->delete();

        return $moved;
    }

    /** @return int 1 when the duplicate's login was moved or merged */
    private function mergeLogins(Member $keep, Member $dup): int
    {
        if (!$dup->user_id) {
            return 0;
        }

        if (!$keep->user_id) {
            $keep->user_id = $dup->user_id;
            $dup->user_id = null;
            $dup->saveQuietly();

            return 1;
        }

        // Both have logins: everything done by the duplicate's login is
        // re-pointed to the kept login, then the duplicate login is removed
        // (its number is now an extra number of the kept person).
        $from = (int) $dup->user_id;
        $to = (int) $keep->user_id;
        foreach (self::USER_COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    DB::table($table)->where($column, $from)->update([$column => $to]);
                }
            }
        }
        if (Schema::hasTable('personal_access_tokens')) {
            DB::table('personal_access_tokens')->where('tokenable_type', User::class)->where('tokenable_id', $from)->delete();
        }
        $dup->user_id = null;
        $dup->saveQuietly();
        User::whereKey($from)->delete();

        return 1;
    }
}
