-- 添加Shuttle网站配置

-- 检查Shuttle配置是否已存在
INSERT INTO `website_configs` (`name`, `homepage`, `token_url`, `username`, `password`, `password_encrypted`, `is_active`)
SELECT * FROM (
    SELECT 'Shuttle' as name, 'https://shuttle.example.com' as homepage, 'https://shuttle.example.com/api/auth/token' as token_url, 'your-username' as username, '<在此填写密码哈希>' as password, '<在此填写密码哈希>' as password_encrypted, 1 as is_active
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM `website_configs` WHERE `name` = 'Shuttle'
);