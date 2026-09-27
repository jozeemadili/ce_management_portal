<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\Rules\Password;

/**
 * Login helpers shared by the web portal and the mobile API: members sign in
 * with either their email or their phone number, and accounts created by
 * staff start with the shared initial password (config/auth.php).
 */
class AccountLogin
{
    public static function defaultPassword(): string
    {
        return (string) config('auth.member_default_password');
    }

    /**
     * users.mobile is an integer column holding the 9-digit local number
     * (leading 0 dropped). Accepts 0712345678, 712345678, 255712345678,
     * +255 712 345 678. Returns null when it isn't a valid number.
     */
    public static function normaliseMobile($value): ?int
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if (strlen($digits) === 12 && str_starts_with($digits, '255')) {
            $digits = substr($digits, 3);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^[1-9]\d{8}$/', $digits) ? (int) $digits : null;
    }

    /**
     * Email when the value contains "@", otherwise a phone number.
     */
    public static function findUser(?string $login): ?User
    {
        $login = trim((string) $login);

        if ($login === '') {
            return null;
        }

        if (str_contains($login, '@')) {
            return User::whereRaw('LOWER(email) = ?', [mb_strtolower($login)])->first();
        }

        $mobile = self::normaliseMobile($login);

        return $mobile ? User::where('mobile', $mobile)->first() : null;
    }

    public static function findUserByEmail(?string $email): ?User
    {
        $email = trim((string) $email);

        return $email === '' ? null : User::whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();
    }

    /**
     * Rules for a password the member chooses themselves.
     */
    public static function newPasswordRules(): array
    {
        return [
            'required',
            'confirmed',
            Password::min(8)->letters()->numbers(),
            function ($attribute, $value, $fail) {
                if ($value === self::defaultPassword()) {
                    $fail('Choose a new password - not the initial password.');
                }
            },
        ];
    }
}
