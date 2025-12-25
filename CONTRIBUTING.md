# Contributing

Thank you for considering contributing to Laravel Auth Logs! This document provides guidelines for contributing to the project.

## Code of Conduct

This project adheres to a code of conduct that all contributors are expected to follow. Be respectful, inclusive, and professional in all interactions.

## How to Contribute

### Reporting Bugs

If you discover a bug, please create an issue on GitHub with:

- A clear, descriptive title
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- Laravel version, PHP version, and package version
- Any relevant logs or error messages

### Suggesting Features

Feature requests are welcome! Please:

- Check existing issues to avoid duplicates
- Clearly describe the feature and its use case
- Explain why it would be valuable to the package
- Consider if it could be implemented as an extension rather than core functionality

### Pull Requests

We actively welcome pull requests:

1. **Fork the repository** and create your branch from `main`
2. **Install dependencies**: `composer install`
3. **Make your changes** with clear, focused commits
4. **Add tests** for any new functionality
5. **Ensure all tests pass**: `composer test`
6. **Follow coding standards**: `composer lint`
7. **Update documentation** if needed
8. **Submit your pull request** with a clear description

## Development Setup

### Requirements

- PHP 8.4 or higher
- Composer
- Git

### Installation

```bash
# Clone your fork
git clone https://github.com/your-username/laravel-auth-logs.git
cd laravel-auth-logs

# Install dependencies
composer install

# Install Node dependencies (for commit linting)
npm install
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suites
./vendor/bin/pest --filter=LoginTest

# Run with coverage
composer test:coverage

# Run type coverage
composer test:type-coverage
```

### Code Quality

The project uses several tools to maintain code quality:

```bash
# Lint code (Laravel Pint)
composer lint

# Fix code style issues
./vendor/bin/pint

# Static analysis (PHPStan)
composer test:types

# Refactoring checks (Rector)
composer test:refactor
```

## Coding Standards

### PHP Code Style

- Follow PSR-12 coding standards
- Use strict types: `declare(strict_types=1);`
- Use type hints for parameters and return types
- Use readonly properties where appropriate
- Keep methods focused and single-purpose

### Example

```php
<?php

declare(strict_types=1);

namespace Akira\LaravelAuthLogs\Actions;

use Akira\LaravelAuthLogs\AuthenticationLog;
use Illuminate\Contracts\Auth\Authenticatable;

final class CreateAuthenticationLog
{
    /**
     * Create a new authentication log.
     */
    public static function for(Authenticatable $authenticatable, bool $isSuccessFull = false): AuthenticationLog
    {
        return $authenticatable->authenticationLogs()
            ->create([
                'login_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'location' => request()->location,
                'login_successful' => $isSuccessFull,
            ]);
    }
}
```

### Documentation Standards

- Document all public methods with PHPDoc
- Include parameter types and return types
- Add examples for complex functionality
- Keep documentation up-to-date with code changes

### Commit Messages

We use [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add support for custom notification channels
fix: resolve issue with logout timestamp
docs: update installation instructions
test: add tests for failed login notifications
refactor: simplify device recognition logic
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `test`: Test additions or modifications
- `refactor`: Code refactoring
- `chore`: Maintenance tasks
- `perf`: Performance improvements

## Testing Guidelines

### Test Structure

```php
<?php

use Akira\LaravelAuthLogs\Actions\CreateAuthenticationLog;
use App\Models\User;

it('creates authentication log for successful login', function () {
    $user = User::factory()->create();
    
    $log = CreateAuthenticationLog::for($user, isSuccessFull: true);
    
    expect($log)
        ->toBeInstanceOf(\Akira\LaravelAuthLogs\AuthenticationLog::class)
        ->login_successful->toBeTrue()
        ->authenticatable_id->toBe($user->id);
});
```

### Test Coverage

- Aim for high test coverage (minimum 80%)
- Test both success and failure paths
- Include edge cases
- Test integration with Laravel features

### Running Specific Tests

```bash
# Run a specific test file
./vendor/bin/pest tests/Feature/LoginListenerTest.php

# Run tests matching a pattern
./vendor/bin/pest --filter="login"

# Run with verbose output
./vendor/bin/pest --verbose
```

## Documentation Contributions

Documentation improvements are always welcome:

- Fix typos and grammatical errors
- Improve clarity and examples
- Add missing documentation
- Update outdated information

Documentation is located in the `/docs` directory.

## Branch Naming

Use descriptive branch names:

- `feature/add-slack-notifications`
- `fix/logout-timestamp-issue`
- `docs/improve-installation-guide`
- `test/add-device-recognition-tests`

## Pull Request Process

1. **Update your branch** with the latest `main` before submitting
2. **Ensure all tests pass** and linting is clean
3. **Write a clear PR description** explaining:
   - What changes were made
   - Why the changes were necessary
   - Any breaking changes
4. **Link related issues** using keywords like "Fixes #123"
5. **Be responsive** to feedback and review comments

## Release Process

Releases are managed by the maintainers:

1. Version bump following [Semantic Versioning](https://semver.org/)
2. Update `CHANGELOG.md`
3. Tag the release
4. Push to Packagist

## Questions?

If you have questions about contributing:

- Check existing documentation
- Search closed issues for similar discussions
- Open a new discussion on GitHub
- Contact the maintainers

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Recognition

Contributors will be recognized in:

- The project README
- Release notes
- The GitHub contributors page

Thank you for helping make Laravel Auth Logs better!
