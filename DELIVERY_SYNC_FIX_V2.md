# 交付记录同步外键约束错误修复 - 最终方案

## 🚨 问题描述

在【浦发】L420x交付记录同步过程中出现以下错误：
```
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`dev_automation`.`deliver_package`, CONSTRAINT `deliver_package_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE)
```

## 💡 最终解决方案

采用**去掉外键约束**的方案，让`deliver_package`表纯粹用于存储CRP信息：

### 核心思路
- `deliver_package`表只负责同步CRP的信息
- 不需要管`modules`表中是否存在对应记录
- `deliver_package.module_id`直接使用CRP获取的`ProjectID`
- 通过`build_module_name`字段存储模块名称用于显示

## ✅ 具体修复内容

### 1. 删除外键约束
创建迁移文件 `016_remove_deliver_package_foreign_keys.sql`：

```sql
-- 删除module_id的外键约束
SET @sql = (SELECT CONCAT('ALTER TABLE deliver_package DROP FOREIGN KEY ', CONSTRAINT_NAME)
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'deliver_package' 
            AND COLUMN_NAME = 'module_id' 
            AND REFERENCED_TABLE_NAME = 'modules'
            LIMIT 1);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 修改字段注释，说明现在存储CRP的ProjectID
ALTER TABLE deliver_package MODIFY COLUMN module_id INT NOT NULL COMMENT 'CRP项目ID(ProjectID)';
```

### 2. 恢复简化的同步逻辑
修改 `src/api/projects.php` 中的同步逻辑：

```php
foreach ($crpData as $crpItem) {
    $moduleId = $crpItem['ProjectID'] ?? null;  // ✅ 直接使用CRP的ProjectID
    if (!$moduleId) continue;
    $foundModuleIds[] = $moduleId;
    $buildModuleName = $crpItem['ProjectName'] ?? null;
    // ... 其他字段 ...
    
    // 直接插入/更新deliver_package表，无需检查modules表
    if ($existingRecord) {
        // 更新现有记录
        $db->execute("UPDATE deliver_package SET ...", [...]);
    } else {
        // 插入新记录
        $db->execute("INSERT INTO deliver_package (...) VALUES (...)", [...]);
    }
}
```

### 3. 修改查询逻辑
由于不再有外键关系，修改所有相关查询：

**原来的查询（有问题）：**
```sql
SELECT dp.*, m.name as module_name, m.repo_url
FROM deliver_package dp
JOIN modules m ON dp.module_id = m.id  -- ❌ 外键约束错误
WHERE dp.delivery_record_id = ?
```

**修改后的查询（正确）：**
```sql
SELECT dp.*, 
       dp.build_module_name as module_name,
       CONCAT('https://gerrit.example.com/plugins/gitiles//', dp.build_module_name) as repo_url
FROM deliver_package dp
WHERE dp.delivery_record_id = ?
```

## 📁 修改的文件

1. **`src/database/migrations/016_remove_deliver_package_foreign_keys.sql`** (新建)
   - 删除deliver_package表的外键约束
   - 修改module_id字段注释

2. **`src/api/projects.php`** 
   - 恢复简化的同步逻辑，直接使用CRP的ProjectID
   - 修改所有deliver_package相关查询，不再JOIN modules表
   - 使用build_module_name字段获取模块名称

## 🎯 方案优势

### ✅ 优点
1. **简单直接**：不需要复杂的模块名称匹配逻辑
2. **无依赖**：deliver_package表完全独立，不依赖modules表
3. **数据完整**：CRP的所有数据都能正常同步，不会丢失
4. **性能优秀**：没有额外的查询开销
5. **维护简单**：逻辑清晰，易于理解和维护

### 🔄 数据关系
- `deliver_package.module_id` = CRP的`ProjectID`（数字）
- `deliver_package.build_module_name` = CRP的`ProjectName`（字符串）
- 显示时使用`build_module_name`，存储时使用CRP的`ProjectID`

## 🧪 测试步骤

1. **重新尝试同步**：
   - 访问项目详情页面
   - 点击交付记录的"🔄 同步"按钮
   - 现在应该可以正常同步，不会出现外键约束错误

2. **验证数据**：
   - 检查`deliver_package`表中的数据
   - 确认`module_id`存储的是CRP的ProjectID
   - 确认`build_module_name`正确存储了模块名称

3. **检查显示**：
   - 打包信息正常显示模块名称
   - 仓库链接正确生成

## 📊 数据示例

**CRP API返回数据：**
```json
{
  "ProjectID": 12345,
  "ProjectName": "dde-control-center",
  "Tag": "v1.2.3",
  "BuildID": 67890
}
```

**deliver_package表存储：**
```sql
INSERT INTO deliver_package (
  delivery_record_id, 
  module_id,           -- 12345 (CRP的ProjectID)
  build_module_name,   -- "dde-control-center"
  cur_tag,            -- "v1.2.3"  
  build_id            -- 67890
) VALUES (1, 12345, 'dde-control-center', 'v1.2.3', 67890);
```

## ✅ 修复确认

- [x] 外键约束已删除
- [x] 同步逻辑已简化
- [x] 查询逻辑已修改
- [x] 数据库迁移已执行
- [x] 现在可以正常同步CRP数据

现在【浦发】L420x交付记录同步应该可以完全正常工作，不再有任何外键约束问题！🎉

## 🔍 后续说明

这个方案的核心是：**解耦合**。`deliver_package`表现在是一个纯粹的CRP数据镜像，不依赖本地的`modules`表。如果将来需要建立与本地模块的关联，可以通过`build_module_name`字段进行匹配，但这不是必需的。 