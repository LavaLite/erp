<?php

namespace App\Traits;

use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasMultiOrganizationRolesAndPermissions
{
    /**
     * The organizations that the user belongs to.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot('is_active', 'joined_at')
            ->withTimestamps();
    }

    /**
     * The roles that belong to the user (with organization context).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('organization_id')
            ->withTimestamps();
    }

    /**
     * The permissions that belong to the user (direct permissions).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Get roles for a specific organization (includes global roles where organization_id is null or 'global').
     */
    public function rolesInOrganization(Organization|int|string $organization): BelongsToMany
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        return $this->roles()->where(function ($query) use ($organizationId) {
            $query->wherePivot('organization_id', $organizationId)
                ->orWhereNull('roles.organization_id')
                ->orWhere('roles.organization_id', 'global');
        });
    }

    /**
     * Assign role to user in specific organization.
     */
    public function assignRoleInOrganization(Role|string $role, Organization|int|string $organization): self
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        if (is_string($role)) {
            $roleSlug = $role;

            $role = Role::where('slug', $roleSlug)
                ->where(function ($query) {
                    $query->where('organization_id', 'global')
                        ->orWhereNull('organization_id');
                })
                ->first();

            if (! $role) {
                $role = Role::where('slug', $roleSlug)
                    ->where('organization_id', $organizationId)
                    ->first();

                if (! $role) {
                    throw new \Exception("Role '{$roleSlug}' not found for organization {$organizationId} or as global role");
                }
            }
        }

        $this->roles()->syncWithoutDetaching([
            $role->id => ['organization_id' => $organizationId],
        ]);

        return $this;
    }

    /**
     * Remove role from user in specific organization.
     */
    public function removeRoleInOrganization(Role|string $role, Organization|int|string $organization): self
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        if (is_string($role)) {
            $role = Role::where('slug', $role)
                ->where('organization_id', $organizationId)
                ->firstOrFail();
        }

        $this->roles()->wherePivot('organization_id', $organizationId)->detach($role);

        return $this;
    }

    /**
     * Sync roles for user in specific organization.
     */
    public function syncRolesInOrganization(array $roles, Organization|int|string $organization): self
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        $this->roles()->wherePivot('organization_id', $organizationId)->detach();

        foreach ($roles as $role) {
            $this->assignRoleInOrganization($role, $organizationId);
        }

        return $this;
    }

    /**
     * Check if user has role in specific organization (includes global roles).
     */
    public function hasRoleInOrganization(Role|string|array $role, Organization|int|string $organization): bool
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        if (is_array($role)) {
            return $this->roles()
                ->where('role_user.organization_id', $organizationId)
                ->whereIn('slug', $role)
                ->exists();
        }

        if (is_string($role)) {
            return $this->roles()
                ->where('role_user.organization_id', $organizationId)
                ->where('slug', $role)
                ->exists();
        }

        return $this->roles()
            ->where('role_user.organization_id', $organizationId)
            ->where('roles.id', $role->id)
            ->exists();
    }

    /**
     * Check if user has any of the given roles in organization.
     */
    public function hasAnyRoleInOrganization(array $roles, Organization|int|string $organization): bool
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        return $this->roles()
            ->where('role_user.organization_id', $organizationId)
            ->whereIn('slug', $roles)
            ->exists();
    }

    /**
     * Check if user has all of the given roles in organization.
     */
    public function hasAllRolesInOrganization(array $roles, Organization|int|string $organization): bool
    {
        foreach ($roles as $role) {
            if (! $this->hasRoleInOrganization($role, $organization)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Give permission to user (direct permission).
     */
    public function givePermissionTo(Permission|string $permission, Organization|int|string $organization): self
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)
                ->where('organization_id', $organizationId)
                ->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching($permission);

        return $this;
    }

    /**
     * Revoke permission from user.
     */
    public function revokePermissionTo(Permission|string $permission, Organization|int|string $organization): self
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)
                ->where('organization_id', $organizationId)
                ->firstOrFail();
        }

        $this->permissions()->detach($permission);

        return $this;
    }

    /**
     * Check if user has permission in specific organization (includes global permissions).
     */
    public function hasPermissionInOrganization(Permission|string $permission, Organization|int|string $organization): bool
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        if (is_string($permission)) {
            if ($this->permissions()
                ->where('slug', $permission)
                ->where(function ($query) use ($organizationId) {
                    $query->where('organization_id', $organizationId)
                        ->orWhereNull('organization_id')
                        ->orWhere('organization_id', 'global');
                })
                ->exists()) {
                return true;
            }
        } else {
            if ($this->permissions()->where('id', $permission->id)->exists()) {
                return true;
            }
        }

        $roles = $this->rolesInOrganization($organizationId)->get();

        foreach ($roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions in organization.
     */
    public function hasAnyPermissionInOrganization(array $permissions, Organization|int|string $organization): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermissionInOrganization($permission, $organization)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions in organization.
     */
    public function hasAllPermissionsInOrganization(array $permissions, Organization|int|string $organization): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermissionInOrganization($permission, $organization)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for user in specific organization (direct + from roles, includes global).
     */
    public function getAllPermissionsInOrganization(Organization|int|string $organization)
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        $directPermissions = $this->permissions()
            ->where(function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId)
                    ->orWhereNull('organization_id')
                    ->orWhere('organization_id', 'global');
            })
            ->get();

        $roles = $this->rolesInOrganization($organizationId)->get();

        $rolePermissions = $roles->map(function ($role) {
            return $role->permissions;
        })->flatten();

        return $directPermissions->merge($rolePermissions)->unique('id');
    }

    /**
     * Add user to organization.
     */
    public function joinOrganization(Organization|int|string $organization): self
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        $this->organizations()->syncWithoutDetaching($organizationId);

        return $this;
    }

    /**
     * Remove user from organization.
     */
    public function leaveOrganization(Organization|int|string $organization): self
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        $this->roles()->wherePivot('organization_id', $organizationId)->detach();

        $this->organizations()->detach($organizationId);

        return $this;
    }

    /**
     * Check if user belongs to organization.
     */
    public function belongsToOrganization(Organization|int|string $organization): bool
    {
        $organizationId = $organization instanceof Organization ? $organization->id : $organization;

        return $this->organizations()->where('organization_id', $organizationId)->exists();
    }

    /**
     * Get all active organizations for user.
     */
    public function getActiveOrganizations()
    {
        return $this->organizations()->wherePivot('is_active', true)->get();
    }
}
