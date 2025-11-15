# Multi-Organization System Documentation

## Overview

The Lavalite Laravel application includes a comprehensive Multi-Organization RBAC (Role-Based Access Control) system with the following features:

- **Organizations**: Multi-organization support with isolated data
- **Roles**: Group permissions together (Admin, Moderator, User) - scoped per organization
- **Permissions**: Define specific actions users can perform - scoped per organization
- **Global Roles & Permissions**: System-wide roles/permissions available across all organizations
- **Direct Permissions**: Assign permissions directly to users
- **Role-based Permissions**: Users inherit permissions from their roles
- **Middleware**: Protect routes based on roles and permissions within organization context

## Database Structure

### Tables Created

1. **roles** - Stores role definitions
   - `id`, `name`, `slug`, `description`, `timestamps`

2. **permissions** - Stores permission definitions
   - `id`, `name`, `slug`, `description`, `timestamps`

3. **role_user** - Pivot table linking users to roles
   - `id`, `role_id`, `user_id`, `timestamps`

4. **permission_role** - Pivot table linking permissions to roles
   - `id`, `permission_id`, `role_id`, `timestamps`

5. **permission_user** - Pivot table for direct user permissions
   - `id`, `permission_id`, `user_id`, `timestamps`

## Default Roles and Permissions

### Roles

1. **Admin** - Full system access
2. **Moderator** - Content moderation access
3. **User** - Basic user access (default for new registrations)

### Permissions

**User Management:**
- `view-users` - Can view users list
- `create-users` - Can create new users
- `edit-users` - Can edit existing users
- `delete-users` - Can delete users

**Post Management:**
- `view-posts` - Can view posts
- `create-posts` - Can create posts
- `edit-posts` - Can edit posts
- `delete-posts` - Can delete posts

**Role Management:**
- `view-roles` - Can view roles
- `create-roles` - Can create roles
- `edit-roles` - Can edit roles
- `delete-roles` - Can delete roles
- `assign-roles` - Can assign roles to users
- `assign-permissions` - Can assign permissions

### Default Permission Assignment

- **Admin**: All permissions
- **Moderator**: view-users, view-posts, create-posts, edit-posts, delete-posts
- **User**: view-posts, create-posts

## API Endpoints

### Authentication Endpoints

#### Register
```
POST /api/register
```
**Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```
**Response:** User automatically assigned 'user' role

#### Login
```
POST /api/login
```
**Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```
**Response:** Includes user with roles and permissions

### User Endpoints

#### Get Current User with Roles & Permissions
```
GET /api/me
Authorization: Bearer {token}
```

#### Get All Users (Admin only)
```
GET /api/users
Authorization: Bearer {token}
```

#### Get Specific User (Admin only)
```
GET /api/users/{id}
Authorization: Bearer {token}
```

### Role Management Endpoints (Admin Only)

#### List All Roles
```
GET /api/roles
Authorization: Bearer {token}
```

#### Create Role
```
POST /api/roles
Authorization: Bearer {token}

Body:
{
    "name": "Editor",
    "slug": "editor",
    "description": "Can edit content"
}
```

#### Get Single Role
```
GET /api/roles/{id}
Authorization: Bearer {token}
```

#### Update Role
```
PUT /api/roles/{id}
Authorization: Bearer {token}

Body:
{
    "name": "Senior Editor",
    "description": "Senior content editor"
}
```

#### Delete Role
```
DELETE /api/roles/{id}
Authorization: Bearer {token}
```

#### Assign Role to User
```
POST /api/roles/{id}/assign-user
Authorization: Bearer {token}

Body:
{
    "user_id": 1
}
```

#### Remove Role from User
```
POST /api/roles/{id}/remove-user
Authorization: Bearer {token}

Body:
{
    "user_id": 1
}
```

#### Assign Permission to Role
```
POST /api/roles/{id}/assign-permission
Authorization: Bearer {token}

Body:
{
    "permission_id": 1
}
```

#### Remove Permission from Role
```
POST /api/roles/{id}/remove-permission
Authorization: Bearer {token}

Body:
{
    "permission_id": 1
}
```

### Permission Management Endpoints (Admin Only)

#### List All Permissions
```
GET /api/permissions
Authorization: Bearer {token}
```

#### Create Permission
```
POST /api/permissions
Authorization: Bearer {token}

Body:
{
    "name": "Publish Posts",
    "slug": "publish-posts",
    "description": "Can publish posts"
}
```

#### Get Single Permission
```
GET /api/permissions/{id}
Authorization: Bearer {token}
```

#### Update Permission
```
PUT /api/permissions/{id}
Authorization: Bearer {token}

Body:
{
    "name": "Publish Content",
    "description": "Can publish any content"
}
```

#### Delete Permission
```
DELETE /api/permissions/{id}
Authorization: Bearer {token}
```

