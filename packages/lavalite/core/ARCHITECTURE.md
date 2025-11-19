# Lavalite Core Package - Architecture Overview

## 🏗️ Microservices Architecture

### Auth Microservice (Central Authority)
**Has full database tables:**
- ✅ `users` table
- ✅ `roles` table
- ✅ `permissions` table
- ✅ `organizations` table (complete data)
- ✅ `organization_user` pivot
- ✅ `role_user` pivot
- ✅ `permission_role` pivot

**Responsibilities:**
- User authentication (JWT token issuance)
- User management
- Role & permission management
- Organization management
- Access control validation

---

### Other Microservices (Inventory, CRM, Accounting, etc.)
**Has minimal tables:**
- ✅ `organizations` table (cache only - id, name, slug, timezone, currency, status)
- ✅ Business-specific tables with `organization_id` foreign key
- ❌ NO `users` table
- ❌ NO `roles` table
- ❌ NO `permissions` table

**Responsibilities:**
- Verify JWT tokens from Auth service
- Use organization context from header
- Store business data with organization reference
- Check permissions via Auth service API

---

## 📦 What Lavalite Core Package Provides

### For Non-Auth Microservices

#### 1. **User DTO (Not a Database Model)**
```php
Lavalite\Core\Models\User
```
- Just a data structure, NOT a database table
- Represents user info from JWT token
- Used for type-hinting and working with user data
- **Supports both int and UUID for user ID** (configurable in Auth service)

#### 2. **Organization Cache Model**
```php
Lavalite\Core\Models\Organization
```
- Minimal table: id, name, slug, timezone, currency, status
- Synced from Auth service when needed
- Used for relationships and queries

#### 3. **HasOrganization Trait**
```php
Lavalite\Core\Traits\HasOrganization
```
- Automatically scopes queries by organization
- Adds organization relationship
- Sets organization_id on create

#### 4. **Middleware**
- `SetOrganizationContext` - Validates X-Organization-ID header
- `EnsureOrganizationAccess` - Checks user access via Auth service

#### 5. **BaseController**
- Standard API responses (success, error, paginated)
- Helper methods (getOrganizationId, getOrganization, user)

#### 6. **AuthServiceClient**
- Communicate with Auth microservice
- Verify tokens
- Check permissions
- Get user/organization data

#### 7. **Testing Helpers**
- Mock JWT authentication
- Create organization cache
- Mock Auth service responses

---

## 🔄 How It Works

### Request Flow in Non-Auth Microservice

```
1. Client → Request with JWT token + X-Organization-ID header
   ↓
2. Middleware: auth:api (Laravel JWT guard)
   - Verifies JWT token signature
   - Extracts user data from token
   ↓
3. Middleware: organization
   - Validates X-Organization-ID header
   - Loads organization from cache
   - Stores in request context
   ↓
4. Middleware: organization.access (optional)
   - Calls Auth service API
   - Verifies user has access to organization
   ↓
5. Controller
   - $request->user() = user data from JWT
   - $this->getOrganization() = org from cache
   - Business logic
   ↓
6. Model with HasOrganization
   - Queries automatically scoped by organization_id
   - organization_id auto-set on create
   ↓
7. Response
   - Standard format via BaseController
```

### Data Storage Pattern

**Auth Microservice:**
```sql
organizations table (complete):
- id, name, slug, description, email, phone, website
- address, city, state, country, postal_code
- timezone, currency, status, settings

users table:
- id, email, password, name, first_name, last_name, etc.
```

**Other Microservices:**
```sql
organizations table (cache):
- id (synced), name, slug
- timezone, currency, status, settings

invoices table (example):
- id, organization_id, invoice_number, amount, etc.
```

---

## 🎯 Key Principles

### 1. **No User Tables in Non-Auth Services**
- User data comes from JWT tokens
- No local user storage
- Auth service is source of truth

### 2. **Organization Cache**
- Lightweight reference table
- Synced from Auth service when needed
- Used for queries and relationships

### 3. **Permission Checks via API**
- Don't store permissions locally
- Call Auth service API to verify
- Cache results temporarily

### 4. **JWT for Authentication**
- Stateless authentication
- No session management needed
- Token contains user data

---

## 📋 Example Usage

### In Inventory Microservice

```php
// Model
class Product extends Model
{
    use HasOrganization; // Auto-scopes by organization_id
    
    protected $fillable = ['organization_id', 'name', 'sku', 'price'];
}

// Controller
class ProductController extends BaseController
{
    public function index(Request $request)
    {
        // User data from JWT (no database query)
        $user = $request->user();
        
        // Products auto-scoped by organization
        $products = Product::paginate(15);
        
        return $this->paginated($products);
    }
    
    public function store(Request $request)
    {
        // Check permission via Auth service
        if (!$this->authClient->hasPermission(
            $request->user()->id, 
            $this->getOrganizationId(), 
            'products.create'
        )) {
            return $this->error('Unauthorized', 403);
        }
        
        // organization_id auto-set by HasOrganization trait
        $product = Product::create($request->validated());
        
        return $this->success($product, 'Created', 201);
    }
}

// Route
Route::middleware(['auth:api', 'organization'])->group(function () {
    Route::apiResource('products', ProductController::class);
});

// Test
class ProductTest extends TestCase
{
    public function test_create_product()
    {
        // Mock user (no database)
        $user = $this->actingAsUser('org-123', [
            'id' => 1,
            'permissions' => ['products.create']
        ]);
        
        $this->postJson('/api/products', ['name' => 'Widget'])
            ->assertCreated();
    }
}
```

---

## ✅ Database Migrations

### What Gets Created

```sql
-- Only this table in non-auth microservices
CREATE TABLE organizations (
    id UUID PRIMARY KEY,           -- Synced from Auth
    name VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    timezone VARCHAR(255) DEFAULT 'UTC',
    currency VARCHAR(255) DEFAULT 'USD',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    settings JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Your business tables
CREATE TABLE products (
    id BIGINT PRIMARY KEY,
    organization_id UUID REFERENCES organizations(id),
    name VARCHAR(255),
    sku VARCHAR(255),
    price DECIMAL(10,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🚀 Benefits

1. **Separation of Concerns**: Auth logic centralized
2. **Scalability**: Each service independent
3. **No Data Duplication**: Users/roles only in Auth service
4. **Consistency**: All services use same patterns
5. **Security**: Single source for authentication
6. **Maintainability**: Update auth logic in one place

---

## 📝 Summary

- ✅ **Auth Microservice**: Full user/role/permission tables
- ✅ **Other Microservices**: Organization cache + business data
- ✅ **Lavalite Core**: Shared utilities for non-auth services
- ✅ **JWT**: Stateless authentication across services
- ✅ **API Calls**: Check permissions via Auth service
- ✅ **Organization Context**: Header-based multi-tenancy

**Remember**: User tables only exist in the Auth microservice. All other services work with user data from JWT tokens!
