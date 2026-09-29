<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Models\Church;
use App\Models\Program;
use App\Services\SelfCheckIn;
use Illuminate\Http\Request;

/**
 * Self check-in from a printed QR poster: public pages (no login) behind
 * /checkin/{token}[/{church}], and the poster itself for staff.
 */
class SelfCheckInController extends Controller
{
    use AuthorizesPrograms;

    public function __construct(private SelfCheckIn $selfCheckIn)
    {
    }

    /* ---------------- public ---------------- */

    public function show(string $token, ?int $church = null)
    {
        [$program, $church] = $this->resolve($token, $church);
        $status = $this->selfCheckIn->status($program, $church);
        $details = $status['open'] ? [] : $this->selfCheckIn->details($program, $church);

        return view('guest.checkin', ['step' => 'identify'] + compact('program', 'church', 'status', 'details'));
    }

    public function identify(Request $request, string $token, ?int $church = null)
    {
        [$program, $church] = $this->resolve($token, $church);
        $data = $request->validate(['identifier' => 'required|string|max:120']);

        if (!SelfCheckIn::isValidIdentifier($data['identifier'])) {
            return back()->withInput()->withErrors(['identifier' => 'Enter a valid phone number (e.g. 0712345678) or email address.']);
        }

        $member = $this->selfCheckIn->findPerson($data['identifier'], $program);

        if (!$member) {
            // Not known yet: ask for their name to record them as a new soul.
            return view('guest.checkin', [
                'step' => 'new',
                'identifier' => trim($data['identifier']),
                'status' => $this->selfCheckIn->status($program, $church),
            ] + compact('program', 'church'));
        }

        $result = $this->selfCheckIn->checkInMember($program, $church, $member);

        return $this->done($program, $church, $member, $result, false);
    }

    public function register(Request $request, string $token, ?int $church = null)
    {
        [$program, $church] = $this->resolve($token, $church);
        $data = $request->validate([
            'identifier' => 'required|string|max:120',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'gender' => 'required|in:male,female',
            'location' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
        ]);

        abort_unless(SelfCheckIn::isValidIdentifier($data['identifier']), 422);

        // Someone who registered meanwhile (or pressed back) isn't duplicated.
        if ($member = $this->selfCheckIn->findPerson($data['identifier'], $program)) {
            return $this->done($program, $church, $member, $this->selfCheckIn->checkInMember($program, $church, $member), false);
        }

        $result = $this->selfCheckIn->registerNewSoul($program, $church, $data);

        return $this->done($program, $church, $result['member'], $result, true);
    }

    /* ---------------- staff: printable poster ---------------- */

    public function poster(Request $request, Program $program)
    {
        $this->authorizeProgram('PROGRAMS_VIEW');

        $isService = $this->selfCheckIn->isChurchService($program);
        $churches = collect();
        $church = null;

        if ($isService) {
            $churches = $program->scope === 'church'
                ? Church::whereKey($program->church_id)->get()
                : Church::where('status', 'ACTIVE')->orderBy('name')->get();
            $church = $churches->firstWhere('id', (int) $request->get('church', optional(auth()->user()->member)->church_id))
                ?? $churches->first();
        }

        $url = $church
            ? route('self-checkin.church', [$program->checkinToken(), $church->id])
            : route('self-checkin.show', $program->checkinToken());
        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(420)->margin(1)->errorCorrection('M')->generate($url);

        return view('portal.programs.checkin-poster', compact('program', 'isService', 'churches', 'church', 'url', 'qrSvg'));
    }

    /* ---------------- helpers ---------------- */

    private function resolve(string $token, ?int $churchId): array
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{16}$/', $token), 404);
        $program = Program::where('checkin_token', $token)->firstOrFail();
        $church = $churchId ? Church::where('status', 'ACTIVE')->find($churchId) : null;
        abort_if($churchId && !$church, 404);

        return [$program, $church];
    }

    private function done(Program $program, ?Church $church, ?\App\Models\Member $member, array $result, bool $isNew)
    {
        return view('guest.checkin', [
            'step' => 'done',
            'result' => $result,
            'isNew' => $isNew,
            'firstName' => $member ? ucfirst(mb_strtolower($member->first_name)) : '',
            'status' => ['open' => true, 'message' => ''],
        ] + compact('program', 'church'));
    }
}
