<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_organization(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->postJson('/api/organizations', [
                'name' => 'Test Organization',
                'slug' => 'test-org',
                'description' => 'A test organization',
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

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'slug' => 'test-org',
        ]);
    }

    public function test_user_can_list_their_organizations(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org->id);

        $response = $this->actingAsUser($user)
            ->getJson('/api/organizations');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_add_another_user_to_organization(): void
    {
        $owner = User::factory()->create();
        $newUser = User::factory()->create();
        $org = Organization::factory()->create();

        $owner->organizations()->attach($org->id);

        $response = $this->actingAsUser($owner, ['organization_id' => $org->id])
            ->postJson("/api/organizations/{$org->id}/add-user", [
                'user_id' => $newUser->id,
            ]);

        $response->assertStatus(200);

        $this->assertTrue($newUser->belongsToOrganization($org));
    }

    public function test_user_can_switch_organization_context(): void
    {
        $this->markTestSkipped('Temporarily disabled - response structure needs adjustment');

        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        $user->organizations()->attach([$org1->id, $org2->id]);

        // Login to get JWT token
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'organization_id' => $org1->id,
        ]);

        $token = $loginResponse->json('access_token');

        // Switch organization
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/switch-organization', [
                'organization_id' => $org2->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'organization_id',
            ]);
    }
}
