<?php

namespace App\Services;

use App\Models\CellGroup;
use App\Models\Church;
use App\Models\Member;
use App\Models\MemberFoundationClass;
use App\Models\MemberDesignation;
use App\Models\MemberRole;
use App\Models\NewSoulAssignment;
use App\Models\ProgramAttendance;
use App\Models\ProgramAuditLog;
use App\Models\ProgramOccurrence;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * New invitees (new souls): assigning them to the church that will follow
 * them up (with history), following them up until they become members, and
 * closing a service's report once its invitees are assigned.
 */
class InviteeManager
{
    public function __construct(private ChurchServices $services)
    {
    }

    /** People who came for the first time to this service (whatever they are now). */
    public function inviteesOf(ProgramOccurrence $occurrence): Collection
    {
        $memberIds = ProgramAttendance::where('occurrence_id', $occurrence->id)
            ->whereIn('attendance_status', ['present', 'late'])
            ->pluck('member_id');

        return Member::with(['church', 'invitedByMember', 'assignments.fromChurch', 'assignments.toChurch', 'assignments.assignedBy'])
            ->whereIn('id', $memberIds)
            ->where(function ($q) use ($occurrence) {
                $q->where('member_type', 'new_soul')
                  ->orWhere(fn ($q2) => $q2->where('first_visit_program_id', $occurrence->program_id)
                      ->whereDate('first_visit_date', $occurrence->occurrence_date));
            })
            ->orderBy('first_name')
            ->get();
    }

    public function assign(Member $invitee, Church $to, ?ProgramOccurrence $occurrence, ?string $note, ?int $userId): NewSoulAssignment
    {
        return DB::transaction(function () use ($invitee, $to, $occurrence, $note, $userId) {
            $assignment = NewSoulAssignment::create([
                'member_id' => $invitee->id,
                'from_church_id' => $invitee->church_id,
                'to_church_id' => $to->id,
                'occurrence_id' => optional($occurrence)->id,
                'assigned_by' => $userId,
                'note' => trim((string) $note) ?: null,
            ]);

            $old = ['church_id' => $invitee->church_id];
            $invitee->update(['church_id' => $to->id]);
            ProgramAuditLog::record('new_soul.assigned', $invitee, $old, ['church_id' => $to->id, 'note' => $assignment->note]);

            return $assignment;
        });
    }

    /**
     * Follow-up is complete: the invitee becomes a regular church member
     * (normal member designation) and gets a login - phone (or email) + the
     * initial password - when their phone/email isn't used by another login.
     *
     * @return string what happened, for the flash message
     */
    /**
     * Saves a follow-up (portal and mobile app): contact details, baptism,
     * a dated note, then the progress (classes, cell, status).
     *
     * @param  array<string, mixed>  $fields  location, phone, email, baptism_status, baptism_date
     * @param  array<int, string|null>  $classes  done class number => date
     * @return string|null message when they became a member
     */
    public function updateFollowUp(Member $invitee, array $fields, array $classes, ?int $cellGroupId, string $status, ?string $note, ?int $userId): ?string
    {
        $old = $invitee->toArray() + ['foundation_classes' => $invitee->foundationClasses()->pluck('class_no')->all()];

        $fields = array_intersect_key($fields, array_flip(['location', 'phone', 'email', 'baptism_status', 'baptism_date']));
        $note = trim((string) $note);
        if ($note !== '') {
            $fields['notes'] = trim(($invitee->notes ? $invitee->notes . "\n" : '') . now()->format('d M Y') . ' - ' . $note);
        }

        $becameMember = DB::transaction(function () use ($invitee, $fields, $classes, $cellGroupId, $status, $userId) {
            $invitee->update($fields);

            return $this->recordProgress($invitee, $classes, $cellGroupId, $status, $userId);
        });

        ProgramAuditLog::record('new_soul.follow_up_updated', $invitee, $old,
            $invitee->fresh()->toArray() + ['foundation_classes' => $invitee->foundationClasses()->pluck('class_no')->all()]);

        return $becameMember;
    }

