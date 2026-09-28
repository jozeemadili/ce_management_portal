<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Member;
use App\Models\Program;
use App\Models\ProgramAttendance;
use App\Models\ProgramAuditLog;
use App\Models\ProgramOccurrence;
use App\Models\ServiceChurchTime;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Church services: recurring programs (Sunday / Wednesday / Friday service)
 * held in every church (scope "global") or in one church (scope "church"),
 * each church at its own time (service_church_times, else the program's
 * default time).
 *
 * For a church and date a service has a window:
 *   opens   = start - OPEN_BEFORE_MINUTES   check-in allowed from here
 *   late    = start + LATE_AFTER_MINUTES    checked in after this = late
 *   closes  = end                           then everyone not checked in
 *                                           is marked absent
 *
 * sync() (run every minute by the scheduler, and on the check-in page)
 * creates the occurrence when a window opens and closes it when it ends.
 */
class ChurchServices
{
    public const OPEN_BEFORE_MINUTES = 30;
    public const LATE_AFTER_MINUTES = 15;

    public const STANDARD_SERVICES = [
        'sunday' => ['Sunday Service', '09:00', '12:00'],
        'wednesday' => ['Wednesday Service', '17:30', '19:30'],
        'friday' => ['Friday Service', '17:30', '19:30'],
    ];

    /** Active recurring programs that are per-church services. */
    public function services(): Collection
    {
        return Program::where('classification', 'recurring')
            ->orderBy('start_time')
            ->get()
            ->filter(fn ($p) => $this->whyNotRunning($p) === null)
            ->values();
    }

    /**
     * Why a recurring program doesn't run as a church service (null = it
     * does): shown on the Services page so a mis-set service is easy to fix.
     */
    public function whyNotRunning(Program $program): ?string
    {
        if ($program->status !== 'active') {
            return 'Status is ' . ucfirst($program->status) . ' - it only opens for check-in when Active.';
        }
        if (!in_array($program->scope, ['global', 'church'], true)) {
            return 'Scope is ' . ucfirst($program->scope) . ' - a church service must be Global (every church) or one Church.';
        }
        if ($program->scope === 'church' && !$program->church_id) {
            return 'Scope is Church but no church is chosen.';
        }
        if (!in_array($program->recurrence_frequency, ['daily', 'weekly', 'custom'], true)) {
            return 'Frequency is ' . ($program->recurrence_frequency ? ucfirst($program->recurrence_frequency) : 'not set') . ' - choose Weekly and the day(s).';
        }
        if ($program->recurrence_frequency !== 'daily' && empty($program->recurrence_days)) {
            return 'No day chosen - pick the day(s) it happens (e.g. Sunday).';
        }
        if (!$program->start_time || !$program->end_time) {
            return 'No start/end time.';
        }

        return null;
    }

