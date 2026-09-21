<?php

namespace App\Http\Controllers\API\Church;

use App\Exports\DepartmentsExport;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Department;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class DepartmentManagementController extends Controller
{
    /**
     * Build the base, hierarchy-scoped + filtered departments query shared
     * by the index listing and the Excel export.
     */
    private function scopedDepartmentsQuery(Request $request)
    {
        $member = Auth::user()->member;

        if (!$member) {
            abort(403, 'No member record found.');
        }

        $userChurch = Church::find($member->church_id);

        if (!$userChurch) {
            abort(403, 'Church not found.');
        }

        $query = Department::with([
            'church',
            'members.church',
            'members.member_roles.member_designation',
        ])->withCount('members');

        if (!is_null($userChurch->parent_church_id)) {
            $churchIds = Church::where('id', $userChurch->id)
                ->orWhere('parent_church_id', $userChurch->id)
                ->pluck('id');

            $query->whereIn('church_id', $churchIds);
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('church_id')) {
            $query->where('church_id', $request->church_id);
        }

        return $query->orderBy('name');
    }

    public function index(Request $request)
    {
        $departments = $this->scopedDepartmentsQuery($request)
            ->paginate(10)
            ->withQueryString();

        $member = Auth::user()->member;
        $userChurch = Church::find($member->church_id);

        $churches = is_null($userChurch->parent_church_id)
            ? Church::orderBy('name')->get()
            : Church::where('id', $userChurch->id)
                ->orWhere('parent_church_id', $userChurch->id)
                ->orderBy('name')
                ->get();

        $allDepartments = $this->scopedDepartmentsQuery($request)->get();

        $stats = [
            'total'       => $allDepartments->count(),
            'members'     => $allDepartments->sum('members_count'),
            'with_head'   => $allDepartments->filter(fn($d) => $d->members->contains(fn($m) => $m->pivot->role === 'DEPARTMENT_HEAD'))->count(),
        ];
        $stats['without_head'] = $stats['total'] - $stats['with_head'];

        return view('portal.departments.index', compact('departments', 'churches', 'stats'));
    }

    public function export(Request $request)
    {
        $departments = $this->scopedDepartmentsQuery($request)->get();

        $filename = 'departments-' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new DepartmentsExport($departments), $filename);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $department->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Department updated successfully.');
    }

    public function removeMember(Department $department, Member $member)
    {
        $department->members()->detach($member->id);

        return back()->with('success', 'Member removed from department.');
    }
}
