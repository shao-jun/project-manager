-- 移除project_modules表的module_id外键约束
-- 迁移文件：004_remove_module_fk_constraint.sql

-- 检查外键约束是否存在
SET @constraint_name = (SELECT CONSTRAINT_NAME 
                       FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                       WHERE TABLE_SCHEMA = DATABASE() 
                       AND TABLE_NAME = 'project_modules' 
                       AND COLUMN_NAME = 'module_id' 
                       AND REFERENCED_TABLE_NAME = 'modules');

-- 如果外键约束存在，则移除
SET @sql = (SELECT IF(
    @constraint_name IS NOT NULL,
    CONCAT('ALTER TABLE project_modules DROP FOREIGN KEY ', @constraint_name),
    'SELECT "外键约束不存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 