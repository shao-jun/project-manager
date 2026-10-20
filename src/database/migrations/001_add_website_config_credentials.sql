-- 为已有的website_configs表添加用户名和密码字段
-- 执行此SQL文件来升级已有的数据库结构

-- 检查并添加用户名字段
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'website_configs' 
                   AND COLUMN_NAME = 'username');

SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE `website_configs` ADD COLUMN `username` varchar(100) NULL COMMENT ''用户名'' AFTER `token_url`;',
              'SELECT "Column username already exists" as message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加密码字段
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'website_configs' 
                   AND COLUMN_NAME = 'password');

SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE `website_configs` ADD COLUMN `password` varchar(255) NULL COMMENT ''密码'' AFTER `username`;',
              'SELECT "Column password already exists" as message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 检查并添加加密密码字段
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'website_configs' 
                   AND COLUMN_NAME = 'password_encrypted');

SET @sql = IF(@col_exists = 0, 
              'ALTER TABLE `website_configs` ADD COLUMN `password_encrypted` varchar(255) NULL COMMENT ''加密后的密码'' AFTER `password`;',
              'SELECT "Column password_encrypted already exists" as message;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 为现有记录设置默认的用户名和密码（如果需要）
UPDATE `website_configs` 
SET `username` = 'your-username', 
    `password` = '<在此填写密码哈希>', 
    `password_encrypted` = '<在此填写密码哈希>'
WHERE `username` IS NULL OR `username` = '';

-- 升级完成提示
SELECT 'Database upgrade completed successfully!' as message; 