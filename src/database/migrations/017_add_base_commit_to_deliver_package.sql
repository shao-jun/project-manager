-- 为deliver_package表添加base_commit字段
-- 迁移文件：017_add_base_commit_to_deliver_package.sql

-- 为deliver_package表添加base_commit字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'deliver_package' 
     AND COLUMN_NAME = 'base_commit') = 0,
    'ALTER TABLE deliver_package ADD COLUMN base_commit VARCHAR(100) NULL COMMENT "基础提交哈希" AFTER base_tag',
    'SELECT "base_commit字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 为新字段添加索引（可选，用于查询优化）
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'deliver_package' 
     AND INDEX_NAME = 'idx_base_commit') = 0,
    'ALTER TABLE deliver_package ADD INDEX idx_base_commit (base_commit)',
    'SELECT "idx_base_commit索引已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 验证字段添加
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'deliver_package' 
AND COLUMN_NAME = 'base_commit';

-- 验证索引添加
SELECT 
    INDEX_NAME,
    COLUMN_NAME,
    NON_UNIQUE
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'deliver_package' 
AND INDEX_NAME = 'idx_base_commit'; 