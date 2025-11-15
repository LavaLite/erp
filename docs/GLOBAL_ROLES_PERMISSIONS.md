# Global Roles and Permissions

## Overview

The Lavalite Auth system supports both **organization-scoped** and **global** roles and permissions. Global roles and permissions have a `organization_id` of `null` and can be accessed across all organizations.

## Key Concepts

### Organization-Scoped Roles/Permissions
- Have a specific `organization_id` value (UUID)
- Only available within that specific organization
- Most common use case for multi-organization applications

### Global Roles/Permissions
- Have `organization_id` set to `null`
- Available across ALL organizations
- Can only be created and managed by **Super Admins**
- Useful for system-wide permissions like "super_admin", "support_staff", etc.

## Super Admin

### What is a Super Admin?

A Super Admin is a special user who has the `is_super_admin` flag set to `true` in the users table. Super Admins have the exclusive right to:

1. Create global roles (with `organization_id = null`)
2. Update existing roles to be global
3. Create global permissions (with `organization_id = null`)
4. Update existing permissions to be global

### Creating a Super Admin

```php
use App\Models\User;

$superAdmin = User::create([
    'first_name' => 'Super',
    'last_name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'is_super_admin' => true,
    'is_active' => true,
]);
```

### Checking if User is Super Admin

```php
if ($user->isSuperAdmin()) {
    // User is a super admin
}
```

## Creating Global Roles

### Via API (Super Admin Only)

**Endpoint:** `POST /api/roles`

**Request:**
```json
{
    "name": "System Administrator",
    "slug": "system-admin",
    "description": "Global system administrator role",
    "organization_id": null
}
```

**Response (Success - 201):**
```json
{
    "id": 1,
    "name": "System Administrator",
    "slug": "system-admin",
    "description": "Global system administrator role",
    "organization_id": null,
    "created_at": "2025-01-12T10:00:00.000000Z",
    "updated_at": "2025-01-12T10:00:00.000000Z"
}
```

**Response (Forbidden - 403):**
```json
{
    "error": "Only super admins can create global roles"
}
```

### Programmatically

```php
use App\Models\Role;

// This will fail if the authenticated user is not a super admin
$globalRole = Role::create([
    'name' => 'System Administrator',
    'slug' => 'system-admin',
    'description' => 'Global system administrator role',
    'organization_id' => null,
]);
```

## Creating Global Permissions

### Via API (Super Admin Only)

**Endpoint:** `POST /api/permissions`

**Request:**
```json
{
    "name": "Manage All Organizations",
    "slug": "manage-all-organizations",
    "description": "Can manage all organizations in the system",
    "organization_id": null
}
```

**Response (Success - 201):**
```json
{
    "id": 1,
    "name": "Manage All Organizations",
    "slug": "manage-all-organizations",
    "description": "Can manage all organizations in the system",
    "organization_id": null,
    "created_at": "2025-01-12T10:00:00.000000Z",
    "updated_at": "2025-01-12T10:00:00.000000Z"
}
```

**Response (Forbidden - 403):**
```json
{
    "error": "Only super admins can create global permissions"
}
```

## How Global Roles/Permissions Work

### Automatic Inclusion

When checking roles or permissions for a user in a specific organization, the system **automatically includes** global roles and permissions:

```php
// Check if user has role in organization
// This will return true if:
// 1. User has the role specifically in that organization, OR
// 2. User has a GLOBAL role with that slug
$hasRole = $user->hasRoleInOrganization('admin', $organization);

// Get all roles for user in organization
// Returns both organization-specific AND global roles
$roles = $user->rolesInOrganization($organization)->get();

// Check if user has permission in organization
// This will return true if:
// 1. User has the permission directly in that organization, OR
// 2. User has a GLOBAL permission with that slug, OR
// 3. User has a role (organization-specific or global) that includes this permission
$hasPermission = $user->hasPermissionInOrganization('view-reports', $organization);

// Get all permissions for user in organization
// Returns organization-specific AND global permissions
$permissions = $user->getAllPermissionsInOrganization($organization);
```

### Trait Methods Updated

The following trait methods have been updated to automatically include global roles/permissions:

- `rolesInOrganization()` - Includes global roles (`organization_id = null`)
- `hasRoleInOrganization()` - Checks both organization-specific and global roles
- `hasPermissionInOrganization()` - Checks both organization-specific and global permissions
- `getAllPermissionsInOrganization()` - Returns both organization-specific and global permissions

