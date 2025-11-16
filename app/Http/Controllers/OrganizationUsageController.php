<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationUsageController extends Controller
{
    /**
     * Get comprehensive usage data for an organization.
     * This endpoint is designed for billing service integration.
     */
    public function getUsage(string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);

        return response()->json([
            'success' => true,
            'data' => $organization->getUsageData(),
        ]);
    }

    /**
     * Get active users count for an organization.
     */
    public function getUsersCount(string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);

        return response()->json([
            'success' => true,
            'data' => [
                'organization_id' => $organization->id,
                'active_users_count' => $organization->getActiveUsersCount(),
                'total_users_count' => $organization->getTotalUsersCount(),
                'max_users' => $organization->max_users,
                'can_add_users' => $organization->canAddUsers(),
            ],
        ]);
    }

    /**
     * Get enabled modules for an organization.
     */
    public function getModules(string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);

        $modules = $organization->enabledModules()->get()->map(function ($module) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'code' => $module->code,
                'category' => $module->category,
                'is_core' => $module->is_core,
                'requires_license' => $module->requires_license,
                'enabled_at' => $module->pivot->enabled_at,
                'expires_at' => $module->pivot->expires_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'organization_id' => $organization->id,
                'modules_count' => $modules->count(),
                'modules' => $modules,
            ],
        ]);
    }

    /**
     * Get subscription status for an organization.
     */
    public function getSubscriptionStatus(string $organizationId): JsonResponse
    {
        $organization = Organization::findOrFail($organizationId);

        return response()->json([
            'success' => true,
            'data' => [
                'organization_id' => $organization->id,
                'subscription_status' => $organization->subscription_status,
                'subscription_id' => $organization->subscription_id,
                'plan_id' => $organization->plan_id,
                'has_active_subscription' => $organization->hasActiveSubscription(),
                'is_on_trial' => $organization->isOnTrial(),
                'is_trial_expired' => $organization->isTrialExpired(),
                'trial_ends_at' => $organization->trial_ends_at,
                'is_suspended' => $organization->isSuspended(),
                'is_cancelled' => $organization->isCancelled(),
                'is_active' => $organization->is_active,
            ],
        ]);
    }

    /**
     * Update subscription details from billing service.
     * This endpoint allows the billing service to update subscription status.
     */
    public function updateSubscription(Request $request, string $organizationId): JsonResponse
    {
        $validated = $request->validate([
            'subscription_status' => 'sometimes|string|in:active,trial,suspended,cancelled,pending',
            'subscription_id' => 'sometimes|string|nullable',
            'plan_id' => 'sometimes|string|nullable',
            'max_users' => 'sometimes|integer|nullable|min:1',
            'trial_ends_at' => 'sometimes|date|nullable',
        ]);

        $organization = Organization::findOrFail($organizationId);
        $organization->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subscription updated successfully',
            'data' => [
                'organization_id' => $organization->id,
                'subscription_status' => $organization->subscription_status,
                'subscription_id' => $organization->subscription_id,
                'plan_id' => $organization->plan_id,
                'max_users' => $organization->max_users,
            ],
        ]);
    }

    /**
     * Check if organization can add specified number of users.
     */
    public function checkUserLimit(Request $request, string $organizationId): JsonResponse
    {
        $validated = $request->validate([
            'count' => 'sometimes|integer|min:1',
        ]);

        $organization = Organization::findOrFail($organizationId);
        $count = $validated['count'] ?? 1;

        return response()->json([
            'success' => true,
            'data' => [
                'organization_id' => $organization->id,
                'current_active_users' => $organization->getActiveUsersCount(),
                'max_users' => $organization->max_users,
                'requested_count' => $count,
                'can_add_users' => $organization->canAddUsers($count),
                'available_slots' => $organization->max_users
                    ? max(0, $organization->max_users - $organization->getActiveUsersCount())
                    : null,
            ],
        ]);
    }

    /**
     * Get bulk usage data for multiple organizations.
     * Useful for billing service to fetch usage for multiple orgs at once.
     */
    public function getBulkUsage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_ids' => 'required|array',
            'organization_ids.*' => 'required|string',
        ]);

        $organizations = Organization::whereIn('id', $validated['organization_ids'])->get();

        $usageData = $organizations->map(function ($organization) {
            return $organization->getUsageData();
        });

        return response()->json([
            'success' => true,
            'data' => $usageData,
        ]);
    }
}
