# Changelog

All notable changes to `lavalite/core` will be documented in this file.

## [1.0.0] - 2025-11-17

### Added
- Initial release of Lavalite Core package
- JWT authentication integration
- Multi-organization support with `HasOrganization` trait
- Base models: User, Organization
- Middleware: SetOrganizationContext, EnsureOrganizationAccess
- BaseController with standard API responses
- AuthServiceClient for microservice communication
- Database migrations for organizations and pivot tables
- Testing helpers with TestCase class
- Comprehensive documentation

### Features
- Automatic organization scoping for queries
- HTTP client integration with caching
- Configurable models and middleware
- Support for Laravel 11.x and 12.x
- PHPDoc annotations for better IDE support

[1.0.0]: https://github.com/lavalite/core/releases/tag/v1.0.0
