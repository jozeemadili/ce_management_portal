<?php

namespace App\Http\Controllers\API\Programs;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Member;
use App\Models\NewSoulFollowup;
use App\Models\ProgramAttendance;
use App\Models\ProgramOccurrence;
use App\Models\ServiceChurchTime;
use App\Services\ChurchServices;
use App\Services\NewSoulFollowupSms;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Church services (Sunday / Wednesday / Friday service...):
 *  - service times per church
 *  - check-in: scan a member's QR or search by name, add new souls they
 *    brought, walk-in new souls
 *  - dashboard for pastors, follow-up SMS to new souls
 *  - the member's own check-in QR
 */
class ChurchServiceController extends Controller
{
    use AuthorizesPrograms;

    public function __construct(private ChurchServices $services)
    {
    }

    /* =================================================================
     | SERVICE TIMES
     |=================================================================*/

    public function times()
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $services = $this->services->services();
        $churches = $this->visibleChurches();
        $overrides = ServiceChurchTime::whereIn('church_id', $churches->pluck('id'))->get()
            ->keyBy(fn ($t) => $t->program_id . '-' . $t->church_id);

        return view('portal.programs.services.times', compact('services', 'churches', 'overrides'));
    }

    public function saveTimes(Request $request)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $data = $request->validate([
            'times' => 'array',
            'times.*.*.start' => 'nullable|date_format:H:i',
            'times.*.*.end' => 'nullable|date_format:H:i',
        ]);

        $services = $this->services->services()->keyBy('id');
        $churchIds = $this->visibleChurches()->pluck('id')->all();
        $saved = 0;

        foreach ($data['times'] ?? [] as $serviceId => $perChurch) {
            $service = $services->get((int) $serviceId);
            if (!$service) {
                continue;
            }

            foreach ($perChurch as $churchId => $time) {
                if (!in_array((int) $churchId, $churchIds, true)) {
                    continue;
                }

                $start = $time['start'] ?? null;
                $end = $time['end'] ?? null;
                $isDefault = (!$start && !$end)
                    || ($start === substr($service->start_time, 0, 5) && $end === substr($service->end_time, 0, 5));

                if ($isDefault) {
                    $saved += ServiceChurchTime::where('program_id', $service->id)->where('church_id', $churchId)->delete();
                    continue;
                }

                if (!$start || !$end) {
                    throw ValidationException::withMessages(['times' => 'Give both a start and an end time (or leave both empty to use the default).']);
                }

                ServiceChurchTime::updateOrCreate(
                    ['program_id' => $service->id, 'church_id' => $churchId],
                    ['start_time' => $start . ':00', 'end_time' => $end . ':00', 'updated_by' => Auth::id()]
                );
                $saved++;
            }
        }

        return back()->with('success', 'Service times saved.');
    }

    /* =================================================================
     | CHECK-IN
     |=================================================================*/

    public function checkin(Request $request)
    {
        $this->authorizeProgram('ATTENDANCE_RECORD');

        $this->services->sync();

        $churches = $this->visibleChurches();
        $church = $churches->firstWhere('id', (int) $request->get('church', session('checkin_church_id', optional(Auth::user()->member)->church_id)))
            ?? $churches->first();

        abort_unless($church, 404, 'No church available for check-in.');
        session(['checkin_church_id' => $church->id]);

        $today = $this->services->todayFor($church);
        $current = $today->firstWhere('state', 'open');
        if ($request->filled('service')) {
            $current = $today->first(fn ($s) => $s->service->id === (int) $request->service) ?? $current;
        }

        $occurrence = null;
        $counts = null;
        $recent = collect();
        if ($current && $current->state === 'open') {
            $occurrence = $current->occurrence ?? $this->services->occurrenceFor($current->service, $church, now()->startOfDay());
            $occurrence->setRelation('program', $current->service)->setRelation('church', $church);
            $counts = $this->services->counts($occurrence);
            $recent = $this->recentCheckIns($occurrence);
        }

        return view('portal.programs.services.checkin', compact('churches', 'church', 'today', 'current', 'occurrence', 'counts', 'recent'));
    }

    /** Church members (and new souls) matching a name/phone, with their status. */
    public function searchMembers(Request $request, ProgramOccurrence $occurrence)
    {
        $this->authorizeProgram('ATTENDANCE_RECORD');
        $this->assertVisible($occurrence);

        $q = trim((string) $request->get('q'));
        $members = Member::where('church_id', $occurrence->church_id)
            ->whereIn('member_type', ['member', 'new_soul'])
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . mb_strtolower($q) . '%';
                $digits = preg_replace('/\D/', '', $q);
                $query->where(function ($w) use ($like, $digits) {
                    $w->whereRaw('LOWER(first_name) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                      ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", [$like]);
                    if (strlen($digits) >= 3) {
                        $w->orWhere('phone', 'like', '%' . $digits . '%');
                    }
                });
            })
            ->orderBy('first_name')->orderBy('last_name')
            ->limit(30)
            ->get(['id', 'first_name', 'last_name', 'phone', 'member_type']);

        $status = ProgramAttendance::where('occurrence_id', $occurrence->id)
            ->whereIn('member_id', $members->pluck('id'))
            ->get(['member_id', 'attendance_status', 'checked_in_at'])
            ->keyBy('member_id');

        return response()->json($members->map(fn ($m) => [
            'id' => $m->id,
            'name' => trim($m->first_name . ' ' . $m->last_name),
            'phone' => $m->phone,
            'new_soul' => $m->member_type === 'new_soul',
            'status' => optional($status->get($m->id))->attendance_status,
            'time' => optional(optional($status->get($m->id))->checked_in_at)->format('H:i'),
        ]));
    }

    public function checkInMember(Request $request, ProgramOccurrence $occurrence, Member $member)
    {
        $this->authorizeProgram('ATTENDANCE_RECORD');
        $this->assertVisible($occurrence);

        return $this->checkInResponse($occurrence, $member, 'manual');
    }

    /** From the in-page QR scanner: the scanned text holds the member's token. */
    public function scanCheckIn(Request $request, ProgramOccurrence $occurrence)
    {
        $this->authorizeProgram('ATTENDANCE_RECORD');
        $this->assertVisible($occurrence);

        $member = $this->memberFromScan((string) $request->input('code'));
        if (!$member) {
            return response()->json(['message' => 'This QR code is not a member check-in code.'], 422);
        }

        return $this->checkInResponse($occurrence, $member, 'qr');
    }

    /**
     * A staff member scanned a member's QR with their phone camera: check
     * the member in to the service open now at the staff's check-in church.
     */
    public function scanLanding(string $token)
    {
        $this->authorizeProgram('ATTENDANCE_RECORD');

        $member = Member::with('church')->where('checkin_token', $token)->first();
        abort_unless($member, 404, 'This QR code is not a member check-in code.');

        $this->services->sync();

        $churchId = session('checkin_church_id', optional(Auth::user()->member)->church_id) ?? $member->church_id;
        $church = Church::find($churchId) ?? $member->church;
        $open = $church ? $this->services->todayFor($church)->firstWhere('state', 'open') : null;

        $attendance = null;
        $already = false;
        $error = null;
        $occurrence = null;

        if ($open) {
            $occurrence = $open->occurrence ?? $this->services->occurrenceFor($open->service, $church, now()->startOfDay());
            $occurrence->setRelation('program', $open->service)->setRelation('church', $church);
            try {
                [$attendance, $already] = $this->services->checkIn($occurrence, $member, 'qr', Auth::id());
            } catch (ValidationException $e) {
                $error = collect($e->errors())->flatten()->first();
            }
        } else {
            $error = 'No service is open for check-in at ' . optional($church)->name . ' right now.';
        }

        return view('portal.programs.services.scan-result', compact('member', 'church', 'occurrence', 'attendance', 'already', 'error'));
    }

    /** New souls who came with a member (member_id) or on their own. */
    public function addNewSouls(Request $request, ProgramOccurrence $occurrence)
    {
        $this->authorizeProgram('NEW_SOULS_CREATE');
        $this->assertVisible($occurrence);

        // Forms may carry spare empty rows.
        $request->merge(['people' => array_values(array_filter(
            (array) $request->input('people', []),
            fn ($p) => is_array($p) && trim((string) ($p['first_name'] ?? '')) !== ''
        ))]);

        $data = $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'people' => 'required|array|min:1|max:20',
            'people.*.first_name' => 'required|string|max:100',
            'people.*.last_name' => 'nullable|string|max:100',
            'people.*.phone' => 'nullable|string|max:30',
            'people.*.gender' => 'nullable|in:male,female',
        ]);

        $broughtBy = isset($data['member_id']) ? Member::find($data['member_id']) : null;
        $souls = $this->services->addNewSouls($occurrence->load('program', 'church'), $data['people'], $broughtBy, Auth::id());

        $message = $souls->count() . ' new soul' . ($souls->count() === 1 ? '' : 's') . ' recorded'
            . ($broughtBy ? ', brought by ' . trim($broughtBy->first_name . ' ' . $broughtBy->last_name) : '') . '.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'counts' => $this->services->counts($occurrence)]);
        }

        return redirect()->route('services.checkin', ['church' => $occurrence->church_id])->with('success', $message);
    }

    /* =================================================================
     | DASHBOARD
     |=================================================================*/

    public function dashboard(Request $request)
    {
        $this->authorizeProgram('PROGRAM_REPORTS_VIEW');

        $this->services->sync();

        $churches = $this->visibleChurches();
        $services = $this->services->services();
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : now()->subWeeks(8)->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : now()->endOfDay();
        $churchIds = $request->filled('church') ? [(int) $request->church] : $churches->pluck('id')->all();
        $churchIds = array_values(array_intersect($churchIds, $churches->pluck('id')->all()));

        $occurrences = ProgramOccurrence::with(['program', 'church'])
            ->whereIn('church_id', $churchIds)
            ->whereIn('program_id', $services->pluck('id'))
            ->when($request->filled('service'), fn ($q) => $q->where('program_id', (int) $request->service))
            ->whereBetween('occurrence_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('occurrence_date')
            ->get();

        // Counts for all occurrences in one query.
        $rows = ProgramAttendance::whereIn('program_attendances.occurrence_id', $occurrences->pluck('id'))
            ->join('members', 'members.id', '=', 'program_attendances.member_id')
            ->selectRaw('program_attendances.occurrence_id, members.member_type, program_attendances.attendance_status, COUNT(*) as total')
            ->groupBy('program_attendances.occurrence_id', 'members.member_type', 'program_attendances.attendance_status')
            ->get()
            ->groupBy('occurrence_id');

        $followups = NewSoulFollowup::whereIn('occurrence_id', $occurrences->pluck('id'))
            ->selectRaw('occurrence_id, COUNT(*) as sent, COUNT(submitted_at) as replies')
            ->groupBy('occurrence_id')->get()->keyBy('occurrence_id');

        $occurrences->each(function ($o) use ($rows, $followups) {
            $r = $rows->get($o->id, collect());
            $count = fn ($type, $status) => (int) optional($r->first(fn ($x) => $x->member_type === $type && $x->attendance_status === $status))->total;
            $stats = [
                'present' => $count('member', 'present'),
                'late' => $count('member', 'late'),
                'absent' => $count('member', 'absent'),
                'new_souls' => (int) $r->where('member_type', 'new_soul')->sum('total'),
            ];
            $stats['attended'] = $stats['present'] + $stats['late'];
            $o->stats = $stats;
            $o->followup = $followups->get($o->id);
        });

        $totals = [
            'services' => $occurrences->count(),
            'attended' => $occurrences->sum(fn ($o) => $o->stats['attended']),
            'late' => $occurrences->sum(fn ($o) => $o->stats['late']),
            'absent' => $occurrences->sum(fn ($o) => $o->stats['absent']),
            'new_souls' => $occurrences->sum(fn ($o) => $o->stats['new_souls']),
        ];
        $closed = $occurrences->filter(fn ($o) => $o->isClosed());
        $totals['average'] = $closed->count() ? round($closed->avg(fn ($o) => $o->stats['attended'])) : null;

        // Trend: per date, summed over the selected churches/services.
        $trend = $occurrences->groupBy(fn ($o) => $o->occurrence_date->format('Y-m-d'))
            ->sortKeys()
            ->map(fn ($group, $date) => [
                'label' => Carbon::parse($date)->format('d M'),
                'attended' => $group->sum(fn ($o) => $o->stats['attended']),
                'new_souls' => $group->sum(fn ($o) => $o->stats['new_souls']),
            ])->values();

        $testimonies = NewSoulFollowup::with(['member.church', 'occurrence.program'])
            ->whereNotNull('submitted_at')
            ->whereHas('member', fn ($q) => $q->whereIn('church_id', $churchIds))
            ->orderByDesc('submitted_at')
            ->limit(10)
            ->get();

        return view('portal.programs.services.dashboard', compact(
            'churches', 'services', 'occurrences', 'totals', 'trend', 'testimonies', 'from', 'to'
        ));
    }

    /** New souls of one service who haven't had the follow-up SMS yet get it now. */
    public function sendFollowups(ProgramOccurrence $occurrence, NewSoulFollowupSms $followups)
    {
        $this->authorizeProgram('NEW_SOULS_CREATE');

        $this->assertVisible($occurrence);

        $queued = $followups->queue($occurrence);

        if ($queued === 0) {
            return back()->withErrors(['sms' => 'No new souls to send to: everyone already got the follow-up SMS, has no phone number, or there is no active "New soul follow-up" SMS template.']);
        }

        return back()->with('success', "Follow-up SMS is being sent to {$queued} new soul" . ($queued === 1 ? '' : 's') . '.');
    }

    /* =================================================================
     | MY CHECK-IN QR
     |=================================================================*/

    public function myQr()
    {
        $member = Auth::user()->member;
        abort_unless($member, 404, 'Your account is not linked to a member record.');

        $url = route('services.scan', $member->checkinToken());
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(240)->margin(1)->generate($url);

        return view('portal.programs.services.my-qr', compact('member', 'qrSvg'));
    }

    /* =================================================================
     | HELPERS
     |=================================================================*/

    private function checkInResponse(ProgramOccurrence $occurrence, Member $member, string $method)
    {
        $occurrence->load('program', 'church');
        [$attendance, $already] = $this->services->checkIn($occurrence, $member, $method, Auth::id());
        $name = trim($member->first_name . ' ' . $member->last_name);

        return response()->json([
            'member' => ['id' => $member->id, 'name' => $name, 'new_soul' => $member->member_type === 'new_soul', 'church_id' => $member->church_id],
            'status' => $attendance->attendance_status,
            'time' => optional($attendance->checked_in_at)->format('H:i'),
            'already' => $already,
            'message' => $already
                ? "{$name} was already checked in at " . optional($attendance->checked_in_at)->format('H:i') . '.'
                : "{$name} checked in" . ($attendance->attendance_status === 'late' ? ' (late)' : '') . '.',
            'other_church' => (int) $member->church_id !== (int) $occurrence->church_id,
            'counts' => $this->services->counts($occurrence),
        ]);
    }

    private function assertVisible(ProgramOccurrence $occurrence): void
    {
        abort_unless(
            $occurrence->church_id && $this->visibleChurches()->contains('id', $occurrence->church_id),
            403,
            'This service is not at one of your churches.'
        );
    }

    private function memberFromScan(string $code): ?Member
    {
        $code = trim($code);
        // Our QR holds .../services/scan/{token}; accept a bare token too.
        if (preg_match('~/services/scan/([A-Za-z0-9]{20,40})~', $code, $m)) {
            $code = $m[1];
        }

        return preg_match('/^[A-Za-z0-9]{20,40}$/', $code) ? Member::where('checkin_token', $code)->first() : null;
    }

    private function recentCheckIns(ProgramOccurrence $occurrence): Collection
    {
        return ProgramAttendance::with('member')
            ->where('occurrence_id', $occurrence->id)
            ->whereIn('attendance_status', ['present', 'late'])
            ->orderByDesc('checked_in_at')
            ->limit(15)
            ->get();
    }

    /**
     * Churches the user may work with: their own church and every church
     * under it; the top-level church (or a user without a member record)
     * sees all active churches.
     */
    private function visibleChurches(): Collection
    {
        $all = Church::where('status', 'ACTIVE')->orderBy('name')->get();
        $member = Auth::user()->member;
        $own = $member ? $all->firstWhere('id', $member->church_id) : null;

        if (!$own || is_null($own->parent_church_id)) {
            return $all;
        }

        $byParent = $all->groupBy('parent_church_id');
        $ids = [$own->id];
        for ($i = 0; $i < count($ids); $i++) {
            foreach ($byParent->get($ids[$i], collect()) as $child) {
                $ids[] = $child->id;
            }
        }

        return $all->whereIn('id', $ids)->values();
    }
}
