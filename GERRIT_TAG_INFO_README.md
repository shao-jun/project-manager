# Gerrit Tag信息获取功能

## 功能概述

在`src/api/gerrit-api.php`中添加了新的函数`getTagInfo()`，用于获取Gerrit项目的tag信息。

## 函数签名

```php
function getTagInfo($moduleName, $tagName, $db)
```

### 参数说明

- `$moduleName` (string): 模块名称，例如：`base/network-manager`
- `$tagName` (string): tag名称，例如：`v1.0.0`
- `$db` (Database): 数据库实例

### 返回值

返回一个包含以下键的数组：
- `success` (bool): 操作是否成功
- `message` (string): 操作结果消息
- `data` (mixed): 返回的数据，失败时为null

## 使用示例

### 1. 基本用法

```php
<?php
require_once 'src/api/gerrit-api.php';
require_once 'src/config/database.php';

// 获取数据库实例
$db = Database::getInstance();

// 获取tag信息
$result = getTagInfo('base/network-manager', 'v1.0.0', $db);

if ($result['success']) {
    echo "Tag信息: " . json_encode($result['data']);
} else {
    echo "获取失败: " . $result['message'];
}
?>
```

### 2. 在现有代码中使用

```php
// 在需要获取tag信息的地方调用
$tagInfo = getTagInfo($moduleName, $buildTag, $db);

if ($tagInfo['success']) {
    // 处理tag信息
    $tagData = $tagInfo['data'];
    // ... 其他逻辑
} else {
    // 处理错误
    error_log("获取tag信息失败: " . $tagInfo['message']);
}
```

## API调用详情

函数内部会调用以下Gerrit API：

```
GET https://gerrit.example.com/a/projects/{moduleName}/tags/{tagName}
```

### 认证信息

使用配置文件中定义的认证信息：
- 用户名：`ut000190`
- 密码：``

### 请求头

- `Content-Type: application/json`
- `Accept: application/json`
- `User-Agent: Mozilla/5.0 (compatible; ProjectManager/1.0)`

## 错误处理

函数会处理以下错误情况：

1. **参数验证错误**：模块名称或tag名称为空
2. **认证错误**：无法获取Gerrit认证信息
3. **网络错误**：Gerrit API请求失败
4. **HTTP错误**：Gerrit返回非200状态码

## 注意事项

1. 确保Gerrit服务器可访问
2. 确保认证信息正确
3. 模块名称和tag名称需要URL编码
4. 函数会自动处理认证和错误重试

## 相关文件

- `src/api/gerrit-api.php` - 主要实现文件
- `src/config/database.php` - 数据库配置
- 配置文件中的Gerrit认证信息

## 更新日志

- 2025-09-02: 添加`getTagInfo()`函数
- 支持获取指定模块和tag的详细信息
- 完整的错误处理和日志记录
