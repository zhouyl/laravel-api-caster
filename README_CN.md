# Laravel API Caster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mellivora/laravel-api-caster.svg?style=flat-square)](https://packagist.org/packages/mellivora/laravel-api-caster)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/zhouyl/laravel-api-caster/run-tests?label=tests)](https://github.com/zhouyl/laravel-api-caster/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/zhouyl/laravel-api-caster/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/zhouyl/laravel-api-caster/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mellivora/laravel-api-caster.svg?style=flat-square)](https://packagist.org/packages/mellivora/laravel-api-caster)

[English](README.md) | 中文

将 API 响应结果转换为类似 Laravel Eloquent 的实体对象。这个包提供了一个强大而灵活的方式，将 API 响应转换为结构化、类型安全的实体，支持类型转换、映射、数据变换以及增强的集合处理和元数据支持。

## 特性

- 🚀 **类似 Laravel Eloquent 的 API** - Laravel 开发者熟悉的语法
- 🔄 **自动类型转换** - 内置常见数据类型支持
- 🎯 **自定义转换器** - 创建您自己的转换逻辑
- 📦 **实体映射** - 将嵌套数据映射到实体对象
- 🔧 **灵活配置** - 包含、排除、重命名等功能
- 📊 **集合与元数据** - 增强的集合处理，支持元数据
- 🛡️ **类型安全集合** - 所有集合操作的完整类型验证
- 🧪 **完全测试** - 84.79% 覆盖率的综合测试套件
- ⚡ **高性能** - 针对速度和内存效率优化
- 🔒 **类型安全** - 完整的 PHP 8.3+ 类型声明

## 系统要求

- PHP 8.3 或 8.4
- Laravel 10.x、11.x 或 12.x

## 安装

您可以通过 composer 安装此包：

```bash
composer require mellivora/laravel-api-caster
```

## 快速开始

### 基本用法

```php
use Mellivora\Http\Api\Entity;
use Mellivora\Http\Api\Response;

// 从 HTTP 响应创建
$response = new Response($httpResponse);
$entity = Entity::from($response);

// 从数组数据创建
$entity = new Entity([
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// 访问数据
echo $entity->id;    // 123
echo $entity->name;  // John Doe
echo $entity->email; // john@example.com
```

### 集合

```php
// 从响应创建集合
$collection = Entity::collectionResponse($response);

// 从数组创建集合
$collection = Entity::collection([
    ['id' => 1, 'name' => '用户 1'],
    ['id' => 2, 'name' => '用户 2'],
]);

foreach ($collection as $entity) {
    echo $entity->name;
}
```

## 高级用法

### 类型转换

```php
class UserEntity extends Entity
{
    protected array $casts = [
        'id' => 'int',
        'emailVerifiedAt' => 'datetime',
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

// 自动转换
$user->id;                 // int(123)
$user->emailVerifiedAt;    // Carbon 实例
$user->settings;           // array ['theme' => 'dark']
$user->score;              // string '95.75'
$user->status;             // UserStatusEnum::ACTIVE
```

### 实体映射

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
    'name' => '笔记本电脑',
    'category' => ['id' => 1, 'name' => '电子产品'],
    'tags' => [
        ['id' => 1, 'name' => '科技'],
        ['id' => 2, 'name' => '数码'],
    ],
]);

$product->category;        // CategoryEntity 实例
$product->tags;            // TagEntity 实例的集合
$product->tags->first();   // TagEntity 实例
```

### 字段配置

```php
class UserEntity extends Entity
{
    // 只包含特定字段
    protected array $includes = ['id', 'name', 'email'];
    
    // 排除特定字段
    protected array $excludes = ['password', 'secret'];
    
    // 重命名字段
    protected array $renames = [
        'user_id' => 'id',
        'full_name' => 'name',
    ];
    
