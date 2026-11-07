<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Models\Church;
use App\Models\Program;
use App\Services\SelfCheckIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

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
            'invited_by' => 'nullable|string|max:120',
        ]);

        abort_unless(SelfCheckIn::isValidIdentifier($data['identifier']), 422);

        // Someone who registered meanwhile (or pressed back) isn't duplicated.
        if ($member = $this->selfCheckIn->findPerson($data['identifier'], $program)) {
            return $this->done($program, $church, $member, $this->selfCheckIn->checkInMember($program, $church, $member), false);
        }

        $result = $this->selfCheckIn->registerNewSoul($program, $church, $data);

        return $this->done($program, $church, $result['member'], $result, true);
    }

    /**
     * "Did you come with a new soul?" - people the person who just checked
     * in brought along. Who brought them comes from the signed token issued
     * on the welcome page (valid 30 minutes), never from a plain id.
     */
    public function brought(Request $request, string $token, ?int $church = null)
    {
        [$program, $church] = $this->resolve($token, $church);

        // Forms carry a spare empty row.
        $request->merge(['people' => array_values(array_filter(
            (array) $request->input('people', []),
            fn ($p) => is_array($p) && trim((string) ($p['first_name'] ?? '')) !== ''
        ))]);

        $data = $request->validate([
            'by' => 'required|string',
            'people' => 'required|array|min:1|max:10',
            'people.*.first_name' => 'required|string|max:100',
            'people.*.last_name' => 'nullable|string|max:100',
            'people.*.phone' => 'nullable|string|max:30',
            'people.*.location' => 'nullable|string|max:255',
            'people.*.gender' => 'nullable|in:male,female',
        ], ['people.required' => 'Enter at least one first name.']);

        $broughtBy = $this->memberFromToken($data['by'], $program, $church);
        abort_unless($broughtBy, 403, 'This page has expired. Please check in again.');

        $result = $this->selfCheckIn->registerBroughtSouls($program, $church, $broughtBy, $data['people']);

        return view('guest.checkin', [
            'step' => 'brought',
            'result' => $result,
            'firstName' => ucfirst(mb_strtolower($broughtBy->first_name)),
            'broughtToken' => $data['by'],
            'status' => ['open' => true, 'message' => ''],
        ] + compact('program', 'church'));
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
                ? Church::whereIn('id', $program->churchIds())->orderBy('name')->get()
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
            // Lets them add the people they came with (see brought()).
            'broughtToken' => $member && $result['ok'] ? $this->tokenFor($member, $program, $church) : null,
            'status' => ['open' => true, 'message' => ''],
        ] + compact('program', 'church'));
    }

    private function tokenFor(\App\Models\Member $member, Program $program, ?Church $church): string
    {
        return Crypt::encryptString(json_encode([
            'm' => $member->id, 'p' => $program->id, 'c' => optional($church)->id, 'exp' => now()->addMinutes(30)->timestamp,
        ]));
    }

    private function memberFromToken(string $token, Program $program, ?Church $church): ?\App\Models\Member
    {
        try {
            $data = json_decode(Crypt::decryptString($token), true);
        } catch (\Throwable $e) {
            return null;
        }

        $valid = is_array($data)
            && ($data['p'] ?? null) === $program->id
            && ($data['c'] ?? null) === optional($church)->id
            && ($data['exp'] ?? 0) >= now()->timestamp;

        return $valid ? \App\Models\Member::find($data['m'] ?? 0) : null;
    }
}
