<?php

namespace App\Http\Controllers\API\Pledges;

use App\Exports\PledgeContributionsExport;
use App\Http\Controllers\Concerns\AuthorizesPledges;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Pledge;
use App\Models\PledgeAuditLog;
use App\Models\PledgeContribution;
use App\Models\PledgePaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PledgeContributionController extends Controller
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

    private function scopedContributionsQuery(Request $request)
    {
        $churchIds = $this->scopedChurchIds();

        $query = PledgeContribution::with(['pledge.member.church', 'pledge.campaign', 'recorder']);

        if (!is_null($churchIds)) {
            $query->whereHas('pledge.member', fn($q) => $q->whereIn('church_id', $churchIds));
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($outer) use ($search) {
                $outer->where('payment_reference', 'like', "%{$search}%")
                    ->orWhereHas('pledge', fn($q) => $q->where('pledge_reference', 'like', "%{$search}%"))
                    ->orWhereHas('pledge.member', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderByDesc('created_at');
    }

    public function index(Request $request)
    {
        $this->authorizePledge('PLEDGES_VIEW');

        $contributions = $this->scopedContributionsQuery($request)->paginate(15)->withQueryString();

        $total = (clone $this->scopedContributionsQuery($request))->sum('amount');

        $paymentMethods = PledgePaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('portal.pledges.contributions.index', compact('contributions', 'total', 'paymentMethods'));
    }

    /**
     * Look up open pledges (not fulfilled/cancelled) by reference or member
     * name, for the "Record Contribution" search box.
     */
    public function searchPledges(Request $request)
    {
        $this->authorizePledge('PLEDGES_MANAGE_CONTRIBUTIONS');

        $churchIds = $this->scopedChurchIds();
        $search = trim((string) $request->get('q'));

        $query = Pledge::with(['member', 'campaign'])
            ->whereNotIn('status', ['fulfilled', 'cancelled'])
            ->limit(15);

        if (!is_null($churchIds)) {
            $query->whereHas('member', fn($q) => $q->whereIn('church_id', $churchIds));
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('pledge_reference', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $results = $query->get()->map(fn($p) => [
            'id' => $p->id,
            'reference' => $p->pledge_reference,
            'member' => trim(optional($p->member)->first_name . ' ' . optional($p->member)->last_name),
            'campaign' => optional($p->campaign)->name,
            'currency' => optional($p->campaign)->currency,
            'outstanding' => $p->outstanding(),
        ]);

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $this->authorizePledge('PLEDGES_MANAGE_CONTRIBUTIONS');

        $data = $request->validate([
            'pledge_id' => 'required|exists:pledges,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_reference' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $pledge = Pledge::findOrFail($data['pledge_id']);

        abort_unless($pledge->status !== 'cancelled', 422, 'This pledge has been cancelled and cannot receive contributions.');

        $data['recorded_by'] = Auth::id();

        $contribution = PledgeContribution::create($data);

        $pledge->recalculateStatus();

        PledgeAuditLog::record('contribution.recorded', $contribution, null, $contribution->toArray());

        return back()->with('success', "Contribution of {$pledge->campaign->currency} " . number_format($contribution->amount, 2) . " recorded against {$pledge->pledge_reference}.");
    }

    public function export(Request $request)
    {
        $this->authorizePledge('PLEDGES_VIEW_REPORTS');

        $contributions = $this->scopedContributionsQuery($request)->get();

        return Excel::download(new PledgeContributionsExport($contributions), 'pledge-contributions-' . now()->format('Y-m-d_His') . '.xlsx');
    }
}
