<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Member;
use App\Models\Program;
use App\Models\ProgramAttendance;
use App\Models\ProgramAuditLog;
use App\Models\ProgramRegistration;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Self check-in from a printed program QR poster (/checkin/{token}): the
 * person types their phone or email; a known member is checked in with the
 * same rules staff use, an unknown person gives their name and gender and
 * is recorded as a new soul, then checked in.
 *
 *  - church service (ChurchServices): at the poster's church, while the
 *    service is open; present / late as usual
 *  - special program: registration rules (ProgramCheckIn) - a free program
 *    registers people automatically, a paid one needs a paid registration
 *  - any other recurring program: once per day, on its days
 */
class SelfCheckIn
{
    public function __construct(
        private ChurchServices $services,
        private ProgramCheckIn $programCheckIn,
    ) {
    }

    public function isChurchService(Program $program): bool
    {
        return $program->classification === 'recurring' && $this->services->whyNotRunning($program) === null;
    }

    /**
     * Is check-in open right now? Returns [open, message, occurrence|null].
     *
     * @return array{open: bool, message: string, occurrence: ?\App\Models\ProgramOccurrence}
     */
    public function status(Program $program, ?Church $church): array
    {
        $closed = fn (string $message) => ['open' => false, 'message' => $message, 'occurrence' => null];

        if ($program->status !== 'active') {
            return $closed($program->status === 'draft' ? 'This program is not open yet.' : 'This program is not taking check-ins.');
        }

        if ($this->isChurchService($program)) {
            if (!$church || !$this->services->heldAt($program, $church)) {
                return $closed('This check-in code is not for a church. Please ask an usher.');
            }

            $this->services->sync();
            $today = $this->services->todayFor($church)->first(fn ($s) => $s->service->id === $program->id);

            if (!$today) {
                return $closed("{$program->name} is not held today.");
            }
            if ($today->state === 'upcoming') {
                return $closed('Check-in opens at ' . $today->window['opens']->format('H:i') . '.');
            }
            if ($today->state === 'closed') {
                return $closed("{$program->name} has ended for today. See you next time!");
            }

            $occurrence = $today->occurrence ?? $this->services->occurrenceFor($program, $church, now()->startOfDay());
            $occurrence->setRelation('program', $program)->setRelation('church', $church);

            return ['open' => true, 'message' => '', 'occurrence' => $occurrence];
        }

        if ($program->classification === 'special') {
            if ($program->start_date) {
                $start = $program->start_date->copy()->startOfDay();
                $end = ($program->end_date ?: $program->start_date)->copy()->endOfDay();
                if (now()->lt($start)) {
                    return $closed('Check-in opens on ' . $start->format('d M Y') . '.');
                }
                if (now()->gt($end)) {
                    return $closed('This program has ended.');
                }
            }

            return ['open' => true, 'message' => '', 'occurrence' => null];
        }

        // Other recurring programs (department / cell meetings, ...).
        if (!$this->services->isServiceDay($program, now())) {
            return $closed("{$program->name} is not held today.");
        }

        return ['open' => true, 'message' => '', 'occurrence' => $program->occurrenceForDate(now()->toDateString())];
    }

    /**
     * What to show before the program opens: when, where, which church and
     * who to call. Keys with no value are left out.
     *
     * @return array<string, string>
     */
    public function details(Program $program, ?Church $church): array
    {
        $church = $church ?? $program->church;
        $time = fn ($start, $end) => $start ? substr($start, 0, 5) . ($end ? '–' . substr($end, 0, 5) : '') : '';

        if ($program->classification === 'special') {
            $dates = $program->start_date
                ? $program->start_date->format('D d M Y')
                    . ($program->end_date && !$program->end_date->isSameDay($program->start_date) ? ' – ' . $program->end_date->format('D d M Y') : '')
                : '';
            $when = trim($dates . ($program->start_time ? ' · ' . $time($program->start_time, $program->end_time) : ''), ' ·');
        } else {
            [$start, $end] = $church && $this->isChurchService($program)
                ? $this->services->timesFor($program, $church)
                : [$program->start_time, $program->end_time];
            $days = $program->recurrence_frequency === 'daily' || $program->is_training
                ? 'Every day'
                : 'Every ' . collect((array) $program->recurrence_days)->map(fn ($d) => ucfirst($d))->implode(', ');
            $when = $days . ($start ? ' · ' . $time($start, $end) : '');
        }

        return array_filter([
            'When' => $when,
            'Church' => $church ? ucwords(mb_strtolower($church->name)) : null,
            'Church address' => optional($church)->physical_location,
            'Venue' => $program->location,
            'Contact phone' => $program->contact_phone,
        ]);
    }

    /** A person by phone (any common format) or email; church members first. */
    public function findPerson(string $identifier, Program $program): ?Member
    {
        $identifier = trim($identifier);
        $query = Member::query()
            ->when(!$program->is_training, fn ($q) => $q->where('is_training', false))
            ->orderByRaw("CASE WHEN member_type = 'member' THEN 0 ELSE 1 END")
            ->orderBy('id');

        if (str_contains($identifier, '@')) {
            $email = mb_strtolower($identifier);
            $userIds = User::whereRaw('LOWER(email) = ?', [$email])->pluck('id');

            return $query->where(fn ($q) => $q->whereRaw('LOWER(email) = ?', [$email])->orWhereIn('user_id', $userIds))->first();
        }

        $mobile = AccountLogin::normaliseMobile($identifier);
        if (!$mobile) {
            return null;
        }

        $userIds = User::where('mobile', $mobile)->pluck('id');

        return $query->where(function ($q) use ($mobile, $userIds) {
            $q->whereIn('phone', ['0' . $mobile, '255' . $mobile, '+255' . $mobile, (string) $mobile])
              ->orWhereIn('user_id', $userIds);
        })->first();
    }

