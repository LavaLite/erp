# Lavalite ERP - New Microservice Skeleton Creation Guide

**Version:** 1.0  
**Last Updated:** November 14, 2025  
**Based on:** Lavalite Core Auth Microservice (Laravel 12.x)

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Quick Start Commands](#quick-start-commands)
4. [Step-by-Step Instructions](#step-by-step-instructions)
5. [Architecture Patterns](#architecture-patterns)
6. [Integration with Auth Microservice](#integration-with-auth-microservice)
7. [Docker Configuration](#docker-configuration)
8. [Testing Setup](#testing-setup)
9. [Deployment Checklist](#deployment-checklist)

---

## 📖 Overview

This guide helps you create a new microservice following the same patterns, structure, and conventions as the **Lavalite Core Authentication Microservice**.

### What You'll Build

A production-ready Laravel 12.x microservice with:
- ✅ JWT authentication integration
- ✅ Multi-organization support
- ✅ Docker containerization
- ✅ API-first architecture
- ✅ Standardized project structure
- ✅ Testing framework
- ✅ Documentation templates

---

## 🔧 Prerequisites

### Required Software

```bash
# Check versions
php --version        # Must be >= 8.2
composer --version   # Must be >= 2.7
node --version       # Must be >= 18.x
docker --version     # Optional but recommended
git --version
```

### Required Knowledge

- Laravel 12.x framework
- RESTful API design
- Docker basics (optional)
- JWT authentication concepts
- Multi-tenancy patterns

---

## ⚡ Quick Start Commands

```bash
# 1. Create new Laravel project
composer create-project laravel/laravel lavalite-[SERVICE-NAME] "12.*"
cd lavalite-[SERVICE-NAME]

# 2. Install required packages
composer require tymon/jwt-auth:^2.2
composer require laravel/sanctum:^4.2
composer require --dev laravel/pint:^1.24

# 3. Copy base configuration files from auth microservice
# (See detailed steps below)

# 4. Initialize Git repository
git init
git add .
git commit -m "Initial commit: [SERVICE-NAME] microservice skeleton"

# 5. Setup environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# 6. Run migrations
php artisan migrate

# 7. Start development server
php artisan serve
```

---

## 📚 Step-by-Step Instructions

### Step 1: Create New Laravel Project

```bash
# Replace [SERVICE-NAME] with your microservice name (e.g., inventory, crm, accounting)
composer create-project laravel/laravel lavalite-[SERVICE-NAME] "12.*"
cd lavalite-[SERVICE-NAME]
```

**Example microservice names:**
- `lavalite-inventory` - Inventory management
- `lavalite-crm` - Customer relationship management
- `lavalite-accounting` - Accounting and finance
- `lavalite-hrm` - Human resources management
- `lavalite-pos` - Point of sale

---

### Step 2: Install Core Dependencies

```bash
# JWT Authentication
composer require tymon/jwt-auth:^2.2

# Laravel Sanctum (for API tokens)
composer require laravel/sanctum:^4.2

# Development tools
composer require --dev laravel/pint:^1.24
composer require --dev laravel/pail:^1.2.2

# Optional: If you need HTTP client
composer require guzzlehttp/guzzle:^7.8
```

---

### Step 3: Copy Configuration Files from Auth Microservice

#### 3.1 Copy JWT Configuration

```bash
# From lavalite/erp directory
cp lavalite/erp/config/jwt.php config/jwt.php
```

#### 3.2 Update `config/auth.php`

Add JWT guard to your `config/auth.php`:

```php
'defaults' => [
    'guard' => 'api',
    'passwords' => 'users',
],

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

#### 3.3 Setup Database Configuration

Update `config/database.php` to support multiple database types:

```php
'default' => env('DB_CONNECTION', 'sqlite'),

'connections' => [
    'sqlite' => [
        'driver' => 'sqlite',
        'url' => env('DB_URL'),
        'database' => env('DB_DATABASE', database_path('database.sqlite')),
        'prefix' => '',
        'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        'busy_timeout' => null,
        'journal_mode' => null,
        'synchronous' => null,
    ],
    
    'mysql' => [
        'driver' => 'mysql',
        'url' => env('DB_URL'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'laravel'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'unix_socket' => env('DB_SOCKET', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
    ],
    
    'pgsql' => [
        'driver' => 'pgsql',
        'url' => env('DB_URL'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'laravel'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8'),
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
    ],
],
```

---

### Step 4: Create Base Models and Traits

#### 4.1 Create Organization Model Stub

```bash
php artisan make:model Organization
```

**File: `app/Models/Organization.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'timezone',
        'currency',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }
}
```

#### 4.2 Update User Model

**File: `app/Models/User.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'avatar',
        'timezone',
        'language',
        'status',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }
}
```

#### 4.3 Create HasOrganization Trait

```bash
mkdir -p app/Traits
```

**File: `app/Traits/HasOrganization.php`**

```php
<?php

namespace App\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasOrganization
{
    /**
     * Get the organization that owns the model.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope a query to only include models from a specific organization.
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Check if model belongs to given organization.
     */
    public function belongsToOrganization($organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }
}
```

---

### Step 5: Create Middleware for Organization Context

```bash
php artisan make:middleware OrganizationContext
```

**File: `app/Http/Middleware/OrganizationContext.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizationContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->header('X-Organization-ID');
        
        if (!$organizationId) {
            return response()->json([
                'message' => 'Organization context required. Please provide X-Organization-ID header.',
            ], 400);
        }

        // Store in request for easy access
        $request->merge(['organization_id' => $organizationId]);

        return $next($request);
    }
}
```

Register middleware in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'organization' => \App\Http\Middleware\OrganizationContext::class,
    ]);
})
```

---

### Step 6: Create Base API Controller

```bash
php artisan make:controller Api/BaseController
```

**File: `app/Http/Controllers/Api/BaseController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Return success response.
     */
    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return error response.
     */
    protected function error(string $message, int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return paginated response.
     */
    protected function paginated($data, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
        ]);
    }

    /**
     * Get current organization ID from request.
     */
    protected function getOrganizationId(): ?string
    {
        return request()->header('X-Organization-ID') ?? request()->get('organization_id');
    }

    /**
     * Get authenticated user.
     */
    protected function user()
    {
        return auth()->user();
    }
}
```

---

### Step 7: Setup API Routes

**File: `routes/api.php`**

```php
<?php

use App\Http\Controllers\Api\YourController;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => config('app.name'),
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Public routes
Route::prefix('v1')->group(function () {
    // Add your public endpoints here
});

// Protected routes - require JWT authentication
Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    
    // Organization-scoped routes
    Route::middleware(['organization'])->group(function () {
        // Add your protected endpoints here
        // Example:
        // Route::apiResource('items', ItemController::class);
    });
});
```

---

### Step 8: Create Database Migrations

#### 8.1 Create Organizations Table

```bash
php artisan make:migration create_organizations_table
```

**File: `database/migrations/xxxx_xx_xx_create_organizations_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('currency')->default('USD');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
```

#### 8.2 Create Organization-User Pivot Table

```bash
php artisan make:migration create_organization_user_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('organization_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member');
            $table->enum('status', ['active', 'inactive', 'invited'])->default('active');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->unique(['organization_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_user');
    }
};
```

---

### Step 9: Setup Environment Variables

**File: `.env.example`**

```bash
APP_NAME="Lavalite [SERVICE-NAME]"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Application
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug

# Database
DB_CONNECTION=sqlite
# For MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=lavalite_[service]
# DB_USERNAME=root
# DB_PASSWORD=

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

# JWT Configuration
JWT_SECRET=
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256

# Auth Microservice Integration
AUTH_SERVICE_URL=http://localhost:8000
AUTH_SERVICE_API_KEY=

# Mail
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@lavalite.org"
MAIL_FROM_NAME="${APP_NAME}"

# Redis (Optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

### Step 10: Create Docker Configuration

#### 10.1 Dockerfile

**File: `Dockerfile`**

```dockerfile
# Multi-stage Dockerfile for Lavalite [SERVICE-NAME] Microservice

# Stage 1: Composer dependencies
FROM composer:2.7 AS composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

# Stage 2: Production image
FROM php:8.2-fpm-alpine

LABEL maintainer="Lavalite Team <team@lavalite.org>"
LABEL description="[SERVICE-NAME] microservice for Lavalite ERP"

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    mysql-client \
    postgresql-dev \
    sqlite

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# Set working directory
WORKDIR /var/www/html

# Copy application files from composer stage
COPY --from=composer /app /var/www/html

# Copy configuration files
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

# Start supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

#### 10.2 Docker Compose

**File: `docker-compose.yml`**

```yaml
version: '3.8'

services:
  # Application service
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: lavalite-[service]-app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache
    environment:
      - APP_NAME=${APP_NAME:-Lavalite [SERVICE]}
      - APP_ENV=${APP_ENV:-production}
      - APP_DEBUG=${APP_DEBUG:-false}
      - APP_URL=${APP_URL:-http://localhost}
      - DB_CONNECTION=${DB_CONNECTION:-mysql}
      - DB_HOST=db
      - DB_PORT=3306
      - DB_DATABASE=${DB_DATABASE:-lavalite_[service]}
      - DB_USERNAME=${DB_USERNAME:-lavalite}
      - DB_PASSWORD=${DB_PASSWORD:-secret}
      - CACHE_DRIVER=${CACHE_DRIVER:-redis}
      - QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}
      - SESSION_DRIVER=${SESSION_DRIVER:-redis}
      - REDIS_HOST=redis
      - REDIS_PORT=6379
    ports:
      - "${APP_PORT:-8001}:80"
    networks:
      - lavalite-network
    depends_on:
      - db
      - redis

  # Database service (MySQL)
  db:
    image: mysql:8.0
    container_name: lavalite-[service]-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE:-lavalite_[service]}
      MYSQL_USER: ${DB_USERNAME:-lavalite}
      MYSQL_PASSWORD: ${DB_PASSWORD:-secret}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-root_secret}
    volumes:
      - db-data:/var/lib/mysql
    ports:
      - "${DB_PORT:-3307}:3306"
    networks:
      - lavalite-network

  # Redis service
  redis:
    image: redis:7-alpine
    container_name: lavalite-[service]-redis
    restart: unless-stopped
    volumes:
      - redis-data:/data
    ports:
      - "${REDIS_PORT:-6380}:6379"
    networks:
      - lavalite-network

networks:
  lavalite-network:
    driver: bridge

volumes:
  db-data:
  redis-data:
```

---

### Step 11: Create Docker Config Files

#### Create directory structure:

```bash
mkdir -p docker/nginx docker/php
```

#### 11.1 Nginx Config

**File: `docker/nginx/nginx.conf`**

```nginx
user www-data;
worker_processes auto;
pid /run/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    access_log /var/log/nginx/access.log main;
    error_log /var/log/nginx/error.log warn;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;

    gzip on;
    gzip_disable "msie6";

    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/http.d/*.conf;
}
```

**File: `docker/nginx/default.conf`**

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/html/public;

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 11.2 PHP Config

**File: `docker/php/php.ini`**

```ini
[PHP]
post_max_size = 100M
upload_max_filesize = 100M
memory_limit = 512M
max_execution_time = 300

[Date]
date.timezone = UTC

[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
```

#### 11.3 Supervisor Config

**File: `docker/supervisord.conf`**

```ini
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stderr_logfile=/var/log/php-fpm.err.log
stdout_logfile=/var/log/php-fpm.out.log

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/var/log/nginx.err.log
stdout_logfile=/var/log/nginx.out.log
```

---

### Step 12: Create README Documentation

**File: `README.md`**

```markdown
# Lavalite [SERVICE-NAME] - Microservice

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)](https://php.net)

[SERVICE DESCRIPTION] microservice for Lavalite ERP platform.

## Features

- 🔐 JWT Authentication Integration
- 🏢 Multi-Organization Support
- 📦 RESTful API
- 🐳 Docker Support
- ✅ Comprehensive Testing
- 📊 API Documentation

## Quick Start

### Local Development

\`\`\`bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Run migrations
php artisan migrate

# Start server
php artisan serve
\`\`\`

### Docker Deployment

\`\`\`bash
# Start all services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# View logs
docker-compose logs -f
\`\`\`

## API Documentation

### Authentication

All protected endpoints require JWT token in Authorization header:

\`\`\`
Authorization: Bearer {your-jwt-token}
\`\`\`

### Organization Context

Multi-tenant endpoints require organization context:

\`\`\`
X-Organization-ID: {organization-uuid}
\`\`\`

### Endpoints

See [docs/API.md](docs/API.md) for complete API documentation.

## Integration with Auth Service

This microservice integrates with the Lavalite Auth microservice for:

- User authentication
- Organization management
- Role-based access control

Configure in `.env`:

\`\`\`
AUTH_SERVICE_URL=http://localhost:8000
AUTH_SERVICE_API_KEY=your-api-key
\`\`\`

## Testing

\`\`\`bash
# Run tests
php artisan test

# With coverage
php artisan test --coverage
\`\`\`

## License

MIT License

---

**Version:** 1.0.0  
**Maintainer:** Lavalite Team
```

---

### Step 13: Create .gitignore

**File: `.gitignore`**

```gitignore
/.phpunit.cache
/node_modules
/public/build
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.env.production
.phpunit.result.cache
Homestead.json
Homestead.yaml
auth.json
npm-debug.log
yarn-error.log
/.fleet
/.idea
/.vscode
```

---

## 🏛️ Architecture Patterns

### 1. Multi-Organization Pattern

All business models should:

1. Include `organization_id` foreign key
2. Use `HasOrganization` trait
3. Scope queries by organization
4. Validate organization access

**Example Model:**

```php
<?php

namespace App\Models;

use App\Traits\HasOrganization;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'sku',
        'price',
        // ... other fields
    ];

    // Automatically scope all queries by organization
    protected static function booted()
    {
        static::addGlobalScope('organization', function ($query) {
            if ($organizationId = request()->header('X-Organization-ID')) {
                $query->where('organization_id', $organizationId);
            }
        });
    }
}
```

### 2. API Response Pattern

Use consistent JSON responses:

```php
// Success
{
    "success": true,
    "message": "Resource created successfully",
    "data": { ... }
}

// Error
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field": ["Error message"]
    }
}

// Paginated
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

### 3. Controller Pattern

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;

class ProductController extends BaseController
{
    public function index()
    {
        $products = Product::paginate(15);
        return $this->paginated($products, 'Products retrieved successfully');
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create([
            'organization_id' => $this->getOrganizationId(),
            ...$request->validated(),
        ]);

        return $this->success($product, 'Product created successfully', 201);
    }

    public function show(Product $product)
    {
        // Automatically scoped by organization via global scope
        return $this->success($product, 'Product retrieved successfully');
    }
}
```

---

## 🔗 Integration with Auth Microservice

### 1. Create HTTP Client Service

```bash
php artisan make:class Services/AuthServiceClient
```

**File: `app/Services/AuthServiceClient.php`**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AuthServiceClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.auth.url');
        $this->apiKey = config('services.auth.api_key');
    }

    /**
     * Verify JWT token and get user data.
     */
    public function verifyToken(string $token): ?array
    {
        $cacheKey = "auth:token:{$token}";

        return Cache::remember($cacheKey, 300, function () use ($token) {
            $response = Http::withToken($token)
                ->get("{$this->baseUrl}/api/user");

            return $response->successful() ? $response->json() : null;
        });
    }

    /**
     * Get organization details.
     */
    public function getOrganization(string $organizationId): ?array
    {
        $cacheKey = "auth:org:{$organizationId}";

        return Cache::remember($cacheKey, 3600, function () use ($organizationId) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->get("{$this->baseUrl}/api/organizations/{$organizationId}");

            return $response->successful() ? $response->json('data') : null;
        });
    }

    /**
     * Check user permissions.
     */
    public function hasPermission(int $userId, string $organizationId, string $permission): bool
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'X-Organization-ID' => $organizationId,
        ])->post("{$this->baseUrl}/api/users/{$userId}/check-permission", [
            'permission' => $permission,
        ]);

        return $response->successful() && $response->json('data.has_permission');
    }
}
```

### 2. Add Service Configuration

**File: `config/services.php`**

```php
'auth' => [
    'url' => env('AUTH_SERVICE_URL', 'http://localhost:8000'),
    'api_key' => env('AUTH_SERVICE_API_KEY'),
],
```

---

## 🧪 Testing Setup

### Create Base Test Case

**File: `tests/TestCase.php`**

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->artisan('migrate:fresh');
    }

    /**
     * Create a test user with JWT token.
     */
    protected function actingAsUser($organizationId = null)
    {
        $user = User::factory()->create();
        
        if ($organizationId) {
            $organization = Organization::factory()->create(['id' => $organizationId]);
            $user->organizations()->attach($organization);
        }

        $token = auth()->login($user);
        
        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Organization-ID' => $organizationId ?? $user->organizations->first()?->id,
        ]);

        return $user;
    }
}
```

