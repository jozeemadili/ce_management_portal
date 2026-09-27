<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Same rules as the web login (PortalUsersController::loginWeb) - email
     * or phone number + password, status must be Active - but stateless:
     * issues a Sanctum token. "login" is the new field; "email" is still
     * accepted from older app versions (it may hold a phone number too).
     * user.must_change_password tells the app to show the set-password
     * screen; other endpoints answer 403 until that's done.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required_without:email', 'nullable', 'string'],
            'email' => ['required_without:login', 'nullable', 'string'],
            'password' => ['required'],
        ]);

        $user = AccountLogin::findUser($request->input('login') ?? $request->input('email'));

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email/phone or password.'], 401);
        }

        if ($user->status !== 'Active') {
            return response()->json(['message' => 'Your account has been deactivated.'], 403);
        }

        $token = $user->createToken('flutter')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    /**
     * Set a new password. On first login (must_change_password) the current
     * password is not asked again; otherwise it is required.
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => [$user->must_change_password ? 'nullable' : 'required', 'string'],
            'password' => AccountLogin::newPasswordRules(),
        ]);

        if (!$user->must_change_password && !Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.', 'errors' => ['current_password' => ['Current password is incorrect.']]], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'is_first_time_pin' => false,
        ])->save();

        return response()->json(['message' => 'Password updated.', 'user' => $this->userPayload($user->fresh())]);
    }

    /**
     * Forgot password, step 1: if the email belongs to an active account,
     * return a one-time reset token valid for 10 minutes (no email is sent -
     * most members have no working email).
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = AccountLogin::findUserByEmail($request->email);

        if (!$user) {
            return response()->json(['message' => 'No account was found with that email.'], 404);
        }

        if ($user->status !== 'Active') {
            return response()->json(['message' => 'This account has been deactivated. Please contact the church office.'], 403);
        }

        $token = Str::random(64);
        Cache::put($this->resetCacheKey($user->id), hash('sha256', $token), now()->addMinutes(10));

        return response()->json(['message' => 'Email confirmed. Choose a new password.', 'reset_token' => $token, 'expires_in' => 600]);
    }

    /**
     * Forgot password, step 2: email + reset_token from step 1 + new password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_token' => 'required|string',
            'password' => AccountLogin::newPasswordRules(),
        ]);

        $user = AccountLogin::findUserByEmail($request->email);
        $expected = $user ? Cache::get($this->resetCacheKey($user->id)) : null;

        if (!$expected || !hash_equals($expected, hash('sha256', $request->reset_token))) {
            return response()->json(['message' => 'This reset has expired - please enter your email again.'], 422);
        }

        Cache::forget($this->resetCacheKey($user->id));

        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'is_first_time_pin' => false,
        ])->save();

        return response()->json(['message' => 'Password updated. You can now log in.']);
    }

    private function resetCacheKey(int $userId): string
    {
        return "mobile-password-reset:{$userId}";
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return response()->json($this->userPayload($request->user()));
    }

    private function userPayload(User $user): array
    {
        $member = $user->member;

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'role' => $user->role,
            'must_change_password' => (bool) $user->must_change_password,
            'member' => $member ? [
                'id' => $member->id,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'phone' => $member->phone,
                'kingschat_username' => $member->kingschat_username,
                'church_id' => $member->church_id,
                'church' => optional($member->church)->name,
            ] : null,
        ];
    }
}
