<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_full_name_attribute(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    public function test_user_initials_attribute(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('JD', $user->initials);
    }

    public function test_user_can_verify_email(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $token = $user->generateEmailVerificationToken();

        $result = $user->verifyEmail($token);

        $this->assertTrue($result);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_user_cannot_verify_with_invalid_token(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $user->generateEmailVerificationToken();

        $result = $user->verifyEmail('invalid-token');

        $this->assertFalse($result);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_enable_two_factor(): void
    {
        $user = User::factory()->create();

        $user->enableTwoFactor('TESTSECRET', ['code1', 'code2', 'code3']);

        $this->assertTrue($user->fresh()->two_factor_enabled);
        $this->assertEquals('TESTSECRET', $user->getTwoFactorSecret());
        $this->assertCount(3, $user->getTwoFactorRecoveryCodes());
    }

    public function test_user_can_disable_two_factor(): void
    {
        $user = User::factory()->create();
        $user->enableTwoFactor('TESTSECRET', ['code1', 'code2']);

        $user->disableTwoFactor();

        $this->assertFalse($user->fresh()->two_factor_enabled);
        $this->assertNull($user->getTwoFactorSecret());
    }

    public function test_user_can_use_recovery_code(): void
    {
        $user = User::factory()->create();
        $codes = ['code1-12345', 'code2-67890', 'code3-11111'];
        $user->enableTwoFactor('TESTSECRET', $codes);

        $result = $user->useRecoveryCode('code1-12345');

        $this->assertTrue($result);
        $remainingCodes = $user->fresh()->getTwoFactorRecoveryCodes();
        $this->assertCount(2, $remainingCodes);
        $this->assertNotContains('code1-12345', $remainingCodes);
    }

    public function test_user_cannot_use_invalid_recovery_code(): void
    {
        $user = User::factory()->create();
        $codes = ['code1-12345', 'code2-67890'];
        $user->enableTwoFactor('TESTSECRET', $codes);

        $result = $user->useRecoveryCode('invalid-code');

        $this->assertFalse($result);
        $this->assertCount(2, $user->fresh()->getTwoFactorRecoveryCodes());
    }

    public function test_is_super_admin_check(): void
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $regularUser = User::factory()->create(['is_super_admin' => false]);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($regularUser->isSuperAdmin());
    }

    public function test_update_last_login(): void
    {
        $user = User::factory()->create([
            'last_login_at' => null,
            'last_login_ip' => null,
        ]);

        $user->updateLastLogin('192.168.1.1');

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertEquals('192.168.1.1', $user->last_login_ip);
    }
}
