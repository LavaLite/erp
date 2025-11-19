<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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

        return response()->json([
            'message' => __('Verification email sent successfully.'),
        ]);
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
                'error' => __('User not found.'),
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('Email already verified.'),
            ]);
        }

        if (! $user->verifyEmail($request->token)) {
            return response()->json([
                'error' => __('Invalid or expired verification token.'),
            ], 400);
        }

        return response()->json([
            'message' => __('Email verified successfully.'),
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
                'error' => __('User not found.'),
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => __('Email already verified.'),
            ], 400);
        }

        // Rate limiting: check if last email was sent less than 1 minute ago
        if ($user->email_verification_sent_at) {
            $secondsSinceLastEmail = now()->diffInSeconds($user->email_verification_sent_at);
            
            // Only enforce rate limit if email was sent within last 60 seconds
            if ($secondsSinceLastEmail < 60) {
                $waitSeconds = 60 - $secondsSinceLastEmail;
                return response()->json([
                    'error' => __('Please wait :seconds seconds before requesting another verification email.', ['seconds' => ceil($waitSeconds)]),
                ], 429);
            }
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

        return response()->json([
            'message' => __('Verification email resent successfully.'),
        ]);
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
                'message' => __('Invalid verification link. Missing token or email parameter.'),
                'title' => __('Verification Failed'),
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return view('auth.email-verification-result', [
                'success' => false,
                'message' => __('User not found.'),
                'title' => __('Verification Failed'),
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return view('auth.email-verification-result', [
                'success' => true,
                'message' => __('Your email address has already been verified.'),
                'title' => __('Already Verified'),
            ]);
        }

        if (! $user->verifyEmail($request->token)) {
            return view('auth.email-verification-result', [
                'success' => false,
                'message' => __('The verification link is invalid or has expired. Please request a new verification email.'),
                'title' => __('Verification Failed'),
            ]);
        }

        return view('auth.email-verification-result', [
            'success' => true,
            'message' => __('Your email address has been verified successfully! You can now log in to your account.'),
            'title' => __('Email Verified'),
        ]);
    }
}
