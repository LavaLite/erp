<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle web login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if 2FA is enabled
        if ($user->two_factor_enabled) {
            if (! $request->two_factor_code) {
                return back()->withErrors([
                    'email' => 'Two-factor authentication code required.',
                ])->withInput();
            }

            // Verify 2FA code
            $google2fa = new \PragmaRX\Google2FA\Google2FA;
            $secret = $user->getTwoFactorSecret();

            // Check if it's a recovery code
            $valid = false;
            if (strlen($request->two_factor_code) > 6) {
                $valid = $user->useRecoveryCode($request->two_factor_code);
            } else {
                $valid = $google2fa->verifyKey($secret, $request->two_factor_code);
            }

            if (! $valid) {
                return back()->withErrors([
                    'email' => 'Invalid two-factor authentication code.',
                ])->withInput();
            }
        }

        // Check email verification if required (will be handled by middleware after login)
        // But we should check here to provide better error message
        if (config('auth.email_verification_required', true) && !$user->hasVerifiedEmail()) {
            // Log them in temporarily so middleware can redirect properly
            Auth::guard('web')->login($user, $request->filled('remember'));
            return redirect()->route('email.not.verified', ['email' => $user->email]);
        }

        // Update last login information
        $user->updateLastLogin($request->ip());

        // Log the user in
        Auth::guard('web')->login($user, $request->filled('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle web logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
