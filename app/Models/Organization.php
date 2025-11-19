<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'description',
        'settings',
        'timezone',
        'country',
        'currency',
        'is_active',
        'subscription_status',
        'max_users',
        'trial_ends_at',
        'subscription_id',
        'plan_id',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The users that belong to the organization.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_active', 'joined_at')
            ->withTimestamps();
    }

    /**
     * The roles that belong to the organization.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * The permissions that belong to the organization.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * The modules that are enabled for this organization.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'organization_module')
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
     * Get only enabled modules for this organization
     */
    public function enabledModules()
    {
        return $this->modules()->wherePivot('is_enabled', true);
    }

    /**
     * The teams that belong to the organization.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Active teams only.
     */
    public function activeTeams(): HasMany
    {
        return $this->hasMany(Team::class)->where('is_active', true);
    }

    /**
     * Root teams (no parent) in this organization.
     */
    public function rootTeams(): HasMany
    {
        return $this->hasMany(Team::class)->whereNull('parent_team_id');
    }

    /**
     * Check if organization has access to a specific module
     */
    public function hasModule($moduleSlug): bool
    {
        return $this->modules()
            ->where('slug', $moduleSlug)
            ->wherePivot('is_enabled', true)
            ->exists();
    }

    /**
     * Enable a module for this organization
     */
    public function enableModule(Module $module, array $settings = [], array $limits = [], $expiresAt = null): self
    {
        $this->modules()->syncWithoutDetaching([
            $module->id => [
                'is_enabled' => true,
                'enabled_at' => now(),
                'expires_at' => $expiresAt,
                'settings' => $settings,
                'limits' => $limits,
            ],
        ]);

        return $this;
    }

    /**
     * Disable a module for this organization
     */
    public function disableModule(Module $module): self
    {
        $this->modules()->updateExistingPivot($module->id, [
            'is_enabled' => false,
        ]);

        return $this;
    }

    /**
     * Check if organization is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Add user to organization.
     */
    public function addUser(User $user): self
    {
        $this->users()->syncWithoutDetaching($user);

        return $this;
    }

    /**
     * Remove user from organization.
     */
    public function removeUser(User $user): self
    {
        $this->users()->detach($user);

        return $this;
    }

    /**
     * Check if user belongs to organization.
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    // ==========================================
    // Billing & Subscription Methods
    // ==========================================

    /**
     * Get the count of active users in this organization.
     */
    public function getActiveUsersCount(): int
    {
        return $this->users()->wherePivot('is_active', true)->count();
    }

    /**
     * Get the count of all users (active and inactive) in this organization.
     */
    public function getTotalUsersCount(): int
    {
        return $this->users()->count();
    }

    /**
     * Get the count of enabled modules for this organization.
     */
    public function getEnabledModulesCount(): int
    {
        return $this->modules()->wherePivot('is_enabled', true)->count();
    }

    /**
     * Check if organization can add more users based on max_users limit.
     */
    public function canAddUsers(int $count = 1): bool
    {
        // If max_users is null, unlimited users allowed
        if ($this->max_users === null) {
            return true;
        }

        $currentActiveUsers = $this->getActiveUsersCount();

        return ($currentActiveUsers + $count) <= $this->max_users;
    }

    /**
     * Check if organization has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'trial']);
    }

    /**
     * Check if organization is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has expired.
     */
    public function isTrialExpired(): bool
    {
        return $this->subscription_status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isPast();
    }

    /**
     * Check if organization subscription is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->subscription_status === 'suspended';
    }

    /**
     * Check if organization subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->subscription_status === 'cancelled';
    }

    /**
     * Get usage data for billing purposes.
     */
    public function getUsageData(): array
    {
        return [
            'organization_id' => $this->id,
            'organization_name' => $this->name,
            'subscription_status' => $this->subscription_status,
            'subscription_id' => $this->subscription_id,
            'plan_id' => $this->plan_id,
            'max_users' => $this->max_users,
            'active_users_count' => $this->getActiveUsersCount(),
            'total_users_count' => $this->getTotalUsersCount(),
            'enabled_modules_count' => $this->getEnabledModulesCount(),
            'enabled_modules' => $this->enabledModules()->get(['modules.id', 'modules.name', 'modules.slug', 'modules.code'])->map(function ($module) {
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'slug' => $module->slug,
                    'code' => $module->code,
                    'enabled_at' => $module->pivot->enabled_at,
                    'expires_at' => $module->pivot->expires_at,
                ];
            }),
            'is_trial' => $this->isOnTrial(),
            'trial_ends_at' => $this->trial_ends_at,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Activate subscription.
     */
    public function activateSubscription(string $subscriptionId, ?string $planId = null): self
    {
        $this->update([
            'subscription_status' => 'active',
            'subscription_id' => $subscriptionId,
            'plan_id' => $planId,
        ]);

        return $this;
    }

    /**
     * Suspend subscription.
     */
    public function suspendSubscription(): self
    {
        $this->update(['subscription_status' => 'suspended']);

        return $this;
    }

    /**
     * Cancel subscription.
     */
    public function cancelSubscription(): self
    {
        $this->update(['subscription_status' => 'cancelled']);

        return $this;
    }

    /**
     * Update user limit.
     */
    public function updateUserLimit(?int $maxUsers): self
    {
        $this->update(['max_users' => $maxUsers]);

        return $this;
    }
}
