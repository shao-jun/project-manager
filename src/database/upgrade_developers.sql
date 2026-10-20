-- 数据库升级脚本 - 开发者配置功能
-- 运行此脚本来创建必要的数据库表

-- 1. 创建开发者用户表
CREATE TABLE IF NOT EXISTS `develope_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(100) NOT NULL COMMENT '用户名',
    `email` varchar(255) NOT NULL COMMENT '邮箱',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用：1-启用，0-禁用',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='开发者用户表';

-- 2. 创建代码审查配置表
CREATE TABLE IF NOT EXISTS `code_review_configs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `auto_scoring_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用自动评分：1-启用，0-禁用',
    `cherry_pick_score` int(11) NOT NULL DEFAULT 2 COMMENT 'Cherry-pick评分',
    `no_conflict_score` int(11) NOT NULL DEFAULT 2 COMMENT '无冲突评分',
    `scoring_trigger` varchar(100) NOT NULL DEFAULT 'cherry_pick_and_no_conflict' COMMENT '评分触发条件',
    `required_file_types` text COMMENT '必需的文件类型',
    `skip_review_dirs` text COMMENT '跳过审查的目录',
    `min_reviewers` int(11) NOT NULL DEFAULT 2 COMMENT '最少审查员数量',
    `force_review_large_files` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否强制审查大文件：1-是，0-否',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='代码审查配置表';

-- 3. 插入示例开发者数据
INSERT INTO `develope_users` (`username`, `email`, `status`) VALUES
('张三', 'zhangsan@example.com', 1),
('李四', 'lisi@example.com', 1),
('王五', 'wangwu@example.com', 0)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

-- 4. 插入默认代码审查配置
INSERT INTO `code_review_configs` (
    `auto_scoring_enabled`, 
    `cherry_pick_score`, 
    `no_conflict_score`, 
    `scoring_trigger`, 
    `required_file_types`, 
    `skip_review_dirs`, 
    `min_reviewers`, 
    `force_review_large_files`
) VALUES (
    1, 2, 2, 'cherry_pick_and_no_conflict', 
    '.js,.php,.py,.java,.cpp,.c,.h,.hpp', 
    'node_modules,vendor,dist,build', 
    2, 1
) ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- 5. 创建代码审查服务表
CREATE TABLE IF NOT EXISTS `code_review_services` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `service_id` varchar(100) NOT NULL COMMENT '服务唯一ID',
    `reviewer` varchar(100) NOT NULL COMMENT '审查员',
    `branch` varchar(100) NOT NULL COMMENT '审查分支',
    `threshold` int(11) NOT NULL DEFAULT 80 COMMENT '评分阈值',
    `status` enum('running','stopped','error') NOT NULL DEFAULT 'running' COMMENT '服务状态',
    `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '启动时间',
    `stopped_at` timestamp NULL DEFAULT NULL COMMENT '停止时间',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_service_id` (`service_id`),
    KEY `idx_status` (`status`),
    KEY `idx_started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='代码审查服务表';

-- 完成提示
SELECT '数据库升级完成！开发者配置功能已准备就绪。' AS message; 