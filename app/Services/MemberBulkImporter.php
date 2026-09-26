<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Member;
use App\Models\MemberDesignation;
use App\Models\MemberRole;
use App\Services\Concerns\ReadsSpreadsheetRows;
use Illuminate\Support\Facades\DB;

/**
 * Bulk member upload for Member Management. analyse() checks every sheet row
 * without writing anything (the upload modal's preview); import() saves the
 * rows that passed. Every imported member belongs to the uploader's own
 * church - the sheet has no church column on purpose.
 */
class MemberBulkImporter
{
    use ReadsSpreadsheetRows;

    public const MAX_ROWS = 2000;

    /**
     * @param  array<int, array<string, mixed>>  $rows  heading-keyed sheet rows (first data row = sheet row 2)
     * @return array{valid: array<int, array>, skipped: array<int, array>, total: int}
     */
    public function analyse(array $rows): array
    {
        $valid = [];
        $skipped = [];
        $seenPhones = [];
        $seenEmails = [];
        $total = 0;

        $rows = array_values($rows);

        // Existing members matched by phone/email in one query each, instead
        // of one lookup per row.
        $phones = [];
        $emails = [];
        foreach ($rows as $row) {
            if ($p = $this->normalisePhone($row['phone'] ?? null)) {
                $phones[] = $p;
            }
            if ($e = $this->normaliseEmail($row['email'] ?? null)) {
                $emails[] = $e;
            }
        }
        $existingByPhone = Member::with('church')->whereIn('phone', array_unique($phones))->get()->keyBy('phone');
        $existingByEmail = Member::with('church')->whereIn(DB::raw('LOWER(email)'), array_unique($emails))->get()
            ->keyBy(fn ($m) => strtolower($m->email));

        foreach ($rows as $index => $row) {
            $sheetRow = $index + 2; // row 1 is the heading row

            if ($this->isBlank($row)) {
                continue;
            }
            $total++;

            $firstName = $this->text($row['first_name'] ?? null);
            $lastName = $this->text($row['last_name'] ?? null);
            $name = trim("{$firstName} {$lastName}") ?: '—';
            $phone = $this->normalisePhone($row['phone'] ?? null);
            $email = $this->normaliseEmail($row['email'] ?? null);

            $problem = null;
            if ($firstName === null || $lastName === null) {
                $problem = 'First name and last name are required.';
            } elseif ($phone === null) {
                $problem = 'Phone is missing or not a valid phone number.';
            } elseif (($row['email'] ?? null) && $email === null) {
                $problem = 'Email is not a valid email address.';
            } elseif (isset($seenPhones[$phone])) {
                $problem = "Same phone as row {$seenPhones[$phone]} in this file.";
            } elseif ($email && isset($seenEmails[$email])) {
                $problem = "Same email as row {$seenEmails[$email]} in this file.";
            } elseif ($match = $existingByPhone->get($phone)) {
                $problem = 'Already registered: ' . $this->describe($match) . ' has this phone.';
            } elseif ($email && ($match = $existingByEmail->get($email))) {
                $problem = 'Already registered: ' . $this->describe($match) . ' has this email.';
            }

            $dates = [];
            if (!$problem) {
                foreach ([
                    'date_of_birth' => 'Date of Birth',
                    'foundation_classes_date' => 'Foundation Classes Date',
                    'baptism_date' => 'Baptism Date',
                    'marriage_date' => 'Marriage Date',
                ] as $key => $label) {
                    $value = $row[$key] ?? null;
                    if ($this->isEmpty($value)) {
                        $dates[$key] = null;
                        continue;
                    }
                    $date = $this->parseDate($value);
                    if (!$date) {
                        $problem = "{$label} \"{$value}\" is not a valid date (use YYYY-MM-DD).";
                        break;
                    }
                    $dates[$key] = $date;
                }
            }

            if ($problem) {
                $skipped[] = ['row' => $sheetRow, 'name' => $name, 'reason' => $problem];
                continue;
            }

            $seenPhones[$phone] = $sheetRow;
            if ($email) {
                $seenEmails[$email] = $sheetRow;
            }

            $valid[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'email' => $email,
                'gender' => $this->choice($row['gender'] ?? null, ['male', 'female'], ['m' => 'male', 'f' => 'female', 'me' => 'male', 'ke' => 'female']),
                'date_of_birth' => $dates['date_of_birth'],
                'foundation_clases' => $this->yesNo($row['foundation_classes'] ?? null),
                'foundation_clases_date' => $dates['foundation_classes_date'],
                'baptism_status' => $this->yesNo($row['baptism_status'] ?? null),
                'baptism_date' => $dates['baptism_date'],
                'marriage_status' => $this->choice($row['marriage_status'] ?? null, ['married', 'single']),
                'marriage_dates' => $dates['marriage_date'],
            ];
        }

        return ['valid' => $valid, 'skipped' => $skipped, 'total' => $total];
    }

    /**
     * Saves analysed rows as regular members of $church, each with the
     * "normal member" designation (when that designation exists). All or
     * nothing: one transaction.
     */
    public function import(array $validRows, Church $church, ?int $recordedBy): int
    {
        $designationId = MemberDesignation::whereRaw('LOWER(name) = ?', ['normal member'])->value('id');

        DB::transaction(function () use ($validRows, $church, $recordedBy, $designationId) {
            foreach ($validRows as $attributes) {
                $member = Member::create($attributes + [
                    'church_id' => $church->id,
                    'member_type' => 'member',
                    'recorded_by' => $recordedBy,
                ]);

                if ($designationId) {
                    MemberRole::create(['member_id' => $member->id, 'designation_id' => $designationId]);
                }
            }
        });

        return count($validRows);
    }
}
