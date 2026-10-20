-- 019: 创建CRP分支表
-- 用于存储从CRP系统同步的分支信息

CREATE TABLE IF NOT EXISTS crp_branch (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '主键ID',
    crp_id INT NOT NULL COMMENT 'CRP系统中的分支ID',
    name VARCHAR(255) NOT NULL COMMENT '分支名称',
    description TEXT COMMENT '分支描述',
    repo_type VARCHAR(100) COMMENT '仓库类型',
    managers TEXT COMMENT '管理员列表',
    is_check TINYINT DEFAULT 0 COMMENT '是否检查标志',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    
    UNIQUE KEY uk_crp_id (crp_id) COMMENT 'CRP ID唯一索引',
    INDEX idx_name (name) COMMENT '分支名称索引',
    INDEX idx_is_check (is_check) COMMENT '检查标志索引'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRP分支信息表'; 