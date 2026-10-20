-- 为交付记录表添加主题名称字段
-- 迁移文件：007_add_crp_name_to_deliver_record.sql

-- 为project_deliver_record表添加crp_name字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_deliver_record' 
     AND COLUMN_NAME = 'crp_name') = 0,
    'ALTER TABLE project_deliver_record ADD COLUMN crp_name VARCHAR(200) NULL COMMENT "主题名称" AFTER crp_id',
    'SELECT "crp_name字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 为deliver_crp表添加topic_name字段（如果需要）
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'deliver_crp' 
     AND COLUMN_NAME = 'topic_name') = 0,
    'ALTER TABLE deliver_crp ADD COLUMN topic_name VARCHAR(200) NULL COMMENT "主题名称" AFTER topicid',
    'SELECT "topic_name字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 