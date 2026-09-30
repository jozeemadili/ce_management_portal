<?php

namespace App\Http\Controllers\API\Programs;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Member;
use App\Models\ProgramAuditLog;
use App\Services\InviteeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NewSoulController extends Controller
{
    use AuthorizesPrograms;

    /** Same hierarchy-scoping pattern used across every other module. */
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

    /**
     * The active New Souls worklist - members still tagged member_type=
     * new_soul. Once promoted (became_member), they naturally drop off this
     * list and simply live on as a regular member.
     */
    private function scopedVisitorsQuery(Request $request)
    {
        $churchIds = $this->scopedChurchIds();

        $query = Member::newSouls()->with(['church', 'firstVisitProgram', 'assignments.fromChurch', 'assignments.toChurch', 'assignments.assignedBy']);

        if (!is_null($churchIds)) {
            $query->whereIn('church_id', $churchIds);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('follow_up_status', $request->status);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->authorizeProgram('NEW_SOULS_VIEW');

        $visitors = $this->scopedVisitorsQuery($request)->orderByDesc('first_visit_date')->paginate(15)->withQueryString();

        $churchIds = $this->scopedChurchIds();
        $base = is_null($churchIds) ? Member::newSouls() : Member::newSouls()->whereIn('church_id', $churchIds);

        $stats = [
            'total' => (clone $base)->count(),
            'new' => (clone $base)->where('follow_up_status', 'new')->count(),
            'in_progress' => (clone $base)->whereIn('follow_up_status', ['contacted', 'follow_up', 'foundation_classes'])->count(),
            'connected' => (clone $base)->where('follow_up_status', 'connected_to_cell')->count(),
        ];

        $allChurches = Church::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'physical_location']);

        return view('portal.programs.new-souls.index', compact('visitors', 'stats', 'allChurches'));
    }

    public function updateStatus(Request $request, Member $visitor)
    {
        $this->authorizeProgram('NEW_SOULS_EDIT');

        $data = $request->validate([
            'status' => 'required|in:new,contacted,follow_up,foundation_classes,connected_to_cell,became_member,closed',
            'notes' => 'nullable|string',
        ]);

        $old = $visitor->toArray();

        if ($data['status'] === 'became_member' && $visitor->member_type === 'new_soul') {
            // Same as "Make member" on New Invitees: designation + login.
            if (!empty($data['notes'])) {
                $visitor->update(['notes' => trim(($visitor->notes ? $visitor->notes . "\n" : '') . $data['notes'])]);
            }

            return back()->with('success', app(InviteeManager::class)->makeMember($visitor, Auth::id()));
        }

        $visitor->follow_up_status = $data['status'];
        if (!empty($data['notes'])) {
            $visitor->notes = trim(($visitor->notes ? $visitor->notes . "\n" : '') . $data['notes']);
        }
        $visitor->save();

        ProgramAuditLog::record('new_soul.status_updated', $visitor, $old, $visitor->toArray());

        return back()->with('success', $visitor->first_name . "'s status updated to " . ucfirst(str_replace('_', ' ', $data['status'])) . '.');
    }

    /**
     * New Souls dashboard: today/week/month counts, breakdown by program and
     * by church, and the follow-up funnel. This is a historical "how many
     * new souls came in" view - unlike the worklist above, it is NOT
     * filtered to member_type=new_soul, so someone promoted to a full
     * member still counts under their original acquisition date and their
     * became_member funnel stage.
     */
    public function dashboard(Request $request)
    {
        $this->authorizeProgram('NEW_SOULS_VIEW');

        // Also opened from Church Setup (route new-invitees.dashboard): there it
        // is church-focused - pick a church (default: your own) and follow up
        // its assigned new souls from the worklist.
        $churchView = $request->routeIs('invitees.dashboard');

        $churchIds = $this->scopedChurchIds();
        $churches = Church::where('status', 'ACTIVE')
            ->when(!is_null($churchIds), fn ($q) => $q->whereIn('id', $churchIds))
            ->orderBy('name')->get(['id', 'name']);
        $selectedChurch = $request->filled('church')
            ? $churches->firstWhere('id', (int) $request->church)
            : ($churchView ? $churches->firstWhere('id', optional(Auth::user()->member)->church_id) : null);
        if ($selectedChurch) {
            $churchIds = collect([$selectedChurch->id]);
        }

        // Everyone who came as a new soul: still one, or promoted since (has a
        // first visit date). Some new souls (e.g. from program registration)
        // have no first visit date - their "first seen" is when they were added.
        $base = Member::where('is_training', false)
            ->where(fn ($q) => $q->whereNotNull('first_visit_date')->orWhere('member_type', 'new_soul'));
        if (!is_null($churchIds)) {
            $base->whereIn('church_id', $churchIds);
        }

        $firstSeen = 'COALESCE(first_visit_date, CAST(created_at AS DATE))';
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $counts = [
            'today' => (clone $base)->whereRaw("{$firstSeen} = ?", [$today])->count(),
            'week' => (clone $base)->whereRaw("{$firstSeen} >= ?", [$weekStart])->count(),
            'month' => (clone $base)->whereRaw("{$firstSeen} >= ?", [$monthStart])->count(),
            'total' => (clone $base)->count(),
        ];

        $byProgram = (clone $base)->select('first_visit_program_id', DB::raw('count(*) as total'))
            ->whereNotNull('first_visit_program_id')
            ->groupBy('first_visit_program_id')
            ->with('firstVisitProgram')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $byChurch = (clone $base)->select('church_id', DB::raw('count(*) as total'))
            ->groupBy('church_id')
            ->with('church')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $funnel = [];
        foreach (['new', 'contacted', 'follow_up', 'foundation_classes', 'connected_to_cell', 'became_member', 'closed'] as $status) {
            $funnel[$status] = (clone $base)->where('follow_up_status', $status)->count();
        }

        // Worklist: new souls still being followed up, longest waiting first.
        $worklist = Member::newSouls()
            ->with(['church', 'firstVisitProgram', 'assignments.fromChurch', 'assignments.toChurch', 'assignments.assignedBy'])
            ->when(!is_null($churchIds), fn ($q) => $q->whereIn('church_id', $churchIds))
            ->whereNotIn('follow_up_status', ['became_member', 'closed'])
            ->orderBy('first_visit_date')->orderBy('id')
            ->limit(25)
            ->get();
        $allChurches = Church::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'physical_location']);

        return view('portal.programs.new-souls.dashboard', compact(
            'counts', 'byProgram', 'byChurch', 'funnel', 'worklist', 'churches', 'selectedChurch', 'churchView', 'allChurches'
        ));
    }
}