#### Assign Permission Directly to User
```
POST /api/permissions/{id}/assign-user
Authorization: Bearer {token}

Body:
{
    "user_id": 1
}
```

#### Remove Permission from User
```
POST /api/permissions/{id}/remove-user
Authorization: Bearer {token}

Body:
{
    "user_id": 1
}
```

## Using Middleware

### Protect Routes by Role

```php
// Single role
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin only routes
});

// Multiple roles (user needs ANY of these roles)
Route::middleware(['auth:sanctum', 'role:admin,moderator'])->group(function () {
    // Admin or Moderator routes
});
```

### Protect Routes by Permission

```php
// Single permission
Route::middleware(['auth:sanctum', 'permission:edit-posts'])->group(function () {
    // Routes for users with edit-posts permission
});

// Multiple permissions (user needs ANY of these permissions)
Route::middleware(['auth:sanctum', 'permission:edit-posts,delete-posts'])->group(function () {
    // Routes requiring either permission
});
```

## Programmatic Usage

### Check if User Has Role

```php
// Single role
if ($user->hasRole('admin')) {
    // User is admin
}

// Multiple roles (has ANY)
if ($user->hasAnyRole(['admin', 'moderator'])) {
    // User is admin or moderator
}

// Multiple roles (has ALL)
if ($user->hasAllRoles(['admin', 'moderator'])) {
    // User has both roles
}
```

### Check if User Has Permission

```php
// Single permission
if ($user->hasPermission('edit-posts')) {
    // User can edit posts
}

// Multiple permissions (has ANY)
if ($user->hasAnyPermission(['edit-posts', 'delete-posts'])) {
    // User can edit or delete posts
}

// Multiple permissions (has ALL)
if ($user->hasAllPermissions(['view-posts', 'edit-posts'])) {
    // User can both view and edit posts
}
```

### Assign Roles to Users

```php
// Assign by slug
$user->assignRole('admin');

// Assign by model
$role = Role::find(1);
$user->assignRole($role);

// Sync roles (removes all others)
$user->syncRoles(['admin', 'moderator']);

// Remove role
$user->removeRole('moderator');
```

### Assign Permissions to Users

```php
// Direct permission to user
$user->givePermissionTo('edit-posts');

// Revoke permission
$user->revokePermissionTo('edit-posts');

// Get all permissions (direct + from roles)
$allPermissions = $user->getAllPermissions();
```

### Assign Permissions to Roles

```php
$role = Role::find(1);

// Give permission
$role->givePermissionTo('edit-posts');

// Revoke permission
$role->revokePermissionTo('edit-posts');

// Check if role has permission
if ($role->hasPermission('edit-posts')) {
    // Role has this permission
}
```

## Testing Examples

### Create an Admin User

```bash
# First, register a user
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Then manually assign admin role via Tinker
php artisan tinker
>>> $user = User::where('email', 'admin@example.com')->first();
>>> $user->assignRole('admin');
>>> exit
```

### Test Role-Based Access

```bash
# Login as admin
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'

# Use the token to access admin routes
curl -X GET http://localhost:8000/api/roles \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Assign Role to Another User

```bash
curl -X POST http://localhost:8000/api/roles/2/assign-user \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 3
  }'
```

## Advanced Features

### Custom Permissions

You can create custom permissions for your specific needs:

```bash
curl -X POST http://localhost:8000/api/permissions \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Export Data",
    "slug": "export-data",
    "description": "Can export data to CSV"
  }'
```

### Direct User Permissions

Grant specific permissions to individual users without changing their role:

```bash
# Give user permission to delete posts even if their role doesn't have it
curl -X POST http://localhost:8000/api/permissions/8/assign-user \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 5
  }'
```

## Best Practices

1. **Use Roles for Groups**: Assign roles to users, not individual permissions
2. **Use Permissions for Fine-Grained Control**: Only assign direct permissions for exceptions
3. **Consistent Naming**: Use kebab-case for slugs (e.g., `edit-posts`)
4. **Descriptive Names**: Make permission names clear and actionable
5. **Middleware Protection**: Always protect admin routes with role middleware
6. **Regular Audits**: Review user roles and permissions periodically

## Troubleshooting

### User Can't Access Route
1. Check if user is authenticated
2. Verify user has the required role/permission
3. Check middleware is properly applied to route
4. Review role-permission assignments

### Permission Not Working
1. Ensure permission exists in database
2. Check permission slug matches exactly
3. Verify permission is assigned to user's role or directly to user
4. Clear application cache: `php artisan cache:clear`

## Security Notes

- All role and permission endpoints require authentication
- Only admins can manage roles and permissions
- Users automatically get 'user' role on registration
- Middleware checks both direct permissions and role-based permissions
- Role/permission checks are case-sensitive for slugs

## Database Seeding

To reset and reseed roles and permissions:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

## License

This role and permission system is part of the Lavalite project and follows the same MIT license.
