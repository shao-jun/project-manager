-- 项目详情数据库改造
-- 迁移文件：006_restructure_project_tables.sql

-- 1. 备份现有数据（可选）
-- CREATE TABLE projects_backup AS SELECT * FROM projects;
-- CREATE TABLE project_modules_backup AS SELECT * FROM project_modules;
-- CREATE TABLE delivery_records_backup AS SELECT * FROM delivery_records;

-- 2. 删除旧表（如果存在）
DROP TABLE IF EXISTS delivery_modules;
DROP TABLE IF EXISTS delivery_records;
DROP TABLE IF EXISTS project_goals;
DROP TABLE IF EXISTS project_timeline;

-- 3. 修改项目表结构
-- 添加新字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'start_date') = 0,
    'ALTER TABLE projects ADD COLUMN start_date DATE NULL COMMENT "开始时间" AFTER status',
    'SELECT "start_date字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'stop_date') = 0,
    'ALTER TABLE projects ADD COLUMN stop_date DATE NULL COMMENT "结束时间" AFTER start_date',
    'SELECT "stop_date字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'system_version') = 0,
    'ALTER TABLE projects ADD COLUMN system_version VARCHAR(50) NULL COMMENT "系统版本" AFTER stop_date',
    'SELECT "system_version字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. 修改项目模块表结构
-- 重命名字段
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_modules' 
     AND COLUMN_NAME = 'project_branch') > 0,
    'ALTER TABLE project_modules CHANGE COLUMN project_branch module_branch VARCHAR(100) COMMENT "模块分支"',
    'SELECT "project_branch字段不存在，无需重命名" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_modules' 
     AND COLUMN_NAME = 'project_version') > 0,
    'ALTER TABLE project_modules CHANGE COLUMN project_version release_version VARCHAR(50) COMMENT "集成版本"',
    'SELECT "project_version字段不存在，无需重命名" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. 创建新的交付记录表
CREATE TABLE IF NOT EXISTS project_deliver_record (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL COMMENT '项目ID',
    deliver_id VARCHAR(100) NOT NULL COMMENT '交付ID',
    crp_id VARCHAR(100) NULL COMMENT '主题ID',
    pms_id VARCHAR(100) NULL COMMENT '工单ID',
    repo_addr TEXT NULL COMMENT '仓库地址',
    crp_addr TEXT NULL COMMENT '主题地址',
    crp_state VARCHAR(50) NULL COMMENT '主题状态',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project_id (project_id),
    INDEX idx_deliver_id (deliver_id),
    INDEX idx_crp_id (crp_id),
    INDEX idx_pms_id (pms_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目交付记录表';

-- 6. 创建交付主题表
CREATE TABLE IF NOT EXISTS deliver_crp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deliver_id VARCHAR(100) NOT NULL COMMENT '交付ID',
    topicid VARCHAR(100) NOT NULL COMMENT 'CRP主题ID',
    module_id INT NOT NULL COMMENT '模块ID',
    base_tag VARCHAR(100) NULL COMMENT '基础版本',
    cur_tag VARCHAR(100) NULL COMMENT '当前版本',
    build_id INT NULL COMMENT '编译ID',
    build_commit VARCHAR(100) NULL COMMENT '编译提交',
    build_state VARCHAR(50) NULL COMMENT '编译状态',
    build_log TEXT NULL COMMENT '编译日志',
    build_module_name VARCHAR(200) NULL COMMENT '模块名称',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    INDEX idx_deliver_id (deliver_id),
    INDEX idx_topicid (topicid),
    INDEX idx_module_id (module_id),
    INDEX idx_build_id (build_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='交付主题表';

-- 7. 创建交付工单表
CREATE TABLE IF NOT EXISTS deliver_pms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deliver_id VARCHAR(100) NOT NULL COMMENT '交付ID',
    pms_url TEXT NULL COMMENT '工单链接',
    type VARCHAR(50) NULL COMMENT '类型',
    level VARCHAR(20) NULL COMMENT '级别',
    influence TEXT NULL COMMENT '影响范围',
    author VARCHAR(100) NULL COMMENT '解决者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_deliver_id (deliver_id),
    INDEX idx_type (type),
    INDEX idx_level (level),
    INDEX idx_author (author)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='交付工单表';

-- 8. 数据迁移（从旧表迁移到新表）
-- 迁移交付记录数据（如果有的话）
INSERT INTO project_deliver_record (project_id, deliver_id, repo_addr, crp_state, created_at, updated_at)
SELECT 
    project_id,
    CONCAT('DELIVER_', id) as deliver_id,
    repository_url as repo_addr,
    workflow_status as crp_state,
    created_at,
    updated_at
FROM delivery_records 
WHERE EXISTS (SELECT 1 FROM delivery_records LIMIT 1);

-- 9. 清理旧表（可选，建议先备份）
-- DROP TABLE IF EXISTS delivery_records_backup;
-- DROP TABLE IF EXISTS delivery_modules_backup;
-- DROP TABLE IF EXISTS project_goals_backup;
-- DROP TABLE IF EXISTS project_timeline_backup;

-- 10. 添加约束和索引优化
-- 为项目模块表添加状态字段（如果不存在）
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'project_modules' 
     AND COLUMN_NAME = 'status') = 0,
    'ALTER TABLE project_modules ADD COLUMN status ENUM("active", "inactive") DEFAULT "active" COMMENT "状态" AFTER release_version',
    'SELECT "status字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 为项目表保留必要的字段（如果不存在）
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'category_id') = 0,
    'ALTER TABLE projects ADD COLUMN category_id INT NOT NULL COMMENT "技术分类ID" AFTER name',
    'SELECT "category_id字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'crp_topicid') = 0,
    'ALTER TABLE projects ADD COLUMN crp_topicid VARCHAR(100) NULL COMMENT "CRP仓库ID" AFTER system_version',
    'SELECT "crp_topicid字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'projects' 
     AND COLUMN_NAME = 'integration_repo') = 0,
    'ALTER TABLE projects ADD COLUMN integration_repo TEXT NULL COMMENT "集成仓库地址" AFTER crp_topicid',
    'SELECT "integration_repo字段已存在" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 