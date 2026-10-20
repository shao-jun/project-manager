-- 023: 为project_deliver_record表添加交付日期和描述字段
-- 添加 delivery_date 和 description 字段

ALTER TABLE project_deliver_record ADD COLUMN delivery_date DATE DEFAULT NULL COMMENT '交付日期' AFTER crp_state;
ALTER TABLE project_deliver_record ADD COLUMN description TEXT DEFAULT NULL COMMENT '交付描述' AFTER delivery_date; 