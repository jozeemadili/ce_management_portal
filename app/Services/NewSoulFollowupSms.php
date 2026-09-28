<?php

namespace App\Services;

use App\Models\NewSoulFollowup;
use App\Models\ProgramAuditLog;
use App\Models\ProgramOccurrence;
use App\Models\ProgramSmsTemplate;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * After a church service: SMS each new soul of that service a thank-you
 * with a personal link (feedback/{token}) where they can share a testimony
 * or what blessed them. Uses the "New soul follow-up" SMS template.
 */
class NewSoulFollowupSms
{
    public function __construct(private SmsService $sms)
    {
    }

    /**
     * Creates the follow-up rows now (so nobody gets it twice) and sends
     * after the response. Returns how many will be sent.
     */
    public function queue(ProgramOccurrence $occurrence): int
    {
        $occurrence->loadMissing('program', 'church');

        if (!ProgramSmsTemplate::resolve(ProgramSmsTemplate::NEW_SOUL_FOLLOWUP, $occurrence->program)) {
            return 0;
        }

        $alreadySent = NewSoulFollowup::where('occurrence_id', $occurrence->id)->pluck('member_id');

        $souls = $occurrence->attendances()
            ->whereIn('attendance_status', ['present', 'late'])
            ->whereHas('member', fn ($q) => $q->where('member_type', 'new_soul'))
            ->whereNotIn('member_id', $alreadySent)
            ->with('member')
            ->get()
            ->pluck('member')
            ->filter(fn ($m) => SmsService::normalisePhone($m->phone));

        $ids = $souls->map(fn ($member) => NewSoulFollowup::create([
            'member_id' => $member->id,
            'occurrence_id' => $occurrence->id,
            'token' => NewSoulFollowup::newToken(),
            'sms_status' => 'queued',
        ])->id)->all();

        if ($ids) {
            $occurrenceId = $occurrence->id;
            dispatch(function () use ($ids, $occurrenceId) {
                app(self::class)->send($ids, $occurrenceId);
            })->afterResponse();
        }

        return count($ids);
    }

    public function send(array $followupIds, int $occurrenceId): array
    {
        @set_time_limit(0);
        $sent = 0;
        $failed = 0;

        $occurrence = ProgramOccurrence::with(['program', 'church'])->find($occurrenceId);
        $template = $occurrence ? ProgramSmsTemplate::resolve(ProgramSmsTemplate::NEW_SOUL_FOLLOWUP, $occurrence->program) : null;

        foreach (NewSoulFollowup::with('member')->whereIn('id', $followupIds)->get() as $followup) {
            try {
                if (!$template) {
                    throw new \RuntimeException('No active New soul follow-up template.');
                }

                $result = $this->sms->send($followup->member->phone, $template->renderForNewSoul($followup, $occurrence));
                $followup->update([
                    'sms_status' => $result['success'] ? 'sent' : 'failed',
                    'sms_sent_at' => $result['success'] ? now() : null,
                ]);
                $result['success'] ? $sent++ : $failed++;
            } catch (Throwable $e) {
                $followup->update(['sms_status' => 'failed']);
                $failed++;
                Log::channel('sms')->error('New soul follow-up SMS failed', ['followup_id' => $followup->id, 'error' => $e->getMessage()]);
            }
        }

        if ($occurrence) {
            ProgramAuditLog::record('sms.new_soul_followup_sent', $occurrence, null, compact('sent', 'failed'));
        }

        return compact('sent', 'failed');
    }
}