    /** Recurring programs that are NOT running as services, with the reason. */
    public function notRunning(): Collection
    {
        return Program::where('classification', 'recurring')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => (object) ['program' => $p, 'reason' => $this->whyNotRunning($p)])
            ->filter(fn ($row) => $row->reason !== null)
            ->values();
    }

    /** Standard service days (sunday/wednesday/friday) no recurring program covers yet. */
    public function missingStandardDays(): array
    {
        $covered = Program::where('classification', 'recurring')->get()
            ->flatMap(fn ($p) => array_map('strtolower', (array) $p->recurrence_days))
            ->unique()
            ->all();

        return array_values(array_diff(array_keys(self::STANDARD_SERVICES), $covered));
    }

    public function heldAt(Program $service, Church $church): bool
    {
        return $service->scope === 'global' || (int) $service->church_id === (int) $church->id;
    }

    public function isServiceDay(Program $service, Carbon $date): bool
    {
        if ($service->recurrence_frequency === 'daily') {
            return true;
        }

        $days = array_map('strtolower', (array) $service->recurrence_days);

        return in_array(strtolower($date->englishDayOfWeek), $days, true);
    }

    /** [start, end] "H:i:s" for the church - its own time or the default. */
    public function timesFor(Program $service, Church $church, ?Collection $overrides = null): array
    {
        $override = $overrides
            ? $overrides->first(fn ($t) => $t->program_id === $service->id && $t->church_id === $church->id)
            : ServiceChurchTime::where('program_id', $service->id)->where('church_id', $church->id)->first();

        return $override
            ? [$override->start_time, $override->end_time]
            : [$service->start_time, $service->end_time];
    }

    /**
     * @return array{opens: Carbon, starts: Carbon, late: Carbon, ends: Carbon}
     */
    public function window(Program $service, Church $church, Carbon $date, ?Collection $overrides = null): array
    {
        [$start, $end] = $this->timesFor($service, $church, $overrides);
        $starts = $date->copy()->setTimeFromTimeString($start);
        $ends = $date->copy()->setTimeFromTimeString($end);
        if ($ends->lessThanOrEqualTo($starts)) {
            $ends->addDay(); // e.g. a night vigil 22:00-02:00
        }

        return [
            'opens' => $starts->copy()->subMinutes(self::OPEN_BEFORE_MINUTES),
            'starts' => $starts,
            'late' => $starts->copy()->addMinutes(self::LATE_AFTER_MINUTES),
            'ends' => $ends,
        ];
    }

    /**
     * Today's services for a church with their window and state
     * (upcoming / open / closed) - for the check-in page.
     */
    public function todayFor(Church $church, ?Carbon $now = null): Collection
    {
        $now = $now ?? now();
        $overrides = ServiceChurchTime::where('church_id', $church->id)->get();

        return $this->services()
            ->filter(fn ($s) => $this->heldAt($s, $church) && $this->isServiceDay($s, $now))
            ->map(function ($service) use ($church, $now, $overrides) {
                $window = $this->window($service, $church, $now->copy()->startOfDay(), $overrides);
                $occurrence = ProgramOccurrence::where('program_id', $service->id)
                    ->where('church_id', $church->id)
                    ->whereDate('occurrence_date', $now->toDateString())
                    ->first();

                $state = $now->lt($window['opens']) ? 'upcoming'
                    : (($now->gt($window['ends']) || optional($occurrence)->isClosed()) ? 'closed' : 'open');

                return (object) compact('service', 'window', 'occurrence', 'state');
            })
            ->values();
    }

    public function occurrenceFor(Program $service, Church $church, Carbon $date, ?Collection $overrides = null): ProgramOccurrence
    {
        [$start, $end] = $this->timesFor($service, $church, $overrides);

        return ProgramOccurrence::firstOrCreate(
            ['program_id' => $service->id, 'church_id' => $church->id, 'occurrence_date' => $date->toDateString()],
            ['start_time' => $start, 'end_time' => $end, 'status' => 'scheduled']
        );
    }

    /**
     * Opens services whose window has started and closes those that have
     * ended (today's, and any earlier ones left open). Safe to run often.
     *
     * @return array{opened: int, closed: int}
     */
    public function sync(?Carbon $now = null): array
    {
        $now = $now ?? now();
        $opened = 0;
        $closed = 0;

        $services = $this->services();
        if ($services->isNotEmpty()) {
            $churches = Church::where('status', 'ACTIVE')->get();
            $overrides = ServiceChurchTime::all();
            $today = $now->copy()->startOfDay();

            foreach ($services as $service) {
                if (!$this->isServiceDay($service, $today)) {
                    continue;
                }

                foreach ($churches as $church) {
                    if (!$this->heldAt($service, $church)) {
                        continue;
                    }

                    $window = $this->window($service, $church, $today, $overrides);
                    if ($now->lt($window['opens'])) {
                        continue;
                    }

                    $occurrence = $this->occurrenceFor($service, $church, $today, $overrides);
                    if ($occurrence->wasRecentlyCreated) {
                        $opened++;
                    }

                    if ($now->gt($window['ends']) && !$occurrence->isClosed()) {
                        $this->close($occurrence);
                        $closed++;
                    }
                }
            }
        }

        // Earlier days still open (scheduler wasn't running then).
        ProgramOccurrence::whereNotNull('church_id')
            ->whereNull('closed_at')
            ->whereDate('occurrence_date', '<', $now->toDateString())
            ->where('status', '!=', 'cancelled')
            ->get()
            ->each(function ($occurrence) use (&$closed) {
                $this->close($occurrence);
                $closed++;
            });

        return compact('opened', 'closed');
    }

    /**
     * Ends attendance: every member of the church not checked in is marked
     * absent. Only church members (not new souls) are expected.
     */
    public function close(ProgramOccurrence $occurrence): int
    {
        return DB::transaction(function () use ($occurrence) {
            $occurrence = ProgramOccurrence::whereKey($occurrence->id)->lockForUpdate()->first();
            if (!$occurrence || $occurrence->isClosed()) {
                return 0;
            }

            $marked = ProgramAttendance::where('occurrence_id', $occurrence->id)->whereNotNull('member_id')->pluck('member_id');
            $now = now();
            $absent = 0;

            Member::where('church_id', $occurrence->church_id)
                ->where('member_type', 'member')
                ->whereNotIn('id', $marked)
                ->select('id')
                ->chunkById(500, function ($members) use ($occurrence, $now, &$absent) {
                    ProgramAttendance::insert($members->map(fn ($m) => [
                        'program_id' => $occurrence->program_id,
                        'occurrence_id' => $occurrence->id,
                        'member_id' => $m->id,
                        'attendance_status' => 'absent',
                        'check_in_method' => 'manual',
                        'notes' => 'Not checked in - marked absent automatically',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all());
                    $absent += $members->count();
                });

            $occurrence->update(['status' => 'completed', 'closed_at' => $now]);
            ProgramAuditLog::record('attendance.closed', $occurrence, null, ['absent' => $absent]);

            return $absent;
        });
    }

    /**
     * Checks a person in to an open service. Returns [attendance, alreadyCheckedIn].
     *
     * @return array{0: ProgramAttendance, 1: bool}
     */
    public function checkIn(ProgramOccurrence $occurrence, Member $member, string $method, ?int $userId, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $this->assertOpen($occurrence, $now);

        $existing = ProgramAttendance::where('occurrence_id', $occurrence->id)
            ->where('member_id', $member->id)
            ->whereNull('session_id')
            ->first();

        if ($existing && in_array($existing->attendance_status, ['present', 'late'], true)) {
            return [$existing, true];
        }

        $window = $this->window($occurrence->program, $occurrence->church, $occurrence->occurrence_date->copy());

        $attendance = ProgramAttendance::updateOrCreate(
            ['occurrence_id' => $occurrence->id, 'member_id' => $member->id, 'session_id' => null],
            [
                'program_id' => $occurrence->program_id,
                'attendance_status' => $now->gt($window['late']) ? 'late' : 'present',
                'check_in_method' => $method === 'qr' ? 'qr' : 'manual',
                'checked_in_at' => $now,
                'recorded_by' => $userId,
                'notes' => null,
            ]
        );

        return [$attendance, false];
    }

    /**
     * Records new souls who came to the service (with the member who
     * brought them, if any) and checks them in.
     *
     * @param  array<int, array{first_name: string, last_name?: ?string, phone?: ?string, gender?: ?string}>  $people
     * @return Collection<int, Member>
     */
    public function addNewSouls(ProgramOccurrence $occurrence, array $people, ?Member $broughtBy, ?int $userId): Collection
    {
        $this->assertOpen($occurrence, now());

        return DB::transaction(function () use ($occurrence, $people, $broughtBy, $userId) {
            return collect($people)->map(function ($person) use ($occurrence, $broughtBy, $userId) {
                $phone = trim((string) ($person['phone'] ?? '')) ?: null;
                $soul = Member::findOrCreateNewSoul([
                    'first_name' => trim($person['first_name']),
                    'last_name' => trim((string) ($person['last_name'] ?? '')) ?: null,
                    'phone' => $phone,
                    'gender' => $person['gender'] ?? null,
                    'invited_by' => $broughtBy ? trim($broughtBy->first_name . ' ' . $broughtBy->last_name) : null,
                    'invited_by_member_id' => optional($broughtBy)->id,
                    'first_visit_program_id' => $occurrence->program_id,
                    'first_visit_date' => $occurrence->occurrence_date->toDateString(),
                ], $occurrence->church_id, $userId);

                ProgramAttendance::updateOrCreate(
                    ['occurrence_id' => $occurrence->id, 'member_id' => $soul->id, 'session_id' => null],
                    [
                        'program_id' => $occurrence->program_id,
                        'attendance_status' => 'present',
                        'check_in_method' => 'manual',
                        'checked_in_at' => now(),
                        'recorded_by' => $userId,
                    ]
                );

                ProgramAuditLog::record($soul->wasRecentlyCreated ? 'new_soul.created' : 'new_soul.returned', $soul, null, [
                    'occurrence_id' => $occurrence->id,
                    'brought_by' => optional($broughtBy)->id,
                ]);

                return $soul;
            });
        });
    }

    /** Attendance numbers for one occurrence. */
    public function counts(ProgramOccurrence $occurrence): array
    {
        $rows = ProgramAttendance::where('program_attendances.occurrence_id', $occurrence->id)
            ->join('members', 'members.id', '=', 'program_attendances.member_id')
            ->selectRaw('members.member_type, program_attendances.attendance_status, COUNT(*) as total')
            ->groupBy('members.member_type', 'program_attendances.attendance_status')
            ->get();

        $count = fn ($type, $status) => (int) optional($rows->first(fn ($r) => $r->member_type === $type && $r->attendance_status === $status))->total;

        return [
            'present' => $count('member', 'present'),
            'late' => $count('member', 'late'),
            'absent' => $count('member', 'absent'),
            'attended' => $count('member', 'present') + $count('member', 'late'),
            'new_souls' => (int) $rows->where('member_type', 'new_soul')->sum('total'),
        ];
    }

    private function assertOpen(ProgramOccurrence $occurrence, Carbon $now): void
    {
        if ($occurrence->isClosed()) {
            throw ValidationException::withMessages(['service' => 'This service has ended - attendance is closed.']);
        }

        $window = $this->window($occurrence->program, $occurrence->church, $occurrence->occurrence_date->copy());
        if ($now->lt($window['opens'])) {
            throw ValidationException::withMessages(['service' => 'Check-in opens at ' . $window['opens']->format('H:i') . '.']);
        }
        if ($now->gt($window['ends'])) {
            throw ValidationException::withMessages(['service' => 'This service has ended - attendance is closed.']);
        }
    }
}
