<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $permissions = Permission::paginate($request->get('per_page', 15));

        return PermissionResource::collection($permissions);
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
            'organization_id' => 'nullable|string|max:50', // Can be UUID, 'global', or null
        ]);

        // Use organization from context if not explicitly provided in request
        // If organization_id is explicitly set to null, keep it null (for global permissions)
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

        // Check if permission already exists with this slug and organization_id
        $existingPermission = Permission::where('slug', $request->slug)
            ->where('organization_id', $organizationId)
            ->first();

        if ($existingPermission) {
            return response()->json([
                'error' => 'A permission with this slug already exists for this tenant',
            ], 422);
        }

        // Authorization checks
        if (is_null($organizationId) || $organizationId === 'global') {
            // Creating a global permission - only super admins or global admins can do this
            if (! $request->user()->canManageGlobalRoles()) {
                return response()->json([
                    'error' => 'Only global admins can create global permissions',
                ], 403);
            }
        } else {
            // Creating an organization-scoped permission - user must be admin in that organization
            $organization = Organization::findOrFail($organizationId);
            if (! $request->user()->hasRoleInOrganization('admin', $organization)) {
                return response()->json([
                    'error' => 'Only organization admins can create permissions in their organization',
                ], 403);
            }
        }

        $permission = Permission::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'organization_id' => $organizationId,
        ]);

        return new PermissionResource($permission);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permission = Permission::findOrFail($id);

        return new PermissionResource($permission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'nullable|string|max:50',
        ]);

        // Check for duplicate slug
        if ($request->has('slug')) {
            $existingPermission = Permission::where('slug', $request->slug)
                ->where('organization_id', $request->organization_id ?? $permission->organization_id)
                ->where('id', '!=', $id)
                ->first();

            if ($existingPermission) {
                return response()->json([
                    'error' => 'A permission with this slug already exists for this tenant',
                ], 422);
            }
        }

        // Only global admins can update to global permissions (organization_id = null or 'global')
        if ($request->has('organization_id') && (is_null($request->organization_id) || $request->organization_id === 'global') && ! $request->user()->canManageGlobalRoles()) {
            return response()->json([
                'error' => 'Only global admins can create or update global permissions',
            ], 403);
        }

        $permission->update($request->all());

        return new PermissionResource($permission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully',
        ]);
    }

    /**
     * Assign permission to user directly.
     */
    public function assignToUser(Request $request, string $permissionId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $permission = Permission::findOrFail($permissionId);
        $user = User::findOrFail($request->user_id);

        $user->givePermissionTo($permission);

        return response()->json([
            'message' => 'Permission assigned to user successfully',
            'user' => $user->load('permissions'),
        ]);
    }

    /**
     * Remove permission from user.
     */
    public function removeFromUser(Request $request, string $permissionId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $permission = Permission::findOrFail($permissionId);
        $user = User::findOrFail($request->user_id);

        $user->revokePermissionTo($permission);

        return response()->json([
            'message' => 'Permission removed from user successfully',
            'user' => $user->load('permissions'),
        ]);
    }
}
