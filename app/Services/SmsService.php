<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends SMS through Beem Africa (config/services.php "beem").
 *
 *   app(SmsService::class)->send('0712345678', 'Hello');
 *   app(SmsService::class)->send(['0712345678', '255754000000'], 'Hello all');
 *   app(SmsService::class)->balance();
 *
 * Numbers may be written 0712345678, 712345678, 255712345678 or
 * +255 712 345 678; they are sent as 255XXXXXXXXX. Invalid numbers are
 * skipped and listed in the result. send() never throws - check
 * $result['success']. With SMS_PRETEND=true nothing is sent; the message is
 * only written to storage/logs/sms-YYYY-MM-DD.log.
 */
class SmsService
{
    /** Recipients per API request. */
    private const CHUNK = 100;

    /**
     * @param  string|array<int, string>  $phones
     * @return array{success: bool, sent: int, invalid: array<int, string>, message: string, request_ids: array<int, mixed>}
     */
    public function send($phones, string $message): array
    {
        $message = trim($message);
        $invalid = [];
        $recipients = [];

        foreach ((array) $phones as $phone) {
            $normalised = self::normalisePhone($phone);
            if ($normalised === null) {
                $invalid[] = (string) $phone;
            } else {
                $recipients[$normalised] = $normalised; // de-duplicate
            }
        }
        $recipients = array_values($recipients);

        if ($message === '') {
            return $this->result(false, 0, $invalid, 'The message is empty.');
        }
        if (!$recipients) {
            return $this->result(false, 0, $invalid, 'No valid phone number to send to.');
        }

        $config = config('services.beem');

        if ($config['pretend']) {
            $this->log('info', 'PRETEND (not sent)', ['to' => $recipients, 'message' => $message]);

            return $this->result(true, count($recipients), $invalid, 'Pretend mode: logged, not sent.');
        }

        if (empty($config['api_key']) || empty($config['secret_key'])) {
            return $this->result(false, 0, $invalid, 'SMS is not configured: set SMS_GW_API_KEY and SMS_GW_API_SECRET in .env.');
        }
        if (empty($config['sender_id'])) {
            return $this->result(false, 0, $invalid, 'SMS is not configured: set SMS_GW_SENDER_ID in .env.');
        }

        $sent = 0;
        $requestIds = [];
        $errors = [];

        foreach (array_chunk($recipients, self::CHUNK) as $chunk) {
            $payload = [
                'source_addr' => $config['sender_id'],
                'encoding' => 0,
                'message' => $message,
                'recipients' => array_map(
                    fn ($phone, $i) => ['recipient_id' => $i + 1, 'dest_addr' => $phone],
                    $chunk,
                    array_keys($chunk)
                ),
            ];

            try {
                $response = $this->client($config)->post('/v1/send', $payload);
                $body = $response->json() ?? [];

                if ($response->successful() && ($body['successful'] ?? false)) {
                    $sent += (int) ($body['valid'] ?? count($chunk));
                    $requestIds[] = $body['request_id'] ?? null;
                    $this->log('info', 'Sent', ['to' => $chunk, 'request_id' => $body['request_id'] ?? null, 'valid' => $body['valid'] ?? null, 'invalid' => $body['invalid'] ?? null]);
                } else {
                    $error = $body['message'] ?? $body['data']['message'] ?? ('HTTP ' . $response->status());
                    $errors[] = $error;
                    $this->log('warning', 'Rejected by gateway', ['to' => $chunk, 'status' => $response->status(), 'code' => $body['code'] ?? $body['data']['code'] ?? null, 'error' => $error]);
                }
            } catch (Throwable $e) {
                $errors[] = $e instanceof ConnectionException ? 'Could not reach the SMS gateway.' : $e->getMessage();
                $this->log('error', 'Send failed', ['to' => $chunk, 'error' => $e->getMessage()]);
            }
        }

        $message = $errors
            ? 'Failed: ' . implode('; ', array_unique($errors))
            : "Submitted to {$sent} recipient(s).";

        return $this->result($sent > 0 && !$errors, $sent, $invalid, $message, array_values(array_filter($requestIds)));
    }

    /**
     * Remaining SMS credit on the Beem account, or null if it can't be read.
     */
    public function balance(): ?float
    {
        $config = config('services.beem');

        if (empty($config['api_key']) || empty($config['secret_key'])) {
            return null;
        }

        try {
            $response = $this->client($config)->get('/public/v1/vendors/balance');
            $balance = $response->json('data.credit_balance');

            return $response->successful() && is_numeric($balance) ? (float) $balance : null;
        } catch (Throwable $e) {
            $this->log('error', 'Balance check failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Tanzanian mobile number -> 255XXXXXXXXX, or null if not valid.
     */
    public static function normalisePhone($value): ?string
    {
        $mobile = AccountLogin::normaliseMobile($value);

        return $mobile ? '255' . $mobile : null;
    }

    private function client(array $config)
    {
        return Http::baseUrl(rtrim($config['base_url'], '/'))
            ->withBasicAuth($config['api_key'], $config['secret_key'])
            ->acceptJson()
            ->asJson()
            ->timeout($config['timeout'] ?? 20);
    }

    private function result(bool $success, int $sent, array $invalid, string $message, array $requestIds = []): array
    {
        return [
            'success' => $success,
            'sent' => $sent,
            'invalid' => $invalid,
            'message' => $message,
            'request_ids' => $requestIds,
        ];
    }

    private function log(string $level, string $event, array $context): void
    {
        try {
            Log::channel('sms')->{$level}($event, $context);
        } catch (Throwable $e) {
            // Logging must never break sending.
        }
    }
}
