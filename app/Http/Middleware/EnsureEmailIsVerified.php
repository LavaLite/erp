<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if email verification is required
        if (! config('auth.email_verification_required', true)) {
            return $next($request);
        }

        // Get authenticated user
        $user = $request->user();

        // If no user or email already verified, proceed
        if (! $user || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        // Email not verified - check if API or web request
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Email verification required.',
                'message' => 'Please verify your email address before accessing this resource. Check your inbox for the verification link.',
                'code' => 'EMAIL_NOT_VERIFIED',
            ], 403);
        }

        // Redirect web requests to email not verified page
        return redirect()->route('email.not.verified', ['email' => $user->email]);
    }
}
