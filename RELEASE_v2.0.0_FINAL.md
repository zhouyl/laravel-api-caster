# Laravel API Caster v2.0.0 正式发布

## 🎉 发布成功！

**版本**: v2.0.0  
**发布日期**: 2024-10-11  
**Git 标签**: [v2.0.0](https://github.com/zhouyl/laravel-api-caster/releases/tag/v2.0.0)  
**状态**: ✅ 已成功发布到 GitHub 和 Packagist

## 🚀 重大更新

### ✅ 1. 完整的文档系统
- **双语支持**: 完整的英文和中文文档
- **API 参考**: 100% 的方法都有详细的 PHPDoc 注释
- **使用示例**: 丰富的代码示例和最佳实践
- **IDE 支持**: 完整的类型提示和自动完成

### ✅ 2. 全面的版本支持
- **PHP**: 8.3, 8.4 (最新稳定版本)
- **Laravel**: 10.x, 11.x, 12.x (LTS 和最新版本)
- **跨平台**: Ubuntu + Windows 完整测试
- **依赖策略**: prefer-stable + prefer-lowest 双重验证

### ✅ 3. 卓越的代码质量
- **测试覆盖率**: 92.04% (48 个测试，414 个断言)
- **静态分析**: PHPStan Level 6 + 严格规则，0 错误
- **代码风格**: PHP-CS-Fixer 完全合规
- **类型安全**: 完整的 PHP 8.3+ 类型声明

### ✅ 4. 增强的功能
- **Caster 类**: 所有方法都有完整的文档和类型约束
- **Response::timestamp**: 完善的时间戳处理和测试
- **Entity 映射**: 强大的数据转换和映射功能
- **错误处理**: 完善的异常处理和验证

## 📊 质量指标

### 测试质量
```
Tests: 48, Assertions: 414
Coverage: 92.04% (312/339 lines)
- Caster: 92.59% (125/135 lines)
- Entity: 90.64% (155/171 lines)  
- Response: 96.97% (32/33 lines)
```

### 代码质量
```
PHPStan: Level 6 + Strict Rules ✅ 0 errors
PHP-CS-Fixer: ✅ 0 style issues
Type Coverage: 100% public methods
Documentation: 100% PHPDoc coverage
```

### CI 状态
```
✅ Ubuntu + PHP 8.3 + Laravel 10.x/11.x/12.x
✅ Ubuntu + PHP 8.4 + Laravel 10.x/11.x/12.x
✅ Windows + PHP 8.3/8.4 + Laravel 11.x/12.x
✅ prefer-stable + prefer-lowest testing
```

## 🔧 技术亮点

### 类型安全
```php
// 严格的类型声明
public function cast(string $cast, mixed $value): mixed

// 泛型支持
Collection<int, Entity>

// 联合类型
int|string $decimals
```

### 文档质量
```php
/**
 * Cast data according to the specified cast type.
 * 
 * @param string $cast The cast type (e.g., 'int', 'datetime', 'decimal:2')
 * @param mixed $value The value to cast
 * @return mixed The casted value
 * 
 * @example
 * $caster->cast('int', '123'); // returns 123
 * $caster->cast('datetime', '2023-01-01'); // returns Carbon instance
 */
```

### IDE 支持
- PHPStorm meta 文件用于类型推断
- VS Code 配置和扩展推荐
- 完整的自动完成支持
- 实时错误检测

## 🌐 国际化

### 双语文档
- **English**: README.md, CHANGELOG.md
- **中文**: README_CN.md, CHANGELOG_CN.md
- **一致性**: 内容完全同步，准确翻译

### 本地化特色
- 适合中文开发者的代码示例
- 符合中文技术文档习惯
- 完整的术语翻译

## 📦 安装和使用

### 安装
```bash
composer require mellivora/laravel-api-caster:^2.0
```

### 基本使用
```php
use Mellivora\Http\Api\Entity;

// 创建实体
$user = new Entity([
    'id' => '123',
    'name' => 'John Doe',
    'created_at' => '2023-01-01'
]);

// 类型转换
class UserEntity extends Entity
{
    protected array $casts = [
        'id' => 'int',
        'created_at' => 'datetime',
    ];
}
```

### 高级功能
```php
// 字段映射
protected array $mappings = [
    'author' => UserEntity::class,
    'comments[]' => CommentEntity::class,
];

// 计算属性
protected array $appends = ['fullName'];

public function getFullNameAttribute(): string
{
    return $this->firstName . ' ' . $this->lastName;
}
```

## 🎯 升级指南

### 从 v1.x 升级
1. 升级 PHP 到 8.3+
2. 运行 `composer update mellivora/laravel-api-caster`
3. 检查类型声明兼容性
4. 运行测试确保功能正常

### 新项目
直接安装 v2.0.0 即可享受所有新特性。

## 🔮 未来规划

### 短期目标
- 监控社区反馈和问题报告
- 性能优化和内存使用改进
- 更多实用的转换器类型

### 长期规划
- 自动代码生成工具
- 更深度的 IDE 集成
- 扩展的生态系统支持

## 🙏 致谢

感谢所有为这个项目做出贡献的开发者和用户！

特别感谢：
- Laravel 社区的支持和反馈
- PHP 生态系统的持续发展
- 所有测试用户的宝贵建议

## 📞 支持和反馈

- **GitHub**: https://github.com/zhouyl/laravel-api-caster
- **Issues**: https://github.com/zhouyl/laravel-api-caster/issues
- **Packagist**: https://packagist.org/packages/mellivora/laravel-api-caster
- **Email**: 81438567@qq.com

---

**Laravel API Caster v2.0.0 现已正式发布！** 🎉

这是一个里程碑式的版本，提供了：
- 🚀 **现代化的 PHP 8.3/8.4 支持**
- 📚 **完整的双语文档**
- 🔧 **卓越的开发体验**
- ✅ **生产就绪的稳定性**

立即开始使用，体验全新的 API 数据处理体验！
