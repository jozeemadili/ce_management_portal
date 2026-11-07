<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberPhone;
use App\Services\MemberPhones;
use Illuminate\Http\Request;

/**
 * Mobile app: the signed-in person's extra phone numbers (same rules as
 * the portal profile - a number can belong to one person only). Any of
 * them can be used to log in and to check in.
 */
class MyPhonesController extends Controller
{
    public function __construct(private MemberPhones $phones)
    {
    }

    public function index(Request $request)
    {
        return response()->json($this->payload($this->member($request)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:30',
            'label' => 'nullable|string|max:50',
        ]);

        $member = $this->member($request);
        $phone = $this->phones->add($member, $data['phone'], $data['label'] ?? null, $request->user()->id);

        return response()->json(['message' => $phone->phone . ' was added.'] + $this->payload($member->fresh()), 201);
    }

    public function destroy(Request $request, MemberPhone $phone)
    {
        $member = $this->member($request);
        $this->phones->remove($member, $phone);

        return response()->json(['message' => $phone->phone . ' was removed.'] + $this->payload($member->fresh()));
    }

    private function member(Request $request): Member
    {
        $member = $request->user()->member;
        abort_unless($member, 404, 'Your account is not linked to a member record.');

        return $member;
    }

    private function payload(Member $member): array
    {
        return [
            'main_phone' => $member->phone ? Member::normalisedPhone($member->phone) ?? $member->phone : null,
            'phones' => $member->phones()->orderBy('id')->get(['id', 'phone', 'label']),
        ];
    }
}
