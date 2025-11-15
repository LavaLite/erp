# Billing & Subscriptions - Complete Guide

> **Comprehensive billing integration, subscription management, and usage tracking documentation**

## Table of Contents

1. [Overview](#overview)
2. [Subscription Model](#subscription-model)
3. [API Reference](#api-reference)
4. [Usage Tracking](#usage-tracking)
5. [Middleware & Enforcement](#middleware--enforcement)
6. [Webhooks & Events](#webhooks--events)
7. [Code Examples](#code-examples)
8. [Best Practices](#best-practices)

---

## Overview

The authentication microservice provides comprehensive billing integration supporting:
- **User-based pricing** - Charge per active user
- **Module-based pricing** - Charge per enabled module
- **Subscription management** - Active, trial, suspended, cancelled states
- **Usage tracking** - Real-time user and module counts
- **Limit enforcement** - Automatic blocking via middleware
- **JWT integration** - Zero-query subscription validation

---

## Database Schema

### Organizations Table - New Subscription Fields

| Field | Type | Description |
|-------|------|-------------|
| `subscription_status` | string | Status: `active`, `trial`, `suspended`, `cancelled`, `pending` |
| `max_users` | integer (nullable) | Maximum allowed users (null = unlimited) |
| `trial_ends_at` | timestamp (nullable) | Trial period expiration date |
| `subscription_id` | string (nullable, unique) | Reference to billing service subscription |
| `plan_id` | string (nullable) | Quick reference to subscription plan |

### Organization-Module Pivot Table

Already includes these fields for module-based billing:
- `is_enabled` - Whether module is active
- `enabled_at` - When module was enabled
- `expires_at` - Module expiration date (nullable)
- `settings` - Module-specific settings (JSON)
- `limits` - Module-specific limits (JSON)

---

## API Endpoints for Billing Service

All billing endpoints are prefixed with `/api/billing` and require authentication.

### 1. Get Comprehensive Usage Data

**Endpoint:** `GET /api/billing/organizations/{organizationId}/usage`

**Response:**
```json
{
  "success": true,
  "data": {
    "organization_id": "uuid",
    "organization_name": "Acme Corp",
    "subscription_status": "active",
    "subscription_id": "sub_123456",
    "plan_id": "plan_business",
    "max_users": 50,
    "active_users_count": 35,
    "total_users_count": 40,
    "enabled_modules_count": 5,
    "enabled_modules": [
      {
        "id": 1,
        "name": "Inventory Management",
        "slug": "inventory",
        "code": "INV",
        "enabled_at": "2025-01-01T00:00:00Z",
        "expires_at": null
      }
    ],
    "is_trial": false,
    "trial_ends_at": null,
    "is_active": true
  }
}
```

### 2. Get Active Users Count

**Endpoint:** `GET /api/billing/organizations/{organizationId}/users/count`

**Response:**
```json
{
  "success": true,
  "data": {
    "organization_id": "uuid",
    "active_users_count": 35,
    "total_users_count": 40,
    "max_users": 50,
    "can_add_users": true
  }
}
```

### 3. Get Enabled Modules

**Endpoint:** `GET /api/billing/organizations/{organizationId}/modules`

**Response:**
```json
{
  "success": true,
  "data": {
    "organization_id": "uuid",
    "modules_count": 5,
    "modules": [
      {
        "id": 1,
        "name": "Inventory Management",
        "slug": "inventory",
        "code": "INV",
        "category": "operations",
        "is_core": false,
        "requires_license": true,
        "enabled_at": "2025-01-01T00:00:00Z",
        "expires_at": null
      }
    ]
  }
}
```

### 4. Get Subscription Status

**Endpoint:** `GET /api/billing/organizations/{organizationId}/subscription`

**Response:**
```json
{
  "success": true,
  "data": {
    "organization_id": "uuid",
    "subscription_status": "active",
    "subscription_id": "sub_123456",
    "plan_id": "plan_business",
    "has_active_subscription": true,
    "is_on_trial": false,
    "is_trial_expired": false,
    "trial_ends_at": null,
    "is_suspended": false,
    "is_cancelled": false,
    "is_active": true
  }
}
```

### 5. Update Subscription (From Billing Service)

**Endpoint:** `PATCH /api/billing/organizations/{organizationId}/subscription`

**Request Body:**
```json
{
  "subscription_status": "active",
  "subscription_id": "sub_123456",
  "plan_id": "plan_business",
  "max_users": 100,
  "trial_ends_at": null
}
```

**Response:**
```json
{
  "success": true,
  "message": "Subscription updated successfully",
  "data": {
    "organization_id": "uuid",
    "subscription_status": "active",
    "subscription_id": "sub_123456",
    "plan_id": "plan_business",
    "max_users": 100
  }
}
```

### 6. Check User Limit

**Endpoint:** `POST /api/billing/organizations/{organizationId}/check-user-limit`

**Request Body:**
```json
{
  "count": 5
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "organization_id": "uuid",
    "current_active_users": 35,
    "max_users": 50,
    "requested_count": 5,
    "can_add_users": true,
    "available_slots": 15
  }
}
```

### 7. Bulk Usage Data

**Endpoint:** `POST /api/billing/organizations/bulk-usage`

**Request Body:**
```json
{
  "organization_ids": ["uuid1", "uuid2", "uuid3"]
}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "organization_id": "uuid1",
      "active_users_count": 35,
      "enabled_modules_count": 5,
      ...
    },
    {
      "organization_id": "uuid2",
      "active_users_count": 20,
      "enabled_modules_count": 3,
      ...
    }
  ]
}
```

---

## Middleware

### 1. CheckOrganizationLimits

**Usage:**
```php
Route::middleware(['check.limits'])->post('/organizations/{organizationId}/users', ...);
```

**Features:**
- Checks if organization has active subscription
- Prevents suspended organizations from performing actions
- Validates user limits before adding new users
- Auto-detects user addition requests

**Error Responses:**

Subscription inactive:
```json
{
  "success": false,
  "message": "Organization subscription is not active...",
  "subscription_status": "cancelled",
  "is_trial_expired": false
}
```

User limit reached:
```json
{
  "success": false,
  "message": "User limit reached. Please upgrade your plan...",
  "current_users": 50,
  "max_users": 50,
  "requested_count": 1
}
```

### 2. CheckModuleAccess

**Usage:**
```php
Route::middleware(['module.access:inventory'])->get('/inventory/items', ...);
```

**Features:**
- Validates organization has module enabled
- Checks module expiration dates
- Returns available modules if access denied

**Error Responses:**

Module not enabled:
```json
{
  "success": false,
  "message": "Access denied. Module 'inventory' is not enabled...",
  "module_slug": "inventory",
  "available_modules": ["users", "roles", "permissions"]
}
```

Module expired:
```json
{
  "success": false,
  "message": "Module 'inventory' access has expired.",
  "module_slug": "inventory",
  "expired_at": "2025-01-01T00:00:00Z"
}
```

---

## Organization Model Methods

### Usage Tracking

```php
$organization->getActiveUsersCount();         // Returns int
$organization->getTotalUsersCount();          // Returns int
$organization->getEnabledModulesCount();      // Returns int
$organization->getUsageData();                // Returns array (comprehensive)
```

### Limit Checking

```php
$organization->canAddUsers(5);                // Returns bool
$organization->hasActiveSubscription();       // Returns bool
$organization->isOnTrial();                   // Returns bool
$organization->isTrialExpired();              // Returns bool
$organization->isSuspended();                 // Returns bool
$organization->isCancelled();                 // Returns bool
```

### Subscription Management

```php
$organization->activateSubscription('sub_123', 'plan_business');
$organization->suspendSubscription();
$organization->cancelSubscription();
$organization->updateUserLimit(100);
```

---

## Billing Integration Workflow

### 1. New Organization Registration
1. Organization created with `subscription_status = 'trial'`
2. Set `trial_ends_at` to 14 or 30 days from now
3. Set `max_users = null` (unlimited during trial) or specific number
4. Billing service creates subscription record

### 2. Billing Service Monitors Usage
- Poll `/api/billing/organizations/{id}/usage` daily or in real-time
- Calculate charges based on:
  - `active_users_count` × user price
  - `enabled_modules_count` or specific module prices

### 3. Subscription Updates
- When payment successful: Update via `PATCH /subscription` with `status = 'active'`
- When payment fails: Update with `status = 'suspended'`
- When cancelled: Update with `status = 'cancelled'`

### 4. Plan Upgrades/Downgrades
- Update `max_users` via `PATCH /subscription`
- Enable/disable modules via existing module endpoints
- Update `plan_id` for reference

### 5. Enforcement
- Core service automatically blocks:
  - Adding users beyond `max_users`
  - Access when subscription is suspended/cancelled
  - Module access when not subscribed

---

## Example Integration Flow

```
┌─────────────────┐         ┌──────────────────┐         ┌─────────────────┐
│  Billing Service│         │   Core Auth API  │         │  Organization   │
└────────┬────────┘         └────────┬─────────┘         └────────┬────────┘
         │                           │                            │
         │  GET /billing/.../usage   │                            │
         ├──────────────────────────>│                            │
         │                           │  getUsageData()            │
         │                           ├───────────────────────────>│
         │                           │                            │
         │   {users: 35, modules: 5} │                            │
         │<──────────────────────────┤                            │
         │                           │                            │
         │  Calculate charges        │                            │
         │  $350 (users) + $250 (mods)│                           │
         │                           │                            │
         │  Process payment          │                            │
         │  ✓ Success                │                            │
         │                           │                            │
         │ PATCH /subscription       │                            │
         │ {status: 'active'}        │                            │
         ├──────────────────────────>│                            │
         │                           │  update()                  │
         │                           ├───────────────────────────>│
         │                           │                            │
         │   {success: true}         │                            │
         │<──────────────────────────┤                            │
         │                           │                            │
```

---

## Migration Instructions

1. **Run the migration:**
   ```bash
   php artisan migrate
   ```

2. **Update existing organizations** (if any):
   ```bash
   php artisan tinker
   ```
   ```php
   Organization::query()->update([
       'subscription_status' => 'trial',
       'trial_ends_at' => now()->addDays(14),
   ]);
   ```

3. **Test the endpoints:**
   ```bash
   # Get usage
   curl -X GET http://your-api/api/billing/organizations/{id}/usage \
     -H "Authorization: Bearer {token}"
   
   # Update subscription
   curl -X PATCH http://your-api/api/billing/organizations/{id}/subscription \
     -H "Authorization: Bearer {token}" \
     -H "Content-Type: application/json" \
     -d '{"subscription_status": "active", "max_users": 100}'
   ```

---

## Security Considerations

1. **Authentication Required:** All billing endpoints require authentication
2. **Consider Rate Limiting:** Add rate limiting to billing endpoints
3. **API Key for Billing Service:** Consider separate authentication for billing service
4. **Webhook Alternative:** Consider webhooks instead of polling for real-time updates
5. **Audit Logging:** Log all subscription status changes

---

## Future Enhancements

1. **Webhooks:** Send events when usage thresholds are reached
2. **Usage Analytics:** Track historical usage trends
3. **Metered Billing:** Support for API calls, storage, or other metrics
4. **Overage Handling:** Allow temporary overage with notifications
5. **Grace Periods:** Configurable grace periods before suspension
6. **Module Bundles:** Support for module packages/bundles

---

## Support

For questions or issues with billing integration, please refer to:
- Main Documentation: `/docs`
- API Reference: `/docs/API_REFERENCE.md`
- Contact: team@lavalite.org
