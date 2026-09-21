<?php

namespace App\Http\Controllers\API\Church;

use App\Exports\ChurchesExport;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\ChurchDesignation;
use App\Models\ChurchHierarchy;
use App\Models\HeadOfChurchUnit;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ChurchManagementController extends Controller
{
    /**
     * Build the base, hierarchy-scoped + filtered churches query
     * shared by the index listing and the Excel export.
     */
    private function scopedChurchesQuery(Request $request)
    {
        $member = Auth::user()->member;

        if (!$member) {
            abort(403, 'No member record found for this user.');
        }

        $userChurch = Church::with('church_designation')->find($member->church_id);

        if (!$userChurch) {
            abort(403, 'No church found for this member.');
        }

        $query = Church::with('church_designation', 'church', 'current_head.member')->withCount('members');

        if (is_null($userChurch->parent_church_id)) {
            // Root church → sees all churches
        } else {
            // Not root → member's church + direct sub-churches only
            $query->where(function ($q) use ($userChurch) {
                $q->where('id', $userChurch->id)
                  ->orWhere('parent_church_id', $userChurch->id);
            });
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('physical_location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        return $query->orderBy('id', 'desc');
    }

    /**
     * Members visible to the current user (their church + sub-churches, or
     * all members if the user sits at the root of the hierarchy). Used to
     * populate the "Head of Church" picker on the create/edit forms.
     */
    private function scopedMembers()
    {
        $member = Auth::user()->member;
        $userChurch = $member ? Church::find($member->church_id) : null;

        $query = Member::with('church', 'member_roles.member_designation')->where('member_type', 'member');

        if ($userChurch && !is_null($userChurch->parent_church_id)) {
            $churchIds = Church::where('id', $userChurch->id)
                ->orWhere('parent_church_id', $userChurch->id)
                ->pluck('id');

            $query->whereIn('church_id', $churchIds);
        }

        return $query->orderBy('first_name')->get();
    }

    /**
     * LIST CHURCHES
     */
    public function index(Request $request)
    {
        $churches = $this->scopedChurchesQuery($request)
            ->paginate(10)
            ->withQueryString();

        $designations = ChurchDesignation::orderBy('id')->get();
        $members = $this->scopedMembers();

        $stats = [
            'total'    => $this->scopedChurchesQuery($request)->count(),
            'active'   => $this->scopedChurchesQuery($request)->where('status', 'ACTIVE')->count(),
            'inactive' => $this->scopedChurchesQuery($request)->where('status', 'INACTIVE')->count(),
            'members'  => $this->scopedChurchesQuery($request)->get()->sum('members_count'),
        ];

        return view('portal.churches.index', compact('churches', 'designations', 'members', 'stats'));
    }

    /**
     * EXPORT CHURCHES TO EXCEL
     */
    public function export(Request $request)
    {
        $churches = $this->scopedChurchesQuery($request)->get();

        $filename = 'churches-list-' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new ChurchesExport($churches), $filename);
    }





    /**
     * STORE CHURCH
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'physical_location' => 'required|string',
            'designation_id'    => 'required|exists:church_designations,id',
            'parent_church_id'  => 'nullable|exists:churches,id',
            'head_of_unit'      => 'nullable|exists:members,id',
        ]);

        /* --------------------------------
         | GET DESIGNATION ORDER
         |---------------------------------*/
        $designations = ChurchDesignation::orderBy('id')->pluck('id')->values();
        $currentIndex = $designations->search((int)$request->designation_id);

        /* --------------------------------
         | BACKEND SAFETY VALIDATION
         |---------------------------------*/
        if ($currentIndex === 0 && $request->parent_church_id) {
            return back()->withErrors('Root designation cannot have a parent church');
        }

        if ($currentIndex > 0) {
            $requiredParentDesignationId = $designations[$currentIndex - 1];

            $parent = Church::find($request->parent_church_id);

            if (!$parent || $parent->designation_id !== $requiredParentDesignationId) {
                return back()->withErrors('Invalid parent church selected for this designation');
            }
        }

        /* --------------------------------
         | CREATE CHURCH
         |---------------------------------*/
        $church = Church::create([
            'name'              => strtoupper($request->name),
            'physical_location' => $request->physical_location,
            'designation_id'    => $request->designation_id,
            'parent_church_id'  => $request->parent_church_id,
            'status'            => 'ACTIVE',
        ]);

        /* --------------------------------
         | AUTO SAVE HIERARCHY
         |---------------------------------*/
        if ($request->parent_church_id) {
            ChurchHierarchy::create([
                'church_id'        => $church->id,
                'parent_church_id' => $request->parent_church_id,
                'start_date'       => now(),
                'changed_by'       => Auth::id(),
            ]);
        }

        /* --------------------------------
         | ASSIGN HEAD OF CHURCH (PASTOR)
         |---------------------------------*/
        if ($request->filled('head_of_unit')) {
            HeadOfChurchUnit::create([
                'unit_id'      => $church->id,
                'head_of_unit' => $request->head_of_unit,
                'reg_date'     => now(),
                'reg_by'       => Auth::id(),
            ]);
        }

        return back()->with('success', 'Church registered successfully');
    }

    /**
     * UPDATE CHURCH
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'physical_location' => 'required|string',
            'designation_id'    => 'required|exists:church_designations,id',
            'parent_church_id'  => 'nullable|exists:churches,id',
            'head_of_unit'      => 'nullable|exists:members,id',
        ]);

        $church = Church::with('current_head')->findOrFail($id);

        $designations = ChurchDesignation::orderBy('id')->pluck('id')->values();
        $currentIndex = $designations->search((int)$request->designation_id);

        if ($currentIndex === 0 && $request->parent_church_id) {
            return back()->withErrors('Root designation cannot have a parent');
        }

        if ($currentIndex > 0) {
            $requiredParentDesignationId = $designations[$currentIndex - 1];
            $parent = Church::find($request->parent_church_id);

            if (!$parent || $parent->designation_id !== $requiredParentDesignationId) {
                return back()->withErrors('Invalid parent church selected');
            }
        }

        $church->update([
            'name'              => strtoupper($request->name),
            'physical_location' => $request->physical_location,
            'designation_id'    => $request->designation_id,
            'parent_church_id'  => $request->parent_church_id,
        ]);

        /* --------------------------------
         | REASSIGN HEAD OF CHURCH (PASTOR)
         |---------------------------------*/
        $currentHeadMemberId = optional($church->current_head)->head_of_unit;

        if ($request->filled('head_of_unit') && (int) $request->head_of_unit !== (int) $currentHeadMemberId) {
            HeadOfChurchUnit::create([
                'unit_id'      => $church->id,
                'head_of_unit' => $request->head_of_unit,
                'reg_date'     => now(),
                'reg_by'       => Auth::id(),
            ]);
        }

        return back()->with('success', 'Church updated successfully');
    }

    /**
     * DEACTIVATE
     */
    public function deactivate($id)
    {
        Church::findOrFail($id)->update(['status' => 'INACTIVE']);
        return back()->with('success', 'Church deactivated successfully');
    }

    /**
     * ACTIVATE
     */
    public function activate($id)
    {
        Church::findOrFail($id)->update(['status' => 'ACTIVE']);
        return back()->with('success', 'Church activated successfully');
    }

    /**
     * TRANSFER CHURCH
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'church_id'        => 'required|exists:churches,id',
            'parent_church_id' => 'required|exists:churches,id',
        ]);

        $church = Church::findOrFail($request->church_id);
        $newParent = Church::findOrFail($request->parent_church_id);

        /* --------------------------------
         | VALIDATE HIERARCHY ORDER
         |---------------------------------*/
        $designations = ChurchDesignation::orderBy('id')->pluck('id')->values();
        $churchIndex  = $designations->search($church->designation_id);

        if ($churchIndex === false || $churchIndex === 0) {
            return back()->withErrors('Root church cannot be transferred');
        }

        if ($newParent->designation_id !== $designations[$churchIndex - 1]) {
            return back()->withErrors('Invalid parent for this church');
        }

        /* --------------------------------
         | CLOSE OLD HIERARCHY
         |---------------------------------*/
        ChurchHierarchy::where('church_id', $church->id)
            ->whereNull('end_date')
            ->update(['end_date' => now()]);

        /* --------------------------------
         | SAVE NEW HIERARCHY
         |---------------------------------*/
        ChurchHierarchy::create([
            'church_id'        => $church->id,
            'parent_church_id' => $newParent->id,
            'start_date'       => now(),
            'changed_by'       => Auth::id(),
        ]);

        /* --------------------------------
         | UPDATE MAIN RECORD
         |---------------------------------*/
        $church->update([
            'parent_church_id' => $newParent->id
        ]);

        return back()->with('success', 'Church transferred successfully');
    }
    /**
 * GET CHURCHES BY DESIGNATION (FOR PARENT DROPDOWN)
 */
