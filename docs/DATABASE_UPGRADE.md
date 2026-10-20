# 数据库升级指南

## 概述

本文档说明如何升级项目数据库结构，包括新安装和已有数据库的升级。

## 数据库结构变更

### 最新变更：项目目标字段优化

**变更时间**: 2025-07-21  
**变更内容**: 将项目目标从独立表改为项目表的内置字段

#### 新增字段

| 字段名 | 类型 | 长度 | 是否为空 | 默认值 | 说明 |
|--------|------|------|----------|--------|------|
| `goals` | text | - | YES | NULL | 项目目标（多行文本） |

#### 字段位置
新字段插入在 `description` 字段之后。

#### 变更说明

1. **简化数据结构**: 将项目目标从独立的 `project_goals` 表改为 `projects` 表的 `goals` 字段
2. **移除复杂表**: 删除了 `project_goals` 和 `project_timeline` 表
3. **保持API兼容**: 修改后的API仍然返回相同格式的数据结构
4. **文本格式**: 目标以换行符分隔的多行文本形式存储

#### 数据迁移

- 如果存在 `project_goals` 表，会自动将数据合并到 `projects.goals` 字段
- 目标格式：`目标名称: 目标描述`（如果有描述的话）

### 网站配置表增强

**变更时间**: 2025-07-15  
**变更内容**: 为 `website_configs` 表添加用户凭证字段

#### 新增字段

| 字段名 | 类型 | 长度 | 是否为空 | 默认值 | 说明 |
|--------|------|------|----------|--------|------|
| `username` | varchar | 100 | YES | NULL | 用户名 |
| `password` | varchar | 255 | YES | NULL | 密码（明文存储） |
| `password_encrypted` | varchar | 255 | YES | NULL | 加密后的密码 |

#### 字段位置
新字段插入在 `token_url` 字段之后，`token_obtained_at` 字段之前。

## 升级方法

### 1. 新安装项目

对于新安装的项目，直接执行初始化脚本即可：

```bash
# 创建数据库
mysql -u root -p
CREATE DATABASE dev_automation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 导入基础表结构
mysql -u root -p dev_automation < src/database/init.sql

# 导入配置表（已包含最新字段）
mysql -u root -p dev_automation < src/database/config_tables.sql

# 执行最新迁移
mysql -u root -p dev_automation < src/database/migrations/008_add_goals_to_projects.sql
```

### 2. 升级已有数据库

#### 方法一：使用PHP升级脚本（推荐）

```bash
# 自动执行所有迁移文件
php src/database/upgrade.php
```

**优点**：
- 自动检测并执行所有迁移
- 有错误处理和状态反馈
- 支持批量执行

#### 方法二：手动执行SQL文件

```bash
# 执行特定迁移
mysql -u root -p dev_automation < src/database/migrations/008_add_goals_to_projects.sql

# 或批量执行所有迁移
for file in src/database/migrations/*.sql; do
    echo "执行迁移: $file"
    mysql -u root -p dev_automation < "$file"
done
```

## 迁移文件说明

### 008_add_goals_to_projects.sql

**功能**: 为 `projects` 表添加 `goals` 字段，移除独立的项目目标表

**特点**:
- 智能检测字段是否已存在
- 自动迁移现有数据（如果有）
- 删除不再需要的表
- 验证修改结果

### 001_add_website_config_credentials.sql

**功能**: 为 `website_configs` 表添加用户凭证字段

**特点**:
- 智能检测字段是否已存在
- 避免重复执行导致的错误
- 支持增量升级

## 验证升级

### 检查字段是否添加成功

```sql
-- 检查projects表的goals字段
DESCRIBE projects;

-- 检查website_configs表的凭证字段
DESCRIBE website_configs;
```

### 测试API功能

```bash
# 测试项目目标API
curl "http://localhost:8000/api/projects?action=goals&project_id=1"

# 测试添加目标API
curl -X POST "http://localhost:8000/api/projects" \
  -H "Content-Type: application/json" \
  -d '{"action": "add_goal", "project_id": 1, "goal_name": "测试目标"}'
```

## 注意事项

1. **备份数据**: 升级前请务必备份数据库
2. **测试环境**: 建议先在测试环境验证升级过程
3. **API兼容性**: 升级后API返回格式保持不变
4. **数据格式**: 项目目标现在以文本形式存储，支持多行

## 故障排除

### 常见问题

1. **字段已存在错误**
   - 迁移脚本会自动检测，不会重复添加字段

2. **表不存在错误**
   - 迁移脚本使用 `DROP TABLE IF EXISTS`，不会报错

3. **数据迁移失败**
   - 检查原表是否存在数据
   - 查看错误日志获取详细信息

### 回滚方案

如果需要回滚，可以手动删除新字段：

```sql
-- 删除goals字段（谨慎操作）
ALTER TABLE projects DROP COLUMN goals;
```

**注意**: 回滚会丢失已迁移的数据，请谨慎操作。

## 最佳实践

1. **升级前备份**：
   ```bash
   mysqldump -u root -p dev_automation > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **测试环境验证**：先在测试环境执行升级，确认无误后再在生产环境执行。

3. **监控升级过程**：关注升级脚本的输出，确保所有步骤都成功执行。

4. **功能测试**：升级后进行完整的功能测试，确保系统正常运行。

## 联系支持

如果在升级过程中遇到问题，请：

1. 检查错误日志
2. 确认数据库配置
3. 查看本文档的故障排除部分
4. 联系技术支持团队 