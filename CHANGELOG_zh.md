# 更新日志

本项目的所有重要更改都将记录在此文件中。

格式基于 [Keep a Changelog](https://keepachangelog.com/zh-CN/1.0.0/)，
并且本项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。

## [2.0.0] - 2024-10-11

### 新增
- 🚀 **完整的 PHPDoc 文档**: 为所有类和方法添加了详细的 PHPDoc 注释
- 🔧 **增强的类型安全**: 严格的类型声明和泛型支持
- 💡 **IDE 支持增强**: 
  - PHPStorm meta 文件用于类型推断
  - VS Code 配置和扩展推荐
  - 完整的自动完成支持
- 📊 **静态分析支持**: 
  - Psalm 配置 (Level 3)
  - PHPStan 增强 (Level 6)
  - 零静态分析错误
- 🧪 **文档验证测试**: 确保所有文档示例都能正确运行
- 🌐 **中文文档**: 完整的中文 README 和 CHANGELOG
- 📝 **Response::timestamp()** 方法增强和完整测试覆盖

### 改进
- ✨ **Caster 类完善**: 所有方法都有完整的文档和类型声明
- 🔄 **Entity 类增强**: 改进的泛型支持和方法文档
- 📋 **Response 类优化**: 更好的类型推断和文档
- 🎯 **测试覆盖率**: 提升至 88.22%
- 🛠️ **开发工具**: 
  - VS Code 任务和设置
  - Composer 脚本增强
  - 代码质量工具集成

### 修复
- 🐛 **renames 功能**: 修正了字段重命名的文档和行为描述
- 🔧 **类型声明**: 修复了所有 PHPStan 警告
- 📚 **文档一致性**: 确保所有示例与实际实现一致
- 🧹 **代码风格**: 统一的代码格式和注释风格

### 技术改进
- **类型系统**: 
  - 使用 PHP 8.0+ 联合类型
  - 泛型类型注解 (`@template`, `@implements`)
  - 精确的集合类型 (`Collection<int, Entity>`)
- **文档质量**:
  - 100% 的公共方法有 PHPDoc
  - 实用的代码示例
  - 详细的参数和返回值说明
- **开发体验**:
  - 智能的 IDE 自动完成
  - 实时错误检测
  - 类型安全的重构支持

### 质量指标
- **测试**: 43 个测试，407 个断言
- **覆盖率**: 88.22% 总体覆盖率
- **静态分析**: PHPStan Level 6, Psalm Level 3
- **文档**: 100% PHPDoc 覆盖率

## [1.0.0] - 2024-01-01

### 新增
- 🎉 **初始发布**
- 🏗️ **Entity 类**: 核心实体类，支持类型转换和字段映射
- 🔄 **Caster 类**: 强大的类型转换系统
- 📡 **Response 类**: HTTP 响应包装器
- 🎯 **基本功能**:
  - 类型转换 (int, string, bool, datetime, decimal 等)
  - 字段映射和重命名
  - 计算属性支持
  - 字段过滤 (includes/excludes)
  - 枚举类型支持
  - 自定义转换器接口

### 支持的转换类型
- **基本类型**: int, string, bool, float, array, object
- **特殊类型**: json, collection, decimal, date, datetime, timestamp
- **自定义格式**: date:format, datetime:format
- **枚举**: PHP 8.1+ 枚举支持
- **自定义转换器**: 可扩展的转换器系统

### 核心特性
- **驼峰命名**: 自动转换 snake_case 到 camelCase
- **数组访问**: 实现 ArrayAccess 接口
- **序列化**: 支持序列化和反序列化
- **集合支持**: 与 Laravel Collection 集成
- **类型安全**: 严格的类型检查和转换

---

## 版本说明

### 语义化版本
- **主版本号**: 不兼容的 API 更改
- **次版本号**: 向后兼容的功能新增
- **修订号**: 向后兼容的问题修复

### 支持的 PHP 版本
- **v2.0.0+**: PHP 8.3+
- **v1.0.0**: PHP 8.1+

### 支持的 Laravel 版本
- Laravel 10.x
- Laravel 11.x  
- Laravel 12.x

---

## 迁移指南

### 从 1.x 升级到 2.0

#### 重大变更
- **PHP 版本要求**: 最低 PHP 8.3
- **类型声明**: 更严格的类型检查
- **文档格式**: PHPDoc 注释格式更新

#### 新功能
- **IDE 支持**: 完整的类型推断和自动完成
- **静态分析**: Psalm 和 PHPStan 支持
- **文档验证**: 所有示例都经过测试验证

#### 建议的升级步骤
1. 升级 PHP 到 8.3+
2. 运行 `composer update mellivora/laravel-api-caster`
3. 运行测试确保兼容性
4. 可选：配置 IDE 支持文件

---

## 贡献指南

### 报告问题
- 使用 GitHub Issues
- 提供详细的重现步骤
- 包含环境信息 (PHP/Laravel 版本)

### 提交代码
- Fork 项目
- 创建功能分支
- 编写测试
- 确保代码质量检查通过
- 提交 Pull Request

### 开发环境
```bash
# 克隆项目
git clone https://github.com/zhouyl/laravel-api-caster.git

# 安装依赖
composer install

# 运行测试
composer test

# 代码质量检查
composer quality
```
