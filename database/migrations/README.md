# Database Migrations - Consolidated Structure

## Overview

The migrations have been consolidated into logical groups for better organization and maintainability.

---

## Migration Files (In Order)

### 1. Laravel Default Migrations
These are the standard Laravel framework migrations:

- `0001_01_01_000000_create_users_table.php` - Users table
- `0001_01_01_000001_create_cache_table.php` - Cache table
- `0001_01_01_000002_create_jobs_table.php` - Queue jobs table

### 2. Authentication Migrations

**`2025_11_12_111023_create_personal_access_tokens_table.php`**
- Creates `personal_access_tokens` table for Sanctum
- Used for API token authentication

**`2025_11_12_161412_create_refresh_tokens_table.php`**
- Creates `refresh_tokens` table for JWT refresh tokens
- Manages token rotation and revocation

**`2025_11_12_184845_add_is_super_admin_to_users_table.php`**
- Adds `is_super_admin` field to users table
- Supports global admin functionality

### 3. Multi-Organization & Subscription (CONSOLIDATED)

**`2025_11_12_120001_create_organizations_and_subscription_tables.php`**

Consolidates:
- ~~`create_organizations_table.php`~~ (merged)
- ~~`add_subscription_fields_to_organizations_table.php`~~ (merged)

Creates:
- `organizations` table with subscription fields:
  - Basic fields: name, slug, domain, description, settings
  - Status: is_active
  - Subscription: subscription_status, max_users, trial_ends_at, subscription_id, plan_id
- `organization_user` pivot table:
  - Links users to organizations
  - Tracks active status and join date

### 4. RBAC System (CONSOLIDATED)

**`2025_11_12_120002_create_roles_and_permissions_tables.php`**

Consolidates:
- ~~`create_roles_table.php`~~ (merged)
- ~~`create_permissions_table.php`~~ (merged)

Creates:
- `roles` table - Organization-scoped or global roles
- `permissions` table - Organization-scoped or global permissions
- `role_user` pivot table - User role assignments per organization
- `permission_role` pivot table - Permission assignments to roles
- `permission_user` pivot table - Direct permission assignments to users

### 5. Module Management & Billing (CONSOLIDATED)

**`2025_11_13_092045_create_modules_and_organization_module_tables.php`**

Consolidates:
- ~~`create_modules_table.php`~~ (merged)
- ~~`create_organization_module_table.php`~~ (merged)

Creates:
- `modules` table - Available ERP modules:
  - Module info: name, slug, code, description, icon, version
  - Categorization: category, display_order
  - Configuration: dependencies, metadata
  - Flags: is_core, is_active, requires_license
- `organization_module` pivot table - Module subscriptions per organization:
  - Status: is_enabled, enabled_at, expires_at
  - Configuration: settings, limits

---

## Migration Groups Summary

| Group | File Count | Purpose |
|-------|-----------|---------|
| Laravel Default | 3 | Framework tables (users, cache, jobs) |
| Authentication | 3 | Tokens and admin flags |
| **Organizations** | **1** | **Multi-tenancy + Billing** |
| **RBAC** | **1** | **Roles + Permissions** |
| **Modules** | **1** | **Module Management + Billing** |
| **Total** | **9** | Complete SaaS ERP Auth System |

---

## Benefits of Consolidation

✅ **Fewer files** - Easier to navigate and understand  
✅ **Logical grouping** - Related tables together  
✅ **Single source** - All related schema in one place  
✅ **Atomic operations** - Create related tables together  
✅ **Easier rollback** - Roll back entire feature set at once  
✅ **Better documentation** - Clear purpose per migration  

---

## Database Schema Overview

### Core Tables
- `users` - User accounts
- `organizations` - Organizations/tenants with subscription info
- `roles` - Roles (global or org-scoped)
- `permissions` - Permissions (global or org-scoped)
- `modules` - Available ERP modules

### Pivot Tables
- `organization_user` - Users in organizations
- `role_user` - User role assignments per org
- `permission_role` - Permissions assigned to roles
- `permission_user` - Direct user permissions
- `organization_module` - Module subscriptions per org

### Authentication Tables
- `personal_access_tokens` - API tokens (Sanctum)
- `refresh_tokens` - JWT refresh tokens

### System Tables
- `cache` - Application cache
- `jobs` - Queue jobs

---

## Running Migrations

### Fresh Installation
```bash
php artisan migrate
```

### Reset and Migrate (Development Only)
```bash
php artisan migrate:fresh --seed
```

### Rollback Last Batch
```bash
php artisan migrate:rollback
```

### Check Migration Status
```bash
php artisan migrate:status
```

---

## Next Steps After Migration

1. **Seed the database:**
   ```bash
   php artisan db:seed
   ```

2. **Create global roles:**
   - Run ModuleSeeder to create core modules
   - Create default global admin role

3. **Test the setup:**
   - Register a test organization
   - Assign modules to organization
   - Create users and assign roles

---

## Notes

- **Consolidated on:** November 13, 2025
- **Old individual migration files:** Removed
- **Database compatibility:** MySQL 8.0+, PostgreSQL 12+
- **Character set:** utf8mb4 (supports emojis and special characters)

---

## File Changes

### Files Removed (Consolidated)
- ❌ `2025_11_12_120003_create_permissions_table.php`
- ❌ `2025_11_13_000001_add_subscription_fields_to_organizations_table.php`
- ❌ `2025_11_13_092104_create_organization_module_table.php`
- ❌ `2025_11_12_120001_create_organizations_table.php` (old version)
- ❌ `2025_11_12_120002_create_roles_table.php` (old version)
- ❌ `2025_11_13_092045_create_modules_table.php` (old version)

### Files Created (Consolidated)
- ✅ `2025_11_12_120001_create_organizations_and_subscription_tables.php`
- ✅ `2025_11_12_120002_create_roles_and_permissions_tables.php`
- ✅ `2025_11_13_092045_create_modules_and_organization_module_tables.php`

---

## Support

For questions about migrations:
- See: `/docs/GETTING_STARTED.md`
- Email: team@lavalite.org