    // 追加计算属性
    protected array $appends = ['displayName'];
    
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->email . ')';
    }
}
```

### 自定义转换器

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

## 可用的转换类型

| 类型 | 描述 | 示例 |
|------|-------------|---------|
| `int`, `integer` | 转换为整数 | `'123'` → `123` |
| `float`, `double`, `real` | 转换为浮点数 | `'12.34'` → `12.34` |
| `string` | 转换为字符串 | `123` → `'123'` |
| `bool`, `boolean` | 转换为布尔值 | `1` → `true` |
| `array` | 将 JSON 转换为数组 | `'[1,2,3]'` → `[1,2,3]` |
| `json` | array 的别名 | 与 array 相同 |
| `object` | 将 JSON 转换为对象 | `'{"a":1}'` → `stdClass` |
| `collection` | 转换为 Collection | `[1,2,3]` → `Collection` |
| `date` | 转换为 Carbon 日期 | `'2023-01-01'` → `Carbon` |
| `datetime` | 转换为 Carbon 日期时间 | `'2023-01-01 12:00:00'` → `Carbon` |
| `timestamp` | 将时间戳转换为 Carbon | `1672574400` → `Carbon` |
| `decimal:2` | 转换为小数字符串 | `12.345` → `'12.35'` |
| `date:Y-m-d` | 自定义日期格式 | 自定义格式 |
| `datetime:Y-m-d H:i` | 自定义日期时间格式 | 自定义格式 |

## 测试

```bash
composer test
```

## 代码质量

```bash
# 运行所有质量检查
composer quality

# 修复代码风格
composer phpcs-fix

# 运行静态分析
composer phpstan

# 运行 rector
composer rector-fix
```

## API 文档

### Entity 类

#### 方法

- `__construct(iterable $attributes = [], array $meta = [])` - 创建新实体
- `from(Response $response): static` - 从 Response 对象创建
- `collection(iterable $items, array $meta = []): Collection` - 创建集合
- `collectionResponse(Response $response): Collection` - 从 Response 创建集合
- `toArray(): array` - 转换为数组
- `toJson(int $options = 0): string` - 转换为 JSON
- `keys(): array` - 获取所有键
- `values(): array` - 获取所有值
- `isEmpty(): bool` - 检查是否为空
- `copy(): static` - 创建副本
- `meta(string $key = null, mixed $default = null): mixed` - 获取元数据
- `origin(string $key = null, mixed $default = null): mixed` - 获取原始数据

### Response 类

#### 方法

- `__construct(HttpResponse|MessageInterface $response)` - 创建新响应
- `code(): int` - 获取响应代码
- `message(): string` - 获取响应消息
- `data(string $key = null, mixed $default = null): mixed` - 获取响应数据
- `meta(string $key = null, mixed $default = null): mixed` - 获取响应元数据
- `toArray(): array` - 转换为数组

### Caster 类

#### 方法

- `cast(string $cast, mixed $value): mixed` - 转换值
- `value(string $cast, mixed $value): mixed` - 获取原始值

## 最佳实践

1. **使用类型提示**：始终在实体类中定义适当的类型
2. **利用缓存**：缓存经常使用的实体以提高性能
3. **验证数据**：在创建实体之前使用 Laravel 的验证
4. **处理空值**：在转换逻辑中始终考虑空值
5. **充分测试**：为您的自定义实体和转换器编写测试

## 性能提示

- 使用 `includes` 限制处理的字段
- 尽可能避免深层嵌套
- 缓存实体实例以供重复使用
- 对批量操作使用集合

## 故障排除

### 常见问题

**问题**：使用自定义转换器时出现"类未找到"错误
**解决方案**：确保您的转换器类实现了 `Castable` 接口

**问题**：循环引用导致无限递归
**解决方案**：使用 `excludes` 打破循环引用

**问题**：大数据集的内存问题
**解决方案**：使用集合分块处理数据

## 许可证

MIT 许可证 (MIT)。请查看 [许可证文件](LICENSE.md) 了解更多信息。
