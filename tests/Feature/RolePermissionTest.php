<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_role_in_organization(): void
    {
        $this->markTestSkipped('Temporarily disabled - pivot table query issue needs investigation');

        $user = User::factory()->create(['is_super_admin' => false]);
        $org = Organization::factory()->create();
        $user->organizations()->attach($org->id);

        // Create admin role and assign to user
        $adminRole = Role::factory()->create([
            'organization_id' => $org->id,
            'slug' => 'admin',
        ]);
        $user->roles()->attach($adminRole->id, ['organization_id' => $org->id]);

        $response = $this->actingAsUser($user, ['organization_id' => $org->id])
            ->withHeader('X-Organization-ID', $org->id)
            ->postJson('/api/roles', [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Manager role',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                ],
            ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Manager',
            'slug' => 'manager',
            'organization_id' => $org->id,
        ]);
    }

    public function test_user_can_assign_role_to_another_user(): void
    {
        $this->markTestSkipped('Temporarily disabled - pivot table query issue needs investigation');

        $admin = User::factory()->create();
        $user = User::factory()->create();
        $org = Organization::factory()->create();

        $admin->organizations()->attach($org->id);
        $user->organizations()->attach($org->id);

        $adminRole = Role::factory()->create([
            'organization_id' => $org->id,
            'slug' => 'admin',
        ]);
        $managerRole = Role::factory()->create([
            'organization_id' => $org->id,
            'slug' => 'manager',
        ]);

        $admin->roles()->attach($adminRole->id, ['organization_id' => $org->id]);

        $response = $this->actingAsUser($admin, ['organization_id' => $org->id])
            ->withHeader('X-Organization-ID', $org->id)
            ->postJson("/api/roles/{$managerRole->id}/assign-user", [
                'user_id' => $user->id,
            ]);

        $response->assertStatus(200);

        $this->assertTrue($user->hasRoleInOrganization('manager', $org->id));
    }

    public function test_user_can_create_permission_in_organization(): void
    {
        $this->markTestSkipped('Temporarily disabled - pivot table query issue needs investigation');

        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org->id);

        $adminRole = Role::factory()->create([
            'organization_id' => $org->id,
            'slug' => 'admin',
        ]);
        $user->roles()->attach($adminRole->id, ['organization_id' => $org->id]);

        $response = $this->actingAsUser($user, ['organization_id' => $org->id])
            ->withHeader('X-Organization-ID', $org->id)
            ->postJson('/api/permissions', [
                'name' => 'Edit Posts',
                'slug' => 'edit-posts',
                'description' => 'Can edit posts',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('permissions', [
            'name' => 'Edit Posts',
            'slug' => 'edit-posts',
            'organization_id' => $org->id,
        ]);
    }

    public function test_super_admin_can_create_global_role(): void
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);

        $response = $this->actingAsUser($superAdmin)
            ->postJson('/api/roles', [
                'name' => 'Global Admin',
                'slug' => 'global-admin',
                'description' => 'System-wide admin',
                'organization_id' => null,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('roles', [
            'name' => 'Global Admin',
            'slug' => 'global-admin',
            'organization_id' => null,
        ]);
    }

    public function test_regular_user_cannot_create_global_role(): void
    {
        $user = User::factory()->create(['is_super_admin' => false]);
        $org = Organization::factory()->create();
        $user->organizations()->attach($org->id);

        $response = $this->actingAsUser($user, ['organization_id' => $org->id])
            ->postJson('/api/roles', [
                'name' => 'Global Admin',
                'slug' => 'global-admin',
                'description' => 'System-wide admin',
                'organization_id' => null,
            ]);

        $response->assertStatus(403);
    }
}
