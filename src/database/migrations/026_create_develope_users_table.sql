-- 创建开发者用户表
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

-- 插入一些示例数据
INSERT INTO `develope_users` (`username`, `email`, `status`) VALUES
('张三', 'zhangsan@example.com', 1),
('李四', 'lisi@example.com', 1),
('王五', 'wangwu@example.com', 0); 