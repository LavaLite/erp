# JWT (JSON Web Tokens) - Complete Guide

> **Comprehensive JWT implementation guide for authentication, authorization, and module validation**

## Table of Contents

1. [Overview](#overview)
2. [JWT Token Structure](#jwt-token-structure)
3. [Enhanced JWT Claims](#enhanced-jwt-claims)
4. [Module Validation](#module-validation)
5. [Middleware Reference](#middleware-reference)
6. [API Integration](#api-integration)
7. [Microservice Integration](#microservice-integration)
8. [Security & Best Practices](#security--best-practices)
9. [Performance Optimization](#performance-optimization)
10. [Testing](#testing)
11. [Troubleshooting](#troubleshooting)

---

## Overview

The Lavalite multi-organization authentication microservice uses JWT (JSON Web Tokens) for stateless authentication. JWTs embed user identity, roles, permissions, modules, and organization context, enabling **zero-query validation** across distributed microservices.

### Key Benefits

✅ **Stateless Authentication** - No server-side session storage  
✅ **Zero-Query Validation** - Module, role, and permission checks without database calls  
✅ **Microservice Ready** - Independent validation across services  
✅ **Performance** - 10-50x faster than database validation  
✅ **Scalability** - Reduces auth service load by 60-80%  
✅ **Security** - Cryptographically signed and verified tokens  

---

## JWT Token Structure

### Complete Token Payload

```json
{
  "iss": "https://api.lavalite.org",           // Issuer
  "sub": 1,                                  // Subject (User ID)
  "iat": 1699876543,                         // Issued at (timestamp)
  "exp": 1699880143,                         // Expiration (timestamp)
  "nbf": 1699876543,                         // Not before (timestamp)
  
  "organization_id": "019a77ec-851a-7028-8f56-5f31232cdf72",
  "organization_slug": "acme-corp",          // URL-friendly slug
  "is_owner": false,                         // Quick owner check
  "subscription_status": "active",           // active|trial|suspended|cancelled
  "max_users": 50,                           // User limit for plan
  
  "roles": ["admin", "manager"],             // User's roles
  "permissions": [                           // User's permissions
    "users.create",
    "users.edit",
    "roles.manage"
  ],
  "modules": [                               // Organization's enabled modules
    "accounting",
    "inventory",
    "crm",
    "invoicing"
  ],
  "teams": ["sales-team", "support"],        // User's teams
  "user_modules": ["dash", "crm"]            // User-specific module access
}
```

### Token Format

```
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2FwaS55aWlyZS5jb20iLCJzdWIiOjEsImlhdCI6MTY5OTg3NjU0MywiZXhwIjoxNjk5ODgwMTQzLCJuYmYiOjE2OTk4NzY1NDMsIm9yZ2FuaXphdGlvbl9pZCI6IjAxOWE3N2VjLTg1MWEtNzAyOC04ZjU2LTVmMzEyMzJjZGY3MiIsIm9yZ2FuaXphdGlvbl9zbHVnIjoiYWNtZS1jb3JwIiwiaXNfb3duZXIiOmZhbHNlLCJzdWJzY3JpcHRpb25fc3RhdHVzIjoiYWN0aXZlIiwibWF4X3VzZXJzIjo1MCwicm9sZXMiOlsiYWRtaW4iXSwicGVybWlzc2lvbnMiOlsidXNlcnMuY3JlYXRlIl0sIm1vZHVsZXMiOlsiYWNjb3VudGluZyIsImludmVudG9yeSJdLCJ0ZWFtcyI6WyJzYWxlcyJdLCJ1c2VyX21vZHVsZXMiOlsiZGFzaCJdfQ.signature
```

**Three parts separated by `.`:**
1. **Header** (algorithm & type)
2. **Payload** (claims/data)
3. **Signature** (verification)

---

## Enhanced JWT Claims

### 1. Organization Slug (`organization_slug`)

**Type:** `string`  
**Purpose:** URL-friendly organization identifier  
**Example:** `"acme-corp"`

**Use Cases:**
- Build clean URLs: `/api/acme-corp/dashboard`
- Display org name without DB query
- Frontend routing

**Access in Code:**
```php
$orgSlug = auth()->payload()->get('organization_slug'); // "acme-corp"
```

---

### 2. Owner Status (`is_owner`)

**Type:** `boolean`  
**Purpose:** Quick ownership verification  
**Values:** `true` | `false`

**Use Cases:**
- Restrict organization deletion to owners
- Hide owner-only UI elements
- Protect billing settings

**Access in Code:**
```php
$isOwner = auth()->payload()->get('is_owner'); // true/false

if ($isOwner) {
    // Allow critical operation
}
```

**Middleware:**
```php
Route::middleware(['auth:api', 'owner.only'])
    ->delete('/organizations/{id}', [OrganizationController::class, 'destroy']);
```

---

### 3. Subscription Status (`subscription_status`)

**Type:** `string`  
**Purpose:** Current subscription state  
**Values:** `active` | `trial` | `suspended` | `cancelled`

**Behavior Matrix:**

| Status | Access | Response Code | Notes |
|--------|--------|---------------|-------|
| `active` | ✅ Full access | 200 | Normal operation |
| `trial` | ✅ Access + warning | 200 | Header: `X-Subscription-Status: trial` |
| `suspended` | ❌ Blocked | 403 | Payment failed or manual suspension |
| `cancelled` | ❌ Blocked | 403 | User cancelled subscription |

**Access in Code:**
```php
$status = auth()->payload()->get('subscription_status'); // "active"

if ($status === 'trial') {
    // Show upgrade banner
}
```

**Middleware:**
```php
Route::middleware(['auth:api', 'subscription.active'])
    ->get('/dashboard', [DashboardController::class, 'index']);
```

---

### 4. User Limit (`max_users`)

**Type:** `integer`  
**Purpose:** Maximum users allowed for organization  
**Example:** `50`

**Use Cases:**
- Prevent over-provisioning
- Show upgrade prompts
- Enforce plan limits

**Access in Code:**
```php
$maxUsers = auth()->payload()->get('max_users'); // 50
$currentCount = $organization->users()->count();
$remaining = $maxUsers - $currentCount;
```

**Middleware:**
```php
Route::middleware(['auth:api', 'user.limit'])
    ->post('/organizations/{id}/users', [UserController::class, 'store']);
```

---

### 5. Modules (`modules`)

**Type:** `array<string>`  
**Purpose:** Organization's enabled module slugs  
**Example:** `["accounting", "inventory", "crm"]`

**Short Slug Reference:**

| Module Name | Slug | Code |
|-------------|------|------|
| Dashboard | `dash` | DSH |
| Accounting | `accounting` | ACC |
| Inventory | `inventory` | STC |
| CRM | `crm` | CRM |
| Invoicing | `invoicing` | INV |
| Point of Sale | `pos` | POS |
| Email Marketing | `email` | EML |
| SMS Marketing | `sms` | SMS |
| Marketing Automation | `automation` | MKA |
| Website Builder | `website` | WEB |
| Live Chat | `livechat` | LVC |
| Social Marketing | `social` | SOC |
| Human Resources | `hr` | HRM |
| Project Management | `projects` | PRJ |
| Help Desk | `helpdesk` | HLP |
| E-commerce | `ecommerce` | ECM |

**Access in Code:**
```php
$modules = auth()->payload()->get('modules', []); // ["accounting", "crm"]

if (in_array('accounting', $modules)) {
    // Organization has accounting module
}
```

**Middleware:**
```php
Route::middleware(['auth:api', 'module.jwt:accounting'])
    ->get('/accounting/reports', [AccountingController::class, 'index']);
```

---

### 6. Roles & Permissions

**Types:** `array<string>`  
**Purpose:** User's roles and permissions within organization

**Example:**
```json
{
  "roles": ["admin", "manager", "accountant"],
  "permissions": [
    "users.create",
    "users.edit",
    "users.delete",
    "roles.manage",
    "accounting.view",
    "accounting.edit"
  ]
}
```

**Access in Code:**
```php
$roles = auth()->payload()->get('roles', []);
$permissions = auth()->payload()->get('permissions', []);

if (in_array('admin', $roles)) {
    // User is admin
}

if (in_array('users.delete', $permissions)) {
    // User can delete users
}
```

---

### 7. Teams (`teams`)

**Type:** `array<string>`  
**Purpose:** User's team memberships (slugs)  
**Example:** `["sales-team", "support-team"]`

**Access in Code:**
```php
$teams = auth()->payload()->get('teams', []); // ["sales-team"]
```

---

### 8. User Modules (`user_modules`)

**Type:** `array<string>`  
**Purpose:** User-specific module access (subset of organization modules)  
**Example:** `["dash", "crm"]`

**Use Case:**
```php
// Organization has: ["accounting", "crm", "inventory"]
// User has access to: ["dash", "crm"]

$userModules = auth()->payload()->get('user_modules', []);

if (in_array('crm', $userModules)) {
    // This specific user can access CRM
}
```

---

## Module Validation

### JWT-Based Module Validation (Recommended)

**Middleware:** `CheckModuleAccessFromJWT`  
**Alias:** `module.jwt`

**Features:**
- ✅ Zero database queries
- ✅ 10-50x faster than DB validation
- ✅ Reduces auth service load by 60-80%
- ✅ Perfect for microservices
- ✅ Clear error messages

**Usage:**

```php
// Single module
Route::middleware(['auth:api', 'module.jwt:accounting'])
    ->get('/accounting/ledger', [AccountingController::class, 'getLedger']);

// Group routes
Route::middleware(['auth:api', 'module.jwt:crm'])->group(function () {
    Route::get('/leads', [LeadController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::get('/opportunities', [OpportunityController::class, 'index']);
});

// Different modules
Route::middleware('auth:api')->group(function () {
    Route::middleware('module.jwt:inventory')
        ->get('/inventory/items', [InventoryController::class, 'index']);
    
    Route::middleware('module.jwt:accounting')
        ->get('/accounting/reports', [AccountingController::class, 'reports']);
});
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "Access denied. Module 'accounting' is not enabled for your organization.",
  "available_modules": ["crm", "inventory", "invoicing"],
  "required_module": "accounting"
}
```

---

### Database-Based Module Validation (Legacy)

**Middleware:** `CheckModuleAccess`  
**Alias:** `module.access`

**Features:**
- ✅ Always accurate (real-time)
- ✅ Checks expiration dates
- ❌ Requires database query (~10-50ms overhead)

**Usage:**
```php
Route::middleware(['auth:api', 'module.access:accounting'])
    ->post('/accounting/close-period', [AccountingController::class, 'closePeriod']);
```

**When to Use:**
- Critical operations (closing periods, deletions)
- When you need to check expiration dates
- When real-time accuracy is required

---

### Hybrid Approach (Best Practice)

```php
// Regular operations: JWT validation (fast)
Route::middleware(['module.jwt:accounting'])->group(function () {
    Route::get('/reports', [AccountingController::class, 'reports']);
    Route::get('/ledger', [AccountingController::class, 'ledger']);
    Route::post('/journal-entry', [AccountingController::class, 'createEntry']);
});

// Critical operations: DB validation (accurate)
Route::middleware(['module.access:accounting'])->group(function () {
    Route::post('/close-period', [AccountingController::class, 'closePeriod']);
    Route::delete('/delete-all-entries', [AccountingController::class, 'deleteAll']);
});
```

---

## Middleware Reference

### Authentication Middlewares

#### 1. `auth:api` - JWT Authentication
```php
Route::middleware(['auth:api'])
    ->get('/user', [UserController::class, 'show']);
```
Validates JWT token and authenticates user.

---

### Authorization Middlewares

#### 2. `subscription.active` - Subscription Validation
```php
Route::middleware(['auth:api', 'subscription.active'])
    ->get('/dashboard', [DashboardController::class, 'index']);
```

**Blocks:**
- `suspended` organizations
- `cancelled` organizations

**Allows:**
- `active` subscriptions
- `trial` subscriptions (with warning header)

**Error (403):**
```json
{
  "success": false,
  "message": "Your organization subscription has been suspended. Please contact support or update your payment method.",
  "subscription_status": "suspended"
}
```

---

#### 3. `user.limit` - User Count Enforcement
```php
Route::middleware(['auth:api', 'user.limit'])
    ->post('/organizations/{id}/users', [UserController::class, 'store']);
```

**Behavior:**
- Only checks on POST requests
- Reads `max_users` from JWT
- Counts current users in organization
- Blocks if limit reached

**Error (403):**
```json
{
  "success": false,
  "message": "User limit reached. Your plan allows 50 users. Please upgrade your subscription to add more users.",
  "current_users": 50,
  "max_users": 50,
  "can_add": false
}
```

**Bonus:** Adds info to request:
```php
$request->get('_current_user_count');  // 45
$request->get('_max_users');           // 50
$request->get('_users_remaining');     // 5
```

---

#### 4. `owner.only` - Owner-Only Access
```php
Route::middleware(['auth:api', 'owner.only'])
    ->delete('/organizations/{id}', [OrganizationController::class, 'destroy']);
```

**Behavior:**
- Reads `is_owner` from JWT
- Blocks non-owners
- Zero database queries

**Error (403):**
```json
{
  "success": false,
  "message": "Access denied. Only organization owners can perform this action."
}
```

---

#### 5. `module.jwt:slug` - JWT Module Validation
```php
Route::middleware(['auth:api', 'module.jwt:accounting'])
    ->get('/accounting/reports', [AccountingController::class, 'reports']);
```

**Features:**
- Zero DB queries
- Fastest validation method
- Microservice-friendly

---

#### 6. `module.access:slug` - DB Module Validation
```php
Route::middleware(['auth:api', 'module.access:accounting'])
    ->post('/accounting/close-period', [AccountingController::class, 'closePeriod']);
```

**Features:**
- Real-time accuracy
- Checks expiration
- Requires DB query

---

### Combining Middlewares

```php
// Protect billing route: active subscription + owner only
Route::middleware(['auth:api', 'subscription.active', 'owner.only'])
    ->put('/billing/payment-method', [BillingController::class, 'updatePaymentMethod']);

// User creation: active subscription + user limit + accounting module
Route::middleware(['auth:api', 'subscription.active', 'user.limit', 'module.jwt:accounting'])
    ->post('/accounting/users', [AccountingUserController::class, 'store']);
```

---

## API Integration

### Login & Token Acquisition

#### Standard Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "SecurePass123!",
  "organization_id": "019a77ec-851a-7028-8f56-5f31232cdf72"
}
```

**Response (200):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "email_verified_at": "2025-01-15T10:30:00Z"
  },
  "current_organization": {
    "id": "019a77ec-851a-7028-8f56-5f31232cdf72",
    "name": "Acme Corporation",
    "slug": "acme-corp",
    "subscription_status": "active"
  }
}
```

---

#### Token Refresh

```http
POST /api/refresh
Authorization: Bearer {current_jwt_token}
```

**Response (200):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 3600
}
```

**New token includes updated:**
- Roles & permissions
- Modules (if changed)
- Subscription status
- User count limit

---

#### Switch Organization

```http
POST /api/organizations/{id}/switch
Authorization: Bearer {jwt_token}
```

**Response (200):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "current_organization": {
    "id": "new-org-id",
    "name": "Different Org",
    "slug": "different-org"
  }
}
```

**New token reflects:**
- New organization context
- New roles/permissions in that org
- New modules for that org
- New owner status

---

### Using JWT Tokens

#### In HTTP Headers

```http
GET /api/user
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

#### In JavaScript/Frontend

```javascript
// Store token
localStorage.setItem('jwt_token', response.access_token);

// Use in requests
fetch('/api/dashboard', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('jwt_token')}`,
    'Content-Type': 'application/json'
  }
});

// Decode JWT (client-side only, for display purposes)
function parseJwt(token) {
  const base64Url = token.split('.')[1];
  const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
  const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => {
    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
  }).join(''));
  return JSON.parse(jsonPayload);
}

const payload = parseJwt(token);
console.log('Organization:', payload.organization_slug);
console.log('Is Owner:', payload.is_owner);
console.log('Modules:', payload.modules);
```

---

### Accessing JWT Claims in Laravel

```php
use Tymon\JWTAuth\Facades\JWTAuth;

// Method 1: Via auth() helper
$payload = auth()->payload();
$userId = $payload->get('sub');                     // 1
$orgId = $payload->get('organization_id');          // "uuid"
$orgSlug = $payload->get('organization_slug');      // "acme-corp"
$isOwner = $payload->get('is_owner');               // true/false
$status = $payload->get('subscription_status');     // "active"
$maxUsers = $payload->get('max_users');             // 50
$modules = $payload->get('modules', []);            // ["accounting", "crm"]
$roles = $payload->get('roles', []);                // ["admin"]
$permissions = $payload->get('permissions', []);    // ["users.create"]

// Method 2: Via JWTAuth facade
$payload = JWTAuth::parseToken()->getPayload();
$orgSlug = $payload->get('organization_slug');

// Method 3: Get authenticated user
$user = auth()->user(); // Returns User model
```

---

## Microservice Integration

### Node.js / Express Example

```javascript
const jwt = require('jsonwebtoken');

// Middleware to validate JWT and check module access
function requireModule(moduleSlug) {
  return function(req, res, next) {
    try {
      // Get token from Authorization header
      const authHeader = req.headers.authorization;
      if (!authHeader || !authHeader.startsWith('Bearer ')) {
        return res.status(401).json({ error: 'No token provided' });
      }
      
      const token = authHeader.substring(7); // Remove 'Bearer '
      
      // Verify and decode JWT (use same secret as Laravel)
      const decoded = jwt.verify(token, process.env.JWT_SECRET);
      
      // Check subscription status
      if (decoded.subscription_status === 'suspended' || 
          decoded.subscription_status === 'cancelled') {
        return res.status(403).json({
          error: 'Organization subscription is not active',
          subscription_status: decoded.subscription_status
        });
      }
      
      // Check if modules array includes required module
      const modules = decoded.modules || [];
      
      if (!modules.includes(moduleSlug)) {
        return res.status(403).json({
          error: `Access denied. Module '${moduleSlug}' not enabled`,
          available_modules: modules,
          required_module: moduleSlug
        });
      }
      
      // Attach decoded data to request
      req.user = {
        id: decoded.sub,
        organizationId: decoded.organization_id,
        organizationSlug: decoded.organization_slug,
        isOwner: decoded.is_owner,
        roles: decoded.roles || [],
        permissions: decoded.permissions || [],
        modules: modules
      };
      
      next();
    } catch (error) {
      if (error.name === 'TokenExpiredError') {
        return res.status(401).json({ error: 'Token expired' });
      }
      return res.status(401).json({ error: 'Invalid token' });
    }
  };
}

// Usage in routes
const express = require('express');
const app = express();

app.get('/api/inventory/items', 
  requireModule('inventory'), 
  async (req, res) => {
    // Access granted - user's org has inventory module
    const items = await InventoryItem.find({
      organizationId: req.user.organizationId
    });
    res.json({ items });
  }
);

app.post('/api/accounting/journal-entry',
  requireModule('accounting'),
  async (req, res) => {
    // Access granted - user's org has accounting module
    const entry = await JournalEntry.create({
      ...req.body,
      organizationId: req.user.organizationId,
      createdBy: req.user.id
    });
    res.json({ entry });
  }
);

// Owner-only route
app.delete('/api/organizations/:id',
  requireModule('dash'), // Require at least dashboard
  async (req, res) => {
    if (!req.user.isOwner) {
      return res.status(403).json({
        error: 'Only organization owners can delete organizations'
      });
    }
    // Proceed with deletion
  }
);
```

---

### Python / Flask Example

```python
import jwt
import os
from functools import wraps
from flask import request, jsonify

JWT_SECRET = os.getenv('JWT_SECRET')
JWT_ALGORITHM = 'HS256'

def require_module(module_slug):
    """Decorator to require specific module access"""
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            # Get token from Authorization header
            auth_header = request.headers.get('Authorization')
            if not auth_header or not auth_header.startswith('Bearer '):
                return jsonify({'error': 'No token provided'}), 401
            
            token = auth_header[7:]  # Remove 'Bearer '
            
            try:
                # Decode JWT
                decoded = jwt.decode(token, JWT_SECRET, algorithms=[JWT_ALGORITHM])
                
                # Check subscription status
                if decoded.get('subscription_status') in ['suspended', 'cancelled']:
                    return jsonify({
                        'error': 'Organization subscription is not active',
                        'subscription_status': decoded.get('subscription_status')
                    }), 403
                
                # Check if module is enabled
                modules = decoded.get('modules', [])
                
                if module_slug not in modules:
                    return jsonify({
                        'error': f"Module '{module_slug}' not enabled",
                        'available_modules': modules,
                        'required_module': module_slug
                    }), 403
                
                # Attach user data to request context
                request.user_id = decoded['sub']
                request.organization_id = decoded['organization_id']
                request.organization_slug = decoded.get('organization_slug')
                request.is_owner = decoded.get('is_owner', False)
                request.roles = decoded.get('roles', [])
                request.permissions = decoded.get('permissions', [])
                request.modules = modules
                
                return f(*args, **kwargs)
                
            except jwt.ExpiredSignatureError:
                return jsonify({'error': 'Token expired'}), 401
            except jwt.InvalidTokenError:
                return jsonify({'error': 'Invalid token'}), 401
        
        return decorated_function
    return decorator

def require_owner():
    """Decorator to require owner status"""
    def decorator(f):
        @wraps(f)
        def decorated_function(*args, **kwargs):
            auth_header = request.headers.get('Authorization')
            if not auth_header:
                return jsonify({'error': 'No token provided'}), 401
            
            token = auth_header[7:]
            
            try:
                decoded = jwt.decode(token, JWT_SECRET, algorithms=[JWT_ALGORITHM])
                
                if not decoded.get('is_owner', False):
                    return jsonify({
                        'error': 'Only organization owners can perform this action'
                    }), 403
                
                request.user_id = decoded['sub']
                request.organization_id = decoded['organization_id']
                request.is_owner = True
                
                return f(*args, **kwargs)
                
            except jwt.InvalidTokenError:
                return jsonify({'error': 'Invalid token'}), 401
        
        return decorated_function
    return decorator

# Usage
from flask import Flask, jsonify
app = Flask(__name__)

@app.route('/api/inventory/items', methods=['GET'])
@require_module('inventory')
def get_inventory_items():
    # Access granted
    items = InventoryItem.query.filter_by(
        organization_id=request.organization_id
    ).all()
    return jsonify({'items': [item.to_dict() for item in items]})

@app.route('/api/accounting/reports', methods=['GET'])
@require_module('accounting')
def get_accounting_reports():
    # Access granted
    reports = generate_reports(request.organization_id)
    return jsonify({'reports': reports})

@app.route('/api/organizations/<org_id>', methods=['DELETE'])
@require_owner()
def delete_organization(org_id):
    # Only owners can access this
    # ... deletion logic
    return jsonify({'message': 'Organization deleted'})
```

---

### Go / Gin Example

```go
package main

import (
	"fmt"
	"net/http"
	"os"
	"strings"

	"github.com/gin-gonic/gin"
	"github.com/golang-jwt/jwt/v5"
)

type JWTClaims struct {
	Sub                  int      `json:"sub"`
	OrganizationID       string   `json:"organization_id"`
	OrganizationSlug     string   `json:"organization_slug"`
	IsOwner              bool     `json:"is_owner"`
	SubscriptionStatus   string   `json:"subscription_status"`
	MaxUsers             int      `json:"max_users"`
	Roles                []string `json:"roles"`
	Permissions          []string `json:"permissions"`
	Modules              []string `json:"modules"`
	Teams                []string `json:"teams"`
	UserModules          []string `json:"user_modules"`
	jwt.RegisteredClaims
}

// Middleware to require specific module
func RequireModule(moduleSlug string) gin.HandlerFunc {
	return func(c *gin.Context) {
		// Get token from Authorization header
		authHeader := c.GetHeader("Authorization")
		if authHeader == "" || !strings.HasPrefix(authHeader, "Bearer ") {
			c.JSON(http.StatusUnauthorized, gin.H{"error": "No token provided"})
			c.Abort()
			return
		}

		tokenString := strings.TrimPrefix(authHeader, "Bearer ")

		// Parse and validate token
		token, err := jwt.ParseWithClaims(tokenString, &JWTClaims{}, func(token *jwt.Token) (interface{}, error) {
			return []byte(os.Getenv("JWT_SECRET")), nil
		})

		if err != nil || !token.Valid {
			c.JSON(http.StatusUnauthorized, gin.H{"error": "Invalid token"})
			c.Abort()
			return
		}

		claims, ok := token.Claims.(*JWTClaims)
		if !ok {
			c.JSON(http.StatusUnauthorized, gin.H{"error": "Invalid token claims"})
			c.Abort()
			return
		}

		// Check subscription status
		if claims.SubscriptionStatus == "suspended" || claims.SubscriptionStatus == "cancelled" {
			c.JSON(http.StatusForbidden, gin.H{
				"error":               "Organization subscription is not active",
				"subscription_status": claims.SubscriptionStatus,
			})
			c.Abort()
			return
		}

		// Check if module is enabled
		hasModule := false
		for _, module := range claims.Modules {
			if module == moduleSlug {
				hasModule = true
				break
			}
		}

		if !hasModule {
			c.JSON(http.StatusForbidden, gin.H{
				"error":             fmt.Sprintf("Module '%s' not enabled", moduleSlug),
				"available_modules": claims.Modules,
				"required_module":   moduleSlug,
			})
			c.Abort()
			return
		}

		// Set user context
		c.Set("user_id", claims.Sub)
		c.Set("organization_id", claims.OrganizationID)
		c.Set("organization_slug", claims.OrganizationSlug)
		c.Set("is_owner", claims.IsOwner)
		c.Set("modules", claims.Modules)

		c.Next()
	}
}

// Middleware to require owner
func RequireOwner() gin.HandlerFunc {
	return func(c *gin.Context) {
		authHeader := c.GetHeader("Authorization")
		if authHeader == "" {
			c.JSON(http.StatusUnauthorized, gin.H{"error": "No token provided"})
			c.Abort()
			return
		}

		tokenString := strings.TrimPrefix(authHeader, "Bearer ")

		token, err := jwt.ParseWithClaims(tokenString, &JWTClaims{}, func(token *jwt.Token) (interface{}, error) {
			return []byte(os.Getenv("JWT_SECRET")), nil
		})

		if err != nil || !token.Valid {
			c.JSON(http.StatusUnauthorized, gin.H{"error": "Invalid token"})
			c.Abort()
			return
		}

		claims, ok := token.Claims.(*JWTClaims)
		if !ok || !claims.IsOwner {
			c.JSON(http.StatusForbidden, gin.H{
				"error": "Only organization owners can perform this action",
			})
			c.Abort()
			return
		}

		c.Set("user_id", claims.Sub)
		c.Set("organization_id", claims.OrganizationID)
		c.Set("is_owner", true)

		c.Next()
	}
}

// Usage
func main() {
	r := gin.Default()

	// Inventory routes
	r.GET("/api/inventory/items", RequireModule("inventory"), func(c *gin.Context) {
		orgID := c.GetString("organization_id")
		// Fetch inventory items for orgID
		c.JSON(http.StatusOK, gin.H{"items": []string{}})
	})

	// Accounting routes
	r.GET("/api/accounting/reports", RequireModule("accounting"), func(c *gin.Context) {
		orgID := c.GetString("organization_id")
		// Generate reports for orgID
		c.JSON(http.StatusOK, gin.H{"reports": []string{}})
	})

	// Owner-only route
	r.DELETE("/api/organizations/:id", RequireOwner(), func(c *gin.Context) {
		orgID := c.Param("id")
		// Delete organization
		c.JSON(http.StatusOK, gin.H{"message": "Organization deleted", "id": orgID})
	})

	r.Run(":8080")
}
```

---

## Security & Best Practices

### Configuration

#### JWT Secret

```env
# .env
JWT_SECRET=base64:your-256-bit-secret-key-here
JWT_TTL=60                    # Access token TTL in minutes (1 hour)
JWT_REFRESH_TTL=20160         # Refresh token TTL in minutes (14 days)
JWT_ALGO=HS256                # Signing algorithm
JWT_LEEWAY=0                  # Leeway for clock skew (seconds)
```

**Generate Secret:**
```bash
php artisan jwt:secret
```

**⚠️ Important:**
- Never commit `.env` to version control
- Use different secrets for dev/staging/production
- Rotate secrets periodically (quarterly)
- Use same secret across all microservices

---

### Token Lifecycle

```
┌──────────────┐
│ 1. Login     │
│ POST /login  │
└──────┬───────┘
       │
       ▼
┌────────────────────┐
│ 2. Issue Tokens    │
│ Access: 1h         │
│ Refresh: 14d       │
└──────┬─────────────┘
       │
       ▼ (after 1 hour)
┌─────────────────┐
│ 3. Refresh      │
│ POST /refresh   │
└──────┬──────────┘
       │
       ▼
┌────────────────┐
│ 4. New Access  │
│ Token (1h)     │
└──────┬─────────┘
       │
       ▼ (when done)
┌─────────────┐
│ 5. Logout   │
│ (blacklist) │
└─────────────┘
```

---

### Token Refresh Strategy

**Problem:** JWT tokens reflect state at login time. If modules/roles change, token is stale.

**Solutions:**

#### 1. **Wait for Expiration** (Default)
- Token expires in 60 minutes
- User refreshes automatically
- New token has updated claims
- **Acceptable delay:** 0-60 minutes

#### 2. **Force Re-login**
- Invalidate all tokens for organization
- Users must log in again
- **Immediate** access to changes
- Disruptive user experience

#### 3. **Hybrid Approach** (Recommended)
```php
// Most operations: JWT (fast)
Route::middleware(['module.jwt:accounting'])->group(function () {
    Route::get('/reports', ...);
    Route::get('/ledger', ...);
});

// Critical operations: Database (accurate)
Route::middleware(['module.access:accounting'])->group(function () {
    Route::post('/close-period', ...);
    Route::delete('/delete-all', ...);
});
```

---

### Security Checklist

- [ ] **HTTPS Only** - Always use TLS in production
- [ ] **Secure Secret** - Use 256-bit random secret
- [ ] **Short TTL** - Keep access tokens short-lived (60 min)
- [ ] **Refresh Tokens** - Store securely (HttpOnly cookies recommended)
- [ ] **Blacklist on Logout** - Invalidate tokens on logout
- [ ] **Signature Verification** - Always verify token signature
- [ ] **Expiration Check** - Reject expired tokens
- [ ] **Claim Validation** - Validate all claims (iss, aud, etc.)
- [ ] **CORS** - Configure properly for your domains
- [ ] **Rate Limiting** - Protect auth endpoints
- [ ] **Logging** - Log failed auth attempts
- [ ] **Monitoring** - Alert on suspicious patterns

---

### Common Vulnerabilities & Mitigations

| Vulnerability | Mitigation |
|---------------|------------|
| Token theft | Use HTTPS, HttpOnly cookies, short TTL |
| XSS attacks | Sanitize output, Content Security Policy |
| CSRF attacks | SameSite cookies, CSRF tokens |
| Token replay | Blacklist on logout, check `jti` claim |
| Algorithm confusion | Enforce HS256, validate `alg` header |
| Weak secret | Use cryptographically random 256-bit key |
| Missing expiration | Always set `exp` claim |
| Clock skew | Use `nbf` and `leeway` |

---

## Performance Optimization

### Token Size Optimization

**Strategy:** Keep JWT tokens small to reduce bandwidth

#### Module Slug Optimization

**Before:**
```json
{
  "modules": [
    "inventory-management",
    "accounting-and-finance",
    "customer-relationship-management",
    "human-resources-management"
  ]
}
```
**Size:** ~200 bytes

**After:**
```json
{
  "modules": ["inventory", "accounting", "crm", "hr"]
}
```
**Size:** ~55 bytes  
**Savings:** 72% reduction

---

### Performance Metrics

**JWT vs. Database Validation:**

| Metric | JWT Validation | DB Validation | Improvement |
|--------|----------------|---------------|-------------|
| Response time | <1ms | 10-50ms | **10-50x faster** |
| DB queries | 0 | 1-3 | **100% reduction** |
| Auth service load | Low | High | **60-80% reduction** |
| Scalability | Excellent | Limited | **Horizontal scaling** |

**Real-world impact:**
- **1000 requests/sec** = 1000-3000 saved DB queries/sec
- **Auth service CPU** = 60-80% reduction
- **Response latency** = 10-50ms improvement per request

---

### Caching Strategy

```php
// Cache organization data for JWT generation
use Illuminate\Support\Facades\Cache;

$orgData = Cache::remember("org:{$orgId}:jwt_data", 3600, function() use ($orgId) {
    return Organization::with(['modules', 'roles', 'permissions'])
        ->find($orgId);
});
```

---

## Testing

### Unit Tests

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Module;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTTest extends TestCase
{
    public function test_jwt_contains_organization_slug()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['slug' => 'test-org']);
        $org->users()->attach($user);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'organization_id' => $org->id,
        ]);

        $token = $response->json('access_token');
        $payload = JWTAuth::setToken($token)->getPayload();

        $this->assertEquals('test-org', $payload->get('organization_slug'));
    }

    public function test_jwt_contains_owner_status()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['owner_id' => $user->id]);
        $org->users()->attach($user);

        $token = auth()->login($user);
        $payload = auth()->payload();

        $this->assertTrue($payload->get('is_owner'));
    }

    public function test_jwt_contains_subscription_status()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['subscription_status' => 'trial']);
        $org->users()->attach($user);

        $token = auth()->login($user);
        $payload = auth()->payload();

        $this->assertEquals('trial', $payload->get('subscription_status'));
    }

    public function test_jwt_contains_enabled_modules()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user);

        $modules = Module::factory()->count(3)->create();
        foreach ($modules as $module) {
            $org->enableModule($module);
        }

        $token = auth()->login($user);
        $payload = auth()->payload();
        $tokenModules = $payload->get('modules');

        $this->assertCount(3, $tokenModules);
        $this->assertContains($modules[0]->slug, $tokenModules);
    }

    public function test_middleware_blocks_without_module()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user);

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/inventory/items');

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => "Access denied. Module 'inventory' is not enabled"
                 ]);
    }

    public function test_middleware_allows_with_module()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user);

        $module = Module::factory()->create(['slug' => 'inventory']);
        $org->enableModule($module);

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/inventory/items');

        $response->assertStatus(200);
    }

    public function test_owner_middleware_blocks_non_owners()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user);

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/organizations/{$org->id}");

        $response->assertStatus(403)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Only organization owners can perform this action'
                 ]);
    }

    public function test_subscription_middleware_blocks_suspended()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create(['subscription_status' => 'suspended']);
        $org->users()->attach($user);

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');

        $response->assertStatus(403)
                 ->assertJsonPath('subscription_status', 'suspended');
    }
}
```

---

### Manual Testing

#### Decode JWT Token

**Online Tool:** https://jwt.io

**Command Line:**
```bash
# macOS/Linux
echo "eyJ0eXAiOiJKV1QiLCJhbGc..." | cut -d'.' -f2 | base64 -d | jq

