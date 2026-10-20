-- 为project_modules表添加构建相关字段
-- 迁移文件：003_add_build_fields_to_project_modules.sql

-- 检查字段是否已存在，如果不存在则添加
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_modules' 
     AND COLUMN_NAME = 'build_base_tag') = 0,
    'ALTER TABLE project_modules ADD COLUMN build_base_tag VARCHAR(100) NULL COMMENT "构建基础标签" AFTER project_version',
    'SELECT "build_base_tag字段已存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_modules' 
     AND COLUMN_NAME = 'build_cur_tag') = 0,
    'ALTER TABLE project_modules ADD COLUMN build_cur_tag VARCHAR(100) NULL COMMENT "构建当前标签" AFTER build_base_tag',
    'SELECT "build_cur_tag字段已存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_modules' 
     AND COLUMN_NAME = 'build_commit') = 0,
    'ALTER TABLE project_modules ADD COLUMN build_commit VARCHAR(100) NULL COMMENT "构建提交哈希" AFTER build_cur_tag',
    'SELECT "build_commit字段已存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_modules' 
     AND COLUMN_NAME = 'build_id') = 0,
    'ALTER TABLE project_modules ADD COLUMN build_id INT NULL COMMENT "构建ID" AFTER build_commit',
    'SELECT "build_id字段已存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_modules' 
     AND COLUMN_NAME = 'build_state') = 0,
    'ALTER TABLE project_modules ADD COLUMN build_state VARCHAR(50) NULL COMMENT "构建状态" AFTER build_id',
    'SELECT "build_state字段已存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_modules' 
     AND COLUMN_NAME = 'build_log') = 0,
    'ALTER TABLE project_modules ADD COLUMN build_log TEXT NULL COMMENT "构建日志" AFTER build_state',
    'SELECT "build_log字段已存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 