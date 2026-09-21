<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PledgeAuditLog
 *
 * Lightweight, append-only audit trail for the Pledges module. This app has
 * no activity-log package installed, so this table is intentionally minimal
 * rather than a general-purpose auditing system.
 *
 * @property int $id
 * @property int|null $actor_id
 * @property string $action
 * @property string $subject_type
 * @property int $subject_id
 * @property array|null $old_values
 * @property array|null $new_values
 */
class PledgeAuditLog extends Model
{
    protected $table = 'pledge_audit_logs';
    const UPDATED_AT = null;

    protected $casts = [
        'actor_id' => 'int',
        'subject_id' => 'int',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    protected $fillable = [
        'actor_id', 'action', 'subject_type', 'subject_id', 'old_values', 'new_values',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function record(string $action, Model $subject, ?array $old = null, ?array $new = null)
    {
        return static::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->getKey(),
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }
}
