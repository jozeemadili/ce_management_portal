<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberPhone;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Extra phone numbers of a person. A number belongs to one person only -
 * if it is already someone else's, that is probably the same person
 * registered twice: merge them on Church Setup > Duplicate Members.
 */
class MemberPhones
{
    public function add(Member $member, string $phone, ?string $label, ?int $userId): MemberPhone
    {
        $normalised = Member::normalisedPhone($phone);
        if (!$normalised) {
            throw ValidationException::withMessages(['phone' => 'Enter a valid phone number, e.g. 0712345678.']);
        }

        if (in_array($normalised, $member->load('phones')->allPhones(), true)) {
            throw ValidationException::withMessages(['phone' => "{$normalised} is already on this profile."]);
        }

        $owner = Member::with('church')->withPhone($normalised)->whereKeyNot($member->id)->first();
        if ($owner) {
            throw ValidationException::withMessages(['phone' => "{$normalised} belongs to " . $owner->describe()
                . '. If that is the same person, merge them under Church Setup > Duplicate Members.']);
        }

        $login = User::where('mobile', (int) substr($normalised, 1))->first();
        if ($login && (int) $login->id !== (int) $member->user_id) {
            throw ValidationException::withMessages(['phone' => "{$normalised} is the login number of " . trim($login->first_name . ' ' . $login->last_name) . '.']);
        }

        return MemberPhone::create([
            'member_id' => $member->id,
            'phone' => $normalised,
            'label' => trim((string) $label) ?: null,
            'added_by' => $userId,
        ]);
    }

    public function remove(Member $member, MemberPhone $phone): void
    {
        abort_unless((int) $phone->member_id === (int) $member->id, 404);
        $phone->delete();
    }
}
