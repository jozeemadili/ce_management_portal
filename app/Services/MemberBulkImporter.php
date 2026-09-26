<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Member;
use App\Models\MemberDesignation;
use App\Models\MemberRole;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

/**
 * Bulk member upload for Member Management. analyse() checks every sheet row
 * without writing anything (the upload modal's preview); import() saves the
 * rows that passed. Every imported member belongs to the uploader's own
 * church - the sheet has no church column on purpose.
 */
class MemberBulkImporter
{
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

    private function describe(Member $member): string
    {
        $name = trim($member->first_name . ' ' . $member->last_name);
        $church = optional($member->church)->name;

        return $church ? "{$name} ({$church})" : $name;
    }

    private function isBlank(array $row): bool
    {
        foreach ($row as $value) {
            if (!$this->isEmpty($value)) {
                return false;
            }
        }

        return true;
    }

    private function isEmpty($value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    private function text($value): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        return mb_convert_case(trim(preg_replace('/\s+/', ' ', (string) $value)), MB_CASE_TITLE);
    }

    /**
     * Stored the way existing members' phones are (0745821083): Excel drops
     * the leading zero from numeric cells (745821083), and people also type
     * +255 745 821 083 or 255745821083.
     */
    public function normalisePhone($value): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $value);

        if (strlen($digits) === 12 && str_starts_with($digits, '255')) {
            $digits = '0' . substr($digits, 3);
        } elseif (strlen($digits) === 9) {
            $digits = '0' . $digits;
        }

        return preg_match('/^0\d{9}$/', $digits) ? $digits : null;
    }

    private function normaliseEmail($value): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        $email = strtolower(trim((string) $value));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function yesNo($value): ?string
    {
        return $this->choice($value, ['yes', 'no'], ['y' => 'yes', 'n' => 'no', 'ndiyo' => 'yes', 'hapana' => 'no']);
    }

    private function choice($value, array $allowed, array $aliases = []): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        $value = strtolower(trim((string) $value));
        $value = $aliases[$value] ?? $value;

        return in_array($value, $allowed, true) ? $value : null;
    }

    /**
     * Accepts real Excel date cells (serial numbers) and typed dates such as
     * 2024-03-10, 10/03/2024 or 10 Mar 2024.
     */
    private function parseDate($value): ?string
    {
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
            } catch (Throwable $e) {
                return null;
            }
        }

        $value = trim((string) $value);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'j/n/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!' . $format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->toDateString();
                }
            } catch (Throwable $e) {
                // not this format - try the next one
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable $e) {
            return null;
        }
    }
}
