<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_get_active_users_count(): void
    {
        $org = Organization::factory()->create();
        $user1 = User::factory()->create(['is_active' => true]);
        $user2 = User::factory()->create(['is_active' => true]);
        $user3 = User::factory()->create(['is_active' => false]);

        $org->users()->attach([
            $user1->id => ['is_active' => true],
            $user2->id => ['is_active' => true],
            $user3->id => ['is_active' => false],
        ]);

        $this->assertEquals(2, $org->getActiveUsersCount());
    }

    public function test_organization_can_check_user_limit(): void
    {
        $org = Organization::factory()->create(['max_users' => 5]);
        
        for ($i = 0; $i < 3; $i++) {
            $user = User::factory()->create();
            $org->users()->attach($user->id);
        }

        $this->assertTrue($org->canAddUsers(2)); // 3 + 2 = 5 (within limit)
        $this->assertFalse($org->canAddUsers(3)); // 3 + 3 = 6 (exceeds limit)
    }

    public function test_organization_subscription_status_checks(): void
    {
        $activeOrg = Organization::factory()->create(['subscription_status' => 'active']);
        $trialOrg = Organization::factory()->create([
            'subscription_status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);
        $suspendedOrg = Organization::factory()->create(['subscription_status' => 'suspended']);

        $this->assertTrue($activeOrg->hasActiveSubscription());
        $this->assertTrue($trialOrg->hasActiveSubscription());
        $this->assertFalse($suspendedOrg->hasActiveSubscription());

        $this->assertFalse($activeOrg->isSuspended());
        $this->assertTrue($suspendedOrg->isSuspended());

        $this->assertTrue($trialOrg->isOnTrial());
        $this->assertFalse($activeOrg->isOnTrial());
    }

    public function test_organization_can_activate_subscription(): void
    {
        $org = Organization::factory()->create(['subscription_status' => 'trial']);

        $org->activateSubscription('sub_123456', 'plan_basic');

        $org->refresh();
        $this->assertEquals('active', $org->subscription_status);
        $this->assertEquals('sub_123456', $org->subscription_id);
        $this->assertEquals('plan_basic', $org->plan_id);
    }

    public function test_organization_can_be_suspended(): void
    {
        $org = Organization::factory()->create(['subscription_status' => 'active']);

        $org->suspendSubscription();

        $org->refresh();
        $this->assertEquals('suspended', $org->subscription_status);
    }

    public function test_organization_can_update_user_limit(): void
    {
        $org = Organization::factory()->create(['max_users' => 10]);

        $org->updateUserLimit(50);

        $org->refresh();
        $this->assertEquals(50, $org->max_users);
    }
}
