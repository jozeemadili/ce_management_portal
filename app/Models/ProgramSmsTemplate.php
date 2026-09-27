<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An SMS template for Programs. Placeholders like {code} are filled in per
 * registration by render(). resolve() picks the program's own active
 * template of a type, falling back to the active "All programs" one.
 *
 * @property int $id
 * @property int|null $program_id
 * @property string $type
 * @property string $body
 * @property bool $is_active
 */
class ProgramSmsTemplate extends Model
{
    public const REGISTRATION = 'registration';
    public const PAYMENT = 'payment';
    public const REMINDER = 'reminder';

    public const TYPES = [
        self::REGISTRATION => 'Registration successful',
        self::PAYMENT => 'Payment received',
        self::REMINDER => 'Reminder',
    ];

    /** When each type is sent - shown on the templates page. */
    public const TYPE_HELP = [
        self::REGISTRATION => 'Sent automatically when someone is registered (portal, app or Excel upload).',
        self::PAYMENT => 'Sent automatically when a payment is confirmed or recorded by staff.',
        self::REMINDER => 'Sent when staff click "Send Reminder SMS" on the program page.',
    ];

    /** placeholder => [description, example] */
    public const PLACEHOLDERS = [
        '{first_name}' => ['First name', 'Grace'],
        '{full_name}' => ['Full name', 'Grace Madili'],
        '{program}' => ['Program name', 'Church Consolidation Conference'],
        '{code}' => ['Registration code', 'REG-000026'],
        '{date}' => ['Program start date', '01 Oct 2026'],
        '{time}' => ['Program start time', '18:00'],
        '{venue}' => ['Program location', 'Loveworld Arena'],
        '{amount_due}' => ['Amount to pay', 'TZS 50,000'],
        '{paid}' => ['Total paid so far', 'TZS 20,000'],
        '{balance}' => ['Balance left', 'TZS 30,000'],
        '{payment_amount}' => ['This payment (payment SMS)', 'TZS 20,000'],
    ];

    protected $fillable = ['program_id', 'type', 'body', 'is_active', 'created_by', 'updated_by'];

    protected $casts = ['is_active' => 'bool'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public static function resolve(string $type, Program $program): ?self
    {
        return static::where('type', $type)
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('program_id', $program->id)->orWhereNull('program_id'))
            ->orderByRaw('CASE WHEN program_id IS NULL THEN 1 ELSE 0 END')
            ->first();
    }

    public function render(ProgramRegistration $registration, ?ProgramPayment $payment = null): string
    {
        $program = $registration->program;
        $member = $registration->member;
        $money = fn ($amount) => $program->currency . ' ' . number_format((float) $amount);

        return self::fillPlaceholders($this->body, [
            '{first_name}' => ucfirst(strtolower((string) optional($member)->first_name)),
            '{full_name}' => ucwords(strtolower(trim(optional($member)->first_name . ' ' . optional($member)->last_name))),
            '{program}' => $program->name,
            '{code}' => $registration->registration_reference,
            '{date}' => optional($program->start_date)->format('d M Y') ?? '',
            '{time}' => $program->start_time ? substr($program->start_time, 0, 5) : '',
            '{venue}' => (string) $program->location,
            '{amount_due}' => $money($registration->amount_due),
            '{paid}' => $money($registration->totalPaid()),
            '{balance}' => $money($registration->balance()),
            '{payment_amount}' => $payment ? $money($payment->amount) : '',
        ]);
    }

    /** The template with example values - for previews. */
    public static function example(string $body): string
    {
        return self::fillPlaceholders($body, array_map(fn ($p) => $p[1], self::PLACEHOLDERS));
    }

    private static function fillPlaceholders(string $body, array $values): string
    {
        return trim(preg_replace('/[ \t]{2,}/', ' ', strtr($body, $values)));
    }
}
