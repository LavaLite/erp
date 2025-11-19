<?php

namespace Lavalite\Core\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AuthServiceClient
{
    private string $baseUrl;

    private ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('lavalite-core.auth_service_url', 'http://localhost:8000');
        $this->apiKey = config('lavalite-core.auth_service_api_key', '');
        
        if (empty($this->baseUrl)) {
            throw new \RuntimeException('Auth service URL is not configured. Please set AUTH_SERVICE_URL in your .env file.');
        }
    }

    /**
     * Verify JWT token and get user data.
     */
    public function verifyToken(string $token): ?array
    {
        $cacheKey = "auth:token:{$token}";

        return Cache::remember($cacheKey, 300, function () use ($token) {
            $response = Http::withToken($token)
                ->get("{$this->baseUrl}/api/user");

            return $response->successful() ? $response->json() : null;
        });
    }

    /**
     * Get organization details.
     */
    public function getOrganization(string $organizationId): ?array
    {
        $cacheKey = "auth:org:{$organizationId}";

        return Cache::remember($cacheKey, 3600, function () use ($organizationId) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->baseUrl}/api/organizations/{$organizationId}");

            return $response->successful() ? $response->json('data') : null;
        });
    }

    /**
     * Check user permissions.
     */
    public function hasPermission(int|string $userId, string $organizationId, string $permission): bool
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'X-Organization-ID' => $organizationId,
        ])->post("{$this->baseUrl}/api/users/{$userId}/check-permission", [
            'permission' => $permission,
        ]);

        return $response->successful() && $response->json('data.has_permission');
    }

    /**
     * Get user by ID.
     */
    public function getUser(int|string $userId): ?array
    {
        $cacheKey = "auth:user:{$userId}";

        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->baseUrl}/api/users/{$userId}");

            return $response->successful() ? $response->json('data') : null;
        });
    }

    /**
     * Clear user cache.
     */
    public function clearUserCache(int|string $userId): void
    {
        Cache::forget("auth:user:{$userId}");
    }

    /**
     * Clear organization cache.
     */
    public function clearOrganizationCache(string $organizationId): void
    {
        Cache::forget("auth:org:{$organizationId}");
    }

    /**
     * Check if user has access to organization.
     */
    public function userHasOrganizationAccess(int|string $userId, string $organizationId): bool
    {
        $cacheKey = "auth:access:{$userId}:{$organizationId}";

        return Cache::remember($cacheKey, 600, function () use ($userId, $organizationId) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->baseUrl}/api/users/{$userId}/organizations/{$organizationId}/access");

            return $response->successful() && $response->json('data.has_access', false);
        });
    }
}
