-- 删除project_modules表中不需要的构建字段
-- 只删除project_modules表中的build_module_name, build_log, build_state, build_base_tag字段

-- 删除project_modules表中的不需要字段
ALTER TABLE project_modules DROP COLUMN IF EXISTS build_module_name;
ALTER TABLE project_modules DROP COLUMN IF EXISTS build_log;
ALTER TABLE project_modules DROP COLUMN IF EXISTS build_state;
ALTER TABLE project_modules DROP COLUMN IF EXISTS build_base_tag; 