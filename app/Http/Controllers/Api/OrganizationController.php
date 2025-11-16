<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations for authenticated user.
     */
    public function index(Request $request)
    {
        $organizations = $request->user()->organizations()->with('roles', 'permissions')
            ->paginate($request->get('per_page', 15));

        return OrganizationResource::collection($organizations);
    }

    /**
     * Store a newly created organization.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations',
            'domain' => 'nullable|string|unique:organizations',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        $organization = Organization::create($request->all());

        // Add creator as first member with admin role
        $request->user()->joinOrganization($organization);

        return new OrganizationResource($organization);
    }

    /**
     * Display the specified organization.
     */
    public function show(Request $request, string $id)
    {
        $organization = Organization::with('users', 'roles', 'permissions')->findOrFail($id);

        // Check if user has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new OrganizationResource($organization);
    }

    /**
     * Update the specified organization.
     */
    public function update(Request $request, string $id)
    {
        $organization = Organization::findOrFail($id);

        // Check if user has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:organizations,slug,'.$id,
            'domain' => 'nullable|string|unique:organizations,domain,'.$id,
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $organization->update($request->all());

        return new OrganizationResource($organization);
    }

    /**
     * Remove the specified organization.
     */
    public function destroy(Request $request, string $id)
    {
        $organization = Organization::findOrFail($id);

        // Check if user has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $organization->delete();

        return response()->json(['message' => 'Organization deleted successfully']);
    }

    /**
     * Add user to organization.
     */
    public function addUser(Request $request, string $organizationId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $organization = Organization::findOrFail($organizationId);
        $user = User::findOrFail($request->user_id);

        // Check if requester has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->joinOrganization($organization);

        return response()->json([
            'message' => 'User added to organization successfully',
            'organization' => new OrganizationResource($organization->load('users')),
        ]);
    }

    /**
     * Remove user from organization.
     */
    public function removeUser(Request $request, string $organizationId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $organization = Organization::findOrFail($organizationId);
        $user = User::findOrFail($request->user_id);

        // Check if requester has access to this organization
        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->leaveOrganization($organization);

        return response()->json([
            'message' => 'User removed from organization successfully',
        ]);
    }

    /**
     * Get current organization context.
     */
    public function current(Request $request)
    {
        $organization = $request->attributes->get('organization') ?? app('organization');

        if (! $organization) {
            return response()->json(['message' => 'No organization context set'], 404);
        }

        return new OrganizationResource($organization->load('roles', 'permissions'));
    }

    /**
     * Get user's roles and permissions in a specific organization.
     */
    public function userContext(Request $request, string $organizationId)
    {
        $organization = Organization::findOrFail($organizationId);

        if (! $request->user()->belongsToOrganization($organization)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $roles = $request->user()->rolesInOrganization($organization)->get();
        $permissions = $request->user()->getAllPermissionsInOrganization($organization);

        return response()->json([
            'organization' => new OrganizationResource($organization),
            'roles' => RoleResource::collection($roles),
            'permissions' => PermissionResource::collection($permissions),
        ]);
    }
}
