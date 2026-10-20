-- 创建deliver_pms表
-- 迁移文件：018_create_deliver_pms_table.sql

-- 创建deliver_pms表
CREATE TABLE IF NOT EXISTS deliver_pms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    delivery_record_id INT NOT NULL COMMENT '交付记录ID',
    module_id INT NOT NULL COMMENT '模块ID',
    commit_hash VARCHAR(100) NOT NULL COMMENT '提交哈希',
    pms_url VARCHAR(500) NULL COMMENT 'PMS链接(bug/task)',
    influence TEXT NULL COMMENT '影响范围',
    author VARCHAR(100) NULL COMMENT '作者',
    message TEXT NULL COMMENT '提交信息',
    remark_status TINYINT(1) DEFAULT 0 COMMENT '备注状态：0未备注，1已备注',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    
    INDEX idx_delivery_record (delivery_record_id),
    INDEX idx_module (module_id),
    INDEX idx_commit_hash (commit_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='交付PMS记录表';

-- 验证表是否创建成功
SELECT 'deliver_pms表创建完成' as message;

-- 显示表结构
DESC deliver_pms; 