<?php

namespace App\Services;

use App\Models\Program;
use App\Models\ProgramAuditLog;
use App\Models\ProgramPayment;
use App\Models\ProgramRegistration;
use App\Models\ProgramSmsTemplate;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends Programs SMS from the templates in program_sms_templates.
 *
 * registered() and paymentConfirmed() send after the HTTP response has gone
 * out, so a slow SMS gateway never slows down registering or confirming - and
 * a failed SMS never undoes a registration (results go to the sms log).
 * No active template of the type = nothing is sent.
 */
class ProgramSmsNotifier
{
    public function __construct(private SmsService $sms)
    {
    }

    public function registered(ProgramRegistration $registration): void
    {
        $id = $registration->id;

        dispatch(function () use ($id) {
            app(self::class)->sendRegistration($id);
        })->afterResponse();
    }

    public function paymentConfirmed(ProgramPayment $payment): void
    {
        $id = $payment->id;

        dispatch(function () use ($id) {
            app(self::class)->sendPayment($id);
        })->afterResponse();
    }

    /** @return array|null SmsService result, or null when nothing was sent */
    public function sendRegistration(int $registrationId): ?array
    {
        // Missing if the surrounding transaction rolled back (bulk upload).
        $registration = ProgramRegistration::with(['program', 'member', 'payments'])->find($registrationId);

        if (!$registration || $registration->registration_status !== 'registered') {
            return null;
        }

        return $this->sendTemplate(ProgramSmsTemplate::REGISTRATION, $registration);
    }

    public function sendPayment(int $paymentId): ?array
    {
        $payment = ProgramPayment::with(['registration.program', 'registration.member', 'registration.payments'])->find($paymentId);

        if (!$payment || $payment->status !== ProgramPayment::CONFIRMED || !$payment->registration) {
            return null;
        }

        return $this->sendTemplate(ProgramSmsTemplate::PAYMENT, $payment->registration, $payment);
    }

    /**
     * Reminder to everyone registered for $program who has a phone number.
     * People who get the same text (template without personal
     * placeholders) are sent in one batch; personalised texts one by one.
     *
     * @return array{recipients: int, sent: int, failed: int, no_phone: int, skipped_reason: ?string}
     */
    public function sendReminder(Program $program): array
    {
        $summary = ['recipients' => 0, 'sent' => 0, 'failed' => 0, 'no_phone' => 0, 'skipped_reason' => null];

        $template = ProgramSmsTemplate::resolve(ProgramSmsTemplate::REMINDER, $program);
        if (!$template) {
            $summary['skipped_reason'] = 'There is no active Reminder SMS template.';

            return $summary;
        }

        @set_time_limit(0);

        $byMessage = [];
        $program->registrations()
            ->where('registration_status', 'registered')
            ->with(['member', 'payments'])
            ->chunkById(200, function ($registrations) use ($template, $program, &$byMessage, &$summary) {
                foreach ($registrations as $registration) {
                    $registration->setRelation('program', $program);
                    $phone = SmsService::normalisePhone(optional($registration->member)->phone);

                    if (!$phone) {
                        $summary['no_phone']++;
                        continue;
                    }

                    $byMessage[$template->render($registration)][$phone] = $phone;
                }
            });

        foreach ($byMessage as $message => $phones) {
            $summary['recipients'] += count($phones);
            $result = $this->sms->send(array_values($phones), $message);
            $summary['sent'] += $result['sent'];
            $summary['failed'] += count($phones) - $result['sent'];
        }

        ProgramAuditLog::record('sms.reminder_sent', $program, null, $summary);

        return $summary;
    }

    private function sendTemplate(string $type, ProgramRegistration $registration, ?ProgramPayment $payment = null): ?array
    {
        try {
            $template = ProgramSmsTemplate::resolve($type, $registration->program);
            $phone = optional($registration->member)->phone;

            if (!$template || !SmsService::normalisePhone($phone)) {
                return null;
            }

            return $this->sms->send($phone, $template->render($registration, $payment));
        } catch (Throwable $e) {
            Log::channel('sms')->error("Program {$type} SMS failed", [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
