<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
                'message' => 'Email already verified.',
            ], 400);
        }

        // Generate verification token
        $token = $user->generateEmailVerificationToken();

        // Build verification URL
        $verificationUrl = config('app.frontend_url', config('app.url'))
            .'/verify-email?token='.$token
            .'&email='.urlencode($user->email);

        // Send email
        Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));

        return response()->json([
            'message' => 'Verification email sent successfully.',
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
                'error' => 'User not found.',
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        if (! $user->verifyEmail($request->token)) {
            return response()->json([
                'error' => 'Invalid or expired verification token.',
            ], 400);
        }

        return response()->json([
            'message' => 'Email verified successfully.',
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
                'error' => 'User not found.',
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }

        // Rate limiting: check if last email was sent less than 1 minute ago
        if ($user->email_verification_sent_at && $user->email_verification_sent_at->addMinute()->isFuture()) {
            return response()->json([
                'error' => 'Please wait before requesting another verification email.',
            ], 429);
        }

        // Generate new verification token
        $token = $user->generateEmailVerificationToken();

        // Build verification URL
        $verificationUrl = config('app.frontend_url', config('app.url'))
            .'/verify-email?token='.$token
            .'&email='.urlencode($user->email);

        // Send email
        Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));

        return response()->json([
            'message' => 'Verification email resent successfully.',
        ]);
    }

    /**
     * Verify email via GET request (for clicking links in emails).
     * Displays a success or error page.
     */
    public function verifyViaGet(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return view('auth.email-verification-result', [
                'success' => false,
                'message' => 'User not found.',
                'title' => 'Verification Failed',
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return view('auth.email-verification-result', [
                'success' => true,
                'message' => 'Your email address has already been verified.',
                'title' => 'Already Verified',
            ]);
        }

        if (! $user->verifyEmail($request->token)) {
            return view('auth.email-verification-result', [
                'success' => false,
                'message' => 'The verification link is invalid or has expired. Please request a new verification email.',
                'title' => 'Verification Failed',
            ]);
        }

        return view('auth.email-verification-result', [
            'success' => true,
            'message' => 'Your email address has been verified successfully! You can now log in to your account.',
            'title' => 'Email Verified',
        ]);
    }
}
