-- 创建代码审查配置表
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

-- 插入默认配置
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
); 