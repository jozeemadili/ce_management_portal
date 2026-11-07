<?php

namespace App\Http\Controllers\API\Church;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Concerns\ScopesChurches;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Member;
use App\Models\MemberPhone;
use App\Services\MemberMerger;
use App\Services\MemberPhones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Church Setup > Duplicate Members (find + merge people registered more
 * than once) and the extra phone numbers on a person's profile.
 */
class DuplicateMemberController extends Controller
{
    use AuthorizesPrograms, ScopesChurches;

    public function index(MemberMerger $merger)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $groups = $merger->duplicateGroups($this->scopeIds());

        return view('portal.churches.duplicates.index', compact('groups'));
    }

    public function merge(Request $request, MemberMerger $merger)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $data = $request->validate([
            'keep_id' => 'required|integer|exists:members,id',
            'merge_ids' => 'required|array|min:1',
            'merge_ids.*' => 'integer|exists:members,id',
        ], ['merge_ids.required' => 'Tick the record(s) to merge into the one you keep.']);

        $keep = Member::findOrFail($data['keep_id']);
        $duplicates = Member::whereIn('id', array_diff($data['merge_ids'], [$keep->id]))->get();
        foreach ($duplicates->push($keep) as $m) {
            $this->assertMine($m);
        }

        $totals = [];
        foreach ($duplicates->where('id', '!=', $keep->id) as $dup) {
            foreach ($merger->merge($keep, $dup, Auth::id()) as $key => $n) {
                $totals[$key] = ($totals[$key] ?? 0) + $n;
            }
        }

        $moved = collect($totals)->filter()->map(fn ($n, $k) => "{$n} " . str_replace('_', ' ', $k))->implode(', ');

        return back()->with('success', 'Merged ' . ($duplicates->count() - 1) . ' record(s) into ' . trim($keep->first_name . ' ' . $keep->last_name)
            . ' (#' . $keep->id . ').' . ($moved ? " Moved: {$moved}." : ''));
    }

    public function ignore(Request $request, MemberMerger $merger)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $data = $request->validate(['member_ids' => 'required|array|min:2', 'member_ids.*' => 'integer|exists:members,id']);
        $merger->ignore($data['member_ids'], Auth::id());

        return back()->with('success', 'Marked as different people - they will not be suggested again.');
    }

    /* ---------------- extra phone numbers ---------------- */

    public function addPhone(Request $request, Member $member, MemberPhones $phones)
    {
        $this->assertCanEditPhones($member);
        $data = $request->validate(['phone' => 'required|string|max:30', 'label' => 'nullable|string|max:50']);

        $added = $phones->add($member, $data['phone'], $data['label'] ?? null, Auth::id());

        return back()->with('success', "{$added->phone} added to " . $member->first_name . "'s phone numbers.");
    }

    public function removePhone(Member $member, MemberPhone $phone, MemberPhones $phones)
    {
        $this->assertCanEditPhones($member);
        $phones->remove($member, $phone);

        return back()->with('success', "{$phone->phone} removed.");
    }

    /* ---------------- helpers ---------------- */

    /** Null for the top church (sees everyone), else the user's churches. */
    private function scopeIds(): ?array
    {
        $visible = $this->visibleChurches();

        return $visible->count() === Church::where('status', 'ACTIVE')->count() ? null : $visible->pluck('id')->all();
    }

    private function assertMine(Member $member): void
    {
        $ids = $this->scopeIds();
        abort_unless($ids === null || in_array($member->church_id, $ids, true), 403, trim($member->first_name . ' ' . $member->last_name) . ' is not at one of your churches.');
    }

    /** Your own profile, or a member of your churches. */
    private function assertCanEditPhones(Member $member): void
    {
        if ((int) optional(Auth::user()->member)->id === (int) $member->id) {
            return;
        }
        $this->authorizeProgram('PROGRAMS_EDIT');
        $this->assertMine($member);
    }
}