    public static function isValidIdentifier(string $identifier): bool
    {
        $identifier = trim($identifier);

        return str_contains($identifier, '@')
            ? (bool) filter_var($identifier, FILTER_VALIDATE_EMAIL)
            : AccountLogin::normaliseMobile($identifier) !== null;
    }

    /**
     * Checks a known person in.
     *
     * @return array{ok: bool, message: string, time: ?string, already: bool}
     */
    public function checkInMember(Program $program, ?Church $church, Member $member): array
    {
        $status = $this->status($program, $church);
        if (!$status['open']) {
            return $this->result(false, $status['message']);
        }

        if ($this->isChurchService($program)) {
            try {
                [$attendance, $already] = $this->services->checkIn($status['occurrence'], $member, 'qr', null);
            } catch (ValidationException $e) {
                return $this->result(false, collect($e->errors())->flatten()->first());
            }

            return $this->result(true, '', optional($attendance->checked_in_at)->format('H:i'), $already);
        }

        if ($program->classification === 'special') {
            return $this->checkInToSpecial($program, $member);
        }

        return $this->markPresent($status['occurrence'], $member);
    }

    /**
     * Records an unknown person as a new soul (at the poster's church, else
     * the program's church, else the top church) and checks them in.
     *
     * @return array{ok: bool, message: string, time: ?string, already: bool, member: ?Member}
     */
    public function registerNewSoul(Program $program, ?Church $church, array $data): array
    {
        $status = $this->status($program, $church);
        if (!$status['open']) {
            return $this->result(false, $status['message']) + ['member' => null];
        }

        $identifier = trim($data['identifier']);
        $person = [
            'first_name' => trim($data['first_name']),
            'last_name' => trim((string) ($data['last_name'] ?? '')) ?: null,
            'gender' => $data['gender'] ?? null,
            'phone' => str_contains($identifier, '@') ? null : '0' . AccountLogin::normaliseMobile($identifier),
        ];

        if ($this->isChurchService($program)) {
            $soul = $this->services->addNewSouls($status['occurrence'], [$person], null, null)->first();
            if (str_contains($identifier, '@')) {
                $soul->update(['email' => mb_strtolower($identifier)]);
            }

            return $this->result(true, '', now()->format('H:i')) + ['member' => $soul];
        }

        $churchId = optional($church)->id ?? $program->church_id ?? Church::whereNull('parent_church_id')->value('id');
        $soul = Member::findOrCreateNewSoul($person + [
            'email' => str_contains($identifier, '@') ? mb_strtolower($identifier) : null,
            'first_visit_program_id' => $program->id,
            'first_visit_date' => now()->toDateString(),
            'is_training' => (bool) $program->is_training,
        ], $churchId, null);

        ProgramAuditLog::record($soul->wasRecentlyCreated ? 'new_soul.created' : 'new_soul.returned', $soul, null, ['source' => 'self_checkin', 'program_id' => $program->id]);

        $result = $program->classification === 'special'
            ? $this->checkInToSpecial($program, $soul)
            : $this->markPresent($status['occurrence'], $soul);

        return $result + ['member' => $soul];
    }

    private function checkInToSpecial(Program $program, Member $member): array
    {
        $registration = ProgramRegistration::where('program_id', $program->id)
            ->where('member_id', $member->id)
            ->where('registration_status', 'registered')
            ->first();

        if (!$registration) {
            // Free programs: walk-ins are registered on the spot.
            $registration = ProgramRegistration::createFor($member, $program, null);
            ProgramAuditLog::record('registration.created', $registration, null, $registration->toArray() + ['source' => 'self_checkin']);
            app(ProgramSmsNotifier::class)->registered($registration);
        }

        $registration->load('program');
        $result = $this->programCheckIn->checkIn($registration, null);

        if (!$result['ok']) {
            $already = str_starts_with($result['message'], 'This registration has already been checked in')
                || str_starts_with($result['message'], 'Already checked in');

            return $this->result($already, $already ? '' : $result['message'] . ' Please see the registration desk.', null, $already);
        }

        return $this->result(true, '', optional($result['attendance']->checked_in_at)->format('H:i'));
    }

    private function markPresent($occurrence, Member $member): array
    {
        $existing = ProgramAttendance::where('occurrence_id', $occurrence->id)
            ->where('member_id', $member->id)
            ->whereIn('attendance_status', ['present', 'late'])
            ->first();

        if ($existing) {
            return $this->result(true, '', optional($existing->checked_in_at)->format('H:i'), true);
        }

        $attendance = ProgramAttendance::updateOrCreate(
            ['occurrence_id' => $occurrence->id, 'member_id' => $member->id, 'session_id' => null],
            [
                'program_id' => $occurrence->program_id,
                'attendance_status' => 'present',
                'check_in_method' => 'qr',
                'checked_in_at' => now(),
                'recorded_by' => null,
                'notes' => 'Self check-in',
            ]
        );

        return $this->result(true, '', $attendance->checked_in_at->format('H:i'));
    }

    private function result(bool $ok, string $message, ?string $time = null, bool $already = false): array
    {
        return compact('ok', 'message', 'time', 'already');
    }
}
