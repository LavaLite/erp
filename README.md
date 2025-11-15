# Lavalite Core - Multi-Organization ERP Core Microservice

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Supported-blue.svg)](https://docker.com)

A production-ready Laravel-based multi-organization ERP core microservice with JWT authentication, role-based access control (RBAC), modular architecture, and comprehensive user management.

## ✨ Features

### Core Features
- 🏢 **Multi-Tenancy** - Users can belong to multiple organizations with different roles
- 🔐 **JWT Authentication** - Secure token-based authentication with embedded permissions
- 👥 **RBAC System** - Flexible role and permission management scoped per organization
- 🔑 **UUID Organization IDs** - Secure, non-enumerable organization identifiers
- 📦 **Module Management** - Modular ERP architecture with 68+ Odoo-style modules
- 🎯 **Selective Module Access** - Organizations can enable/disable specific modules
- ⚙️ **Module Configuration** - Per-organization settings, limits, and licensing
- 👤 **Enhanced User Profiles** - 20+ profile fields with avatar upload support
- 🗑️ **Soft Deletes** - Safe data retention
- 🚀 **API-First Design** - RESTful API with complete documentation
- 🐳 **Docker Support** - Ready-to-deploy containers
- 📊 **Comprehensive Seeding** - Demo data for quick development

### Security Features
- 📧 **Email Verification** - Automated email verification for new registrations
- 🔒 **Password Reset** - Secure forgot password flow with email tokens
- 🔐 **Two-Factor Authentication (2FA)** - TOTP-based 2FA with QR codes and recovery codes
- 🚦 **Rate Limiting** - Intelligent rate limiting on authentication endpoints
- 🛡️ **Security Best Practices** - Password hashing, token expiration, CORS support

### Quality & Testing
- ✅ **Comprehensive Test Suite** - Unit and feature tests for all critical functionality
- 🧪 **Test Coverage** - Authentication, RBAC, organizations, email, 2FA, password reset
- 🏭 **Model Factories** - Easy test data generation

## 🚀 Quick Start

### Local Development

```bash
# Clone the repository
git clone https://github.com/lavaliteerp/core.git
cd core

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Run migrations and seed demo data
php artisan migrate:fresh --seed --seeder=MultiOrganizationSeeder

# Start development server
php artisan serve
```

Visit `http://localhost:8000` and use the [demo credentials](#-demo-credentials) to login.

### Docker Deployment

```bash
# Clone the repository
git clone https://github.com/lavaliteerp/core.git
cd core

# Copy environment file
cp .env.example .env

# Build and start containers
docker-compose up -d

# Run migrations inside container
docker-compose exec app php artisan migrate:fresh --seed --seeder=MultiOrganizationSeeder

# Generate keys inside container
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret
```

Application will be available at `http://localhost:8000`

## 📦 Installation

### Requirements

- PHP 8.2 or higher
- Composer
- MySQL 8.0+ / PostgreSQL 16+ / SQLite
- Redis (optional, for caching and queues)
- Node.js and npm (for frontend assets)

### Step-by-Step Setup

1. **Install PHP Dependencies**
   ```bash
   composer install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   ```
   
   Update `.env` with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lavalite_core
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

3. **Generate Application Keys**
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Run Database Migrations**
   ```bash
   php artisan migrate
   ```

5. **Seed Demo Data (Optional)**
   ```bash
   php artisan db:seed --class=MultiOrganizationSeeder
   ```

6. **Install Frontend Dependencies (Optional)**
   ```bash
   npm install
   npm run build
   ```

7. **Start Development Server**
   ```bash
   php artisan serve
   ```

## 🐳 Docker Deployment

### Using Docker Compose

The project includes a complete Docker setup with MySQL, Redis, and Nginx.

**Start all services:**
```bash
docker-compose up -d
```

**View logs:**
```bash
docker-compose logs -f app
```

**Stop services:**
```bash
docker-compose down
```

**Rebuild containers:**
```bash
docker-compose build --no-cache
docker-compose up -d
```

### Services Included

| Service | Port | Description |
|---------|------|-------------|
| **app** | 8000 | Laravel application with Nginx + PHP-FPM |
| **db** | 3306 | MySQL 8.0 database |
| **redis** | 6379 | Redis for caching and queues |
| **postgres** | 5432 | PostgreSQL (optional, use profile) |

### Optional Services

**Start with PostgreSQL instead of MySQL:**
```bash
docker-compose --profile postgres up -d
```

**Start with queue worker:**
```bash
docker-compose --profile queue up -d
```

**Start with scheduler:**
```bash
docker-compose --profile scheduler up -d
```

### Production Deployment

For production, use the optimized Dockerfile:

```bash
# Build production image
docker build -t lavalite/erp:latest .

# Run container
docker run -d \
  --name lavalite-erp \
  -p 80:80 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e DB_HOST=your-db-host \
  -e DB_DATABASE=your-db-name \
  -e DB_USERNAME=your-db-user \
  -e DB_PASSWORD=your-db-password \
  lavalite/erp:latest
```

## 📚 API Documentation

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | Login with email/password |
| POST | `/api/logout` | Logout and invalidate token |
| GET | `/api/user` | Get current authenticated user |
| POST | `/api/switch-organization` | Switch to different organization |

### User Profile Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/me` | Get complete user profile |
| PUT | `/api/profile` | Update profile information |
| POST | `/api/profile/avatar` | Upload avatar image |
| DELETE | `/api/profile/avatar` | Delete avatar |
| PUT | `/api/profile/password` | Change password |
| PUT | `/api/profile/preferences` | Update user preferences |

### Organization Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/organizations` | List user's organizations |
| POST | `/api/organizations` | Create new organization |
| GET | `/api/organizations/{id}` | Get organization details |
| PUT | `/api/organizations/{id}` | Update organization |
| POST | `/api/organizations/{id}/add-user` | Add user to organization |
| POST | `/api/organizations/{id}/remove-user` | Remove user from organization |

### Roles & Permissions (Admin Only)

All role and permission endpoints require admin privileges and organization context via `X-Organization-ID` header.

**Complete API documentation:** [docs/API_REFERENCE.md](docs/API_REFERENCE.md)

**Postman Collection:** [postman_collection.json](postman_collection.json)

## 🏗️ Architecture

### Multi-Organization Model

```
Users ──┬── Organization A (Admin) ──┬── Roles ──┬── Permissions
        │                      │           └── manage-users
        │                      └── Users
        │
        └── Organization B (User) ───┬── Roles ──┬── Permissions
                               │           └── view-posts
                               └── Users
```

### Database Schema

- **users** - User accounts with 20+ profile fields
- **organizations** - Organizations with UUID primary keys
- **roles** - Organization-scoped roles
- **permissions** - Organization-scoped permissions
- **organization_user** - User-organization relationships
- **role_user** - User-role assignments (per organization)
- **permission_role** - Role-permission assignments
- **permission_user** - Direct user permissions

### JWT Token Structure

```json
{
  "sub": 1,
  "organization_id": "019a77f4-54f3-72c3-beec-c8b1a59dbc23",
  "roles": ["admin"],
  "permissions": ["manage-users", "manage-roles", "..."],
  "exp": 1699876543
}
```

## 🔐 Demo Credentials

After seeding the database, you can use these credentials:

| Email | Password | Organizations | Role |
|-------|----------|---------|------|
| globaladmin@lavalite.org | password | Acme (Admin), TechStart (Admin) | Global Admin |
| superadmin@lavalite.org | password | Acme (Super Admin), TechStart (Super Admin) | Super Admin |
| useradmin@lavalite.org | password | Acme (User Admin), TechStart (User Admin) | User Admin |
| user@lavalite.org | password | Acme (User), TechStart (User) | User |
| client@lavalite.org | password | Acme (Client), TechStart (Client) | Client |

### Example Login Request

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "globaladmin@lavalite.org",
    "password": "password"
  }'
```

## 📖 Documentation

- **[Getting Started Guide](docs/GETTING_STARTED.md)** - Installation and setup
- **[Authentication Guide](docs/AUTHENTICATION.md)** - JWT auth and multi-organization login
- **[API Reference](docs/API_REFERENCE.md)** - Complete endpoint documentation
- **[User Management](docs/USER_MANAGEMENT.md)** - Profile management features
- **[Multi-Organization Guide](docs/MULTI_ORGANIZATION.md)** - Organization and RBAC management
- **[Microservice Skeleton Guide](docs/MICROSERVICE_SKELETON_GUIDE.md)** - Create new microservices based on this architecture
- **[Postman Collection](postman_collection.json)** - Ready-to-use API requests

## 🛠️ Technology Stack

- **Framework:** Laravel 12.x
- **Authentication:** Laravel Sanctum 4.2 + JWT Auth 2.2
- **Database:** MySQL 8.0 / PostgreSQL 16 / SQLite
- **Cache/Queue:** Redis 7
- **PHP:** 8.2+
- **Server:** Nginx + PHP-FPM
- **Container:** Docker + Docker Compose

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
```

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com)
- JWT authentication by [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth)
- Inspired by modern multi-organization SaaS architectures

## 📧 Support

- **Issues:** [GitHub Issues](https://github.com/lavaliteerp/core/issues)
- **Discussions:** [GitHub Discussions](https://github.com/lavaliteerp/core/discussions)
- **Email:** team@lavalite.org

---

<p align="center">Made with ❤️ by the Lavalite Team</p>
<p align="center">
  <a href="https://github.com/lavaliteerp/core">⭐ Star us on GitHub</a>
</p>
