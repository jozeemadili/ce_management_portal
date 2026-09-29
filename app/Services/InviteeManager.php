<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Member;
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
