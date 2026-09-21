<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PledgeCampaign
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $banner_path
 * @property float $target_amount
 * @property string $currency
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string $status
 * @property string $scope
 * @property int|null $church_id
 * @property bool $allow_anonymous
 * @property bool $live_enabled
 * @property bool $live_show_amount
 * @property bool $live_show_pledgers
 * @property bool $live_show_latest
 * @property bool $live_show_graph
 * @property bool $live_show_target
 * @property bool $live_mask_names
 * @property string|null $notes
 * @property int|null $created_by
 */
class PledgeCampaign extends Model
{
    protected $table = 'pledge_campaigns';

    protected $casts = [
        'church_id' => 'int',
        'created_by' => 'int',
        'target_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'allow_anonymous' => 'bool',
        'live_enabled' => 'bool',
        'live_show_amount' => 'bool',
        'live_show_pledgers' => 'bool',
        'live_show_latest' => 'bool',
        'live_show_graph' => 'bool',
        'live_show_target' => 'bool',
        'live_mask_names' => 'bool',
    ];

    protected $fillable = [
        'name', 'description', 'banner_path', 'target_amount', 'currency',
        'start_date', 'end_date', 'status', 'scope', 'church_id',
        'allow_anonymous', 'live_enabled',
        'live_show_amount', 'live_show_pledgers', 'live_show_latest',
        'live_show_graph', 'live_show_target', 'live_mask_names',
        'notes', 'created_by',
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pledges()
    {
        return $this->hasMany(Pledge::class, 'campaign_id');
    }

    public function totalPledged()
    {
        return (float) $this->pledges()->where('status', '!=', 'cancelled')->sum('amount');
    }

    public function totalFulfilled()
    {
        return (float) PledgeContribution::whereIn('pledge_id', $this->pledges()->where('status', '!=', 'cancelled')->pluck('id'))->sum('amount');
    }

    public function pledgersCount()
    {
        return $this->pledges()->where('status', '!=', 'cancelled')->distinct('member_id')->count('member_id');
    }

    public function progressPercent()
    {
        if ((float) $this->target_amount <= 0) {
            return 0;
        }

        return min(100, round(($this->totalPledged() / (float) $this->target_amount) * 100, 2));
    }

    /**
     * Visible to a given member: global campaigns are visible to everyone,
     * church-specific campaigns only to members of that exact church.
     */
    public function scopeVisibleToMember($query, Member $member)
    {
        return $query->where(function ($q) use ($member) {
            $q->where('scope', 'global')
              ->orWhere(function ($q2) use ($member) {
                  $q2->where('scope', 'church')->where('church_id', $member->church_id);
              });
        });
    }
}
