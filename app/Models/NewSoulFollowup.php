<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Follow-up for a new soul after a service: the SMS with a personal link
 * (token) and the testimony/feedback they send back through it.
 */
class NewSoulFollowup extends Model
{
    protected $fillable = ['member_id', 'occurrence_id', 'token', 'sms_sent_at', 'sms_status', 'feedback', 'submitted_at'];

    protected $casts = [
        'sms_sent_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function occurrence()
    {
        return $this->belongsTo(ProgramOccurrence::class, 'occurrence_id');
    }

    public static function newToken(): string
    {
        do {
            $token = Str::random(10);
        } while (static::where('token', $token)->exists());

        return $token;
    }

    public function link(): string
    {
        return route('feedback.show', $this->token);
    }
}
