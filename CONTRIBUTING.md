# Contributing to Lavalite Auth

Thank you for your interest in contributing to Lavalite Auth! We welcome contributions from the community.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Enhancements](#suggesting-enhancements)

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please be respectful and considerate in your interactions.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the issue list as you might find that the issue has already been reported. When creating a bug report, include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples**
- **Describe the behavior you observed and what you expected**
- **Include screenshots if relevant**
- **Include your environment details** (OS, PHP version, Laravel version, etc.)

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, include:

- **Use a clear and descriptive title**
- **Provide a detailed description of the suggested enhancement**
- **Explain why this enhancement would be useful**
- **List any alternative solutions you've considered**

### Pull Requests

1. Fork the repository
2. Create a new branch from `main` for your feature or fix
3. Make your changes following our coding standards
4. Write or update tests as needed
5. Update documentation as needed
6. Commit your changes with clear, descriptive messages
7. Push to your fork
8. Submit a pull request

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js and npm (for frontend assets)

### Local Setup

```bash
# Clone the repository
git clone https://github.com/lavalite/erp.git
cd auth

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Run migrations and seeders
php artisan migrate --seed

# Install frontend dependencies
npm install

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

### Docker Setup

```bash
# Build and start containers
docker-compose up -d

# Run migrations inside container
docker-compose exec app php artisan migrate --seed
```

## Coding Standards

### PHP Code Style

We follow PSR-12 coding standards. Use Laravel Pint to format your code:

```bash
./vendor/bin/pint
```

### Code Quality

- Write clean, readable, and maintainable code
- Follow SOLID principles
- Add comments for complex logic
- Use meaningful variable and function names
- Keep functions small and focused

### Testing

- Write tests for new features
- Ensure all tests pass before submitting PR
- Aim for good test coverage

```bash
php artisan test
```

### Database

- Always use migrations for schema changes
- Never modify existing migrations
- Provide both `up()` and `down()` methods
- Use descriptive migration names

### API Design

- Follow RESTful conventions
- Use proper HTTP status codes
- Return consistent JSON responses
- Document all endpoints

## Pull Request Process

1. **Update the documentation** with details of changes to the interface, if applicable
2. **Update the README.md** with details of any new environment variables or configuration
3. **Increase version numbers** in appropriate files following [SemVer](http://semver.org/)
4. **Ensure all tests pass** and add new tests if needed
5. **Request review** from maintainers
6. **Address review feedback** promptly
7. Your PR will be merged once approved by at least one maintainer

### Commit Messages

Write clear commit messages:

```
feat: Add user avatar upload functionality
fix: Resolve JWT token expiration issue
docs: Update API documentation for roles endpoint
refactor: Simplify permission checking logic
test: Add tests for multi-organization context switching
```

Use conventional commit prefixes:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

## Branch Naming

Use descriptive branch names:

```
feature/user-profile-enhancement
bugfix/jwt-token-expiration
docs/api-reference-update
refactor/permission-system
```

## Code Review Guidelines

When reviewing pull requests:

- Be respectful and constructive
- Ask questions rather than make demands
- Acknowledge good solutions
- Suggest improvements, don't just criticize
- Test the changes locally when possible

## Questions?

Feel free to open an issue for any questions or clarifications needed.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to Lavalite Auth! 🎉
