-- 为projects表添加crp_topicid字段
-- 迁移文件：002_add_crp_topicid_to_projects.sql

-- 检查字段是否已存在，如果不存在则添加
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'crp_topicid') = 0,
    'ALTER TABLE projects ADD COLUMN crp_topicid VARCHAR(100) NULL COMMENT "CRP仓库ID" AFTER status',
    'SELECT "crp_topicid字段已存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 