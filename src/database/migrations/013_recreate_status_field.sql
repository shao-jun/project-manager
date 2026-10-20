-- 重新创建project_modules表的status字段
-- 将status字段改为三个选项：有更新，已更新，未知

-- 先备份现有数据
CREATE TEMPORARY TABLE temp_status AS SELECT id, status FROM project_modules;

-- 删除status字段
ALTER TABLE project_modules DROP COLUMN status;

-- 重新创建status字段
ALTER TABLE project_modules ADD COLUMN status enum('有更新','已更新','未知') DEFAULT '未知';

-- 恢复数据
UPDATE project_modules pm 
JOIN temp_status ts ON pm.id = ts.id 
SET pm.status = '未知' 
WHERE ts.status IN ('active', 'inactive');

-- 删除临时表
DROP TEMPORARY TABLE temp_status; 