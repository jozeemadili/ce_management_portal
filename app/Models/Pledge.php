<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pledge
 *
 * @property int $id
 * @property string|null $pledge_reference
 * @property int $campaign_id
 * @property int $member_id
 * @property float $amount
 * @property string $frequency
 * @property string $status
 * @property bool $anonymous_display
 * @property string $source
 * @property int|null $recorded_by
 * @property string|null $notes
 * @property Carbon|null $pledged_at
 */
class Pledge extends Model
{
    protected $table = 'pledges';

    protected $casts = [
        'campaign_id' => 'int',
        'member_id' => 'int',
        'recorded_by' => 'int',
        'amount' => 'decimal:2',
        'anonymous_display' => 'bool',
        'pledged_at' => 'datetime',
    ];

    protected $fillable = [
        'pledge_reference', 'campaign_id', 'member_id', 'amount', 'frequency',
        'status', 'anonymous_display', 'source', 'recorded_by', 'notes', 'pledged_at',
    ];

    /**
     * Create a pledge and stamp its reference (PLG-000123) in one step -
     * shared by both the member self-service flow and the staff
     * "record on behalf" flow, which differ only in who the member/source is.
     */
    public static function createFor(Member $member, PledgeCampaign $campaign, array $attributes, string $source, ?int $recordedBy = null): self
    {
        $pledge = static::create(array_merge($attributes, [
            'campaign_id' => $campaign->id,
            'member_id' => $member->id,
            'status' => 'pledged',
            'source' => $source,
            'recorded_by' => $recordedBy,
            'pledged_at' => now(),
        ]));

        $pledge->pledge_reference = 'PLG-' . str_pad($pledge->id, 6, '0', STR_PAD_LEFT);
        $pledge->save();

        return $pledge;
    }

    public function campaign()
    {
        return $this->belongsTo(PledgeCampaign::class, 'campaign_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function contributions()
    {
        return $this->hasMany(PledgeContribution::class);
    }

    public function totalFulfilled()
    {
        return (float) $this->contributions()->sum('amount');
    }

    public function outstanding()
    {
        return max(0, (float) $this->amount - $this->totalFulfilled());
    }

    /**
     * Recompute status from the sum of contributions. Cancelled pledges are
     * left untouched - cancellation is an explicit action, not a side effect
     * of contribution totals.
     */
    public function recalculateStatus()
    {
        if ($this->status === 'cancelled') {
            return;
        }

        $fulfilled = $this->totalFulfilled();

        if ($fulfilled <= 0) {
            $this->status = 'pledged';
        } elseif ($fulfilled < (float) $this->amount) {
            $this->status = 'partially_fulfilled';
        } else {
            $this->status = 'fulfilled';
        }

        $this->save();
    }

    /**
     * Name for public/live display. No member name or initial is ever shown
     * here - every pledge reads as "Member from {church}" (church names are
     * stored upper-case in the church module, so it's lower-cased here so it
     * doesn't read like it's shouting on a presentation screen).
     */
    public function displayName()
    {
        $church = optional(optional($this->member)->church)->name;

        return $church ? 'Member from ' . mb_strtolower($church) : 'Anonymous';
    }
}
