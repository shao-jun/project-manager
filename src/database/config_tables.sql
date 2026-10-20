-- 系统配置相关数据库表

-- 用户信息表
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(100) NOT NULL COMMENT '用户名',
    `password` varchar(255) NOT NULL COMMENT '密码（加密存储）',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户信息表';

-- 网站配置表
CREATE TABLE IF NOT EXISTS `website_configs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL COMMENT '网站名称',
    `homepage` varchar(500) NOT NULL COMMENT '主页地址',
    `token` text COMMENT '访问令牌',
    `token_url` varchar(500) NOT NULL COMMENT '获取token的API地址',
    `username` varchar(100) NULL COMMENT '用户名',
    `password` varchar(255) NULL COMMENT '密码',
    `password_encrypted` varchar(255) NULL COMMENT '加密后的密码',
    `token_obtained_at` timestamp NULL COMMENT 'token获取时间',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='网站配置表';

-- 代码审查配置表
CREATE TABLE IF NOT EXISTS `code_review_configs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `auto_scoring_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '启用自动评分',
    `cherry_pick_score` int(11) NOT NULL DEFAULT 2 COMMENT 'Cherry Pick评分',
    `no_conflict_score` int(11) NOT NULL DEFAULT 2 COMMENT '无冲突评分',
    `scoring_trigger` enum('cherry_pick_and_no_conflict','cherry_pick_only','no_conflict_only') NOT NULL DEFAULT 'cherry_pick_and_no_conflict' COMMENT '评分触发条件',
    `required_file_types` text COMMENT '必须审查的文件类型',
    `skip_review_dirs` text COMMENT '跳过审查的目录',
    `min_reviewers` int(11) NOT NULL DEFAULT 2 COMMENT '最小审查人数',
    `force_review_large_files` tinyint(1) NOT NULL DEFAULT 1 COMMENT '强制审查大文件',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='代码审查配置表';

-- 插入默认的网站配置
INSERT INTO `website_configs` (`name`, `homepage`, `token_url`, `username`, `password`, `password_encrypted`) VALUES
('代码仓库', 'https://gerrit.example.com', 'https://gerrit.example.com/api/auth/token', 'your-username', '<在此填写密码哈希>', '<在此填写密码哈希>'),
('CRP', 'https://crp.example.com', 'https://crp.example.com/api/auth/token', 'your-username', '<在此填写密码哈希>', '<在此填写密码哈希>'),
('PMS', 'https://pms.example.com', 'https://pms.example.com/api/auth/token', 'your-username', '<在此填写密码哈希>', '<在此填写密码哈希>');

-- 插入默认的代码审查配置
INSERT INTO `code_review_configs` (`auto_scoring_enabled`, `cherry_pick_score`, `no_conflict_score`, `scoring_trigger`, `required_file_types`, `skip_review_dirs`, `min_reviewers`, `force_review_large_files`) VALUES
(1, 2, 2, 'cherry_pick_and_no_conflict', '.js,.php,.py,.java,.cpp,.c,.h,.hpp', 'node_modules,vendor,dist,build', 2, 1);

-- 插入默认用户（用于测试，实际使用时应该删除或修改）
INSERT INTO `users` (`username`, `password`) VALUES
('admin', '<在此填写密码哈希>'); -- 密码哈希请用 php -r "echo password_hash('你的密码', PASSWORD_DEFAULT);" 生成