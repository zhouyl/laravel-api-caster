# Laravel API Caster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mellivora/laravel-api-caster.svg?style=flat-square)](https://packagist.org/packages/mellivora/laravel-api-caster)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/zhouyl/laravel-api-caster/run-tests?label=tests)](https://github.com/zhouyl/laravel-api-caster/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/zhouyl/laravel-api-caster/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/zhouyl/laravel-api-caster/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mellivora/laravel-api-caster.svg?style=flat-square)](https://packagist.org/packages/mellivora/laravel-api-caster)

Convert API response results to Entity objects like Laravel Eloquent. This package provides a powerful and flexible way to transform API responses into structured, type-safe entities with support for casting, mapping, data transformation, and enhanced collection handling with metadata support.

## Features

- 🚀 **Laravel Eloquent-like API** - Familiar syntax for Laravel developers
- 🔄 **Automatic Type Casting** - Built-in support for common data types
- 🎯 **Custom Casters** - Create your own casting logic
- 📦 **Entity Mapping** - Map nested data to Entity objects
- 🔧 **Flexible Configuration** - Includes, excludes, renames, and more
- 📊 **Collection with Meta** - Enhanced collections with metadata support
- 🛡️ **Type-Safe Collections** - Complete type validation for all collection operations
- 🧪 **Fully Tested** - Comprehensive test suite with 84.79% coverage
- ⚡ **High Performance** - Optimized for speed and memory efficiency
- 🔒 **Type Safe** - Full PHP 8.3+ type declarations

## Requirements

- PHP 8.3 or 8.4
- Laravel 10.x, 11.x, or 12.x

## Installation

You can install the package via composer:

```bash
composer require mellivora/laravel-api-caster
```

## Quick Start

### Basic Usage

```php
use Mellivora\Http\Api\Entity;
use Mellivora\Http\Api\Response;

// From HTTP Response
$response = new Response($httpResponse);
$entity = Entity::from($response);

// From array data
$entity = new Entity([
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Access data
echo $entity->id;    // 123
echo $entity->name;  // John Doe
echo $entity->email; // john@example.com
```

### Collections

```php
// Create collection from response
$collection = Entity::collectionResponse($response);

// Create collection from array
$collection = Entity::collection([
    ['id' => 1, 'name' => 'User 1'],
    ['id' => 2, 'name' => 'User 2'],
]);

foreach ($collection as $entity) {
    echo $entity->name;
}
```

## Advanced Usage

### Type Casting

```php
class UserEntity extends Entity
{
    protected array $casts = [
        'id' => 'int',
        'email_verified_at' => 'datetime',
        'settings' => 'json',
        'score' => 'decimal:2',
        'status' => UserStatusEnum::class,
    ];
}

$user = new UserEntity([
    'id' => '123',
    'email_verified_at' => '2023-01-01 12:00:00',
    'settings' => '{"theme": "dark"}',
    'score' => '95.75',
    'status' => 'active',
]);

// Automatically casted
$user->id;                 // int(123)
$user->email_verified_at;  // Carbon instance
$user->settings;           // array ['theme' => 'dark']
$user->score;              // string '95.75'
$user->status;             // UserStatusEnum::ACTIVE
```

### Entity Mapping

```php
class ProductEntity extends Entity
{
    protected array $mappings = [
        'category' => CategoryEntity::class,
        'tags[]' => TagEntity::class,
    ];
}

$product = new ProductEntity([
    'id' => 1,
    'name' => 'Laptop',
    'category' => ['id' => 1, 'name' => 'Electronics'],
    'tags' => [
        ['id' => 1, 'name' => 'Tech'],
        ['id' => 2, 'name' => 'Gadget'],
    ],
]);

$product->category;        // CategoryEntity instance
$product->tags;            // EntityCollection of TagEntity instances
$product->tags->first();   // TagEntity instance
```

### Field Configuration

```php
class UserEntity extends Entity
{
    // Include only specific fields
    protected array $includes = ['id', 'name', 'email'];
    
    // Exclude specific fields
    protected array $excludes = ['password', 'secret'];
    
    // Rename fields
    protected array $renames = [
        'user_id' => 'id',
        'full_name' => 'name',
    ];
    
    // Append computed attributes
    protected array $appends = ['display_name'];
    
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->email . ')';
    }
}
```

