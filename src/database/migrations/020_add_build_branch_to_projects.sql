-- 020: 为projects表添加build_branch字段
-- 用于存储项目的打包分支信息

ALTER TABLE projects ADD COLUMN build_branch VARCHAR(255) DEFAULT NULL COMMENT '打包分支' AFTER integration_repo; 