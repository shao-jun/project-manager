-- 将projects表的crp_topicid字段重命名为build_arches
-- 迁移文件：024_rename_crp_topicid_to_build_arches.sql

-- 检查旧字段是否存在，新字段是否不存在，然后重命名
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'crp_topicid') = 1
    AND
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'build_arches') = 0,
    'ALTER TABLE projects CHANGE COLUMN crp_topicid build_arches TEXT NULL COMMENT "打包架构配置，多个架构用分号分隔"',
    'SELECT "字段重命名已完成或条件不满足" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;