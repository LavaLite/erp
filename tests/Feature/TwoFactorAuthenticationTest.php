<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession([])->actingAsUser($user)
            ->postJson('/api/2fa/enable');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'secret',
                'qr_code_svg',
                'recovery_codes',
                'message',
            ]);
    }

    public function test_user_cannot_enable_2fa_if_already_enabled(): void
    {
        $user = User::factory()->create();
        $user->enableTwoFactor('TESTSECRET', ['code1', 'code2']);

        $response = $this->actingAsUser($user)
            ->postJson('/api/2fa/enable');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Two-factor authentication is already enabled.',
            ]);
    }

    public function test_user_can_disable_two_factor_authentication(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $user->enableTwoFactor('TESTSECRET', ['code1', 'code2']);

        $response = $this->actingAsUser($user)
            ->postJson('/api/2fa/disable', [
                'password' => 'password',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Two-factor authentication disabled successfully.',
            ]);

        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
    }

    public function test_user_cannot_disable_2fa_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $user->enableTwoFactor('TESTSECRET', ['code1', 'code2']);

        $response = $this->actingAsUser($user)
            ->postJson('/api/2fa/disable', [
                'password' => 'wrongpassword',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid password.',
            ]);

        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
    }

    public function test_user_can_regenerate_recovery_codes(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $user->enableTwoFactor('TESTSECRET', ['code1', 'code2']);

        $response = $this->actingAsUser($user)
            ->postJson('/api/2fa/recovery-codes', [
                'password' => 'password',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'recovery_codes',
            ]);
    }
}
