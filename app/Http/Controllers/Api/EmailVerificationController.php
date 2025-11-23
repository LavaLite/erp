<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends Controller
{
    /**
     * Send email verification notification.
     */
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('Email already verified.'),
            ], 400);
        }

        // Generate verification token
        $token = $user->generateEmailVerificationToken();

        // Build verification URL (use app.url for backend verification)
        $verificationUrl = config('app.url')
            .'/verify-email?token='.$token
            .'&email='.urlencode($user->email);

        // Send email
        Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));

        // Update sent timestamp
        $user->email_verification_sent_at = now();
        $user->save();

        return response()->json(['message' => __('messages.verification.sent')]);
    }

    /**
     * Verify email address.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'error' => __('messages.verification.user_not_found'),
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('messages.verification.already_verified'),
            ]);
        }

        if (! $user->verifyEmail($request->token)) {
            return response()->json([
                'error' => __('messages.verification.invalid_token'),
            ], 400);
        }

        return response()->json([
            'message' => __('messages.verification.verified'),
        ]);
    }

    /**
     * Resend verification email.
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'error' => __('messages.verification.user_not_found'),
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('messages.verification.already_verified'),
            ], 400);
        }

        $throttleKey = 'resend-verification-email:'.$request->email;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'error' => __('messages.verification.rate_limit', ['seconds' => $seconds]),
            ], 429);
        }

        // Generate new verification token
        $token = $user->generateEmailVerificationToken();

        // Build verification URL (use app.url for backend verification)
        $verificationUrl = config('app.url')
            .'/verify-email?token='.$token
            .'&email='.urlencode($user->email);

        // Send email
        Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));

        // Update sent timestamp
        $user->email_verification_sent_at = now();
        $user->save();
        RateLimiter::hit($throttleKey, 60); // Allow 1 attempt per minute

        return response()->json(['message' => __('messages.verification.resent')]);
    }

    /**
     * Verify email via GET request (for clicking links in emails).
     * Displays a success or error page.
     */
    public function verifyViaGet(Request $request)
    {
        // Validate request - if fails, show error page instead of redirecting
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return view('auth.email-verification-result', [
                'success' => false,
                'message' => __('messages.verification.invalid_link'),
                'title' => __('messages.verification.failed_title'),
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return view('auth.email-verification-result', [
                'success' => false,
                'message' => __('messages.verification.user_not_found'),
                'title' => __('messages.verification.failed_title'),
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return view('auth.email-verification-result', [
                'success' => true,
                'message' => __('messages.verification.already_verified_message'),
                'title' => __('messages.verification.already_verified_title'),
            ]);
        }

        if (! $user->verifyEmail($request->token)) {
            return view('auth.email-verification-result', [
                'success' => false,
                'message' => __('messages.verification.expired'),
                'title' => __('messages.verification.failed_title'),
            ]);
        }

        return view('auth.email-verification-result', [
            'success' => true,
            'title' => __('messages.verification.verified_title'),
            'message' => __('messages.verification.verified_message'),
        ]);
    }
}
