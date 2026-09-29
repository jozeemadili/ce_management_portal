<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Member;
use App\Models\MemberDesignation;
use App\Models\MemberRole;
use App\Models\User;
use App\Services\Concerns\ReadsSpreadsheetRows;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        // Stored phones may be 0712.., 255712.., +255712.. or 712..: match all,
        // keyed by the normalised 0712.. form used for the sheet.
        $variants = collect($phones)->unique()->flatMap(fn ($p) => Member::phoneVariants($p))->all();
        $existingByPhone = Member::with('church')->whereIn('phone', $variants ?: ['__none__'])
            ->where('is_training', false)
            ->orderByRaw("CASE WHEN member_type = 'member' THEN 0 ELSE 1 END")
            ->get()
            ->groupBy(fn ($m) => $this->normalisePhone($m->phone))
            ->map->first();
        $existingByEmail = Member::with('church')->whereIn(DB::raw('LOWER(email)'), array_unique($emails) ?: [''])->where('is_training', false)->get()
            ->keyBy(fn ($m) => strtolower($m->email));

        // Every imported member gets a login account, so the phone/email
        // must not already belong to one (users.mobile/email are unique).
        $mobiles = array_filter(array_map(fn ($p) => AccountLogin::normaliseMobile($p), $phones));
        $loginByMobile = User::whereIn('mobile', array_unique($mobiles) ?: [0])->get()->keyBy('mobile');
        $loginByEmail = User::whereIn(DB::raw('LOWER(email)'), array_unique($emails) ?: [''])->get()
            ->keyBy(fn ($u) => strtolower($u->email));

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
                $problem = $this->alreadyRegistered($match, 'phone');
            } elseif ($email && ($match = $existingByEmail->get($email))) {
                $problem = $this->alreadyRegistered($match, 'email');
            } elseif ($login = $loginByMobile->get(AccountLogin::normaliseMobile($phone))) {
                $problem = 'A login account already uses this phone (' . trim($login->first_name . ' ' . $login->last_name) . ').';
            } elseif ($email && ($login = $loginByEmail->get($email))) {
                $problem = 'A login account already uses this email (' . trim($login->first_name . ' ' . $login->last_name) . ').';
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
                'kingschat_username' => Member::normaliseKingschat($row['kingschat_username'] ?? null),
            ];
        }

        return ['valid' => $valid, 'skipped' => $skipped, 'total' => $total];
    }

    private function alreadyRegistered(Member $match, string $what): string
    {
        return $match->member_type === 'new_soul'
            ? "This {$what} belongs to first-time visitor " . $match->describe() . ' - add them with "New Member" to make them a member.'
            : "Already registered: " . $match->describe() . " has this {$what}.";
    }

    /**
     * Saves analysed rows as regular members of $church, each with the
     * "normal member" designation (when that designation exists) and a
     * login account: phone (or email) + the shared initial password, to be
     * changed on first login. All or nothing: one transaction.
     */
    public function import(array $validRows, Church $church, ?int $recordedBy): int
    {
        $designationId = MemberDesignation::whereRaw('LOWER(name) = ?', ['normal member'])->value('id');
        // Same initial password for everyone: hash it once, not per row.
        $passwordHash = Hash::make(AccountLogin::defaultPassword());

        DB::transaction(function () use ($validRows, $church, $recordedBy, $designationId, $passwordHash) {
            foreach ($validRows as $attributes) {
                $user = AccountLogin::createMemberUser(
                    $attributes['first_name'],
                    $attributes['last_name'],
                    AccountLogin::normaliseMobile($attributes['phone']),
                    $attributes['email'],
                    $recordedBy,
                    $passwordHash
                );

                $member = Member::create($attributes + [
                    'user_id' => $user->id,
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
