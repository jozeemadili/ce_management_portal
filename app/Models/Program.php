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
    ];

    protected $fillable = [
        'name', 'description', 'banner_path', 'category', 'classification',
        'scope', 'church_id', 'department_id', 'cell_group_id',
        'organizer', 'location', 'start_date', 'end_date', 'start_time', 'end_time',
        'is_recurring', 'recurrence_frequency', 'recurrence_days',
        'access_type', 'registration_fee', 'currency', 'qr_enabled',
        'status', 'created_by',
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
    public function occurrenceForDate(string $date): ProgramOccurrence
    {
        return $this->occurrences()->firstOrCreate(
            ['occurrence_date' => $date],
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