# Output
{
  "sub": 1,
  "organization_id": "019a77ec-851a-7028-8f56-5f31232cdf72",
  "organization_slug": "acme-corp",
  "is_owner": true,
  "subscription_status": "active",
  "max_users": 50,
  "modules": ["accounting", "crm"],
  "roles": ["admin"],
  "permissions": ["users.create"]
}
```

---

#### Test Module Access

```bash
# 1. Login and get token
TOKEN=$(curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password","organization_id":"uuid"}' \
  | jq -r '.access_token')

# 2. Test endpoint that requires module
curl -X GET http://localhost/api/inventory/items \
  -H "Authorization: Bearer $TOKEN"

# If organization doesn't have inventory module:
# Response: 403 Forbidden

# If organization has inventory module:
# Response: 200 OK
```

---

#### Test Subscription Blocking

```bash
# 1. Suspend organization in database
mysql -u root -p -e "UPDATE organizations SET subscription_status='suspended' WHERE id='uuid';"

# 2. Login (gets new JWT with suspended status)
TOKEN=$(curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password","organization_id":"uuid"}' \
  | jq -r '.access_token')

# 3. Try to access protected route
curl -X GET http://localhost/api/dashboard \
  -H "Authorization: Bearer $TOKEN"

# Response: 403 Forbidden
# {"success": false, "message": "Your organization subscription has been suspended..."}
```

---

## Troubleshooting

### "Invalid token" Error

**Possible Causes:**
1. Token expired
2. Wrong JWT secret
3. Malformed token
4. Algorithm mismatch

**Solutions:**
```bash
# Check token expiration
php artisan tinker
>>> $token = 'your-token-here';
>>> $payload = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->getPayload();
>>> $payload->get('exp'); // Compare with current time

# Verify JWT secret matches
cat .env | grep JWT_SECRET

# Refresh token
curl -X POST http://localhost/api/refresh \
  -H "Authorization: Bearer $OLD_TOKEN"
```

---

### "Module not enabled" But It Should Be

**Possible Causes:**
1. Token issued before module was enabled
2. Need to refresh token

**Solutions:**
```bash
# Force token refresh
curl -X POST http://localhost/api/refresh \
  -H "Authorization: Bearer $CURRENT_TOKEN"

# Or re-login
curl -X POST http://localhost/api/login \
  -d '{"email":"...","password":"...","organization_id":"..."}'

# Verify module is actually enabled
curl -X GET http://localhost/api/organizations/{id}/modules \
  -H "Authorization: Bearer $TOKEN"
```

---

### "Too Many Requests" (429)

**Cause:** Rate limiting triggered

**Solutions:**
```bash
# Wait for rate limit window to reset (usually 60 seconds)
sleep 60

# Check rate limit headers
curl -I http://localhost/api/login \
  -H "Content-Type: application/json"

# Response headers:
# X-RateLimit-Limit: 5
# X-RateLimit-Remaining: 0
# Retry-After: 45
```

---

### Token Too Large

**Symptoms:**
- HTTP 431 (Request Header Fields Too Large)
- Slow request times

**Solutions:**
```php
// Reduce data in JWT payload
// Option 1: Use role categories instead of listing all roles
'role_category' => 'admin'  // Instead of all individual roles

// Option 2: Use module groups
'module_groups' => ['finance', 'sales']  // Instead of individual modules

// Option 3: Increase server limits (nginx)
// nginx.conf
large_client_header_buffers 4 32k;
```

---

### Clock Skew Issues

**Symptoms:**
- "Token used before issued" errors
- Intermittent auth failures

**Solutions:**
```env
# Add leeway in .env
JWT_LEEWAY=60  # Allow 60 seconds clock difference
```

```php
// config/jwt.php
'leeway' => env('JWT_LEEWAY', 0),
```

---

## Migration Guide

### From Session-Based to JWT

**Step 1:** Install tymon/jwt-auth
```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

**Step 2:** Update User model
```php
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'organization_id' => $this->current_organization_id,
            'organization_slug' => $this->currentOrganization?->slug,
            // ... other custom claims
        ];
    }
}
```

**Step 3:** Update routes
```php
// Old
Route::middleware(['auth:sanctum'])->group(function () {
    // ...
});

// New
Route::middleware(['auth:api'])->group(function () {
    // ...
});
```

**Step 4:** Update frontend
```javascript
// Old (Sanctum)
fetch('/api/user', {
  credentials: 'include'  // Send cookies
});

// New (JWT)
fetch('/api/user', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

---

### From Database Module Validation to JWT

**Step 1:** Update routes
```php
// Before
Route::middleware(['module.access:inventory'])

// After
Route::middleware(['module.jwt:inventory'])
```

**Step 2:** Test thoroughly
```bash
php artisan test --filter=ModuleJWT
```

**Step 3:** Deploy gradually
```php
// Week 1: 10% of routes
Route::middleware(['module.jwt:inventory'])->group(function () {
    Route::get('/items', ...);  // New validation
});

Route::middleware(['module.access:accounting'])->group(function () {
    // ... rest still using DB validation
});

// Week 2-4: Increase to 100%
```

---

## Configuration Reference

### Environment Variables

```env
# JWT Configuration
JWT_SECRET=base64:your-secret-key
JWT_TTL=60                    # Access token lifetime (minutes)
JWT_REFRESH_TTL=20160         # Refresh token lifetime (minutes, 14 days)
JWT_ALGO=HS256                # Signing algorithm
JWT_LEEWAY=0                  # Clock skew tolerance (seconds)

# Token Blacklist
JWT_BLACKLIST_ENABLED=true
JWT_BLACKLIST_GRACE_PERIOD=60 # Grace period (seconds)

# Rate Limiting
RATE_LIMIT_ENABLED=true
THROTTLE_LOGIN=5              # Max login attempts per minute
THROTTLE_API=60               # Max API requests per minute
```

---

### Middleware Configuration

```php
// bootstrap/app.php

$middleware->alias([
    // Authentication
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth:api' => \App\Http\Middleware\Authenticate::class,
    
    // Module validation
    'module.jwt' => \App\Http\Middleware\CheckModuleAccessFromJWT::class,
    'module.access' => \App\Http\Middleware\CheckModuleAccess::class,
    
    // Authorization
    'subscription.active' => \App\Http\Middleware\CheckSubscriptionStatus::class,
    'user.limit' => \App\Http\Middleware\CheckUserLimit::class,
    'owner.only' => \App\Http\Middleware\CheckOwnerAccess::class,
]);
```

---

## Summary

### Key Takeaways

✅ **JWT tokens embed complete user context** - No DB queries for validation  
✅ **Enhanced claims enable zero-query checks** - Subscription, owner, modules, limits  
✅ **Module validation via JWT is 10-50x faster** - <1ms vs 10-50ms  
✅ **Microservice-friendly** - Services validate independently  
✅ **Security best practices** - HTTPS, short TTL, signature verification  
✅ **Optimized token size** - Short slugs, minimal overhead  
✅ **Comprehensive middleware** - Ready-to-use protection  
✅ **Multi-language support** - Works in Node.js, Python, Go, etc.  

---

### Quick Reference Card

| Task | Endpoint / Code |
|------|-----------------|
| **Login** | `POST /api/login` |
| **Refresh** | `POST /api/refresh` |
| **Logout** | `POST /api/logout` |
| **Switch Org** | `POST /api/organizations/{id}/switch` |
| **Get Payload** | `auth()->payload()` |
| **Check Module** | `in_array('crm', auth()->payload()->get('modules'))` |
| **Check Owner** | `auth()->payload()->get('is_owner')` |
| **JWT Module MW** | `Route::middleware(['module.jwt:accounting'])` |
| **DB Module MW** | `Route::middleware(['module.access:accounting'])` |
| **Owner MW** | `Route::middleware(['owner.only'])` |
| **Subscription MW** | `Route::middleware(['subscription.active'])` |
| **User Limit MW** | `Route::middleware(['user.limit'])` |

---

**Last Updated:** November 13, 2025  
**Version:** 2.0.0  
**Status:** ✅ Production Ready

---

For more documentation:
- [Security Features](/docs/SECURITY.md)
- [Authentication](/docs/AUTHENTICATION.md)
- [API Reference](/docs/API_REFERENCE.md)
- [Module Access](/docs/MODULE_ACCESS.md)
- [Billing Integration](/docs/BILLING.md)
