<?php

namespace App\Http\Controllers\API\Church;

use App\Exports\CellGroupsExport;
use App\Http\Controllers\Controller;
use App\Models\CellGroup;
use App\Models\Church;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class CellManagementController extends Controller
{
    /**
     * Build the base, hierarchy-scoped + filtered cell groups query shared
     * by the index listing and the Excel export.
     */
    private function scopedCellGroupsQuery(Request $request)
    {
        $member = Auth::user()->member;

        if (!$member && Auth::user()->role !== 'ADMIN') {
            abort(403, 'No member record found.');
        }

        $userChurch = $member ? Church::find($member->church_id) : null;

        if (!$userChurch && Auth::user()->role !== 'ADMIN') {
            abort(403, 'Church not found.');
        }

        $query = CellGroup::with([
            'church',
            'members.church',
            'members.member_roles.member_designation',
        ])->withCount('members');

        // Own church; a pastor / head: their churches and all under them (App\Services\ChurchScope).
        if (Auth::user()->role !== 'ADMIN') {
            $query->whereIn('church_id', app(\App\Services\ChurchScope::class)->ids());
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
        $cellGroups = $this->scopedCellGroupsQuery($request)
            ->paginate(10)
            ->withQueryString();

        $member = Auth::user()->member;

        $churches = Church::whereIn('id', app(\App\Services\ChurchScope::class)->ids())->orderBy('name')->get();

        $allCells = $this->scopedCellGroupsQuery($request)->get();

        $stats = [
            'total'         => $allCells->count(),
            'members'       => $allCells->sum('members_count'),
            'with_leader'   => $allCells->filter(fn($c) => $c->members->contains(fn($m) => $m->pivot->role === 'CELL_LEADER'))->count(),
        ];
        $stats['without_leader'] = $stats['total'] - $stats['with_leader'];

        return view('portal.cells.index', compact('cellGroups', 'churches', 'stats'));
    }

    public function export(Request $request)
    {
        $cellGroups = $this->scopedCellGroupsQuery($request)->get();

        $filename = 'cell-groups-' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new CellGroupsExport($cellGroups), $filename);
    }

    public function update(Request $request, CellGroup $cell)
    {
        app(\App\Services\ChurchScope::class)->authorize((int) $cell->church_id);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $cell->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Cell group updated successfully.');
    }

    public function removeMember(CellGroup $cell, Member $member)
    {
        app(\App\Services\ChurchScope::class)->authorize((int) $cell->church_id);
        $cell->members()->detach($member->id);

        return back()->with('success', 'Member removed from cell group.');
    }
}
