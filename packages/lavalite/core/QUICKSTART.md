# Lavalite Core - Quick Start Guide

## 🏗️ Architecture First!

**IMPORTANT**: This package is for **non-auth microservices** only.

- ✅ **Auth Microservice**: Has users, roles, permissions tables
- ✅ **Other Microservices**: NO user tables - just organization cache
- ✅ **User Data**: Comes from JWT tokens issued by Auth service
- ✅ **Permissions**: Checked via Auth service API

## Package Structure

```
packages/lavalite/core/
├── config/
│   └── lavalite-core.php          # Package configuration
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_organizations_table.php
│       └── 2024_01_01_000002_create_organization_user_table.php
├── src/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── BaseController.php        # Base API controller
│   │   └── Middleware/
│   │       ├── SetOrganizationContext.php
│   │       └── EnsureOrganizationAccess.php
│   ├── Models/
│   │   ├── Organization.php
│   │   └── User.php
│   ├── Services/
│   │   └── AuthServiceClient.php         # Auth microservice integration
│   ├── Testing/
│   │   └── TestCase.php                  # Base test case
│   ├── Traits/
│   │   └── HasOrganization.php           # Multi-tenancy trait
│   └── LavaliteCoreServiceProvider.php
├── CHANGELOG.md
├── composer.json
├── LICENSE
└── README.md
```

## Installation in New Microservice

### 1. Update Root composer.json

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

### 2. Install Package

```bash
composer require lavalite/core
```

### 3. Publish Assets

```bash
# Publish config
php artisan vendor:publish --tag=lavalite-core-config

# Migrations auto-load, or publish them:
php artisan vendor:publish --tag=lavalite-core-migrations
```

### 4. Configure .env

```env
AUTH_SERVICE_URL=http://localhost:8000
AUTH_SERVICE_API_KEY=your-api-key
```

### 5. Run Migrations

```bash
php artisan migrate
```

## Usage Examples

### User Data from JWT (No Database)

```php
<?php

class ProductController extends BaseController
{
    public function index(Request $request)
    {
        // User data comes from JWT token, not database
        $user = $request->user(); // Object with id, email, name, etc.
        
        $products = Product::paginate(15);
        return $this->paginated($products);
    }
}
```

### Organization Cache Model

```php
<?php

use Lavalite\Core\Models\Organization;
use Lavalite\Core\Services\AuthServiceClient;

// Sync from Auth service
$authClient = app(AuthServiceClient::class);
$orgData = $authClient->getOrganization($orgId);
Organization::syncFromAuthService($orgData);

// Now use locally
$org = Organization::find($orgId);
```

### Create a Model with Organization

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lavalite\Core\Traits\HasOrganization;

class Invoice extends Model
{
    use HasOrganization;

    protected $fillable = [
        'organization_id',
        'invoice_number',
        'amount',
        'status',
    ];
}
```

### Create API Controller

```php
<?php

namespace App\Http\Controllers\Api;

use Lavalite\Core\Http\Controllers\BaseController;
use App\Models\Invoice;

class InvoiceController extends BaseController
{
    public function index()
    {
        // Automatically scoped by organization
        $invoices = Invoice::paginate(15);
        return $this->paginated($invoices);
    }

    public function store(Request $request)
    {
        $invoice = Invoice::create([
            'organization_id' => $this->getOrganizationId(),
            ...$request->validated(),
        ]);

        return $this->success($invoice, 'Invoice created', 201);
    }
}
```

### Define Routes

```php
// routes/api.php

use App\Http\Controllers\Api\InvoiceController;

Route::middleware(['auth:api', 'organization'])->group(function () {
    Route::apiResource('invoices', InvoiceController::class);
});
```

### Write Tests (Mock User, No Database)

```php
<?php

namespace Tests\Feature;

use Lavalite\Core\Testing\TestCase;
use App\Models\Invoice;

class InvoiceTest extends TestCase
{
    public function test_can_create_invoice()
    {
        // Mock user with JWT (no database user created)
        $user = $this->actingAsUser('org-123', [
            'id' => 1,
            'email' => 'test@example.com',
            'permissions' => ['invoices.create']
        ]);

        $response = $this->postJson('/api/invoices', [
            'invoice_number' => 'INV-001',
            'amount' => 1000,
        ]);

        $response->assertCreated();
    }
}
```

## What's Included

✅ **User DTO**: Data structure for user info from JWT (NOT a database model)  
✅ **Organization Cache**: Lightweight table for org references  
✅ **Traits**: HasOrganization (auto-scoping, auto-set organization_id)  
✅ **Middleware**: Organization context, access control via Auth service  
✅ **Controllers**: BaseController with success/error/paginated responses  
✅ **Services**: AuthServiceClient for Auth microservice integration  
✅ **Testing**: TestCase with mocked JWT, no database users  
✅ **Migrations**: organizations cache table only  
✅ **Config**: Customizable auth service URL, cache TTL

## Database Tables Created

**Only one table** in non-auth microservices:

```sql
organizations (
    id UUID,           -- Synced from Auth service
    name, slug,
    timezone, currency,
    status, settings
)
```

**NO user, role, or permission tables!**

## Response Format

All API responses use consistent format:

```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

## Next Steps

1. Create your domain models using `HasOrganization` trait
2. Build controllers extending `BaseController`
3. Define routes with `organization` middleware
4. Write tests extending `TestCase`
5. Deploy as independent microservice

---

**Documentation**: See `packages/lavalite/core/README.md` for full details
