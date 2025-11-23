<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    /**
     * Enable two-factor authentication.
     * For API: Returns secret and QR code for client to display.
     * Client must call confirm() with a valid code to activate.
     */
    public function enable(Request $request)
    {
        $user = $request->user() ?? $request->user('web');

        if ($user->two_factor_enabled) {
            return response()->json([
                'error' => __('messages.2fa.already_enabled'),
            ], 400);
        }

        // Generate secret key
        $secret = $this->google2fa->generateSecretKey();

        // Generate QR code
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Generate SVG QR code
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();

        // Store temporarily in user record (not yet confirmed)
        // We'll use a JSON field to store pending 2FA setup
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return response()->json([
            'secret' => $secret,
            'qr_code_svg' => $qrCodeSvg,
            'recovery_codes' => $recoveryCodes,
            'message' => __('messages.2fa.scan_qr'),
        ]);
    }

    /**
     * Confirm and activate two-factor authentication.
     * Verifies the TOTP code and enables 2FA if valid.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|digits:6',
        ]);

        $user = $request->user() ?? $request->user('web');

        if ($user->two_factor_enabled) {
            return response()->json([
                'error' => __('messages.2fa.already_enabled'),
            ], 400);
        }

        // Get the pending secret (stored during enable())
        $secret = $user->getTwoFactorSecret();
        $recoveryCodes = $user->getTwoFactorRecoveryCodes();

        if (! $secret || empty($recoveryCodes)) {
            return response()->json([
                'error' => __('messages.2fa.enable_first'),
            ], 400);
        }

        // Verify the code
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (! $valid) {
            return response()->json([
                'error' => __('messages.2fa.invalid_code'),
            ], 400);
        }

        // Activate 2FA by setting the confirmed flag
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        return response()->json([
            'message' => __('messages.2fa.enabled'),
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user() ?? $request->user('web');

        if (! $user->two_factor_enabled) {
            return response()->json([
                'error' => __('messages.2fa.not_enabled'),
            ], 400);
        }

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => __('messages.auth.invalid_credentials'),
            ], 400);
        }

        // Disable 2FA
        $user->disableTwoFactor();

        return response()->json([
            'message' => __('messages.2fa.disabled'),
        ]);
    }

    /**
     * Verify two-factor authentication code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user || ! $user->two_factor_enabled) {
            return response()->json([
                'error' => __('messages.2fa.not_enabled_user'),
            ], 400);
        }

        $secret = $user->getTwoFactorSecret();

        // Check if it's a recovery code
        if (strlen($request->code) > 6) {
            if ($user->useRecoveryCode($request->code)) {
                return response()->json([
                    'message' => __('messages.2fa.recovery_accepted'),
                    'valid' => true,
                ]);
            }

            return response()->json([
                'error' => __('messages.2fa.invalid_recovery'),
                'valid' => false,
            ], 400);
        }

        // Verify TOTP code
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (! $valid) {
            return response()->json([
                'error' => __('messages.2fa.invalid_code'),
                'valid' => false,
            ], 400);
        }

        return response()->json([
            'message' => __('messages.2fa.code_verified'),
            'valid' => true,
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user() ?? $request->user('web');

        if (! $user->two_factor_enabled) {
            return response()->json([
                'error' => __('messages.2fa.not_enabled'),
            ], 400);
        }

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => __('messages.auth.invalid_credentials'),
            ], 400);
        }

        // Generate new recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return response()->json([
            'message' => __('messages.2fa.recovery_codes_generated'),
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Generate recovery codes.
     */
    protected function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::random(10).'-'.Str::random(10);
        }

        return $codes;
    }
}
