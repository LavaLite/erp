<?php

namespace Tests\Feature;

use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_verify_email_with_valid_token(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => null]);
        $token = $user->generateEmailVerificationToken();

        $response = $this->postJson('/api/email/verify', [
            'email' => $user->email,
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Email verified successfully.',
            ]);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_cannot_verify_email_with_invalid_token(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->postJson('/api/email/verify', [
            'email' => $user->email,
            'token' => 'invalid-token',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid or expired verification token.',
            ]);

        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_can_resend_verification_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->postJson('/api/email/resend', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Verification email resent successfully.',
            ]);

        Mail::assertSent(VerifyEmail::class);
    }

    public function test_already_verified_user_cannot_verify_again(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $token = 'some-token';

        $response = $this->postJson('/api/email/verify', [
            'email' => $user->email,
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Email already verified.',
            ]);
    }

    public function test_rate_limiting_on_resend_verification(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => null]);

        // Send verification email
        $this->postJson('/api/email/resend', ['email' => $user->email]);

        // Try to resend immediately
        $response = $this->postJson('/api/email/resend', ['email' => $user->email]);

        $response->assertStatus(429);
    }
}
