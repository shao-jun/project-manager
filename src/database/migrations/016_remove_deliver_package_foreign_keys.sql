-- 去掉deliver_package表的外键约束
-- 迁移文件：016_remove_deliver_package_foreign_keys.sql

-- 删除module_id的外键约束
SET @sql = (SELECT CONCAT('ALTER TABLE deliver_package DROP FOREIGN KEY ', CONSTRAINT_NAME)
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'deliver_package' 
            AND COLUMN_NAME = 'module_id' 
            AND REFERENCED_TABLE_NAME = 'modules'
            LIMIT 1);

-- 执行删除外键约束
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 修改module_id字段的注释，说明它现在存储CRP的ProjectID
ALTER TABLE deliver_package MODIFY COLUMN module_id INT NOT NULL COMMENT 'CRP项目ID(ProjectID)';

-- 验证外键约束已删除
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'deliver_package' 
AND REFERENCED_TABLE_NAME IS NOT NULL; 