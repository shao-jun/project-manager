-- 为deliver_package表添加build_branch字段
ALTER TABLE deliver_package ADD COLUMN build_branch varchar(100) NULL COMMENT '构建分支名称'; 