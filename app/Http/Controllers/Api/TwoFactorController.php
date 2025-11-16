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
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_enabled) {
            return response()->json([
                'error' => 'Two-factor authentication is already enabled.',
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

        // Store temporarily (user needs to confirm)
        $request->session()->put('2fa_secret', $secret);
        $request->session()->put('2fa_recovery_codes', $recoveryCodes);

        return response()->json([
            'secret' => $secret,
            'qr_code_svg' => $qrCodeSvg,
            'recovery_codes' => $recoveryCodes,
            'message' => 'Scan the QR code with your authenticator app and confirm with a valid code.',
        ]);
    }

    /**
     * Confirm and activate two-factor authentication.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|digits:6',
        ]);

        $user = $request->user();

        if ($user->two_factor_enabled) {
            return response()->json([
                'error' => 'Two-factor authentication is already enabled.',
            ], 400);
        }

        $secret = $request->session()->get('2fa_secret');
        $recoveryCodes = $request->session()->get('2fa_recovery_codes');

        if (! $secret || ! $recoveryCodes) {
            return response()->json([
                'error' => 'Please enable 2FA first.',
            ], 400);
        }

        // Verify the code
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (! $valid) {
            return response()->json([
                'error' => 'Invalid authentication code.',
            ], 400);
        }

        // Enable 2FA
        $user->enableTwoFactor($secret, $recoveryCodes);

        // Clear session
        $request->session()->forget(['2fa_secret', '2fa_recovery_codes']);

        return response()->json([
            'message' => 'Two-factor authentication enabled successfully.',
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

        $user = $request->user();

        if (! $user->two_factor_enabled) {
            return response()->json([
                'error' => 'Two-factor authentication is not enabled.',
            ], 400);
        }

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Invalid password.',
            ], 400);
        }

        // Disable 2FA
        $user->disableTwoFactor();

        return response()->json([
            'message' => 'Two-factor authentication disabled successfully.',
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
                'error' => 'Two-factor authentication is not enabled for this user.',
            ], 400);
        }

        $secret = $user->getTwoFactorSecret();

        // Check if it's a recovery code
        if (strlen($request->code) > 6) {
            if ($user->useRecoveryCode($request->code)) {
                return response()->json([
                    'message' => 'Recovery code accepted.',
                    'valid' => true,
                ]);
            }

            return response()->json([
                'error' => 'Invalid recovery code.',
                'valid' => false,
            ], 400);
        }

        // Verify TOTP code
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (! $valid) {
            return response()->json([
                'error' => 'Invalid authentication code.',
                'valid' => false,
            ], 400);
        }

        return response()->json([
            'message' => 'Authentication code verified.',
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

        $user = $request->user();

        if (! $user->two_factor_enabled) {
            return response()->json([
                'error' => 'Two-factor authentication is not enabled.',
            ], 400);
        }

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Invalid password.',
            ], 400);
        }

        // Generate new recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return response()->json([
            'message' => 'Recovery codes regenerated successfully.',
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
