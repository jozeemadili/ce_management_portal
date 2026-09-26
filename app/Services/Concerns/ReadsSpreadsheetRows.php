<?php

namespace App\Services\Concerns;

use App\Models\Member;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

/**
 * Cell clean-up shared by the spreadsheet uploads (bulk members, bulk
 * program visitors): blank rows, names, phones, emails, yes/no-style
 * choices and dates as people actually type them into Excel.
 */
trait ReadsSpreadsheetRows
{
    protected function describe(Member $member): string
    {
        $name = trim($member->first_name . ' ' . $member->last_name);
        $church = optional($member->church)->name;

        return $church ? "{$name} ({$church})" : $name;
    }

    protected function isBlank(array $row): bool
    {
        foreach ($row as $value) {
            if (!$this->isEmpty($value)) {
                return false;
            }
        }

        return true;
    }

    protected function isEmpty($value): bool
    {
        return $value === null || trim((string) $value) === '';
    }

    protected function text($value): ?string
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

    protected function normaliseEmail($value): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        $email = strtolower(trim((string) $value));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    protected function yesNo($value): ?string
    {
        return $this->choice($value, ['yes', 'no'], ['y' => 'yes', 'n' => 'no', 'ndiyo' => 'yes', 'hapana' => 'no']);
    }

    protected function choice($value, array $allowed, array $aliases = []): ?string
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
    protected function parseDate($value): ?string
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
