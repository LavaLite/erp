<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle web login request.
     */
    public function login(Request $request)
    {
        // If already authenticated, logout first to ensure clean state
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Validate credentials WITHOUT logging in yet
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        
        if ($user && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            // Check if 2FA is enabled - store session data but DON'T log in yet
            if ($user->two_factor_enabled) {
                $request->session()->put([
                    'login.id' => $user->id,
                    'login.remember' => $remember,
                ]);
                $request->session()->save(); // Force save session

                return redirect()->route('2fa.challenge');
            }
            // Normal login - only now we actually log the user in
            Auth::guard('web')->login($user, $remember);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => __('messages.auth.invalid_credentials'),
        ])->onlyInput('email');
    }

    /**
     * Handle web logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        // Clear any 2FA session data
        $request->session()->forget(['login.id', 'login.remember']);
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
