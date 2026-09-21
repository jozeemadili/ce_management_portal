<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PledgeContribution
 *
 * @property int $id
 * @property int $pledge_id
 * @property float $amount
 * @property Carbon $payment_date
 * @property string|null $payment_reference
 * @property string|null $payment_method
 * @property int|null $recorded_by
 * @property string|null $notes
 */
class PledgeContribution extends Model
{
    protected $table = 'pledge_contributions';

    protected $casts = [
        'pledge_id' => 'int',
        'recorded_by' => 'int',
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected $fillable = [
        'pledge_id', 'amount', 'payment_date', 'payment_reference',
        'payment_method', 'recorded_by', 'notes',
    ];

    public function pledge()
    {
        return $this->belongsTo(Pledge::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