## Use Cases

### Example 1: System Administrator

```php
// Create a global "system-admin" role
$systemAdminRole = Role::create([
    'name' => 'System Administrator',
    'slug' => 'system-admin',
    'organization_id' => null, // Global role
]);

// Create global permissions
$manageOrgs = Permission::create([
    'name' => 'Manage All Organizations',
    'slug' => 'manage-all-organizations',
    'organization_id' => null,
]);

$viewAllData = Permission::create([
    'name' => 'View All Data',
    'slug' 'view-all-data',
    'organization_id' => null,
]);

// Assign permissions to the global role
$systemAdminRole->permissions()->attach([$manageOrgs->id, $viewAllData->id]);

// Assign the global role to a user in ANY organization
$user->assignRoleInOrganization($systemAdminRole, $organization);

// Now this user has system admin privileges in ALL organizations
foreach ($user->organizations as $organization) {
    $hasRole = $user->hasRoleInOrganization('system-admin', $organization); // true for all
}
```

### Example 2: Support Staff

```php
// Create a global "support-staff" role
$supportRole = Role::create([
    'name' => 'Support Staff',
    'slug' => 'support-staff',
    'organization_id' => null,
]);

// Create global support permissions
$viewTickets = Permission::create([
    'name' => 'View All Tickets',
    'slug' => 'view-all-tickets',
    'organization_id' => null,
]);

$supportRole->permissions()->attach($viewTickets->id);

// Support staff can access tickets across all organizations
$user->assignRoleInOrganization($supportRole, $anyOrganization);
```

## Database Schema

### Roles Table

```php
Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->uuid('organization_id')->nullable(); // NULL for global roles
    $table->string('name');
    $table->string('slug');
    $table->text('description')->nullable();
    $table->timestamps();
    
    // Unique per organization (allows same slug across different organizations)
    $table->unique(['organization_id', 'slug']);
    $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
});
```

### Permissions Table

```php
Schema::create('permissions', function (Blueprint $table) {
    $table->id();
    $table->uuid('organization_id')->nullable(); // NULL for global permissions
    $table->string('name');
    $table->string('slug');
    $table->text('description')->nullable();
    $table->timestamps();
    
    // Unique per organization (allows same slug across different organizations)
    $table->unique(['organization_id', 'slug']);
    $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
});
```

## Security Considerations

1. **Super Admin Access**: Only users with `is_super_admin = true` can create or modify global roles/permissions
2. **API Validation**: Controllers validate super admin status before allowing `organization_id = null`
3. **Automatic Inclusion**: Global roles/permissions are automatically available in all organizations
4. **Unique Constraints**: The database ensures slug uniqueness per organization (including global with `organization_id = null`)

## Best Practices

1. **Use Global Roles Sparingly**: Only create global roles for truly system-wide functions
2. **Organization-Specific First**: Default to organization-specific roles/permissions for most use cases
3. **Clear Naming**: Use clear names for global roles (e.g., "System Administrator", "Support Staff")
4. **Document Global Roles**: Keep documentation of all global roles and their purposes
5. **Regular Audits**: Regularly review global role assignments for security

## Migration Guide

If you're upgrading from a system without global roles:

1. **Database**: The schema already supports `organization_id = null` (nullable column)
2. **Add Super Admin Flag**: Run fresh migration to add `is_super_admin` column to users table
3. **Create Super Admin**: Update at least one user to be a super admin
4. **Create Global Roles**: Use the super admin account to create global roles as needed
5. **Test**: Verify that global roles are accessible across all organizations

## Troubleshooting

### "Only super admins can create global roles"

**Problem**: Getting 403 error when trying to create a global role/permission.

**Solution**: Ensure the authenticated user has `is_super_admin = true`:

```php
$user->update(['is_super_admin' => true]);
```

### Global roles not appearing in organization

**Problem**: Global roles don't show up when querying roles for a specific organization.

**Solution**: Make sure you're using the updated trait methods:
- Use `rolesInOrganization()` instead of custom queries
- Use `hasRoleInOrganization()` for role checks
- Use `getAllPermissionsInOrganization()` for permission lists

### Can't update existing role to global

**Problem**: Trying to update `organization_id` to `null` fails.

**Solution**: Only super admins can make this change:

```php
// As a super admin
$role->update(['organization_id' => null]);
```
