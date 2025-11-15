# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Admin dashboard API endpoints
- Audit logging system
- API versioning support
- Webhook system
- OAuth2/Social login

## [1.3.0] - 2025-11-13

### Added - Security Features

#### Email Verification
- Email verification flow for new user registrations
- Verification tokens with 24-hour expiration
- Automated verification email sending
- Email resend functionality with rate limiting
- Database fields: `email_verification_token`, `email_verification_sent_at`

#### Password Reset
- Secure password reset flow
- Password reset token generation
- Email notifications for password reset
- Token expiration (60 minutes)
- Security: doesn't reveal if email exists

#### Two-Factor Authentication (2FA)
- TOTP-based 2FA implementation
- QR code generation for authenticator apps (SVG format)
- 8 recovery codes per user
- Recovery code management (use, regenerate)
- Enable/disable 2FA with password confirmation
- Database fields: `two_factor_enabled`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`
- Integrated with login flow
- Dependencies added: `pragmarx/google2fa`, `bacon/bacon-qr-code`

#### Rate Limiting
- Custom authentication rate limiting middleware
- Login: 5 attempts per minute per email/IP
- Registration: 5 attempts per minute
- Password reset: 3 attempts per minute
- Email resend: 3 attempts per minute
- General API: 60 requests per minute
- Automatic rate limit clearing on successful authentication

#### Comprehensive Testing
- Feature tests for authentication flow
- Feature tests for email verification
- Feature tests for password reset
- Feature tests for 2FA
- Feature tests for organizations and RBAC
- Unit tests for User model
- Unit tests for Organization model
- Model factories for testing (User, Organization, Role, Permission)
- 40+ test cases covering critical functionality

### Changed
- Updated User model with new security fields
- Enhanced registration flow to send verification email
- Enhanced login flow to support 2FA
- Updated API routes with rate limiting
- Added comprehensive security documentation

### Technical Details
**New Controllers:**
- `EmailVerificationController` - Email verification endpoints
- `PasswordResetController` - Password reset endpoints
- `TwoFactorController` - 2FA management endpoints

**New Middleware:**
- `ThrottleAuthAttempts` - Custom rate limiting for authentication

**New Mail:**
- `VerifyEmail` - Email verification notification
- `ResetPassword` - Password reset notification

**New Views:**
- `emails/verify-email.blade.php` - Email verification template
- `emails/reset-password.blade.php` - Password reset template

**Documentation:**
- Added `docs/SECURITY_FEATURES.md` - Comprehensive security guide
- Updated README with new features
- API documentation for all new endpoints

## [Unreleased - Previous]

## [1.2.0] - 2025-11-13

### Changed - BREAKING CHANGES

#### Terminology Refactoring: Tenant → Organization
Complete refactoring of all "tenant" terminology to "organization" throughout the entire codebase for better semantic clarity.

**Database:**
- Renamed table: `tenants` → `organizations`
- Renamed table: `tenant_user` → `organization_user`
- Renamed column: `tenant_id` → `organization_id` (in roles, permissions, role_user, refresh_tokens)
- Migration files renamed and updated

**Models:**
- Renamed model: `Tenant` → `Organization`
- Updated all model relationships and methods
- Updated: Role, Permission, User, RefreshToken models

**Traits:**
- Renamed: `HasMultiTenantRolesAndPermissions` → `HasMultiOrganizationRolesAndPermissions`
- All 20+ methods renamed:
  - `joinTenant()` → `joinOrganization()`
  - `rolesInTenant()` → `rolesInOrganization()`
  - `hasRoleInTenant()` → `hasRoleInOrganization()`
  - `belongsToTenant()` → `belongsToOrganization()`
  - And all other tenant-related methods

**Controllers:**
- Renamed: `TenantController` → `OrganizationController`
- Updated all controller methods to use organization terminology
- Updated: AuthController, RoleController, PermissionController, UserController

**Middleware:**
- Renamed: `SetTenantContext` → `SetOrganizationContext`
- Updated header: `X-Tenant-ID` → `X-Organization-ID`
- Updated middleware alias: `'tenant'` → `'organization'`
- Updated: RoleMiddleware, PermissionMiddleware

**Routes:**
- `/api/tenants` → `/api/organizations`
- All route parameters updated: `{tenantId}` → `{organizationId}`
- Middleware references updated

**Seeders:**
- Renamed: `MultiTenantSeeder` → `MultiOrganizationSeeder`

**Documentation:**
- Updated all .md files (10+ files)
- Renamed: `MULTI_TENANCY.md` → `MULTI_ORGANIZATION.md`
- Updated Postman collection (30+ requests)
- Updated composer.json, .env.example

**API Changes:**
- Login endpoint parameter: `tenant_id` → `organization_id`
- JWT claims: `tenant_id` → `organization_id`
- All endpoint paths updated
- All request/response body parameters updated

### Preserved
- ✅ Global roles functionality (organization_id = 'global')
- ✅ Super admin authorization
- ✅ All RBAC features
- ✅ Refresh token system
- ✅ Multi-organization support
- ✅ Complete feature parity

### Migration Notes
For existing deployments:
1. Backup your database before updating
2. Run `php artisan migrate:fresh --seed --seeder=MultiOrganizationSeeder`
3. Update frontend API calls: change all `/api/tenants` to `/api/organizations`
4. Update parameter names: `tenant_id` → `organization_id`
5. Clear Laravel caches: `php artisan config:clear && php artisan route:clear`

## [1.1.0] - 2025-01-12

### Added

#### Database
- Added `is_super_admin` boolean field to `users` table (default: false)
- Support for global roles with `organization_id = null` (schema already supported)
- Support for global permissions with `organization_id = null` (schema already supported)

#### Models & Methods
- Added `is_super_admin` to User model's `$fillable` and `casts()`
- Added `isSuperAdmin()` helper method to User model

#### Controllers - Authorization
- **RoleController**: Added super admin check for creating/updating global roles (`organization_id = null`)
- **PermissionController**: Added super admin check for creating/updating global permissions
- Both controllers now validate `organization_id` as nullable UUID
- Return 403 Forbidden when non-super-admin tries to create global entities

#### Traits - Global Entity Support
- **HasMultiOrganizationRolesAndPermissions**: Updated to automatically include global roles/permissions
  - `rolesInOrganization()`: Now includes global roles
  - `hasRoleInOrganization()`: Checks both organization-specific and global
  - `hasPermissionInOrganization()`: Checks both organization-specific and global
  - `getAllPermissionsInOrganization()`: Returns both organization-specific and global

#### Seeders
- **DatabaseSeeder**: Added super admin user creation (`superadmin@lavalite.org`)

#### Documentation
- **NEW**: `docs/GLOBAL_ROLES_PERMISSIONS.md` - Comprehensive 9-section guide
- **NEW**: `IMPLEMENTATION_SUMMARY.md` - Technical implementation details
- **NEW**: `GLOBAL_ROLES_QUICKREF.md` - Quick reference for developers
- **UPDATED**: `docs/README.md` - Added global roles section

#### API Examples
- Added Postman requests for creating global roles/permissions
- Both marked as "Super Admin Only" with proper descriptions

### Changed
- Global roles/permissions are now automatically included in all organization queries
- Unique validation on roles/permissions now considers `organization_id` context
- Super admin users can create system-wide roles/permissions

### Security
- Only super admins (`is_super_admin = true`) can create/update global roles/permissions
- API returns 403 Forbidden for unauthorized global entity operations
- Controllers enforce authorization at create and update endpoints

### API Changes
- `POST /api/roles` - Now accepts optional `organization_id` (null = global, UUID = organization-scoped)
- `PUT /api/roles/{id}` - Now accepts optional `organization_id`
- `POST /api/permissions` - Now accepts optional `organization_id` (null = global, UUID = organization-scoped)
- `PUT /api/permissions/{id}` - Now accepts optional `organization_id`

### Backward Compatibility
✅ Fully backward compatible - existing organization-scoped roles work unchanged

## [1.0.0] - 2025-11-12

### Added
- Initial release of Lavalite Auth microservice
- Multi-organization authorization architecture with UUID organization IDs
- JWT authentication with tymon/jwt-auth
- Complete RBAC (Role-Based Access Control) system
- Organization-scoped roles and permissions
- User can belong to multiple organizations with different roles
- Enhanced user profile management (20+ fields)
  - Personal information (name, email, phone, date of birth, gender)
  - Address fields (line1, line2, city, state, postal code, country)
  - User preferences (JSON storage)
  - Avatar upload functionality
  - Timezone and language settings
  - User bio and profile customization
- Authentication endpoints
  - User registration with comprehensive profile fields
  - Login with organization selection
  - Organization switching with JWT token refresh
  - Logout with token invalidation
- User profile endpoints
  - Get complete user profile
  - Update profile information
  - Avatar upload/delete (max 2MB, jpeg/png/jpg/gif)
  - Password change with validation
  - Preferences management (JSON merge)
- Organization/Organization management
  - List user's organizations
  - Create/update organizations
  - Add/remove users from organizations
  - Get user context in organization (roles & permissions)
- Role management (organization-scoped)
  - Create/list/view roles
  - Assign/remove roles to users
  - Assign permissions to roles
- Permission management (organization-scoped)
  - Create/list/view permissions
  - Assign permissions directly to users
- Comprehensive seeder with demo data
  - 2 demo organizations (Acme Corporation, TechStart Inc)
  - 3 demo users with complete profiles
  - 11 permissions per organization
  - 3 roles per organization (admin, moderator, user)
- Middleware
  - RoleMiddleware for role-based route protection
  - PermissionMiddleware for permission-based route protection
  - SetOrganizationContext for automatic organization context detection
- JWT features
  - Custom claims for organization_id, roles, and permissions
  - 60-minute token TTL (configurable)
  - Token blacklist on logout
- Database migrations
  - Enhanced users table with 20+ fields and soft deletes
  - Organizations table with UUID primary keys
  - Roles and permissions tables (organization-scoped)
  - Pivot tables for many-to-many relationships
- Documentation
  - Comprehensive README with quick start guide
  - Setup and installation guide
  - Multi-organization authentication guide
  - JWT authentication documentation
  - API reference with all endpoints
  - User management guide
  - RBAC and multi-tenancy guide
  - Postman collection with 30+ API requests
- Docker support
  - Multi-stage Dockerfile for optimized builds
  - docker-compose.yml with MySQL, PostgreSQL, and Redis
  - Nginx and PHP-FPM configuration
  - Supervisor for process management
  - Health check endpoints
  - Support for both development and production
- GitHub Actions CI/CD
  - Automated testing workflow
  - Docker image build and push to GHCR
  - Code style checking with Laravel Pint
  - Multi-PHP version testing (8.2, 8.3)
- Open source preparation
  - MIT License
  - Contributing guidelines
  - Proper composer.json for Packagist publishing
  - Comprehensive .gitignore
  - .dockerignore for optimized builds

### Security
- UUID organization IDs to prevent enumeration attacks
- Password hashing with bcrypt
- JWT token-based authentication
- Token blacklist for logout
- Middleware protection for sensitive routes
- CORS configuration
- Security headers in Nginx
- Environment-based configuration

### Dependencies
- Laravel 12.x
- PHP 8.2+
- Laravel Sanctum 4.2
- tymon/jwt-auth 2.2
- MySQL 8.0 / PostgreSQL 16 / SQLite
- Redis 7 (for caching and queues)

---

## Release Notes

### Version 1.0.0 - Initial Release

This is the first stable release of Lavalite Auth, a production-ready multi-organization authorization microservice. The system provides:

**Core Features:**
- Multi-organization architecture where users can belong to multiple organizations
- JWT-based authentication with embedded permissions
- Complete RBAC system with organization-scoped roles and permissions
- Comprehensive user profile management
- RESTful API with 30+ endpoints

**Deployment Options:**
- Docker containers with full orchestration
- Traditional server deployment
- Cloud-ready with horizontal scaling support

**Developer Experience:**
- Well-documented API
- Postman collection for quick testing
- Seeded demo data for development
- Comprehensive documentation
- Open source with contribution guidelines

For installation instructions, see [README.md](README.md).
For API documentation, see [docs/API_REFERENCE.md](docs/API_REFERENCE.md).

[Unreleased]: https://github.com/lavalite/erp/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/lavalite/erp/releases/tag/v1.0.0
