-- 添加项目详情相关表
-- 迁移文件：005_add_project_detail_tables.sql

-- 项目目标表
CREATE TABLE IF NOT EXISTS project_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL COMMENT '项目ID',
    goal_name VARCHAR(200) NOT NULL COMMENT '目标名称',
    goal_description TEXT COMMENT '目标描述',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium' COMMENT '优先级',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending' COMMENT '状态',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目目标表';

-- 项目时间表
CREATE TABLE IF NOT EXISTS project_timeline (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL COMMENT '项目ID',
    start_date DATE COMMENT '开始日期',
    end_date DATE COMMENT '结束日期',
    milestone VARCHAR(200) COMMENT '里程碑',
    description TEXT COMMENT '时间线描述',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目时间表';

-- 交付记录表
CREATE TABLE IF NOT EXISTS delivery_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL COMMENT '项目ID',
    topic_name VARCHAR(200) NOT NULL COMMENT '主题名称',
    delivery_date DATE NOT NULL COMMENT '交付日期',
    workflow_status ENUM('pending', 'in_progress', 'completed', 'failed', 'cancelled') DEFAULT 'pending' COMMENT '工作流状态',
    repository_url TEXT COMMENT '仓库地址',
    description TEXT COMMENT '交付描述',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id),
    INDEX idx_delivery_date (delivery_date),
    INDEX idx_workflow_status (workflow_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='交付记录表';

-- 交付模块表
CREATE TABLE IF NOT EXISTS delivery_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    delivery_id INT NOT NULL COMMENT '交付记录ID',
    module_id INT NOT NULL COMMENT '模块ID',
    package_branch VARCHAR(100) COMMENT '打包分支',
    build_status ENUM('pending', 'building', 'success', 'failed', 'cancelled') DEFAULT 'pending' COMMENT '构建状态',
    package_url TEXT COMMENT '打包链接',
    build_log TEXT COMMENT '构建日志',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (delivery_id) REFERENCES delivery_records(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    INDEX idx_delivery (delivery_id),
    INDEX idx_module (module_id),
    INDEX idx_build_status (build_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='交付模块表';

-- 为projects表添加基础版本字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'base_version') = 0,
    'ALTER TABLE projects ADD COLUMN base_version VARCHAR(50) NULL COMMENT "基础版本" AFTER description',
    'SELECT "base_version字段已存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 为projects表添加集成仓库字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'integration_repo') = 0,
    'ALTER TABLE projects ADD COLUMN integration_repo TEXT NULL COMMENT "集成仓库地址" AFTER base_version',
    'SELECT "integration_repo字段已存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 为project_modules表添加build_module_name字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_modules' 
     AND COLUMN_NAME = 'build_module_name') = 0,
    'ALTER TABLE project_modules ADD COLUMN build_module_name VARCHAR(200) NULL COMMENT "构建模块名称" AFTER module_id',
    'SELECT "build_module_name字段已存在" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 