<?php

namespace App\Http\Controllers\API\Pledges;

use App\Exports\StaffRecordedPledgesExport;
use App\Http\Controllers\Concerns\AuthorizesPledges;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Pledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PledgeReportController extends Controller
{
    use AuthorizesPledges;

    private function scopedChurchIds()
    {
        $member = Auth::user()->member;

        if (!$member) {
            if (Auth::user()->role === 'ADMIN') {
                return null;
            }
            abort(403, 'No member record found for this user.');
        }

        $userChurch = Church::find($member->church_id);

        if ($userChurch && is_null($userChurch->parent_church_id)) {
            return null;
        }

        return Church::where('id', $userChurch->id ?? 0)
            ->orWhere('parent_church_id', $userChurch->id ?? 0)
            ->pluck('id');
    }

    public function index()
    {
        $this->authorizePledge('PLEDGES_VIEW_REPORTS');

        return view('portal.pledges.reports.index');
    }

    private function scopedStaffRecordedQuery(Request $request)
    {
        $churchIds = $this->scopedChurchIds();

        $query = Pledge::with(['campaign', 'member.church', 'recorder'])->where('source', 'staff');

        if (!is_null($churchIds)) {
            $query->whereHas('member', fn($q) => $q->whereIn('church_id', $churchIds));
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at');
    }

    public function staffRecorded(Request $request)
    {
        $this->authorizePledge('PLEDGES_VIEW_REPORTS');

        $pledges = $this->scopedStaffRecordedQuery($request)->paginate(15)->withQueryString();

        return view('portal.pledges.reports.staff-recorded', compact('pledges'));
    }

    public function exportStaffRecorded(Request $request)
    {
        $this->authorizePledge('PLEDGES_VIEW_REPORTS');

        $pledges = $this->scopedStaffRecordedQuery($request)->get();

        return Excel::download(new StaffRecordedPledgesExport($pledges), 'staff-recorded-pledges-' . now()->format('Y-m-d_His') . '.xlsx');
    }
}
