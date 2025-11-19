<?php

namespace Lavalite\Core\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lavalite\Core\Models\Organization;

trait HasOrganization
{
    /**
     * Get the organization that owns the model.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope a query to only include models from a specific organization.
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Check if model belongs to given organization.
     */
    public function belongsToOrganization($organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }

    /**
     * Boot the trait.
     */
    protected static function bootHasOrganization()
    {
        // Automatically scope all queries by organization from request context
        static::addGlobalScope('organization', function ($query) {
            if ($organizationId = request()->header('X-Organization-ID')) {
                $query->where('organization_id', $organizationId);
            }
        });

        // Automatically set organization_id on create if not provided
        static::creating(function ($model) {
            if (! $model->organization_id && $organizationId = request()->header('X-Organization-ID')) {
                $model->organization_id = $organizationId;
            }
        });
    }
}
