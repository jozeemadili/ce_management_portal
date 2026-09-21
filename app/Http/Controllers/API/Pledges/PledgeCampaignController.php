<?php

namespace App\Http\Controllers\API\Pledges;

use App\Exports\PledgeCampaignsExport;
use App\Http\Controllers\Concerns\AuthorizesPledges;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Pledge;
use App\Models\PledgeAuditLog;
use App\Models\PledgeCampaign;
use App\Models\PledgeContribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PledgeCampaignController extends Controller
{
    use AuthorizesPledges;

    /**
     * Churches visible to the current user (their church + sub-churches, or
     * all churches if root) - used both for scoping campaigns and for the
     * "specific church" picker on the campaign form.
     */
    private function scopedChurchIds()
    {
        $member = Auth::user()->member;

        // An ADMIN account with no personal member record (e.g. a pure system
        // admin) is treated the same as root - unrestricted - rather than
        // being locked out of a module they administer.
        if (!$member) {
            if (Auth::user()->role === 'ADMIN') {
                return null;
            }
            abort(403, 'No member record found for this user.');
        }

        $userChurch = Church::find($member->church_id);

        if (!$userChurch) {
            abort(403, 'No church found for this member.');
        }

        if (is_null($userChurch->parent_church_id)) {
            return null; // null = unrestricted (root)
        }

        return Church::where('id', $userChurch->id)
            ->orWhere('parent_church_id', $userChurch->id)
            ->pluck('id');
    }

    private function scopedCampaignsQuery(Request $request)
    {
        $churchIds = $this->scopedChurchIds();

        $query = PledgeCampaign::with(['church', 'creator'])->withCount('pledges');

        if (!is_null($churchIds)) {
            $query->where(function ($q) use ($churchIds) {
                $q->where('scope', 'global')->orWhereIn('church_id', $churchIds);
            });
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('scope')) {
            $query->where('scope', $request->scope);
        }

        return $query->orderByDesc('id');
    }

    public function index(Request $request)
    {
        $campaigns = $this->scopedCampaignsQuery($request)->paginate(10)->withQueryString();

        $churchIds = $this->scopedChurchIds();
        $churches = is_null($churchIds)
            ? Church::orderBy('name')->get()
            : Church::whereIn('id', $churchIds)->orderBy('name')->get();

        $allCampaigns = $this->scopedCampaignsQuery($request)->get();

        $stats = [
            'total' => $allCampaigns->count(),
            'active' => $allCampaigns->where('status', 'active')->count(),
            'target' => $allCampaigns->sum('target_amount'),
            'pledged' => $allCampaigns->sum(fn($c) => $c->totalPledged()),
        ];

        return view('portal.pledges.campaigns.index', compact('campaigns', 'churches', 'stats'));
    }

    public function store(Request $request)
    {
        $this->authorizePledge('PLEDGES_MANAGE_CAMPAIGNS');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:draft,active,completed,closed',
            'scope' => 'required|in:global,church',
            'church_id' => 'required_if:scope,church|nullable|exists:churches,id',
            'allow_anonymous' => 'nullable|boolean',
            'live_enabled' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'banner' => 'nullable|image|max:4096',
        ]);

        $data['currency'] = $data['currency'] ?? 'TZS';
        $data['church_id'] = $data['scope'] === 'church' ? $data['church_id'] : null;
        $data['allow_anonymous'] = $request->boolean('allow_anonymous');
        $data['live_enabled'] = $request->boolean('live_enabled');
        $data['created_by'] = Auth::id();

        if ($request->hasFile('banner')) {
            $data['banner_path'] = $request->file('banner')->store('pledge-banners', 'public');
        }
        unset($data['banner']);

        $campaign = PledgeCampaign::create($data);

        PledgeAuditLog::record('campaign.created', $campaign, null, $campaign->toArray());

        return back()->with('success', 'Campaign created successfully.');
    }

    public function update(Request $request, PledgeCampaign $campaign)
    {
        $this->authorizePledge('PLEDGES_MANAGE_CAMPAIGNS');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:draft,active,completed,closed',
            'scope' => 'required|in:global,church',
            'church_id' => 'required_if:scope,church|nullable|exists:churches,id',
            'allow_anonymous' => 'nullable|boolean',
            'live_enabled' => 'nullable|boolean',
            'live_show_amount' => 'nullable|boolean',
            'live_show_pledgers' => 'nullable|boolean',
            'live_show_latest' => 'nullable|boolean',
            'live_show_graph' => 'nullable|boolean',
            'live_show_target' => 'nullable|boolean',
            'live_mask_names' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'banner' => 'nullable|image|max:4096',
        ]);

        $old = $campaign->toArray();

        $data['currency'] = $data['currency'] ?? 'TZS';
        $data['church_id'] = $data['scope'] === 'church' ? $data['church_id'] : null;

        foreach (['allow_anonymous', 'live_enabled', 'live_show_amount', 'live_show_pledgers', 'live_show_latest', 'live_show_graph', 'live_show_target', 'live_mask_names'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        if ($request->hasFile('banner')) {
            if ($campaign->banner_path) {
                Storage::disk('public')->delete($campaign->banner_path);
            }
            $data['banner_path'] = $request->file('banner')->store('pledge-banners', 'public');
        }
        unset($data['banner']);

        $campaign->update($data);

        PledgeAuditLog::record('campaign.updated', $campaign, $old, $campaign->fresh()->toArray());

        return back()->with('success', 'Campaign updated successfully.');
    }

    public function setStatus(Request $request, PledgeCampaign $campaign, string $status)
    {
        $this->authorizePledge('PLEDGES_MANAGE_CAMPAIGNS');

        abort_unless(in_array($status, ['draft', 'active', 'completed', 'closed']), 422, 'Invalid status.');

        $old = ['status' => $campaign->status];
        $campaign->update(['status' => $status]);

        PledgeAuditLog::record('campaign.status_changed', $campaign, $old, ['status' => $status]);

        return back()->with('success', 'Campaign status updated to ' . ucfirst($status) . '.');
    }

    public function show(PledgeCampaign $campaign)
    {
        $this->authorizePledge('PLEDGES_VIEW');

        $pledges = $campaign->pledges()->where('status', '!=', 'cancelled')->with('member')->get();

        $growth = $pledges->groupBy(fn($p) => $p->pledged_at ? $p->pledged_at->format('Y-m-d') : $p->created_at->format('Y-m-d'))
            ->map->sum('amount')
            ->sortKeys();

        $latestPledges = $campaign->pledges()->with('member.church')->orderByDesc('created_at')->limit(10)->get();
        $topPledges = $campaign->pledges()->where('status', '!=', 'cancelled')->with('member.church')->orderByDesc('amount')->limit(10)->get();

        $recentContributions = PledgeContribution::whereIn('pledge_id', $pledges->pluck('id'))
            ->with('pledge.member')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentActivity = PledgeAuditLog::where('subject_type', PledgeCampaign::class)
            ->where('subject_id', $campaign->id)
            ->with('actor')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('portal.pledges.campaigns.show', compact(
            'campaign', 'pledges', 'growth', 'latestPledges', 'topPledges', 'recentContributions', 'recentActivity'
        ));
    }

    public function export(Request $request)
    {
        $this->authorizePledge('PLEDGES_VIEW_REPORTS');

        $campaigns = $this->scopedCampaignsQuery($request)->get();

        return Excel::download(new PledgeCampaignsExport($campaigns), 'pledge-campaigns-' . now()->format('Y-m-d_His') . '.xlsx');
    }
}
