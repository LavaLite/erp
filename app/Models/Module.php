<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'icon',
        'version',
        'category',
        'display_order',
        'dependencies',
        'metadata',
        'is_core',
        'is_active',
        'requires_license',
    ];

    protected $casts = [
        'dependencies' => 'array',
        'metadata' => 'array',
        'is_core' => 'boolean',
        'is_active' => 'boolean',
        'requires_license' => 'boolean',
    ];

    /**
     * Organizations that have this module enabled
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_module')
            ->withPivot([
                'is_enabled',
                'enabled_at',
                'expires_at',
                'settings',
                'limits',
            ])
            ->withTimestamps();
    }

    /**
     * Get modules by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get only active modules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get core modules
     */
    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }

    /**
     * Get non-core modules
     */
    public function scopeNonCore($query)
    {
        return $query->where('is_core', false);
    }

    /**
     * Check if module has dependencies
     */
    public function hasDependencies(): bool
    {
        return ! empty($this->dependencies);
    }

    /**
     * Get dependent modules
     */
    public function getDependentModules()
    {
        if (! $this->hasDependencies()) {
            return collect([]);
        }

        return static::whereIn('slug', $this->dependencies)->get();
    }
}
