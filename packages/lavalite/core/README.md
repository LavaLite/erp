# Lavalite Core

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-11.x%20%7C%2012.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)](https://php.net)

Core package for Lavalite microservices providing authentication, multi-tenancy, and shared utilities.

## Architecture

**Important**: This package is designed for **non-auth microservices**. User authentication and authorization are handled by the central **Auth microservice**.

- ✅ **User & Role Management**: Only in Auth microservice
- ✅ **Organization Cache**: Lightweight organization reference in each microservice
- ✅ **JWT Verification**: Tokens issued and verified by Auth service
- ✅ **Permission Checks**: Via API calls to Auth service

## Features

- 🔐 **JWT Authentication** - Token verification from Auth service
- 🏢 **Multi-Organization** - Organization context and caching
- 🎯 **Base Controllers** - Standard API response formats
- 🔧 **Middleware** - Organization context and access control
- 📦 **Models** - Organization cache, User DTO
- 🌐 **Service Integration** - Auth service client for microservice communication
- 🧪 **Testing Helpers** - Mocked JWT authentication for tests
- 🗄️ **Database Migrations** - Minimal organization cache table

## Installation

### Step 1: Add Repository to composer.json

Since this is a local package, add it to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../packages/lavalite/core"
        }
    ],
    "require": {
        "lavalite/core": "*"
    }
}
```

Or from the root of your monorepo:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/lavalite/core"
        }
    ]
}
```

### Step 2: Install Package

```bash
composer require lavalite/core
```

### Step 3: Publish Configuration

```bash
# Publish config file
php artisan vendor:publish --tag=lavalite-core-config

# Publish migrations (optional - they auto-load)
php artisan vendor:publish --tag=lavalite-core-migrations
```

### Step 4: Configure Environment

Add to your `.env`:

```env
# Auth Service Integration
AUTH_SERVICE_URL=http://localhost:8000
AUTH_SERVICE_API_KEY=your-api-key-here

# Optional: Override models
LAVALITE_USER_MODEL=App\Models\User
LAVALITE_ORGANIZATION_MODEL=App\Models\Organization
```

### Step 5: Run Migrations

```bash
php artisan migrate
```

## Usage

### 1. Understanding the Architecture

**Auth Microservice** (has User tables):
- Manages users, roles, permissions
- Issues JWT tokens
- Validates organization access
- Full organization data

**Other Microservices** (NO User tables):
- Verify JWT tokens from Auth service
- Cache basic organization info
- Check permissions via Auth service API
- Store business data with organization_id reference

### 2. Using the User DTO

The `User` class is a Data Transfer Object, NOT a database model:

```php
<?php

namespace App\Http\Controllers\Api;

use Lavalite\Core\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function index(Request $request)
    {
        // $request->user() returns user data from JWT token
        $user = $request->user(); // Array with user data
        
        // User data includes: id, email, name, organizations, roles, permissions
        // Note: User ID can be int or UUID depending on Auth service configuration
        
        $products = Product::paginate(15);
        return $this->paginated($products);
    }
}
```

**User ID Type**: The package supports both **integer** and **UUID** user IDs. Your Auth microservice can use either.

### 3. Using Organization Model

The Organization model caches basic org data:

```php
<?php

namespace App\Models;

use Lavalite\Core\Models\Organization;

// Sync organization from Auth service
$orgData = $authClient->getOrganization($orgId);
Organization::syncFromAuthService($orgData);

// Now you can query locally
$org = Organization::find($orgId);
```

### 5. Using HasOrganization Trait

For any model that belongs to an organization:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lavalite\Core\Traits\HasOrganization;

class Product extends Model
{
    use HasOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'sku',
        'price',
    ];
}
```

The trait automatically:
- Adds organization relationship
- Scopes queries by `X-Organization-ID` header
- Sets `organization_id` on create
- Provides helper methods

### 6. Using Base Controller

```php
<?php

namespace App\Http\Controllers\Api;

use Lavalite\Core\Http\Controllers\BaseController;
use App\Models\Product;

class ProductController extends BaseController
{
    public function index()
    {
        $products = Product::paginate(15);
        
        return $this->paginated($products, 'Products retrieved successfully');
    }

    public function store(Request $request)
    {
        $product = Product::create([
            'organization_id' => $this->getOrganizationId(),
            ...$request->validated(),
        ]);

        return $this->success($product, 'Product created', 201);
    }

    public function show(Product $product)
    {
        return $this->success($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        
        return $this->success(null, 'Product deleted');
    }
}
```

**Available Response Methods:**

- `success($data, $message, $code)` - Success response
- `error($message, $code, $errors)` - Error response
- `paginated($data, $message)` - Paginated response
- `getOrganizationId()` - Get current organization ID
- `getOrganization()` - Get current organization object
- `user()` - Get authenticated user

### 7. Using Middleware

#### Register Routes with Middleware

```php
// routes/api.php

// Protected routes with organization context
Route::middleware(['auth:api', 'organization'])->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orders', OrderController::class);
});

