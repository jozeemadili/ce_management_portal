<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Member;
use App\Models\Program;
use App\Models\ProgramAuditLog;
use App\Models\ProgramRegistration;
use App\Services\Concerns\ReadsSpreadsheetRows;
use Illuminate\Support\Facades\DB;

/**
 * Excel upload for the programs registration desk's "Register Someone Else"
 * - first-time visitors. Same matching as the one-at-a-time visitor form
 * (Member::findOrCreateNewSoul): a phone that is already in the system is
 * registered using that existing person instead of creating a duplicate;
 * everyone else becomes a new soul of the chosen church.
 */
class ProgramVisitorBulkRegistrar
{
    use ReadsSpreadsheetRows;

    public const MAX_ROWS = 2000;

    /**
     * Checks every row without writing anything (the upload preview).
     *
     * @return array{new: array, existing: array, skipped: array, total: int}
     */
    public function analyse(array $rows, Program $program): array
    {
        $new = [];
        $existing = [];
        $skipped = [];
        $seenPhones = [];
        $total = 0;

        $rows = array_values($rows);

        $phones = [];
        foreach ($rows as $row) {
            if ($p = $this->normalisePhone($row['phone'] ?? null)) {
                $phones[] = $p;
            }
        }
        $people = Member::with('church')->whereIn('phone', array_unique($phones))->get()->keyBy('phone');
        $alreadyRegistered = ProgramRegistration::where('program_id', $program->id)
            ->where('registration_status', 'registered')
            ->whereIn('member_id', $people->pluck('id'))
            ->pluck('member_id')
            ->flip();

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

            $problem = null;
            if ($firstName === null) {
                $problem = 'First name is required.';
            } elseif ($phone === null) {
                $problem = 'Phone is missing or not a valid phone number.';
            } elseif (isset($seenPhones[$phone])) {
                $problem = "Same phone as row {$seenPhones[$phone]} in this file.";
            } elseif (($person = $people->get($phone)) && isset($alreadyRegistered[$person->id])) {
                $problem = 'Already registered for this program: ' . $this->describe($person) . '.';
            }

            if ($problem) {
                $skipped[] = ['row' => $sheetRow, 'name' => $name, 'reason' => $problem];
                continue;
            }

            $seenPhones[$phone] = $sheetRow;

            $attributes = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'gender' => $this->choice($row['gender'] ?? null, ['male', 'female'], ['m' => 'male', 'f' => 'female', 'me' => 'male', 'ke' => 'female']),
                'invited_by' => $this->text($row['invited_by'] ?? null),
            ];

            if ($person = $people->get($phone)) {
                $existing[] = [
                    'row' => $sheetRow,
                    'name' => $name,
                    'attributes' => $attributes,
                    'note' => 'Already in the system as ' . $this->describe($person)
                        . ($person->member_type === 'member' ? ' (church member)' : ' (first-time visitor)')
                        . ' - registered using that record.',
                ];
            } else {
                $new[] = ['row' => $sheetRow, 'name' => $name, 'attributes' => $attributes];
            }
        }

        return ['new' => $new, 'existing' => $existing, 'skipped' => $skipped, 'total' => $total];
    }

    /**
     * Registers every analysed row for the program - all or nothing. New
     * people become new souls of $church; registered_by is the uploader, so
     * they show under "People I've Invited" and as Invited By in reports.
     */
    public function register(array $analysis, Program $program, Church $church, int $userId): int
    {
        $rows = array_merge($analysis['new'], $analysis['existing']);

        DB::transaction(function () use ($rows, $program, $church, $userId) {
            foreach ($rows as $row) {
                $member = Member::findOrCreateNewSoul($row['attributes'], $church->id, $userId);
                $registration = ProgramRegistration::createFor($member, $program, $userId);
                ProgramAuditLog::record('registration.created', $registration, null, $registration->toArray() + ['source' => 'excel_upload']);
            }
        });

        return count($rows);
    }
}
