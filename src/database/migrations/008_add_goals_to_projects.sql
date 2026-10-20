-- 为projects表添加goals字段，移除project_goals和project_timeline表
-- 迁移文件：008_add_goals_to_projects.sql

-- 1. 为projects表添加goals字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'goals') = 0,
    'ALTER TABLE projects ADD COLUMN goals TEXT NULL COMMENT "项目目标" AFTER description',
    'SELECT "goals字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. 删除不再需要的表（如果存在的话）
DROP TABLE IF EXISTS project_goals;
DROP TABLE IF EXISTS project_timeline;

-- 3. 验证修改
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'projects' 
AND COLUMN_NAME IN ('goals', 'description')
ORDER BY ORDINAL_POSITION; 