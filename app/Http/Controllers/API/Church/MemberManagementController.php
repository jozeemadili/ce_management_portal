<?php

namespace App\Http\Controllers\API\Church;

use App\Exports\MembersExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{
    Member,
    Church,
    MemberDesignation,
    CellGroup,
    Department
};

class MemberManagementController extends Controller
{
    /**
     * Build the base, hierarchy-scoped + filtered members query shared by
     * the index listing and the Excel export.
     */
    private function scopedMembersQuery(Request $request)
    {
        $member = Auth::user()->member;

        if (!$member) {
            abort(403, 'No member record found for this user.');
        }

        $userChurch = Church::find($member->church_id);

        if (!$userChurch) {
            abort(403, 'No church found for this member.');
        }

        $query = Member::with([
            'church',
            'member_roles.member_designation',
            'cell_groups',
            'departments',
        ]);

        // Default to the real congregation - new souls stay out of Member
        // Management unless explicitly requested (they have their own New
        // Souls screen), same as every other member list/count in the app.
        if ($request->get('type') !== 'all') {
            $query->where('member_type', $request->get('type') === 'new_soul' ? 'new_soul' : 'member');
        }

        if (!is_null($userChurch->parent_church_id)) {
            $churchIds = Church::where('id', $userChurch->id)
                ->orWhere('parent_church_id', $userChurch->id)
                ->pluck('id');

            $query->whereIn('church_id', $churchIds);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('church_id')) {
            $query->where('church_id', $request->church_id);
        }

        if ($request->filled('designation_id')) {
            $query->whereHas('member_roles', function ($q) use ($request) {
                $q->where('designation_id', $request->designation_id);
            });
        }

        return $query->orderBy('first_name');
    }

    /**
     * LIST MEMBERS
     */
    public function index(Request $request)
    {
        $Members = $this->scopedMembersQuery($request)
            ->paginate(10)
            ->withQueryString();

        $churches = Church::with('current_head.member')->orderBy('name')->get();
        $designations = MemberDesignation::orderBy('name')->get();
        $cellGroups = CellGroup::all();
        $departments = Department::all();

        $stats = [
            'total'      => $this->scopedMembersQuery($request)->count(),
            'baptized'   => $this->scopedMembersQuery($request)->where('baptism_status', 'yes')->count(),
            'foundation' => $this->scopedMembersQuery($request)->where('foundation_clases', 'yes')->count(),
            'married'    => $this->scopedMembersQuery($request)->where('marriage_status', 'married')->count(),
        ];

        return view('portal.members.churchmebers', [
            'Members'      => $Members,
            'churches'     => $churches,
            'designations' => $designations,
            'cellGroups'   => $cellGroups,
            'departments'  => $departments,
            'stats'        => $stats,
        ]);
    }

    /**
     * EXPORT MEMBERS TO EXCEL
     */
    public function export(Request $request)
    {
        $members = $this->scopedMembersQuery($request)->get();

        $filename = 'members-list-' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new MembersExport($members), $filename);
    }

    public function update(Request $request, Member $member)
    {
        $member->update($request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'church_id',
            'foundation_clases',
            'foundation_clases_date',
            'baptism_status',
            'baptism_date',
            'marriage_status',
            'marriage_dates',
        ]));

        $member->member_roles()->delete();

        if ($request->designations) {
            foreach ($request->designations as $designation) {
                $member->member_roles()->create([
                    'designation_id' => $designation
                ]);
            }
        }

        $member->cell_groups()->sync($request->cell_groups ?? []);
        $member->departments()->sync($request->departments ?? []);

        return back()->with('success', 'Member updated successfully');
    }
}
