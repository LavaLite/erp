<?php

namespace Lavalite\Core\Testing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Lavalite\Core\Models\Organization;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Create a test user with JWT token (mocked from Auth service).
     */
    protected function actingAsUser(?string $organizationId = null, array $userAttributes = []): array
    {
        // Create mock user data (as would come from Auth service)
        $userData = array_merge([
            'id' => $userAttributes['id'] ?? rand(1, 1000),
            'email' => $userAttributes['email'] ?? 'test@example.com',
            'name' => $userAttributes['name'] ?? 'Test User',
            'first_name' => $userAttributes['first_name'] ?? 'Test',
            'last_name' => $userAttributes['last_name'] ?? 'User',
            'status' => 'active',
        ], $userAttributes);

        if ($organizationId) {
            // Cache organization reference locally
            $organization = $this->createOrganization(['id' => $organizationId]);
            $userData['organizations'] = [['id' => $organization->id]];
        }

        // Mock authenticated user
        $this->be((object) $userData);

        $this->withHeaders([
            'Accept' => 'application/json',
        ]);

        if ($organizationId) {
            $this->withHeader('X-Organization-ID', $organizationId);
        }

        return $userData;
    }

    /**
     * Create a test organization (cached reference).
     */
    protected function createOrganization(array $attributes = []): Organization
    {
        return Organization::updateOrCreate(
            ['id' => $attributes['id'] ?? \Illuminate\Support\Str::uuid()->toString()],
            array_merge([
                'name' => 'Test Organization',
                'slug' => 'test-org-'.rand(1000, 9999),
                'timezone' => 'UTC',
                'currency' => 'USD',
                'status' => 'active',
            ], $attributes)
        );
    }

    /**
     * Set organization context header.
     */
    protected function withOrganization(Organization|string $organization): self
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        $this->withHeader('X-Organization-ID', $organizationId);

        return $this;
    }

    /**
     * Mock Auth Service response for user verification.
     */
    protected function mockAuthServiceUserVerification(array $userData): void
    {
        // Mock HTTP response from Auth service
        \Illuminate\Support\Facades\Http::fake([
            '*/api/user' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => $userData,
            ], 200),
        ]);
    }

    /**
     * Mock Auth Service response for organization access.
     */
    protected function mockOrganizationAccess(int|string $userId, string $organizationId, bool $hasAccess = true): void
    {
        \Illuminate\Support\Facades\Http::fake([
            "*/api/users/{$userId}/organizations/{$organizationId}/access" => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => ['has_access' => $hasAccess],
            ], 200),
        ]);
    }
}
