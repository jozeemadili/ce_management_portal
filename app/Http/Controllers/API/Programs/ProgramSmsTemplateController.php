<?php

namespace App\Http\Controllers\API\Programs;

use App\Http\Controllers\Concerns\AuthorizesPrograms;
use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramSmsTemplate;
use App\Services\ProgramSmsNotifier;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Programs > SMS Templates: create/edit the registration, payment and
 * reminder texts (default for all programs, or for one program), send a
 * test SMS, and send a program's reminder to everyone registered.
 */
class ProgramSmsTemplateController extends Controller
{
    use AuthorizesPrograms;

    public function index()
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $templates = ProgramSmsTemplate::with('program')
            ->orderByRaw('CASE WHEN program_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('type')
            ->get()
            ->groupBy('type');

        $programs = Program::where('classification', 'special')
            ->orderByDesc('start_date')
            ->get(['id', 'name', 'start_date']);

        // One gateway call per 5 minutes at most.
        $balance = Cache::remember('sms-balance', 300, fn () => app(SmsService::class)->balance());

        return view('portal.programs.sms-templates.index', compact('templates', 'programs', 'balance'));
    }

    public function store(Request $request)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $data = $this->validated($request);
        ProgramSmsTemplate::create($data + ['created_by' => Auth::id(), 'updated_by' => Auth::id()]);

        return redirect()->route('program-sms.index')->with('success', 'SMS template saved.');
    }

    public function update(Request $request, ProgramSmsTemplate $template)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $template->update($this->validated($request, $template) + ['updated_by' => Auth::id()]);

        return redirect()->route('program-sms.index')->with('success', 'SMS template updated.');
    }

    public function destroy(ProgramSmsTemplate $template)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $template->delete();

        return redirect()->route('program-sms.index')->with('success', 'SMS template deleted.');
    }

    /**
     * Sends the template text, filled with example values, to one number.
     */
    public function test(Request $request)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        $data = $request->validate([
            'phone' => 'required|string|max:30',
            'body' => 'required|string|max:1000',
        ]);

        if (!SmsService::normalisePhone($data['phone'])) {
            return response()->json(['message' => 'Enter a valid phone number, e.g. 0712345678.'], 422);
        }

        $result = app(SmsService::class)->send($data['phone'], ProgramSmsTemplate::example($data['body']));

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Reminder SMS to everyone registered for the program. Sent after the
     * response (it can take a while for many people); the result is logged
     * under the program's Recent Activity.
     */
    public function remind(Program $program)
    {
        $this->authorizeProgram('PROGRAMS_EDIT');

        if (!ProgramSmsTemplate::resolve(ProgramSmsTemplate::REMINDER, $program)) {
            return back()->withErrors(['sms' => 'There is no active Reminder SMS template. Create one under Programs & Attendance > SMS Templates.']);
        }

        $count = $program->registrations()->where('registration_status', 'registered')->count();
        if ($count === 0) {
            return back()->withErrors(['sms' => 'Nobody is registered for this program yet.']);
        }

        $programId = $program->id;
        $userId = Auth::id();
        dispatch(function () use ($programId, $userId) {
            Auth::onceUsingId($userId); // so the activity log shows who sent it
            app(ProgramSmsNotifier::class)->sendReminder(Program::findOrFail($programId));
        })->afterResponse();

        return back()->with('success', "Reminder SMS is being sent to {$count} registered " . ($count === 1 ? 'person' : 'people')
            . '. The result will appear under Recent Activity in a few minutes.');
    }

    private function validated(Request $request, ?ProgramSmsTemplate $current = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(ProgramSmsTemplate::TYPES))],
            'program_id' => 'nullable|exists:programs,id',
            'body' => 'required|string|max:1000',
        ]);
        $data['program_id'] = $data['program_id'] ?? null;
        $data['body'] = trim($data['body']);
        $data['is_active'] = $request->boolean('is_active');

        $duplicate = ProgramSmsTemplate::where('type', $data['type'])
            ->where(fn ($q) => $data['program_id'] ? $q->where('program_id', $data['program_id']) : $q->whereNull('program_id'))
            ->when($current, fn ($q) => $q->whereKeyNot($current->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'type' => 'A "' . ProgramSmsTemplate::TYPES[$data['type']] . '" template for '
                    . ($data['program_id'] ? 'this program' : 'all programs') . ' already exists - edit that one instead.',
            ]);
        }

        return $data;
    }
}
