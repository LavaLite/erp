<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $roles = Role::with('permissions')->paginate($request->get('per_page', 15));

        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'nullable|string|max:50', // Can be UUID or 'global'
        ]);

        // Use organization from context if not explicitly provided in request
        // If organization_id is explicitly set to null, keep it null (for global roles)
        if ($request->has('organization_id')) {
            $organizationId = $request->input('organization_id');
        } else {
            // Try to get organization from middleware context first
            $organization = $request->attributes->get('organization') ?? (app()->has('organization') ? app('organization') : null);

            // If not set by middleware, try to get from header (manual detection)
            if (! $organization) {
                $orgHeaderId = $request->header('X-Organization-ID');
                if ($orgHeaderId) {
                    $organization = Organization::where('id', $orgHeaderId)
                        ->orWhere('slug', $orgHeaderId)
                        ->first();
                }
            }

            $organizationId = $organization ? $organization->id : null;
        }

        // Check if role already exists with this slug and organization_id
        $existingRole = Role::where('slug', $request->slug)
            ->where('organization_id', $organizationId)
            ->first();

        if ($existingRole) {
            return response()->json([
                'error' => 'A role with this slug already exists for this tenant',
            ], 422);
        }

        // Authorization checks
        if ($organizationId === 'global') {
            // Creating a global role - only super admins or global admins can do this
            if (! $request->user()->canManageGlobalRoles()) {
                return response()->json([
                    'error' => 'Only global admins can create global roles',
                ], 403);
            }
        } else {
            // Creating an organization-scoped role - user must be admin in that organization
            $organization = Organization::findOrFail($organizationId);

            if (! $request->user()->hasRoleInOrganization('admin', $organization)) {
                return response()->json([
                    'error' => 'Only organization admins can create roles in their organization',
                ], 403);
            }
        }

        $role = Role::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'organization_id' => $organizationId,
        ]);

        return new RoleResource($role->load('permissions'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::with('permissions', 'users')->findOrFail($id);

        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'nullable|string|max:50',
        ]);

        // Check for duplicate slug
        if ($request->has('slug')) {
            $existingRole = Role::where('slug', $request->slug)
                ->where('organization_id', $request->organization_id ?? $role->organization_id)
                ->where('id', '!=', $id)
                ->first();

            if ($existingRole) {
                return response()->json([
                    'error' => 'A role with this slug already exists for this tenant',
                ], 422);
            }
        }

        // Only global admins can update to global roles (organization_id = 'global')
        if ($request->has('organization_id') && $request->organization_id === 'global' && ! $request->user()->canManageGlobalRoles()) {
            return response()->json([
                'error' => 'Only global admins can create or update global roles',
            ], 403);
        }

        $role->update($request->all());

        return new RoleResource($role->load('permissions'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * Assign role to user.
     */
    public function assignToUser(Request $request, string $roleId)
    {
        // Get organization from context if not in request
        $organizationId = $request->input('organization_id');
        if (! $organizationId) {
            // Try to get organization from middleware context first
            $organization = $request->attributes->get('organization') ?? (app()->has('organization') ? app('organization') : null);

            // If not set by middleware, try to get from header (manual detection)
            if (! $organization) {
                $orgHeaderId = $request->header('X-Organization-ID');
                if ($orgHeaderId) {
                    $organization = Organization::where('id', $orgHeaderId)
                        ->orWhere('slug', $orgHeaderId)
                        ->first();
                }
            }

            $organizationId = $organization ? $organization->id : null;
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Validate organization_id if provided
        if ($organizationId && $organizationId !== 'global') {
            if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $organizationId)) {
                return response()->json([
                    'error' => 'The organization id must be a valid UUID or "global".',
                ], 422);
            }
            if (! Organization::where('id', $organizationId)->exists()) {
                return response()->json([
                    'error' => 'The selected organization id is invalid.',
                ], 422);
            }
        }

        $role = Role::findOrFail($roleId);
        $user = User::findOrFail($request->user_id);
        $organization = $organizationId ? Organization::findOrFail($organizationId) : null;

        // Only super admins can assign the superadmin role
        if ($role->slug === 'superadmin' && ! $request->user()->isSuperAdmin()) {
            return response()->json([
                'error' => 'Only super admins can assign the superadmin role',
            ], 403);
        }

        // Only global admins can assign global roles (including global admin role)
        if ($role->organization_id === 'global' && ! $request->user()->canManageGlobalRoles()) {
            return response()->json([
                'error' => 'Only global admins can assign global roles',
            ], 403);
        }

        // Verify role belongs to the tenant or is global
        if ($organization && $role->organization_id !== $organization->id && $role->organization_id !== 'global') {
            return response()->json([
                'error' => 'Role does not belong to this tenant',
            ], 400);
        }

        $user->assignRoleInOrganization($role, $organization);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Remove role from user.
     */
    public function removeFromUser(Request $request, string $roleId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organization_id' => 'required|uuid|exists:tenants,id',
        ]);

        $role = Role::findOrFail($roleId);
        $user = User::findOrFail($request->user_id);
        $organization = Organization::findOrFail($request->organization_id);

        // Verify role belongs to the tenant
        if ($role->organization_id !== $organization->id) {
            return response()->json([
                'error' => 'Role does not belong to this tenant',
            ], 400);
        }

        $user->removeRoleInOrganization($role, $organization);

        return response()->json([
            'message' => 'Role removed successfully',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Assign permission to role.
     */
    public function assignPermission(Request $request, string $roleId)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::findOrFail($roleId);
        $role->permissions()->syncWithoutDetaching($request->permission_id);

        return response()->json([
            'message' => 'Permission assigned to role successfully',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Remove permission from role.
     */
    public function removePermission(Request $request, string $roleId)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::findOrFail($roleId);
        $role->permissions()->detach($request->permission_id);

        return response()->json([
            'message' => 'Permission removed from role successfully',
            'role' => $role->load('permissions'),
        ]);
    }
}
