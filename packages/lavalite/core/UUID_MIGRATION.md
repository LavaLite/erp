# User ID Migration Guide: Integer to UUID

This guide explains how to migrate your Auth microservice from integer user IDs to UUIDs.

## Why UUID for User IDs?

### Benefits
- ✅ **Consistency**: Same as organizations (both use UUIDs)
- ✅ **Distributed Systems**: No ID conflicts across services
- ✅ **Security**: Harder to enumerate/guess user IDs
- ✅ **Scalability**: No single point of failure for ID generation
- ✅ **Merging Data**: Easier when combining data from multiple sources

### Drawbacks
- ⚠️ **Storage**: 16 bytes vs 8 bytes (2x larger)
- ⚠️ **Performance**: Slightly slower for joins/indexes
- ⚠️ **Complexity**: Migration required for existing data
- ⚠️ **URLs**: Longer, less "pretty" URLs

## Current Status

Your Auth microservice currently uses:
- Users: **Auto-increment IDs** (bigint)
- Organizations: **UUIDs**

## Should You Migrate?

**Keep Auto-increment IDs if:**
- ✅ You have < 10 million users
- ✅ Single datacenter deployment
- ✅ Performance is critical
- ✅ Simpler is better

**Migrate to UUIDs if:**
- ✅ You need consistency across all entities
- ✅ Multi-datacenter deployment planned
- ✅ Data merging/import scenarios expected
- ✅ Security by obscurity is important

## Migration Steps (If You Choose UUIDs)

### Step 1: Backup Everything
```bash
# Backup your database
php artisan backup:run

# Or manually
mysqldump -u root -p lavalite_auth > backup_before_uuid_migration.sql
```

### Step 2: Create Migration

```bash
php artisan make:migration convert_users_to_uuid
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new UUID column
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
            $table->index('uuid');
        });

        // Step 2: Generate UUIDs for existing users
        DB::table('users')->whereNull('uuid')->cursor()->each(function ($user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });

        // Step 3: Make UUID non-nullable and unique
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });

        // Step 4: Update foreign keys in related tables
        
        // Sessions table
        Schema::table('sessions', function (Blueprint $table) {
            $table->uuid('user_uuid')->after('user_id')->nullable();
            $table->index('user_uuid');
        });
        
        DB::statement('UPDATE sessions s 
            INNER JOIN users u ON s.user_id = u.id 
            SET s.user_uuid = u.uuid');
        
        // Organization_user pivot
        Schema::table('organization_user', function (Blueprint $table) {
            $table->uuid('user_uuid')->after('user_id')->nullable();
        });
        
        DB::statement('UPDATE organization_user ou 
            INNER JOIN users u ON ou.user_id = u.id 
            SET ou.user_uuid = u.uuid');
        
        // Role_user pivot
        Schema::table('role_user', function (Blueprint $table) {
            $table->uuid('user_uuid')->after('user_id')->nullable();
        });
        
        DB::statement('UPDATE role_user ru 
            INNER JOIN users u ON ru.user_id = u.id 
            SET ru.user_uuid = u.uuid');
        
        // Permission_user pivot
        Schema::table('permission_user', function (Blueprint $table) {
            $table->uuid('user_uuid')->after('user_id')->nullable();
        });
        
        DB::statement('UPDATE permission_user pu 
            INNER JOIN users u ON pu.user_id = u.id 
            SET pu.user_uuid = u.uuid');

        // Step 5: Drop old foreign keys and columns
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        
        Schema::table('organization_user', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        
        Schema::table('permission_user', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        // Step 6: Rename UUID columns to id
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('uuid', 'id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->primary('id');
        });
        
        // Rename in related tables
        Schema::table('sessions', function (Blueprint $table) {
            $table->renameColumn('user_uuid', 'user_id');
        });
        
        Schema::table('organization_user', function (Blueprint $table) {
            $table->renameColumn('user_uuid', 'user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
        Schema::table('role_user', function (Blueprint $table) {
            $table->renameColumn('user_uuid', 'user_id');
        });
        
        Schema::table('permission_user', function (Blueprint $table) {
            $table->renameColumn('user_uuid', 'user_id');
        });
    }

    public function down(): void
    {
        // Reverse migration - convert back to integers
        // WARNING: This will lose data if you've added users with UUIDs
        throw new \Exception('Cannot reverse UUID migration without data loss');
    }
};
```

### Step 3: Update User Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasUuids; // Add this trait
    
    // Remove this line if it exists:
    // public $incrementing = true;
    
    // Add these:
    public $incrementing = false;
    protected $keyType = 'string';
    
    // Rest of your model...
}
```

### Step 4: Update Factories

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(), // Add this
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            // ... rest of fields
        ];
    }
}
```

### Step 5: Test Everything

```bash
# Run all tests
php artisan test

# Test specific user operations
php artisan tinker
>>> $user = User::first();
>>> dd($user->id); // Should be a UUID string
>>> $user->organizations; // Should work
>>> $user->roles; // Should work
```

### Step 6: Update JWT Configuration

Check your JWT claims to ensure they handle UUID correctly:

```php
// In User model
public function getJWTCustomClaims()
{
    return [
        'sub' => $this->id, // Will now be UUID
    ];
}
```

### Step 7: Update API Documentation

Update all API docs to reflect that user IDs are now UUIDs:

```json
// Before
{
  "user_id": 123
}

// After
{
  "user_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d"
}
```

## Testing Checklist

- [ ] User login/logout works
- [ ] User registration works
- [ ] JWT tokens contain UUID
- [ ] Organization relationships work
- [ ] Role assignments work
- [ ] Permission checks work
- [ ] All tests pass
- [ ] API responses show UUIDs
- [ ] No foreign key errors

## Rollback Plan

If something goes wrong:

```bash
# Restore from backup
mysql -u root -p lavalite_auth < backup_before_uuid_migration.sql

# Revert code changes
git revert <migration-commit>

# Clear cache
php artisan cache:clear
php artisan config:clear
```

## Post-Migration

After successful migration:

1. Update all API consumers (frontend, mobile apps)
2. Update API documentation
3. Monitor logs for any UUID-related errors
4. Keep backup for at least 30 days

## Lavalite Core Package

**Good news**: The Lavalite Core package already supports both integer and UUID user IDs!

No changes needed in other microservices. They'll automatically work with either type.

---

**Recommendation**: Unless you have a specific need for UUIDs, keeping auto-increment IDs is simpler and more performant. The Lavalite Core package supports both, so you can decide later without affecting other microservices.
