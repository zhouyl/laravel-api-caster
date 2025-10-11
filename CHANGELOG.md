# Changelog

All notable changes to `laravel-api-caster` will be documented in this file.

## 2.0.0 - 2024-10-11

### Added
- **Breaking**: Minimum PHP version requirement raised to 8.3
- **Breaking**: Minimum Laravel version requirement raised to 10.0
- Support for Laravel 11.0 and 12.0
- Support for PHP 8.4
- Strict type declarations across all files
- Comprehensive test suite with edge cases and performance tests
- Integration tests for complete workflow validation
- PHPStan level 8 static analysis
- Rector for code modernization
- GitHub Actions CI/CD pipeline
- Comprehensive documentation and contributing guidelines
- Performance optimizations and memory usage improvements

### Changed
- **Breaking**: Updated type hints to use PHP 8.3+ union types
- **Breaking**: Improved method signatures with proper type declarations
- **Breaking**: Updated Serializable interface implementation
- Enhanced error handling and exception messages
- Improved code quality with modern PHP features
- Updated dependencies to latest stable versions

### Fixed
- Fixed Serializable interface deprecation warnings
- Improved ArrayAccess implementation type safety
- Enhanced null value handling in casters
- Fixed potential memory leaks in large datasets

### Removed
- **Breaking**: Dropped support for PHP 8.1 and 8.2
- **Breaking**: Dropped support for Laravel 9.x

## 1.x - Previous Versions

For previous version history, please see the git history.

---

## Upgrade Guide

### From 1.x to 2.0

#### PHP Version
Update your PHP version to 8.3 or higher:
```bash
# Check your PHP version
php -v

# Update PHP (example for Ubuntu)
sudo apt update
sudo apt install php8.3
```

#### Laravel Version
Update your Laravel version to 10.0 or higher:
```bash
composer require "laravel/framework:^10.0"
```

#### Code Changes
1. **Type Declarations**: The package now uses strict typing. Ensure your custom entities and casters properly type their parameters and return values.

2. **Serializable Interface**: If you extend the Entity or Response classes and implement custom serialization, update your implementation to use the new `__serialize()` and `__unserialize()` methods.

3. **ArrayAccess**: If you implement custom ArrayAccess behavior, update your method signatures to match the new mixed type requirements.

#### Testing
Run your test suite to ensure compatibility:
```bash
composer test
```

For detailed migration assistance, please see our [migration guide](MIGRATION.md) or open an issue.
