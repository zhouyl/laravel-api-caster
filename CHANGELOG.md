# Changelog

All notable changes to `laravel-api-caster` will be documented in this file.

## [2.1.0] - 2024-12-19

### Added
- **EntityCollection with Meta Support**: Enhanced collection handling with metadata
- **Complete Type Safety**: Full type validation for all collection modification methods
- **Collection Meta Methods**: Added `pagination()`, `total()`, `currentPage()`, `perPage()`, `hasMorePages()`
- **Export Methods**: Added `toArrayWithMeta()` and `toJsonWithMeta()` for complete data export
- **Type-Safe Collection Operations**: All collection methods now enforce Entity type validation
- **GitHub Actions CI/CD**: Complete workflow with tests, PHPStan, and code style checks
- **Comprehensive Test Suite**: 59 tests with 84.79% code coverage
- **PHPStan Level 6**: Static analysis with strict type checking

### Changed
- **EntityCollection Enhancement**: Improved meta handling and type safety
- **Entity::collection() Method**: Now properly passes meta to EntityCollection
- **Collection Method Overrides**: All modification methods now validate Entity types
- **Response Class**: Improved boolean checks and type safety
- **Test Coverage**: Enhanced test suite with type safety validation

### Fixed
- **Collection Meta Handling**: Fixed meta information loss in EntityCollection
- **Type Safety Gaps**: Closed all type validation loopholes in collection methods
- **PHPStan Compatibility**: Resolved method signature compatibility issues
- **GitHub Actions**: All CI/CD workflows now pass successfully

## [2.0.0] - 2024-10-11

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
- Improved type safety across all components
- Enhanced error handling and exception messages

## [1.0.0] - 2024-09-15

### Added
- Initial release
- Entity class with Laravel Eloquent-like API
- Response class for HTTP response handling
- Caster system for type conversion
- Basic collection support
- PSR-4 autoloading
- Comprehensive documentation

### Features
- Convert API responses to Entity objects
- Automatic type casting
- Custom casters support
- Entity mapping for nested data
- Flexible configuration options
- High performance optimizations
- Type-safe operations