---

## ✅ Deployment Checklist

### Pre-Deployment

- [ ] Environment variables configured
- [ ] Database migrations tested
- [ ] All tests passing
- [ ] API documentation updated
- [ ] Docker images built and tested
- [ ] Logging configured
- [ ] Error tracking setup (Sentry, etc.)

### Production Configuration

```bash
# .env production values
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error

# Database
DB_CONNECTION=mysql
DB_HOST=your-production-db-host

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Auth Service
AUTH_SERVICE_URL=https://auth.lavalite.org
AUTH_SERVICE_API_KEY=your-production-api-key
```

### Deployment Commands

```bash
# Build production image
docker build -t lavalite/[service]:latest .

# Push to registry
docker push lavalite/[service]:latest

# Deploy with docker-compose
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Cache config
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

---

## 📞 Support

- **Issues:** Create issue in GitHub repository
- **Discussions:** Team Slack channel
- **Documentation:** See `/docs` directory

---

## 📝 Next Steps After Setup

1. **Define your business models** - Create models specific to your microservice
2. **Create migrations** - Define database schema
3. **Build API endpoints** - Implement RESTful controllers
4. **Write tests** - Cover critical functionality
5. **Add Postman collection** - Document API endpoints
6. **Setup CI/CD** - Automate testing and deployment
7. **Configure monitoring** - Setup logging and alerts

---

**Happy coding! 🚀**

---

<p align="center">
  <strong>Lavalite ERP Platform</strong><br>
  Building the future of enterprise resource planning
</p>
