<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganizationLimits
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the organization has reached its user limit
     * and if the subscription is active before allowing certain actions.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Get organization from request (assuming it's passed as route parameter or in request)
        $organizationId = $request->route('organizationId')
            ?? $request->route('organization')
            ?? $request->input('organization_id');

        if (! $organizationId) {
            return response()->json([
                'success' => false,
                'message' => 'Organization ID is required',
            ], 400);
        }

        $organization = \App\Models\Organization::find($organizationId);

        if (! $organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found',
            ], 404);
        }

        // Check if organization has active subscription
        if (! $organization->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'Organization subscription is not active. Please contact support or update your subscription.',
                'subscription_status' => $organization->subscription_status,
                'is_trial_expired' => $organization->isTrialExpired(),
            ], 403);
        }

        // Check if organization is suspended
        if ($organization->isSuspended()) {
            return response()->json([
                'success' => false,
                'message' => 'Organization is suspended. Please contact support.',
            ], 403);
        }

        // For requests that add users, check user limits
        if ($this->isUserAdditionRequest($request)) {
            $count = $request->input('count', 1);

            if (! $organization->canAddUsers($count)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User limit reached. Please upgrade your plan to add more users.',
                    'current_users' => $organization->getActiveUsersCount(),
                    'max_users' => $organization->max_users,
                    'requested_count' => $count,
                ], 403);
            }
        }

        // Attach organization to request for use in controllers
        $request->merge(['_organization' => $organization]);

        return $next($request);
    }

    /**
     * Check if the request is attempting to add users.
     */
    private function isUserAdditionRequest(Request $request): bool
    {
        $route = $request->route();

        if (! $route) {
            return false;
        }

        $action = $route->getActionMethod();
        $uri = $request->path();

        // Check if the request is for adding users
        return $request->isMethod('POST') && (
            str_contains($uri, '/users') ||
            str_contains($action, 'addUser') ||
            str_contains($action, 'createUser') ||
            str_contains($action, 'inviteUser')
        );
    }
}
