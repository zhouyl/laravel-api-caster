# Laravel API Caster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mellivora/laravel-api-caster.svg?style=flat-square)](https://packagist.org/packages/mellivora/laravel-api-caster)
[![Tests](https://img.shields.io/github/actions/workflow/status/zhouyl/laravel-api-caster/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/zhouyl/laravel-api-caster/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/mellivora/laravel-api-caster.svg?style=flat-square)](https://packagist.org/packages/mellivora/laravel-api-caster)

一个强大的 Laravel 包，用于处理 API 响应数据的类型转换和实体映射。提供类似 Laravel Eloquent 的接口来处理 API 响应数据，支持类型转换、字段映射、数据变换等功能。

[English](README.md) | 中文

## 特性

- 🚀 **类型转换**: 支持多种数据类型转换（int、string、bool、datetime、decimal、enum 等）
- 🔄 **字段映射**: 将嵌套数据映射为实体对象
- 📝 **字段重命名**: 灵活的字段名称映射
- 🎯 **字段过滤**: 包含/排除特定字段
- 📊 **计算属性**: 支持动态计算的属性
- 🔧 **自定义转换器**: 可扩展的类型转换系统
- 💡 **IDE 支持**: 完整的 PHPDoc 注释和类型提示
- 🧪 **高测试覆盖率**: 88%+ 的代码覆盖率

## 安装

通过 Composer 安装：

```bash
composer require mellivora/laravel-api-caster
```

## 快速开始

### 基本用法

```php
use Mellivora\Http\Api\Entity;

// 创建实体
$user = new Entity([
    'id' => '123',
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => '2023-01-01 12:00:00'
]);

// 访问属性（自动转换为驼峰命名）
echo $user->id;        // 123
echo $user->name;      // John Doe
echo $user->createdAt; // 2023-01-01 12:00:00
```

### 从 HTTP 响应创建实体

```php
use Mellivora\Http\Api\Response;
use Mellivora\Http\Api\Entity;

// 从 HTTP 响应创建
$httpResponse = Http::get('https://api.example.com/users/123');
$response = new Response($httpResponse);
$user = Entity::from($response);

// 创建实体集合
$users = Entity::collection($response->data());
```

### 类型转换

```php
class UserEntity extends Entity
{
    protected array $casts = [
        'id' => 'int',
        'is_active' => 'bool',
        'created_at' => 'datetime',
        'score' => 'decimal:2',
        'status' => StatusEnum::class,
    ];
}

$user = new UserEntity([
    'id' => '123',           // 转换为 int
    'is_active' => '1',      // 转换为 bool
    'created_at' => '2023-01-01', // 转换为 Carbon 实例
    'score' => '95.678',     // 转换为 '95.68'
    'status' => 1,           // 转换为 StatusEnum 实例
]);
```

### 字段映射

```php
class PostEntity extends Entity
{
    protected array $mappings = [
        'author' => UserEntity::class,      // 单个实体映射
        'comments[]' => CommentEntity::class, // 数组实体映射
        'meta.tags[]' => TagEntity::class,   // 嵌套数组映射
    ];
}

$post = new PostEntity([
    'title' => 'Hello World',
    'author' => ['id' => 1, 'name' => 'John'],
    'comments' => [
        ['id' => 1, 'content' => 'Great post!'],
        ['id' => 2, 'content' => 'Thanks for sharing'],
    ]
]);

// 访问映射的实体
echo $post->author->name;           // John (UserEntity 实例)
echo $post->comments->first()->content; // Great post! (Collection<CommentEntity>)
```

### 字段重命名

```php
class UserEntity extends Entity
{
    protected array $renames = [
        'userId' => 'id',      // 将 userId 重命名为 id
        'fullName' => 'name',  // 将 fullName 重命名为 name
    ];
}

$user = new UserEntity(['user_id' => 123, 'full_name' => 'John Doe']);
echo $user->id;   // 123
echo $user->name; // John Doe
```

### 字段过滤

```php
class UserEntity extends Entity
{
    // 只包含指定字段
    protected array $includes = ['id', 'name', 'email'];
    
    // 或者排除敏感字段
    protected array $excludes = ['password', 'secretKey'];
}
```

### 计算属性

```php
class UserEntity extends Entity
{
    protected array $appends = ['fullName', 'isAdmin'];
    
    public function getFullNameAttribute(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
    
    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }
}

$user = new UserEntity(['first_name' => 'John', 'last_name' => 'Doe', 'role' => 'admin']);
echo $user->fullName; // John Doe
echo $user->isAdmin;  // true
```

## 支持的类型转换

### 基本类型
- `int`, `integer` - 整数
- `float`, `double`, `real` - 浮点数
- `string` - 字符串
- `bool`, `boolean` - 布尔值
- `array` - 数组
- `object` - 对象

### 特殊类型
- `json` - JSON 字符串转数组
- `collection` - Laravel Collection
- `decimal:2` - 指定精度的小数
- `date` - 日期（Carbon 实例）
- `datetime` - 日期时间（Carbon 实例）
- `timestamp` - Unix 时间戳
- `date:Y-m-d` - 自定义日期格式
- `datetime:Y-m-d H:i:s` - 自定义日期时间格式

### 枚举类型
```php
enum StatusEnum: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;
}

class UserEntity extends Entity
{
    protected array $casts = [
        'status' => StatusEnum::class,
    ];
}
```

### 自定义转换器
```php
use Mellivora\Http\Api\Contracts\Castable;

class UppercaseCaster implements Castable
{
    public static function castUsing(array $arguments): static
    {
        return new static();
    }
    
    public function getCastValue(Entity $entity, string $key, mixed $value): string
    {
        return strtoupper($value);
    }
    
    public function fromCastValue(Entity $entity, string $key, mixed $value): string
    {
        return strtolower($value);
    }
}

// 使用自定义转换器
protected array $casts = [
    'name' => UppercaseCaster::class,
];
```

## Response 类

Response 类提供了便捷的方法来处理 API 响应：

```php
use Mellivora\Http\Api\Response;

$response = new Response($httpResponse);

// 获取响应字段
$code = $response->code();       // 响应代码
$message = $response->message(); // 响应消息
$data = $response->data();       // 响应数据
$meta = $response->meta();       // 元数据

// 获取嵌套数据
$userId = $response->data('user.id');
$total = $response->meta('pagination.total');

// 获取时间戳
$timestamp = $response->timestamp(); // 返回 Carbon 实例或 null
```

## 高级功能

### 默认值
```php
class UserEntity extends Entity
{
    protected array $defaults = [
        'status' => 'active',
        'role' => 'user',
    ];
}
```

### 驼峰命名控制
```php
class UserEntity extends Entity
{
    protected bool $useCamel = false; // 禁用驼峰命名转换
}
```

### 数组访问
```php
$user = new UserEntity(['id' => 123, 'name' => 'John']);

// 数组式访问
echo $user['id'];   // 123
echo $user['name']; // John

// 检查属性存在
if (isset($user['email'])) {
    echo $user['email'];
}

// 转换为数组
$array = $user->toArray();
$json = $user->toJson();
```

## 测试

运行测试套件：

```bash
composer test
```

运行带覆盖率的测试：

```bash
composer test-coverage
```

## 代码质量

检查代码风格：

```bash
composer phpcs
```

修复代码风格：

```bash
composer phpcs-fix
```

运行静态分析：

```bash
composer phpstan
composer psalm
```

运行所有质量检查：

```bash
composer quality
```

## 更新日志

请查看 [CHANGELOG](CHANGELOG.md) 了解最近的更改。

## 贡献

请查看 [CONTRIBUTING](CONTRIBUTING.md) 了解详细信息。

## 安全漏洞

如果您发现安全漏洞，请发送邮件至 81438567@qq.com。

## 许可证

MIT 许可证。请查看 [License File](LICENSE.md) 了解更多信息。
