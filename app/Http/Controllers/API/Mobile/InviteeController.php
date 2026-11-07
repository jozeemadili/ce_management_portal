<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\API\Church\InviteeController as PortalInvitees;
use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Concerns\ScopesChurches;
use App\Http\Controllers\Controller;
use App\Models\CellGroup;
use App\Models\Member;
use App\Models\MemberFoundationClass;
use App\Services\InviteeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Mobile app: follow-up of new souls (the portal's Church Setup > New
 * Invitees) - status, cell group, Foundation School classes 1-7, baptism,
 * notes, and making them members. Same rules as the portal.
 */
class InviteeController extends Controller
{
    use AuthorizesPrograms, ScopesChurches;

    public function __construct(private InviteeManager $invitees)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeProgram('NEW_SOULS_VIEW');

        $churchIds = $this->visibleChurches()->pluck('id');
        $base = Member::newSouls()->whereIn('church_id', $churchIds);

        $page = (clone $base)
            ->with(['church', 'firstVisitProgram', 'invitedByMember', 'foundationClasses', 'cell_groups'])
            ->when($request->filled('status'), fn ($q) => $q->where('follow_up_status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%' . mb_strtolower(trim($request->q)) . '%';
                $q->where(fn ($w) => $w->whereRaw('LOWER(first_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(location) LIKE ?', [$like])
                    ->orWhere('phone', 'like', $like));
            })
            ->orderByDesc('first_visit_date')->orderByDesc('id')
            ->paginate(20);

        return response()->json([
            'data' => $page->getCollection()->map(fn ($m) => $this->payload($m))->values(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
            'stats' => [
                'total' => (clone $base)->count(),
                'in_foundation' => (clone $base)->has('foundationClasses')->count(),
                'in_cell' => (clone $base)->has('cell_groups')->count(),
                'baptized' => (clone $base)->where('baptism_status', 'yes')->count(),
            ],
            'statuses' => $this->statuses(),
            'foundation_total' => MemberFoundationClass::TOTAL,
        ]);
    }

    /** One person with the cell groups of their church to choose from. */
    public function show(Member $invitee)
    {
        $this->authorizeProgram('NEW_SOULS_VIEW');
        $this->assertMine($invitee);

        $invitee->load(['church', 'firstVisitProgram', 'invitedByMember', 'foundationClasses', 'cell_groups']);

        return response()->json([
            'data' => $this->payload($invitee, true),
            'cells' => CellGroup::where('church_id', $invitee->church_id)->orderBy('name')->get(['id', 'name']),
            'statuses' => $this->statuses(),
            'foundation_total' => MemberFoundationClass::TOTAL,
        ]);
    }

    /**
     * Body: follow_up_status, cell_group_id, classes {"1": "2026-11-01", ...}
     * (the classes done, date optional), baptism_status, baptism_date,
     * location, phone, email, note.
     */
    public function update(Request $request, Member $invitee)
    {
        $this->authorizeProgram('NEW_SOULS_EDIT');
        $this->assertMine($invitee);

        $data = $request->validate([
            'follow_up_status' => 'required|in:' . implode(',', array_keys(PortalInvitees::STATUSES)),
            'classes' => 'nullable|array',
            'classes.*' => 'nullable|date|before_or_equal:today',
            'cell_group_id' => 'nullable|integer|exists:cell_groups,id',
            'baptism_status' => 'nullable|in:yes,no',
            'baptism_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'note' => 'nullable|string|max:1000',
        ]);

        $classes = collect($data['classes'] ?? [])
            ->mapWithKeys(fn ($date, $no) => [(int) $no => $date])
            ->filter(fn ($date, $no) => $no >= 1 && $no <= MemberFoundationClass::TOTAL)
            ->all();

        $becameMember = $this->invitees->updateFollowUp($invitee, $data, $classes, $data['cell_group_id'] ?? null,
            $data['follow_up_status'], $data['note'] ?? null, Auth::id());

        $invitee->refresh()->load(['church', 'firstVisitProgram', 'invitedByMember', 'foundationClasses', 'cell_groups']);

        return response()->json([
            'message' => $becameMember ?? $invitee->first_name . "'s follow-up was updated.",
            'became_member' => $becameMember !== null,
            'data' => $this->payload($invitee, true),
        ]);
    }

    public function makeMember(Member $invitee)
    {
        $this->authorizeProgram('NEW_SOULS_EDIT');
        $this->assertMine($invitee);

        return response()->json(['message' => $this->invitees->makeMember($invitee, Auth::id())]);
    }

    private function payload(Member $m, bool $full = false): array
    {
        $done = $m->foundationClasses;
        $cell = $m->cell_groups->first();
        $status = $m->follow_up_status ?: 'new';

        return array_merge([
            'id' => $m->id,
            'name' => trim($m->first_name . ' ' . $m->last_name),
            'first_name' => $m->first_name,
            'last_name' => $m->last_name,
            'phone' => $m->phone,
            'email' => $m->email,
            'location' => $m->location,
            'church' => optional($m->church)->name,
            'member_type' => $m->member_type,
            'first_visit_date' => optional($m->first_visit_date)->toDateString(),
            'first_visit_program' => optional($m->firstVisitProgram)->name,
            'invited_by' => $m->invitedByMember ? trim($m->invitedByMember->first_name . ' ' . $m->invitedByMember->last_name) : $m->invited_by,
            'follow_up_status' => $status,
            'follow_up_label' => PortalInvitees::STATUSES[$status] ?? ucfirst(str_replace('_', ' ', $status)),
            'foundation' => [
                'done' => $done->count(),
                'total' => MemberFoundationClass::TOTAL,
                'completed' => $m->foundation_clases === 'yes' || $done->count() >= MemberFoundationClass::TOTAL,
                'classes' => $done->map(fn ($c) => ['no' => $c->class_no, 'date' => optional($c->completed_on)->toDateString()])->values(),
            ],
            'cell' => $cell ? ['id' => $cell->id, 'name' => $cell->name] : null,
            'baptism_status' => $m->baptism_status,
            'baptism_date' => optional($m->baptism_date)->toDateString(),
        ], $full ? ['notes' => $m->notes] : []);
    }

    private function statuses(): array
    {
        return collect(PortalInvitees::STATUSES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()->all();
    }

    private function assertMine(Member $invitee): void
    {
        abort_unless($this->visibleChurches()->contains('id', $invitee->church_id), 403, 'This person is not at one of your churches.');
    }
}
