<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Web password screens for members:
 *  - first login with the shared initial password -> must choose a new one
 *  - forgot password -> enter the account email; if it matches an account,
 *    choose a new password (no email is sent - most members have no
 *    working email, so the portal cannot send reset links)
 */
class MemberPasswordController extends Controller
{
    /** Minutes a matched email may be used to set a new password. */
    private const RESET_WINDOW_MINUTES = 10;

    public function showFirstChange()
    {
        if (!Auth::user()->must_change_password) {
            return redirect()->route('home');
        }

        return view('admin.authentication.password', [
            'mode' => 'first-change',
            'title' => 'Set Your Password',
            'intro' => 'Welcome, ' . ucfirst(Auth::user()->first_name) . '! You logged in with the initial password. Choose your own password to continue.',
            'action' => route('password.first-change.save'),
            'button' => 'Save Password',
        ]);
    }

    public function saveFirstChange(Request $request)
    {
        $request->validate(['password' => AccountLogin::newPasswordRules()]);

        $user = $request->user();
        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'is_first_time_pin' => false,
        ])->save();

        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Your password has been set.');
    }

    public function showForgot()
    {
        return view('admin.authentication.password', [
            'mode' => 'forgot',
            'title' => 'Forgot Password',
            'intro' => 'Enter the email address of your account.',
            'action' => route('password.forgot.check'),
            'button' => 'Continue',
        ]);
    }

    public function checkForgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = AccountLogin::findUserByEmail($request->email);

        if (!$user) {
            return back()->withInput()->with('error', 'No account was found with that email.');
        }

        if ($user->status !== 'Active') {
            return back()->withInput()->with('error', 'This account has been deactivated. Please contact the church office.');
        }

        $request->session()->put('password_reset', [
            'user_id' => $user->id,
            'expires_at' => now()->addMinutes(self::RESET_WINDOW_MINUTES)->timestamp,
        ]);

        return redirect()->route('password.reset');
    }

    public function showReset(Request $request)
    {
        if (!$this->pendingResetUserId($request)) {
            return redirect()->route('password.forgot')->with('error', 'Please enter your email again.');
        }

        return view('admin.authentication.password', [
            'mode' => 'reset',
            'title' => 'Choose a New Password',
            'intro' => 'Enter your new password twice.',
            'action' => route('password.reset.save'),
            'button' => 'Update Password',
        ]);
    }

    public function saveReset(Request $request)
    {
        $userId = $this->pendingResetUserId($request);

        if (!$userId) {
            return redirect()->route('password.forgot')->with('error', 'That took too long - please enter your email again.');
        }

        $request->validate(['password' => AccountLogin::newPasswordRules()]);

        User::whereKey($userId)->firstOrFail()->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'is_first_time_pin' => false,
        ])->save();

        $request->session()->forget('password_reset');

        return redirect()->route('login')->with('success', 'Password updated. You can now log in with your new password.');
    }

    private function pendingResetUserId(Request $request): ?int
    {
        $pending = $request->session()->get('password_reset');

        if (!$pending || ($pending['expires_at'] ?? 0) < now()->timestamp) {
            $request->session()->forget('password_reset');

            return null;
        }

        return (int) $pending['user_id'];
    }
}
