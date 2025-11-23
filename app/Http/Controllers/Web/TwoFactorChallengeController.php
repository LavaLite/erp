<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    /**
     * Show the two factor authentication challenge view.
     */
    public function create(Request $request)
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Attempt to validate the incoming two factor authentication challenge.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $userId = $request->session()->get('login.id');
        
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect()->route('login');
        }

        if ($code = $request->input('code')) {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($user->getTwoFactorSecret(), $code);

            if ($valid) {
                $this->loginUser($request, $user);
                return redirect()->intended(route('dashboard'));
            }
        } elseif ($code = $request->input('recovery_code')) {
            if ($user->useRecoveryCode($code)) {
                $this->loginUser($request, $user);
                return redirect()->intended(route('dashboard'));
            }
        }

        return back()->withErrors(['code' => __('The provided two factor authentication code was invalid.')]);
    }

    /**
     * Authenticate the user and clear the session.
     */
    protected function loginUser(Request $request, User $user)
    {
        Auth::guard('web')->login($user, $request->session()->get('login.remember', false));
        $request->session()->forget(['login.id', 'login.remember']);
        $request->session()->regenerate();
    }
}