    /**
     * Follow-up progress of a new soul: the Foundation School classes done
     * (class number => date, missing = not done), and their cell group.
     *
     * The status follows the progress: in a cell -> "Connected to cell",
     * else any class done -> "Foundation classes" (a manual "Closed" is kept).
     * Finishing all MemberFoundationClass::TOTAL classes makes them a member.
     *
     * @param  array<int, string|null>  $classes
     * @return string|null message when they became a member
     */
    public function recordProgress(Member $invitee, array $classes, ?int $cellGroupId, string $status, ?int $userId): ?string
    {
        if ($cellGroupId) {
            $cell = CellGroup::findOrFail($cellGroupId);
            if ((int) $cell->church_id !== (int) $invitee->church_id) {
                throw ValidationException::withMessages(['cell_group_id' => 'Choose a cell group of ' . optional($invitee->church)->name . '.']);
            }
        } elseif ($status === 'connected_to_cell') {
            throw ValidationException::withMessages(['cell_group_id' => 'Choose the cell group they were connected to.']);
        }

        return DB::transaction(function () use ($invitee, $classes, $cellGroupId, $status, $userId) {
            // Foundation classes: keep ticked ones (with their date), remove unticked.
            $done = collect($classes)
                ->filter(fn ($date, $no) => $no >= 1 && $no <= MemberFoundationClass::TOTAL)
                ->map(fn ($date) => $date ?: now()->toDateString());
            MemberFoundationClass::where('member_id', $invitee->id)->whereNotIn('class_no', $done->keys()->all() ?: [0])->delete();
            foreach ($done as $no => $date) {
                MemberFoundationClass::updateOrCreate(
                    ['member_id' => $invitee->id, 'class_no' => $no],
                    ['completed_on' => $date, 'recorded_by' => $userId]
                );
            }

            // Cell group: one cell per person; keep their role if it is the same cell.
            $inCell = $invitee->cell_groups()->exists();
            if ($cellGroupId) {
                $current = $invitee->cell_groups()->pluck('cell_groups.id')->all();
                if ($current !== [$cellGroupId]) {
                    $invitee->cell_groups()->sync([$cellGroupId => ['role' => 'CELL_MEMBER']]);
                }
                $inCell = true;
            }

            if ($status !== 'closed') {
                if ($inCell) {
                    $status = 'connected_to_cell';
                } elseif ($done->isNotEmpty() && in_array($status, ['new', 'contacted', 'follow_up'], true)) {
                    $status = 'foundation_classes';
                }
            }

            $finished = $done->count() >= MemberFoundationClass::TOTAL;
            $invitee->update(array_filter([
                'follow_up_status' => $status,
                'foundation_clases' => $finished ? 'yes' : null,
                'foundation_clases_date' => $finished ? $done->max() : null,
            ]));

            if ($finished && $invitee->member_type !== 'member') {
                return 'Completed all ' . MemberFoundationClass::TOTAL . ' foundation classes. ' . $this->makeMember($invitee->fresh(), $userId);
            }

            return null;
        });
    }

    public function makeMember(Member $invitee, ?int $userId): string
    {
        if ($invitee->member_type === 'member') {
            throw ValidationException::withMessages(['member' => trim($invitee->first_name . ' ' . $invitee->last_name) . ' is already a member.']);
        }

        return DB::transaction(function () use ($invitee, $userId) {
            $old = $invitee->toArray();
            $invitee->update(['member_type' => 'member', 'follow_up_status' => 'became_member']);

            $designationId = MemberDesignation::whereRaw('LOWER(name) = ?', ['normal member'])->value('id');
            if ($designationId && !MemberRole::where('member_id', $invitee->id)->exists()) {
                MemberRole::create(['member_id' => $invitee->id, 'designation_id' => $designationId]);
            }

            $login = '';
            $mobile = AccountLogin::normaliseMobile($invitee->phone);
            $email = $invitee->email ? mb_strtolower($invitee->email) : null;
            if (!$invitee->user_id && $mobile && !User::where('mobile', $mobile)->exists()
                && (!$email || !User::whereRaw('LOWER(email) = ?', [$email])->exists())) {
                $user = AccountLogin::createMemberUser($invitee->first_name, $invitee->last_name ?: '-', $mobile, $email, $userId);
                $invitee->update(['user_id' => $user->id]);
                $login = ' They can log in with ' . $invitee->phone . ' and the initial password ' . AccountLogin::defaultPassword() . '.';
            } elseif (!$invitee->user_id) {
                $login = ' No login was created (no valid phone, or it is already used by another account).';
            }

            ProgramAuditLog::record('new_soul.became_member', $invitee, $old, $invitee->toArray());

            return trim($invitee->first_name . ' ' . $invitee->last_name) . ' is now a member of ' . optional(Church::find($invitee->church_id))->name . '.' . $login;
        });
    }

    /**
     * Freezes a service's final numbers. Only after the service has ended
     * (so absentees are marked). Returns the saved summary.
     */
    public function closeReport(ProgramOccurrence $occurrence, ?int $userId): array
    {
        if ($occurrence->isReportClosed()) {
            throw ValidationException::withMessages(['report' => 'This report is already closed.']);
        }
        if (!$occurrence->isClosed()) {
            throw ValidationException::withMessages(['report' => 'The service is still open for check-in. Close the report after it ends.']);
        }

        $invitees = $this->inviteesOf($occurrence);
        $summary = $this->services->counts($occurrence) + [
            'invitees' => $invitees->count(),
            'invitees_assigned' => $invitees->filter(fn ($m) => $m->assignments->isNotEmpty())->count(),
            'invitees_by_church' => $invitees->groupBy(fn ($m) => optional($m->church)->name ?? '—')->map->count()->all(),
        ];

        $occurrence->update(['report_closed_at' => now(), 'report_closed_by' => $userId, 'report_summary' => $summary]);
        ProgramAuditLog::record('report.closed', $occurrence, null, $summary);

        return $summary;
    }

    public function reopenReport(ProgramOccurrence $occurrence, ?int $userId): void
    {
        $occurrence->update(['report_closed_at' => null, 'report_closed_by' => null, 'report_summary' => null]);
        ProgramAuditLog::record('report.reopened', $occurrence, null, ['by' => $userId]);
    }
}
