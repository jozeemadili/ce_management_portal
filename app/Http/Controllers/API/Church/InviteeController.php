<?php

namespace App\Http\Controllers\API\Church;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Concerns\ScopesChurches;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Member;
use App\Models\ProgramAuditLog;
use App\Models\ProgramOccurrence;
use App\Services\InviteeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Church Setup > New Invitees: new souls assigned to the user's churches.
 * Pastors follow them up (status, foundation classes, baptism, notes),
 * re-assign them to another church (kept in the history) and make them
 * members when ready.
 */
class InviteeController extends Controller
{
    use AuthorizesPrograms, ScopesChurches;

    public function __construct(private InviteeManager $invitees)
    {
    }

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'follow_up' => 'Follow-up',
        'foundation_classes' => 'Foundation classes',
        'connected_to_cell' => 'Connected to cell',
        'closed' => 'Closed',
    ];

    public function index(Request $request)
    {
        $this->authorizeProgram('NEW_SOULS_VIEW');

        $churches = $this->visibleChurches();
        $churchIds = $churches->pluck('id');

        $base = Member::newSouls()->whereIn('church_id', $churchIds);

        $invitees = (clone $base)
            ->with(['church', 'firstVisitProgram', 'invitedByMember', 'assignments.fromChurch', 'assignments.toChurch', 'assignments.assignedBy'])
            ->when($request->filled('church'), fn ($q) => $q->where('church_id', (int) $request->church))
            ->when($request->filled('status'), fn ($q) => $q->where('follow_up_status', $request->status))
            ->when($request->get('assigned') === 'no', fn ($q) => $q->doesntHave('assignments'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%' . mb_strtolower($request->q) . '%';
                $q->where(fn ($w) => $w->whereRaw('LOWER(first_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(location) LIKE ?', [$like])
                    ->orWhere('phone', 'like', $like));
            })
            ->orderByDesc('first_visit_date')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $stats = [
            'total' => (clone $base)->count(),
            'unassigned' => (clone $base)->doesntHave('assignments')->count(),
            'foundation' => (clone $base)->where('foundation_clases', 'yes')->count(),
            'baptized' => (clone $base)->where('baptism_status', 'yes')->count(),
        ];

        $allChurches = Church::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'physical_location']);

        return view('portal.churches.invitees.index', compact('invitees', 'stats', 'churches', 'allChurches'));
    }

    public function update(Request $request, Member $invitee)
    {
        $this->authorizeProgram('NEW_SOULS_EDIT');
        $this->assertMine($invitee);

        $data = $request->validate([
            'follow_up_status' => 'required|in:' . implode(',', array_keys(self::STATUSES)),
            'foundation_clases' => 'nullable|in:yes,no',
            'foundation_clases_date' => 'nullable|date',
            'baptism_status' => 'nullable|in:yes,no',
            'baptism_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'note' => 'nullable|string|max:1000',
        ]);

        $old = $invitee->toArray();
        $note = trim((string) ($data['note'] ?? ''));
        unset($data['note']);
        if ($note !== '') {
            $data['notes'] = trim(($invitee->notes ? $invitee->notes . "\n" : '') . now()->format('d M Y') . ' - ' . $note);
        }

        $invitee->update($data);
        ProgramAuditLog::record('new_soul.follow_up_updated', $invitee, $old, $invitee->toArray());

        return back()->with('success', $invitee->first_name . "'s follow-up was updated.");
    }

    public function assign(Request $request, Member $invitee)
    {
        $this->authorizeProgram('NEW_SOULS_EDIT');
        $this->assertMine($invitee);

        $data = $request->validate([
            'church_id' => 'required|exists:churches,id',
            'note' => 'nullable|string|max:500',
            'occurrence_id' => 'nullable|exists:program_occurrences,id',
        ]);

        $to = Church::findOrFail($data['church_id']);
        $occurrence = isset($data['occurrence_id']) ? ProgramOccurrence::find($data['occurrence_id']) : null;
        $this->invitees->assign($invitee, $to, $occurrence, $data['note'] ?? null, Auth::id());

        return back()->with('success', trim($invitee->first_name . ' ' . $invitee->last_name) . ' assigned to ' . $to->name . '.');
    }

    public function makeMember(Member $invitee)
    {
        $this->authorizeProgram('NEW_SOULS_EDIT');
        $this->assertMine($invitee);

        return back()->with('success', $this->invitees->makeMember($invitee, Auth::id()));
    }

    /** Only invitees now at one of the user's churches (or who came to a service of theirs). */
    private function assertMine(Member $invitee): void
    {
        abort_unless($this->visibleChurches()->contains('id', $invitee->church_id), 403, 'This person is not at one of your churches.');
    }
}
