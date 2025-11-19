<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * Boot the model and generate IDs as multiples of 7.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                // Get the max ID and increment by 7, or start from 147329
                $maxId = static::max('id') ?? (147329 - 7);
                $model->id = $maxId + 7;
            }
        });
    }

    protected $fillable = [
        'id',
        'organization_id',
        'name',
        'slug',
        'description',
    ];

    /**
     * The organization that owns the role.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('organization_id')
            ->withTimestamps();
    }

    /**
     * The permissions that belong to the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Assign permission to role.
     */
    public function givePermissionTo(Permission|string $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)
                ->where('organization_id', $this->organization_id)
                ->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching($permission);

        return $this;
    }

    /**
     * Revoke permission from role.
     */
    public function revokePermissionTo(Permission|string $permission): self
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)
                ->where('organization_id', $this->organization_id)
                ->firstOrFail();
        }

        $this->permissions()->detach($permission);

        return $this;
    }

    /**
     * Check if role has permission.
     */
    public function hasPermission(Permission|string $permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions()->where('slug', $permission)->exists();
        }

        return $this->permissions()->where('id', $permission->id)->exists();
    }

    // ===================================
    // Module Access Methods
    // ===================================

    /**
     * The modules that belong to the role.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'role_module')
            ->withPivot('organization_id', 'has_access', 'granted_by')
            ->withTimestamps();
    }

    /**
     * Assign module access to role.
     *
     * @param  int|null  $grantedBy  User ID who granted access
     */
    public function giveModuleAccess(Module|int $module, ?int $grantedBy = null): self
    {
        $moduleId = $module instanceof Module ? $module->id : $module;

        $this->modules()->syncWithoutDetaching([
            $moduleId => [
                'organization_id' => $this->organization_id,
                'has_access' => true,
                'granted_by' => $grantedBy,
            ],
        ]);

        return $this;
    }

    /**
     * Revoke module access from role.
     */
    public function revokeModuleAccess(Module|int $module): self
    {
        $moduleId = $module instanceof Module ? $module->id : $module;

        $this->modules()->detach($moduleId);

        return $this;
    }

    /**
     * Check if role has access to module.
     */
    public function hasModuleAccess(Module|string|int $module): bool
    {
        if (is_string($module)) {
            return $this->modules()
                ->where('slug', $module)
                ->wherePivot('has_access', true)
                ->exists();
        }

        $moduleId = $module instanceof Module ? $module->id : $module;

        return $this->modules()
            ->where('modules.id', $moduleId)
            ->wherePivot('has_access', true)
            ->exists();
    }

    /**
     * Sync modules for this role.
     *
     * @param  array  $moduleIds  Array of module IDs
     * @param  int|null  $grantedBy  User ID who granted access
     */
    public function syncModules(array $moduleIds, ?int $grantedBy = null): self
    {
        $syncData = [];
        foreach ($moduleIds as $moduleId) {
            $syncData[$moduleId] = [
                'organization_id' => $this->organization_id,
                'has_access' => true,
                'granted_by' => $grantedBy,
            ];
        }

        $this->modules()->sync($syncData);

        return $this;
    }

    /**
     * Get all module slugs accessible by this role.
     */
    public function getModuleSlugs(): array
    {
        return $this->modules()
            ->wherePivot('has_access', true)
            ->pluck('slug')
            ->toArray();
    }
}
