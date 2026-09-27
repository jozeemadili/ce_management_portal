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
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'phone'      => ['required', 'string', function ($attribute, $value, $fail) {
                $mobile = AccountLogin::normaliseMobile($value);
                if (!$mobile) {
                    $fail('Enter a valid phone number, e.g. 0712345678.');
                } elseif (User::where('mobile', $mobile)->exists()) {
                    $fail('A user with this phone number already exists.');
                }
            }],
            'email'      => 'nullable|email|unique:users,email',
            'kingschat_username' => 'nullable|string|max:100',
            'church_id'  => 'required|exists:churches,id',
            'designation_ids' => 'required|array|min:1',
            'designation_ids.*' => 'exists:member_designations,id',
        ];
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
            $user = User::create([
                'first_name' => $this->first_name,
                'last_name'  => $this->last_name,
                'email'      => $email,
                'mobile'     => $mobile,
                'password'   => Hash::make(AccountLogin::defaultPassword()),
                'must_change_password' => true,
                'status'     => 'Active', // users.status allows Pending/Active/Inactive/Rejected (case-sensitive on PostgreSQL)
                'company_id' => 1,
                'created_by' => Auth::user()->id
            ]);

            /* -----------------------------
             | CREATE MEMBER
             |------------------------------*/
            $member = Member::create([
                'user_id'   => $user->id,
                'church_id' => $this->church_id,
                'first_name'=> $this->first_name,
                'last_name' => $this->last_name,
                'phone'     => $phone,
                'email'     => $email,
                'kingschat_username' => Member::normaliseKingschat($this->kingschat_username),
            ]);

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

        session()->flash('success', "Member registered. They can log in with phone {$phone}"
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
    ]);
}

}
