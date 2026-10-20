-- 在project_modules表中添加build_tag_info字段
-- 用于存储构建标签信息

ALTER TABLE `project_modules` 
ADD COLUMN `build_tag_info` text COMMENT '构建标签信息' AFTER `build_cur_tag`;