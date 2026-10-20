-- 创建代码审查服务表
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