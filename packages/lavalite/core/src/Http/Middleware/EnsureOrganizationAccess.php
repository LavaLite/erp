<?php

namespace Lavalite\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lavalite\Core\Services\AuthServiceClient;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationAccess
{
    public function __construct(
        private AuthServiceClient $authClient
    ) {}

    /**
     * Ensure authenticated user has access to the organization.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // From JWT token
        $organization = $request->attributes->get('organization');

        if (! $organization) {
            return response()->json([
                'message' => 'Organization context not found.',
            ], 400);
        }

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Check if user has access to this organization via Auth service
        if (! $this->authClient->userHasOrganizationAccess($user->id, $organization->id)) {
            return response()->json([
                'message' => 'You do not have access to this organization.',
            ], 403);
        }

        return $next($request);
    }
}
