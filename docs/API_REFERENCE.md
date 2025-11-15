# Lavalite - Laravel Sanctum Authentication API

This project is a Laravel application with Laravel Sanctum authentication configured.

## Setup Instructions

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Configuration**
   Update your `.env` file with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lavalite
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate
   ```

5. **Start the Development Server**
   ```bash
   php artisan serve
   ```

## API Endpoints

### Base URL
```
http://localhost:8000/api
```

### 1. Register a New User

**Endpoint:** `POST /api/register`

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
    "access_token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-11-12T10:00:00.000000Z",
        "updated_at": "2025-11-12T10:00:00.000000Z"
    }
}
```

### 2. Login

**Endpoint:** `POST /api/login`

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "access_token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-11-12T10:00:00.000000Z",
        "updated_at": "2025-11-12T10:00:00.000000Z"
    }
}
```

### 3. Get Authenticated User

**Endpoint:** `GET /api/user`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2025-11-12T10:00:00.000000Z",
    "updated_at": "2025-11-12T10:00:00.000000Z"
}
```

### 4. Logout

**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
    "message": "Successfully logged out"
}
```

## Testing with cURL

### Register
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Get User (replace TOKEN with your actual token)
```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"
```

### Logout (replace TOKEN with your actual token)
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"
```

## Testing with Postman

1. Import the collection or create requests manually
2. Set the base URL to `http://localhost:8000/api`
3. For protected routes, add the `Authorization` header with value `Bearer {token}`
4. Set `Accept: application/json` header for all requests

## Security Notes

- All passwords are automatically hashed using bcrypt
- Tokens are generated using Laravel Sanctum
- The API uses bearer token authentication
- Make sure to keep your tokens secure
- Tokens can be revoked by logging out

## Environment Variables

Key environment variables for Sanctum:

```
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SESSION_DOMAIN=localhost
```

## Additional Configuration

The project includes:
- Laravel Sanctum for API authentication
- Token-based authentication
- User registration and login
- Secure password hashing
- Token revocation on logout

## License

This project is open-sourced software licensed under the MIT license.
# Roles & Permissions Quick Reference

## User Model Methods

```php
// Roles
$user->assignRole('admin');
$user->assignRole($role); // Role model
$user->removeRole('admin');
$user->syncRoles(['admin', 'moderator']); // Remove all others
$user->hasRole('admin'); // true/false
$user->hasAnyRole(['admin', 'moderator']); // true if has any
$user->hasAllRoles(['admin', 'moderator']); // true if has all
$user->roles; // Collection of roles

// Permissions
$user->givePermissionTo('edit-posts');
$user->givePermissionTo($permission); // Permission model
$user->revokePermissionTo('edit-posts');
$user->hasPermission('edit-posts'); // Checks direct + role permissions
$user->hasAnyPermission(['edit-posts', 'delete-posts']);
$user->hasAllPermissions(['edit-posts', 'delete-posts']);
$user->getAllPermissions(); // All permissions (direct + from roles)
$user->permissions; // Direct permissions only
```

## Role Model Methods

```php
$role->givePermissionTo('edit-posts');
$role->givePermissionTo($permission); // Permission model
$role->revokePermissionTo('edit-posts');
$role->hasPermission('edit-posts'); // true/false
$role->permissions; // Collection of permissions
$role->users; // Collection of users with this role
```

## Route Middleware

```php
// Require specific role(s)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {});
Route::middleware(['auth:sanctum', 'role:admin,moderator'])->group(function () {});

// Require specific permission(s)
Route::middleware(['auth:sanctum', 'permission:edit-posts'])->group(function () {});
Route::middleware(['auth:sanctum', 'permission:edit-posts,delete-posts'])->group(function () {});
```

## API Endpoints Summary

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/me` | Get current user with roles/permissions | User |
| GET | `/api/users` | List all users | Admin |
| GET | `/api/users/{id}` | Get user details | Admin |
| GET | `/api/roles` | List all roles | Admin |
| POST | `/api/roles` | Create role | Admin |
| GET | `/api/roles/{id}` | Get role details | Admin |
| PUT | `/api/roles/{id}` | Update role | Admin |
| DELETE | `/api/roles/{id}` | Delete role | Admin |
| POST | `/api/roles/{id}/assign-user` | Assign role to user | Admin |
| POST | `/api/roles/{id}/remove-user` | Remove role from user | Admin |
| POST | `/api/roles/{id}/assign-permission` | Add permission to role | Admin |
| POST | `/api/roles/{id}/remove-permission` | Remove permission from role | Admin |
| GET | `/api/permissions` | List all permissions | Admin |
| POST | `/api/permissions` | Create permission | Admin |
| GET | `/api/permissions/{id}` | Get permission details | Admin |
| PUT | `/api/permissions/{id}` | Update permission | Admin |
| DELETE | `/api/permissions/{id}` | Delete permission | Admin |
| POST | `/api/permissions/{id}/assign-user` | Give permission to user | Admin |
| POST | `/api/permissions/{id}/remove-user` | Revoke permission from user | Admin |

## Default Setup

**Roles:**
- `admin` - Administrator (all permissions)
- `moderator` - Moderator (content management)
- `user` - User (basic access) - *default for new registrations*

**Permissions:**
- User management: `view-users`, `create-users`, `edit-users`, `delete-users`
- Post management: `view-posts`, `create-posts`, `edit-posts`, `delete-posts`
- Role management: `view-roles`, `create-roles`, `edit-roles`, `delete-roles`
- Permission management: `assign-roles`, `assign-permissions`

## Common Tasks

### Make User an Admin
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->assignRole('admin');
```

### Create Custom Permission
```bash
curl -X POST http://localhost:8000/api/permissions \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Custom Action", "slug": "custom-action", "description": "..."}'
```

### Check User's Permissions
```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer TOKEN"
```

### Assign Role to User
```bash
curl -X POST http://localhost:8000/api/roles/1/assign-user \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"user_id": 5}'
```
