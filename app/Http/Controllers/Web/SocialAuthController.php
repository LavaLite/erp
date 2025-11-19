<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to the OAuth provider.
     */
    public function redirect(string $provider)
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth provider callback.
     */
    public function callback(string $provider, Request $request)
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error',
                __('messages.auth.social_login_error', ['provider' => ucfirst($provider)])
            );
        }

        // Find user by provider ID in social_accounts JSON or by email
        $user = User::whereJsonContains('social_accounts->'.$provider, $socialUser->getId())
            ->orWhere('email', $socialUser->getEmail())
            ->first();

        if (! $user) {
            // Create new user
            $user = User::create([
                'first_name' => $socialUser->getName() ?? explode('@', $socialUser->getEmail())[0],
                'last_name' => '',
                'email' => $socialUser->getEmail(),
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(), // Auto-verify social login emails
                'avatar' => $socialUser->getAvatar(),
                'social_accounts' => [$provider => $socialUser->getId()],
            ]);
        } else {
            // Update provider ID if not set
            $socialAccounts = $user->social_accounts ?? [];
            if (! isset($socialAccounts[$provider])) {
                $socialAccounts[$provider] = $socialUser->getId();
                $user->social_accounts = $socialAccounts;
                $user->save();
            }

            // Auto-verify email if not already verified
            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
                $user->save();
            }

            // Update avatar if empty
            if ($socialUser->getAvatar() && empty($user->avatar)) {
                $user->avatar = $socialUser->getAvatar();
                $user->save();
            }
        }

        // Update last login
        $user->updateLastLogin($request->ip());

        // Log the user in
        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success',
            __('messages.auth.social_login_success', ['provider' => ucfirst($provider)])
        );
    }

    /**
     * Validate the OAuth provider.
     */
    protected function validateProvider(string $provider): void
    {
        $enabledProviders = array_keys(array_filter(
            config('social.providers', []),
            fn ($p) => $p['enabled'] ?? false
        ));

        if (! in_array($provider, $enabledProviders)) {
            abort(404);
        }
    }
}
