# 贡献指南

欢迎并感谢您的贡献。

我们通过 [Github](https://github.com/zhouyl/laravel-api-caster) 上的 Pull Request 接受贡献。

## Pull Request

- **[PSR-12 编码标准](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md)** - 使用 `composer phpcs` 检查代码风格，使用 `composer phpcs-fix` 修复。

- **添加测试！** - 如果您的补丁没有测试，将不会被接受。

- **记录任何行为变更** - 确保 `README.md` 和任何其他相关文档保持最新。

- **考虑我们的发布周期** - 我们尝试遵循 [SemVer v2.0.0](http://semver.org/)。随意破坏公共 API 是不可接受的。

- **每个功能一个 pull request** - 如果您想做多件事，请发送多个 pull request。

- **发送连贯的历史** - 确保您的 pull request 中的每个单独提交都是有意义的。如果您在开发过程中必须进行多个中间提交，请在提交之前[压缩它们](http://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages)。

## 运行测试

```bash
composer test
```

## 运行代码风格检查

```bash
composer phpcs
```

## 修复代码风格问题

```bash
composer phpcs-fix
```

## 运行静态分析

```bash
composer phpstan
```

## 运行所有质量检查

```bash
composer quality
```

## 开发环境设置

1. Fork 仓库
2. 克隆您的 fork：`git clone https://github.com/YOUR-USERNAME/laravel-api-caster.git`
3. 安装依赖：`composer install`
4. 创建分支：`git checkout -b my-new-feature`
5. 进行更改
6. 运行测试：`composer test`
7. 运行质量检查：`composer quality`
8. 提交更改：`git commit -am 'Add some feature'`
9. 推送到分支：`git push origin my-new-feature`
10. 提交 pull request

## 编码标准

此项目遵循 PSR-12 编码标准并使用：

- **PHP CS Fixer** 用于代码风格
- **PHPStan** 用于静态分析
- **PHPUnit** 用于测试
- **Rector** 用于代码现代化

## 测试指南

- 为所有新功能编写测试
- 确保所有测试在提交前通过
- 追求高测试覆盖率
- 包含边界情况和错误条件
- 使用描述性的测试方法名称

## 文档

- 为新功能更新 README.md
- 为复杂代码添加内联文档
- 包含使用示例
- 更新 API 文档

## 报告问题

报告问题时，请包含：

- PHP 版本
- Laravel 版本
- 包版本
- 重现步骤
- 预期行为
- 实际行为
- 任何错误消息

## 功能请求

我们欢迎功能请求！请：

- 检查功能是否已存在
- 解释用例
- 提供示例
- 考虑向后兼容性

## 行为准则

请注意，此项目发布时附带了[贡献者行为准则](CODE_OF_CONDUCT.md)。通过参与此项目，您同意遵守其条款。

**祝您编码愉快**！
