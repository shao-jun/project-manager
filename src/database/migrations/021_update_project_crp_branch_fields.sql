-- 021: 更新项目表的CRP分支字段
-- 1. 添加crp_branch_id字段来记录crp_branch表的id
-- 2. 重命名crp_branch为crp_branch_name用来记录分支名称

-- 添加新的crp_branch_id字段
ALTER TABLE projects ADD COLUMN crp_branch_id INT DEFAULT NULL COMMENT 'CRP分支ID' AFTER build_branch;

-- 重命名crp_branch为crp_branch_name
ALTER TABLE projects CHANGE COLUMN crp_branch crp_branch_name VARCHAR(255) DEFAULT NULL COMMENT 'CRP分支名称';

-- 添加外键约束（可选，如果需要强制引用完整性）
-- ALTER TABLE projects ADD CONSTRAINT fk_projects_crp_branch_id FOREIGN KEY (crp_branch_id) REFERENCES crp_branch(crp_id); 