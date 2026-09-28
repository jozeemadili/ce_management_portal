<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Program
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $banner_path
 * @property string $category
 * @property string $classification
 * @property string $scope
 * @property int|null $church_id
 * @property int|null $department_id
 * @property int|null $cell_group_id
 * @property string|null $organizer
 * @property string|null $location
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property bool $is_recurring
 * @property string|null $recurrence_frequency
 * @property array|null $recurrence_days
 * @property string $access_type
 * @property float $registration_fee
 * @property string $currency
 * @property bool $qr_enabled
 * @property string $status
 * @property int|null $created_by
 */
class Program extends Model
{
    protected $table = 'programs';

    protected $casts = [
        'church_id' => 'int',
        'department_id' => 'int',
        'cell_group_id' => 'int',
        'created_by' => 'int',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_recurring' => 'bool',
        'recurrence_days' => 'array',
        'registration_fee' => 'decimal:2',
        'qr_enabled' => 'bool',
        'is_training' => 'bool',
    ];

    protected $fillable = [
        'name', 'description', 'banner_path', 'category', 'classification',
        'scope', 'church_id', 'department_id', 'cell_group_id',
        'organizer', 'location', 'contact_phone', 'start_date', 'end_date', 'start_time', 'end_time',
        'is_recurring', 'recurrence_frequency', 'recurrence_days',
        'access_type', 'registration_fee', 'currency', 'qr_enabled',
        'status', 'created_by', 'is_training',
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function cellGroup()
    {
        return $this->belongsTo(CellGroup::class, 'cell_group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function occurrences()
    {
        return $this->hasMany(ProgramOccurrence::class);
    }

    public function registrations()
    {
        return $this->hasMany(ProgramRegistration::class);
    }

    public function attendances()
    {
        return $this->hasMany(ProgramAttendance::class);
    }

    /** Morning / Noon / ... sessions, repeated on every day of the program. */
    public function sessions()
    {
        return $this->hasMany(ProgramSession::class)->orderBy('sort_order')->orderBy('start_time');
    }

    /** Paid programs: the price for each member designation (group). */
    public function designationPrices()
    {
        return $this->hasMany(ProgramDesignationPrice::class);
    }

    /**
     * What $member pays: the price set for their MOST SENIOR designation -
     * the lowest id in member_designations (zonal pastor = 1 ... staff = 8).
     * Free programs, and people with no designation (e.g. first-time
     * visitors), pay nothing.
     *
     * @return array{amount: float, designation: ?MemberDesignation}
     */
    public function priceFor(Member $member): array
    {
        if ($this->isFree()) {
            return ['amount' => 0.0, 'designation' => null];
        }

        $designationId = $member->member_roles()->min('designation_id');
        if (!$designationId) {
            return ['amount' => 0.0, 'designation' => null];
        }

        $price = $this->designationPrices()->where('designation_id', $designationId)->value('amount');

        return [
            'amount' => (float) ($price ?? 0),
            'designation' => MemberDesignation::find($designationId),
        ];
    }

    /** [lowest, highest] designation price of a paid program. */
    public function priceRange(): array
    {
        $prices = $this->relationLoaded('designationPrices')
            ? $this->designationPrices->pluck('amount')
            : $this->designationPrices()->pluck('amount');

        return [(float) ($prices->min() ?? 0), (float) ($prices->max() ?? 0)];
    }

    /** "FREE", "TZS 20,000" or "TZS 20,000 – 100,000" (paid, by group). */
    public function accessLabel(): string
    {
        if ($this->isFree()) {
            return 'FREE';
        }

        [$min, $max] = $this->priceRange();

        return $min === $max
            ? $this->currency . ' ' . number_format($max)
            : $this->currency . ' ' . number_format($min) . ' – ' . number_format($max);
    }

    /** Sessions as the Edit Program form fills them in (times as HH:MM). */
    public function sessionsForForm(): array
    {
        return $this->sessions->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'start_time' => substr($s->start_time, 0, 5),
            'end_time' => substr($s->end_time, 0, 5),
        ])->values()->all();
    }

    /** "Morning Session 09:00 – 12:00 · Noon Session 14:00 – 17:00" */
    public function sessionsLabel(): string
    {
        return $this->sessions->map(fn ($s) => $s->name . ' ' . $s->timeRange())->implode(' · ');
    }

    public function isFree(): bool
    {
        return $this->access_type === 'free';
    }

    /**
     * Whether QR/barcode check-in is available for this program - always on
     * for special programs, opt-in for recurring ones (business rule 6).
     */
    public function qrAvailable(): bool
    {
        return $this->classification === 'special' || $this->qr_enabled;
    }

    /**
     * Find (or create, on demand) the occurrence row for a given date. No
     * scheduler pre-generates these - they're created the moment they're
     * needed, keeping historical rows untouched if the schedule changes later.
     */
    /** Code in the self check-in poster QR, created on first use. */
    public function checkinToken(): string
    {
        if (!$this->checkin_token) {
            do {
                $token = \Illuminate\Support\Str::random(16);
            } while (static::where('checkin_token', $token)->exists());

            $this->forceFill(['checkin_token' => $token])->save();
        }

        return $this->checkin_token;
    }

    public function occurrenceForDate(string $date): ProgramOccurrence
    {
        // church_id NULL: the program-wide occurrence. Church services have
        // one occurrence per church (see App\Services\ChurchServices).
        return $this->occurrences()->firstOrCreate(
            ['occurrence_date' => $date, 'church_id' => null],
            ['start_time' => $this->start_time, 'end_time' => $this->end_time, 'status' => 'scheduled']
        );
    }

    /**
     * Visible to a given member according to the program's scope: global to
     * everyone, church-scoped to that exact church, department/cell-scoped to
     * members actually assigned to that department/cell.
     */
    public function scopeVisibleToMember($query, Member $member)
    {
        $departmentIds = $member->departments()->pluck('departments.id');
        $cellIds = $member->cell_groups()->pluck('cell_groups.id');

        return $query->where(function ($q) use ($member, $departmentIds, $cellIds) {
            $q->where('scope', 'global')
              ->orWhere(function ($q2) use ($member) {
                  $q2->where('scope', 'church')->where('church_id', $member->church_id);
              })
              ->orWhere(function ($q2) use ($departmentIds) {
                  $q2->where('scope', 'department')->whereIn('department_id', $departmentIds);
              })
              ->orWhere(function ($q2) use ($cellIds) {
                  $q2->where('scope', 'cell')->whereIn('cell_group_id', $cellIds);
              });
        });
    }
}
