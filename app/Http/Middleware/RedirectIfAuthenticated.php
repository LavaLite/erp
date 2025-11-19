<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // If email verification is required and user hasn't verified, redirect to verification page
                if (config('auth.email_verification_required', true) && ! $user->hasVerifiedEmail()) {
                    return redirect()->route('email.not.verified', ['email' => $user->email]);
                }

                // Otherwise redirect to dashboard
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
