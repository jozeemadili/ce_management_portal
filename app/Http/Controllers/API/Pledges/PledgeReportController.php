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
        // One rule everywhere (App\Services\ChurchScope): super admin = no limit;
        // a pastor / head of a church = that church and all churches under it;
        // anyone else = their own church only.
        if (Auth::user()->role === 'ADMIN') {
            return null;
        }

        return collect(app(\App\Services\ChurchScope::class)->ids());
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
