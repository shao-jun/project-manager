# 开发者配置功能说明

## 功能概述

在code-review页面新增了"开发者配置"功能，允许管理员管理开发者用户信息，包括用户名、邮箱和启用状态。

## 数据库表结构

### develope_users表
```sql
CREATE TABLE `develope_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(100) NOT NULL COMMENT '用户名',
    `email` varchar(255) NOT NULL COMMENT '邮箱',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用：1-启用，0-禁用',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='开发者用户表';
```

## 安装步骤

### 1. 数据库升级
在运行功能之前，需要先升级数据库：

```bash
# 方法1：使用MySQL命令行
mysql -u root -p dev_automation < src/database/upgrade_developers.sql

# 方法2：使用phpMyAdmin或其他数据库管理工具
# 运行 src/database/upgrade_developers.sql 文件中的SQL语句
```

### 2. 验证安装
升级完成后，数据库中应该包含以下表：
- `develope_users` - 开发者用户表
- `code_review_configs` - 代码审查配置表

## 使用方法

### 1. 访问开发者配置
- 在code-review页面的左侧配置面板中
- 点击"👥 开发者配置"按钮
- 右侧面板将切换到开发者管理界面

### 2. 新增开发者
- 点击"+ 新增开发者"按钮
- 填写用户名、邮箱和选择状态
- 点击"保存"按钮

### 3. 编辑开发者
- 在开发者列表中点击"✏️ 编辑"按钮
- 修改相关信息
- 点击"保存"按钮

### 4. 删除开发者
- 在开发者列表中点击"🗑️ 删除"按钮
- 确认删除操作

### 5. 返回审查记录
- 点击"📋 返回审查"按钮
- 右侧面板将切换回审查记录界面

## API接口

### 获取开发者列表
```
GET /api/code-review?action=get_developers
```

### 新增开发者
```
POST /api/code-review?action=add_developer
Content-Type: application/json

{
    "username": "用户名",
    "email": "邮箱地址",
    "status": 1
}
```

### 更新开发者
```
PUT /api/code-review?action=update_developer
Content-Type: application/json

{
    "id": 开发者ID,
    "username": "用户名",
    "email": "邮箱地址",
    "status": 1
}
```

### 删除开发者
```
DELETE /api/code-review?action=delete_developer
Content-Type: application/json

{
    "id": 开发者ID
}
```

## 功能特点

1. **用户友好**: 简洁的界面设计，操作直观
2. **数据验证**: 用户名和邮箱唯一性检查
3. **状态管理**: 支持启用/禁用开发者账户
4. **实时更新**: 操作后自动刷新列表
5. **错误处理**: 完善的错误提示和异常处理

## 注意事项

1. 用户名和邮箱必须唯一
2. 删除操作不可恢复
3. 邮箱格式会自动验证
4. 所有操作都有确认提示
5. 支持键盘ESC键关闭模态框

## 故障排除

### 常见错误

1. **Call to undefined method Database::fetchOne()**
   - 原因：使用了不存在的方法名
   - 解决：已修复，使用正确的`fetch()`方法

2. **Table 'develope_users' doesn't exist**
   - 原因：数据库表未创建
   - 解决：运行数据库升级脚本

3. **Table 'code_review_configs' doesn't exist**
   - 原因：代码审查配置表未创建
   - 解决：运行数据库升级脚本

### 调试建议

- 检查浏览器控制台的错误信息
- 查看PHP错误日志
- 确认数据库连接正常
- 验证表结构是否正确

## 技术实现

- 前端：原生JavaScript + CSS3
- 后端：PHP + MySQL
- 架构：RESTful API设计
- 数据：JSON格式交互 