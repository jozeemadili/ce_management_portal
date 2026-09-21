<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PledgePaymentMethod
 *
 * Admin-editable lookup list for the "Payment Method" dropdown on the
 * Record Contribution form - kept in the database rather than hardcoded
 * so it can be adjusted without a code change.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 */
class PledgePaymentMethod extends Model
{
    protected $table = 'pledge_payment_methods';

    protected $casts = [
        'is_active' => 'bool',
    ];

    protected $fillable = [
        'name', 'is_active',
    ];
}