public function getByDesignation($designationId)
{
    $churches = Church::where('designation_id', $designationId)
        ->where('status', 'ACTIVE')
        ->orderBy('name')
        ->get(['id', 'name']);

    return response()->json($churches);
}
// public function tree()
// {
//     $roots = Church::with('churches.churches')
//         ->whereNull('parent_church_id')
//         ->where('status', 'ACTIVE')
//         ->get();

//     return view('portal.churches.tree', compact('roots'));
// }
public function tree()
{
    $member = Auth::user()->member;
    $userChurch = $member ? Church::find($member->church_id) : null;

    $churches = Church::with(['church_designation', 'current_head.member'])
        ->withCount('members')
        ->where('status', 'ACTIVE')
        ->get();

    $byParent = $churches->groupBy('parent_church_id');

    if ($userChurch && !is_null($userChurch->parent_church_id)) {
        // Non-root user → tree rooted at their own church only
        $roots = $churches->where('id', $userChurch->id)->values();
    } else {
        // Root user → the full forest of top-level churches
        $roots = $byParent->get(null, collect())->values();
    }

    $palette = ['#2E5AAC', '#1FA971', '#D08C1D', '#C2418C', '#7C5CD1', '#0EA5A0'];

    $designationColors = ChurchDesignation::orderBy('id')->get()->values()
        ->mapWithKeys(fn($d, $i) => [$d->id => $palette[$i % count($palette)]]);

    $stats = [
        'total'      => $churches->count(),
        'with_head'  => $churches->filter(fn($c) => $c->current_head)->count(),
    ];
    $stats['without_head'] = $stats['total'] - $stats['with_head'];

    return view('portal.churches.tree', compact('roots', 'byParent', 'designationColors', 'stats'));
}

public function transferHistory($churchId)
{
    $church = Church::findOrFail($churchId);

    $history = ChurchHierarchy::with(['church'])
        ->where('church_id', $churchId)
        ->orderBy('start_date', 'desc')
        ->get();

    return view('portal.churches.transfer-history', compact(
        'church',
        'history'
    ));
}

}