// Protected routes with organization access verification
Route::middleware(['auth:api', 'organization', 'organization.access'])->group(function () {
    Route::apiResource('settings', SettingsController::class);
});
```

**Available Middleware:**

- `organization` - Validates and sets organization context from `X-Organization-ID` header
- `organization.access` - Ensures authenticated user has access to the organization

### 8. Using Auth Service Client

```php
<?php

namespace App\Services;

use Lavalite\Core\Services\AuthServiceClient;

class MyService
{
    public function __construct(
        private AuthServiceClient $authClient
    ) {}

    public function verifyUser(string $token)
    {
        return $this->authClient->verifyToken($token);
    }

    public function getOrganizationDetails(string $orgId)
    {
        return $this->authClient->getOrganization($orgId);
    }

    public function checkPermission(int $userId, string $orgId, string $permission)
    {
        return $this->authClient->hasPermission($userId, $orgId, $permission);
    }
}
```

**Available Methods:**

- `verifyToken($token)` - Verify JWT token (cached 5 min)
- `getOrganization($id)` - Get organization details (cached 1 hour)
- `hasPermission($userId, $orgId, $permission)` - Check user permission
- `getUser($userId)` - Get user details (cached 1 hour)
- `clearUserCache($userId)` - Clear user cache
- `clearOrganizationCache($orgId)` - Clear organization cache

### 9. Using Test Helpers

```php
<?php

namespace Tests\Feature;

use Lavalite\Core\Testing\TestCase;

class ProductTest extends TestCase
{
    public function test_can_create_product()
    {
        // Create mock user with JWT (no database user needed)
        $user = $this->actingAsUser('org-uuid-here', [
            'id' => 1,
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
        ]);

        $response->assertStatus(201);
    }

    public function test_can_list_products_in_organization()
    {
        $org = $this->createOrganization();
        $user = $this->actingAsUser($org->id);

        $this->getJson('/api/products')
            ->assertOk();
    }
}
```

**Available Test Methods:**

- `actingAsUser($orgId, $attributes)` - Create mock user with JWT token
- `createOrganization($attributes)` - Create organization cache
- `withOrganization($org)` - Set organization header
- `mockAuthServiceUserVerification($userData)` - Mock Auth service response
- `mockOrganizationAccess($userId, $orgId, $hasAccess)` - Mock access check

## API Response Format

All responses follow a consistent format:

### Success Response

```json
{
    "success": true,
    "message": "Resource created successfully",
    "data": {
        "id": 1,
        "name": "Example"
    }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field": ["Error message"]
    }
}
```

### Paginated Response

```json
{
    "success": true,
    "message": "Success",
    "data": [...],
    "pagination": {
        "total": 100,
        "per_page": 15,
        "current_page": 1,
        "last_page": 7,
        "from": 1,
        "to": 15
    }
}
```

## Configuration

Configuration file: `config/lavalite-core.php`

```php
return [
    // Auth Service URL
    'auth_service_url' => env('AUTH_SERVICE_URL', 'http://localhost:8000'),
    'auth_service_api_key' => env('AUTH_SERVICE_API_KEY'),

    // Model overrides
    'user_model' => env('LAVALITE_USER_MODEL', \Lavalite\Core\Models\User::class),
    'organization_model' => env('LAVALITE_ORGANIZATION_MODEL', \Lavalite\Core\Models\Organization::class),

    // Organization header name
    'organization_header' => 'X-Organization-ID',

    // Cache TTL (seconds)
    'cache_ttl' => [
        'token' => 300,         // 5 minutes
        'user' => 3600,         // 1 hour
        'organization' => 3600, // 1 hour
    ],

    // Auto-register middleware
    'auto_register_middleware' => true,

    // Table prefix (optional)
    'table_prefix' => '',
];
```

## Database Tables

### organizations (Cache Table)

Minimal table to cache organization references:

- `id` (UUID, synced from Auth service)
- `name`, `slug`
- `timezone`, `currency`
- `status` (active, inactive, suspended)
- `settings` (JSON)
- `timestamps`, `soft_deletes`

**Note**: This is a cache table. Complete organization data lives in the Auth microservice.

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x
- tymon/jwt-auth ^2.2

## Development

### Running Tests

```bash
composer test
```

### Code Style

```bash
composer pint
```

## License

The MIT License (MIT). See [License File](LICENSE) for more information.

---

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write tests
5. Submit a pull request

## Support

For issues and questions:
- GitHub Issues: [lavalite/core/issues](https://github.com/lavalite/core/issues)
- Documentation: [docs.lavalite.org](https://docs.lavalite.org)

---

**Version:** 1.0.0  
**Maintainer:** Lavalite Team  
**Email:** team@lavalite.org