### Custom Casters

```php
use Mellivora\Http\Api\Contracts\Castable;
use Mellivora\Http\Api\Contracts\CastsAttributes;

class MoneyCaster implements Castable
{
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes {
            public function getCastValue(Entity $entity, string $key, $value): Money
            {
                return new Money($value);
            }
            
            public function fromCastValue(Entity $entity, string $key, mixed $value): int
            {
                return $value->getCents();
            }
        };
    }
}

class OrderEntity extends Entity
{
    protected array $casts = [
        'total' => MoneyCaster::class,
    ];
}
```

## Available Cast Types

| Type | Description | Example |
|------|-------------|---------|
| `int`, `integer` | Cast to integer | `'123'` → `123` |
| `float`, `double`, `real` | Cast to float | `'12.34'` → `12.34` |
| `string` | Cast to string | `123` → `'123'` |
| `bool`, `boolean` | Cast to boolean | `1` → `true` |
| `array` | Cast JSON to array | `'[1,2,3]'` → `[1,2,3]` |
| `json` | Alias for array | Same as array |
| `object` | Cast JSON to object | `'{"a":1}'` → `stdClass` |
| `collection` | Cast to Collection | `[1,2,3]` → `Collection` |
| `date` | Cast to Carbon date | `'2023-01-01'` → `Carbon` |
| `datetime` | Cast to Carbon datetime | `'2023-01-01 12:00:00'` → `Carbon` |
| `timestamp` | Cast timestamp to Carbon | `1672574400` → `Carbon` |
| `decimal:2` | Cast to decimal string | `12.345` → `'12.35'` |
| `date:Y-m-d` | Custom date format | Custom format |
| `datetime:Y-m-d H:i` | Custom datetime format | Custom format |

## Testing

```bash
composer test
```

## Code Quality

```bash
# Run all quality checks
composer quality

# Fix code style
composer phpcs-fix

# Run static analysis
composer phpstan

# Run rector
composer rector-fix
```

## API Documentation

### Entity Class

#### Methods

- `__construct(iterable $attributes = [], array $meta = [])` - Create new entity
- `from(Response $response): static` - Create from Response object
- `collection(iterable $items, array $meta = []): Collection` - Create collection
- `collectionResponse(Response $response): Collection` - Create collection from Response
- `toArray(): array` - Convert to array
- `toJson(int $options = 0): string` - Convert to JSON
- `keys(): array` - Get all keys
- `values(): array` - Get all values
- `isEmpty(): bool` - Check if empty
- `copy(): static` - Create a copy
- `meta(string $key = null, mixed $default = null): mixed` - Get meta data
- `origin(string $key = null, mixed $default = null): mixed` - Get original data

### Response Class

#### Methods

- `__construct(HttpResponse|MessageInterface $response)` - Create new response
- `code(): int` - Get response code
- `message(): string` - Get response message
- `data(string $key = null, mixed $default = null): mixed` - Get response data
- `meta(string $key = null, mixed $default = null): mixed` - Get response meta
- `toArray(): array` - Convert to array

### Caster Class

#### Methods

- `cast(string $cast, mixed $value): mixed` - Cast value
- `value(string $cast, mixed $value): mixed` - Get original value

## Best Practices

1. **Use Type Hints**: Always define proper types in your entity classes
2. **Leverage Caching**: Cache frequently used entities to improve performance
3. **Validate Data**: Use Laravel's validation before creating entities
4. **Handle Nulls**: Always consider null values in your casting logic
5. **Test Thoroughly**: Write tests for your custom entities and casters

## Performance Tips

- Use `includes` to limit processed fields
- Avoid deep nesting when possible
- Cache entity instances for repeated use
- Use collections for bulk operations

## Troubleshooting

### Common Issues

**Issue**: "Class not found" error when using custom casters
**Solution**: Ensure your caster class implements the `Castable` interface

**Issue**: Infinite recursion with circular references
**Solution**: Use `excludes` to break circular references

**Issue**: Memory issues with large datasets
**Solution**: Process data in chunks using collections

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [zhouyl](https://github.com/zhouyl)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
