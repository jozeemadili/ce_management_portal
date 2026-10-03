<?php

namespace App\Http\Livewire\Members;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


use App\Models\User;
use App\Models\Member;
use App\Models\Church;
use App\Models\MemberDesignation;
use App\Models\MemberRole;
use App\Services\AccountLogin;

class CreateMember extends Component
{
    public $title_id;
    public $first_name;
    public $last_name;
    public $phone;
    public $email;
    public $kingschat_username;
    public $church_id;
    public $designation_ids = []; // multiple roles

    public function rules()
    {
        return [
            'title_id'   => 'nullable|exists:member_titles,id',
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'phone'      => ['required', 'string', function ($attribute, $value, $fail) {
                $mobile = AccountLogin::normaliseMobile($value);
                if (!$mobile) {
                    $fail('Enter a valid phone number, e.g. 0712345678.');
                } elseif ($existing = $this->existingMemberWithPhone($value)) {
                    $fail($existing->describe() . ' is already registered as a member with this phone number'
                        . ((int) $existing->church_id !== (int) $this->church_id ? ' - use Transfer / Edit instead of registering again.' : '.'));
                } elseif ($user = User::where('mobile', $mobile)->first()) {
                    $fail('A login account already uses this phone number (' . trim($user->first_name . ' ' . $user->last_name) . ').');
                }
            }],
            'email'      => ['nullable', 'email', 'unique:users,email', function ($attribute, $value, $fail) {
                $existing = Member::with('church')->where('member_type', 'member')->where('is_training', false)
                    ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($value))])->first();
                if ($existing) {
                    $fail($existing->describe() . ' is already registered as a member with this email.');
                }
            }],
            'kingschat_username' => 'nullable|string|max:100',
            'church_id'  => 'required|exists:churches,id',
            'designation_ids' => 'required|array|min:1',
            'designation_ids.*' => 'exists:member_designations,id',
        ];
    }

    /** Set when an existing new soul record was turned into this member. */
    private ?string $promotedFrom = null;

    /** A church member (not a first-time visitor) with this phone, in any church. */
    private function existingMemberWithPhone($phone): ?Member
    {
        return Member::with('church')->withPhone($phone)
            ->where('member_type', 'member')->where('is_training', false)->first();
    }

    public function save()
    {
        $this->validate();

        $mobile = AccountLogin::normaliseMobile($this->phone);
        $phone = '0' . $mobile;
        $email = $this->email ? mb_strtolower(trim($this->email)) : null;

        DB::transaction(function () use ($mobile, $phone, $email) {

            /* -----------------------------
             | CREATE USER
             | Shared initial password; the member must set their own
             | on first login (must_change_password).
             |------------------------------*/
            $user = AccountLogin::createMemberUser($this->first_name, $this->last_name, $mobile, $email, Auth::id());

            /* -----------------------------
             | CREATE MEMBER - or promote the first-time visitor (new
             | soul) who already has this phone, instead of a duplicate
             |------------------------------*/
            $attributes = [
                'title_id'  => $this->title_id ?: null,
                'user_id'   => $user->id,
                'church_id' => $this->church_id,
                'first_name'=> $this->first_name,
                'last_name' => $this->last_name,
                'phone'     => $phone,
                'email'     => $email,
                'kingschat_username' => Member::normaliseKingschat($this->kingschat_username),
                'member_type' => 'member',
            ];

            $newSoul = Member::withPhone($phone)->where('member_type', 'new_soul')->where('is_training', false)
                ->whereNull('user_id')->orderBy('id')->first();

            if ($newSoul) {
                $this->promotedFrom = trim($newSoul->first_name . ' ' . $newSoul->last_name);
                $attributes['kingschat_username'] = $attributes['kingschat_username'] ?? $newSoul->kingschat_username;
                $newSoul->update($attributes + ['follow_up_status' => 'became_member']);
                $member = $newSoul;
            } else {
                $member = Member::create($attributes);
            }

            /* -----------------------------
             | ASSIGN DESIGNATIONS
             |------------------------------*/
            foreach ($this->designation_ids as $designationId) {
                MemberRole::create([
                    'member_id' => $member->id,
                    'designation_id' => $designationId,
                ]);
            }
        });

        session()->flash('success', ($this->promotedFrom ? "First-time visitor record found and turned into a member. " : '')
            . "Member registered. They can log in with phone {$phone}"
            . ($email ? " or {$email}" : '')
            . ' and the initial password ' . AccountLogin::defaultPassword()
            . ' - they will be asked to set their own password.');

        $this->reset();
    }


public function render()
{
    // Get logged-in member
    $member = Auth::user()->member;

    if (!$member) {
        abort(403, 'No member record found for this user.');
    }

    $userChurch = Church::find($member->church_id);

    if (!$userChurch) {
        abort(403, 'No church found for this member.');
    }

    // Determine which churches to show in dropdown
    if (is_null($userChurch->parent_church_id)) {
        // Root church → show all churches
        $churches = Church::with('current_head.member')->orderBy('name')->get();
    } else {
        // Not root → show only member's church + direct sub-churches
        $churchIds = Church::where('id', $userChurch->id)
            ->orWhere('parent_church_id', $userChurch->id)
            ->pluck('id');

        $churches = Church::with('current_head.member')
            ->whereIn('id', $churchIds)
            ->orderBy('name')
            ->get();
    }

    return view('livewire.members.create-member', [
        'churches' => $churches,
        'designations' => MemberDesignation::orderBy('name')->get(),
        'titles' => \App\Models\MemberTitle::active()->get(),
    ]);
}

}
