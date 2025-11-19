<?php

namespace Lavalite\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Organization Model
 *
 * This model is used to store organization references in microservices.
 * The complete organization data exists in the Auth microservice.
 * This table caches basic organization info for queries and relationships.
 */
class Organization extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',           // Synced from Auth service
        'name',
        'slug',
        'timezone',
        'currency',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Check if organization is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope to only active organizations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Sync organization data from Auth service.
     */
    public static function syncFromAuthService(array $data): self
    {
        return self::updateOrCreate(
            ['id' => $data['id']],
            [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'timezone' => $data['timezone'] ?? 'UTC',
                'currency' => $data['currency'] ?? 'USD',
                'status' => $data['status'] ?? 'active',
                'settings' => $data['settings'] ?? [],
            ]
        );
    }
}
