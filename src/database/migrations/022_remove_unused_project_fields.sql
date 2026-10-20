-- 022: 删除项目表中不再使用的字段
-- 删除 base_version、integration_repo、build_branch 字段

ALTER TABLE projects DROP COLUMN base_version;
ALTER TABLE projects DROP COLUMN integration_repo;
ALTER TABLE projects DROP COLUMN build_branch; 