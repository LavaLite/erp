<?php

namespace Lavalite\Core\Models;

/**
 * User Data Transfer Object
 *
 * This is NOT a database model. It represents user data from JWT token
 * or Auth Service API. The actual User table only exists in the Auth microservice.
 *
 * Use this class to type-hint user data in your microservice.
 *
 * Note: User ID can be either int or string (UUID) depending on Auth service configuration.
 */
class User
{
    public function __construct(
        public int|string $id,  // Supports both int and UUID
        public string $email,
        public string $name,
        public ?string $first_name = null,
        public ?string $last_name = null,
        public ?string $phone = null,
        public ?string $avatar = null,
        public ?string $timezone = null,
        public ?string $language = null,
        public ?string $status = 'active',
        public ?array $organizations = [],
        public ?array $permissions = [],
        public ?array $roles = [],
    ) {}

    /**
     * Create User from JWT payload or API response.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? $data['sub'] ?? 0,
            email: $data['email'] ?? '',
            name: $data['name'] ?? '',
            first_name: $data['first_name'] ?? null,
            last_name: $data['last_name'] ?? null,
            phone: $data['phone'] ?? null,
            avatar: $data['avatar'] ?? null,
            timezone: $data['timezone'] ?? null,
            language: $data['language'] ?? null,
            status: $data['status'] ?? 'active',
            organizations: $data['organizations'] ?? [],
            permissions: $data['permissions'] ?? [],
            roles: $data['roles'] ?? [],
        );
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user has permission.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    /**
     * Check if user has role.
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'status' => $this->status,
            'organizations' => $this->organizations,
            'permissions' => $this->permissions,
            'roles' => $this->roles,
        ];
    }
}
