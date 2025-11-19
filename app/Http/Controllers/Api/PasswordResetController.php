<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ResetPassword as ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            // Don't reveal if user exists
            return response()->json([
                'message' => __('messages.password.reset_sent'),
            ]);
        }

        // Generate password reset token
        $token = Password::createToken($user);

        // Build reset URL
        $resetUrl = config('app.frontend_url', config('app.url'))
            .'/reset-password?token='.$token
            .'&email='.urlencode($user->email);

        // Send email
        Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl, $token));

        return response()->json([
            'message' => __('messages.password.reset_sent'),
        ]);
    }

    /**
     * Reset password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'error' => __('messages.password.invalid_token'),
            ], 400);
        }

        // Verify token
        if (! Password::tokenExists($user, $request->token)) {
            return response()->json([
                'error' => __('messages.password.invalid_token'),
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete password reset token
        Password::deleteToken($user);

        return response()->json([
            'message' => __('messages.password.reset_success'),
        ]);
    }
}
