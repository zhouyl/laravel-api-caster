# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via Pull Requests on [Github](https://github.com/zhouyl/laravel-api-caster).

## Pull Requests

- **[PSR-12 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md)** - Check the code style with ``composer phpcs`` and fix it with ``composer phpcs-fix``.

- **Add tests!** - Your patch won't be accepted if it doesn't have tests.

- **Document any change in behaviour** - Make sure the `README.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

## Running Tests

```bash
composer test
```

## Running Code Style Checks

```bash
composer phpcs
```

## Fixing Code Style Issues

```bash
composer phpcs-fix
```

## Running Static Analysis

```bash
composer phpstan
```

## Running All Quality Checks

```bash
composer quality
```

## Development Setup

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR-USERNAME/laravel-api-caster.git`
3. Install dependencies: `composer install`
4. Create a branch: `git checkout -b my-new-feature`
5. Make your changes
6. Run tests: `composer test`
7. Run quality checks: `composer quality`
8. Commit your changes: `git commit -am 'Add some feature'`
9. Push to the branch: `git push origin my-new-feature`
10. Submit a pull request

## Coding Standards

This project follows PSR-12 coding standards and uses:

- **PHP CS Fixer** for code style
- **PHPStan** for static analysis
- **PHPUnit** for testing
- **Rector** for code modernization

## Testing Guidelines

- Write tests for all new features
- Ensure all tests pass before submitting
- Aim for high test coverage
- Include edge cases and error conditions
- Use descriptive test method names

## Documentation

- Update README.md for new features
- Add inline documentation for complex code
- Include usage examples
- Update API documentation

## Reporting Issues

When reporting issues, please include:

- PHP version
- Laravel version
- Package version
- Steps to reproduce
- Expected behavior
- Actual behavior
- Any error messages

## Feature Requests

We welcome feature requests! Please:

- Check if the feature already exists
- Explain the use case
- Provide examples
- Consider backward compatibility

## Code of Conduct

Please note that this project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project you agree to abide by its terms.

**Happy coding**!
