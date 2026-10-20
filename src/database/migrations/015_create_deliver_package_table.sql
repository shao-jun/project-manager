-- 创建deliver_package表
-- 迁移文件：015_create_deliver_package_table.sql

-- 创建deliver_package表（如果不存在）
CREATE TABLE IF NOT EXISTS deliver_package (
    id INT AUTO_INCREMENT PRIMARY KEY,
    delivery_record_id INT NOT NULL COMMENT '交付记录ID',
    module_id INT NOT NULL COMMENT '模块ID',
    base_tag VARCHAR(100) NULL COMMENT '基础版本标签',
    cur_tag VARCHAR(100) NULL COMMENT '当前版本标签',
    build_id INT NULL COMMENT '构建ID',
    build_commit VARCHAR(100) NULL COMMENT '构建提交哈希',
    build_state VARCHAR(50) NULL COMMENT '构建状态',
    build_log TEXT NULL COMMENT '构建日志',
    build_module_name VARCHAR(200) NULL COMMENT '构建模块名称',
    build_branch VARCHAR(100) NULL COMMENT '构建分支名称',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (delivery_record_id) REFERENCES project_deliver_record(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    INDEX idx_delivery_record (delivery_record_id),
    INDEX idx_module (module_id),
    INDEX idx_build_id (build_id),
    INDEX idx_build_state (build_state),
    UNIQUE KEY uk_delivery_module (delivery_record_id, module_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='交付打包信息表';

-- 验证表创建
SELECT 
    TABLE_NAME,
    TABLE_COMMENT
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'deliver_package';

-- 验证外键约束
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'deliver_package' 
AND REFERENCED_TABLE_NAME IS NOT NULL; 