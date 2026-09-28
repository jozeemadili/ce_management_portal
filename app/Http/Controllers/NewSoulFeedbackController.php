<?php

namespace App\Http\Controllers;

use App\Models\NewSoulFollowup;
use Illuminate\Http\Request;

/**
 * Public page (no login) behind the link in a new soul's follow-up SMS:
 * they share a testimony / what blessed them. The token is the only key,
 * so the page shows nothing beyond their first name and the service.
 */
class NewSoulFeedbackController extends Controller
{
    public function show(string $token)
    {
        $followup = $this->find($token);

        return view('guest.feedback', compact('followup'));
    }

    public function store(Request $request, string $token)
    {
        $followup = $this->find($token);

        $data = $request->validate(['feedback' => 'required|string|min:3|max:3000']);

        $followup->update(['feedback' => trim($data['feedback']), 'submitted_at' => now()]);

        return redirect()->route('feedback.show', $token)->with('success', true);
    }

    private function find(string $token): NewSoulFollowup
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{10}$/', $token), 404);

        return NewSoulFollowup::with(['member', 'occurrence.program', 'occurrence.church'])->where('token', $token)->firstOrFail();
    }
}
