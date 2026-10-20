# CRP主题驳回功能说明

## 功能概述
在项目详情页面的交付记录中，当CRP主题处于"已锁定"状态时，显示"驳回主题"按钮，允许用户驳回该主题。

## 实现细节

### 前端部分 (project-detail.php)

#### 1. 驳回按钮显示
- 位置：交付记录的操作按钮区域
- 显示条件：`delivery.crp_locked === "1"` (主题已锁定)
- 样式：红色按钮，与"提测信息"按钮保持8px间距
- 按钮文本：❌ 驳回主题

#### 2. 驳回函数 `rejectCrpTopic(deliveryId, topicId)`
功能流程：
1. 弹出输入框，要求用户输入驳回原因
2. 验证驳回原因不为空
3. 调用后端API `/api/projects?action=crp_reject`
4. 成功后刷新交付记录列表和模块列表
5. 使用 `notify()` 显示操作结果

### 后端部分 (projects.php)

#### API接口：`action=crp_reject`

**请求参数：**
- GET参数：
  - `delivery_id`: 交付记录ID
  - `topic_id`: CRP主题ID
- POST参数（JSON）：
  - `msg`: 驳回原因

**处理流程：**

1. **调用封装的CRP API函数**
   - 函数：`rejectCrpTopic($topicId, $rejectMsg)`
   - 位置：`src/api/crp-api.php`
   - 内部流程：
     a. 调用 `makeCrpApiRequest()` 获取workflow信息
        - 接口：`GET https://crp.example.com/api/topics/{topicId}/workflow/info`
        - 提取：`ID` 字段（workflow_id）
     b. 调用 `makeCrpApiRequest()` 执行驳回操作
        - 接口：`POST https://crp.example.com/api/topics/workflow/reject`
        - 请求体：`{"id": workflow_id, "topicid": topic_id, "msg": reject_reason}`
     c. 验证返回状态为 `success`

2. **更新数据库**
   - 将 `project_deliver_record` 表中的 `crp_locked` 字段设置为 0
   - SQL: `UPDATE project_deliver_record SET crp_locked = 0 WHERE id = ?`

**返回结果：**
```json
{
  "success": true,
  "message": "主题驳回成功",
  "data": {
    "workflow_id": <workflow_id>,
    "topic_id": <topic_id>
  }
}
```

## 使用流程

1. 用户在项目详情页面查看交付记录
2. 对于已锁定的主题，点击"❌ 驳回主题"按钮
3. 在弹出的输入框中输入驳回原因
4. 系统调用CRP API完成驳回操作
5. 驳回成功后：
   - 数据库中的锁定状态更新为"未锁定"
   - 页面自动刷新显示最新状态
   - 显示成功通知

## 错误处理

- 驳回原因为空：显示警告通知
- 获取workflow信息失败：返回错误信息
- 驳回API调用失败：返回HTTP状态码和错误信息
- 网络错误：显示网络连接错误通知

## 依赖

- CRP API函数库：`src/api/crp-api.php`
  - `rejectCrpTopic($topicId, $rejectMsg)` - 驳回主题的封装函数
  - `makeCrpApiRequest()` - 通用的CRP API请求函数（支持自动token刷新）
- 通知系统：`src/assets/js/notification.js`
- 数据库表：`project_deliver_record`

## 注意事项

1. 驳回操作需要CRP API的有效Token（通过 `makeCrpApiRequest()` 自动管理）
2. 驳回后主题状态会变为未锁定，但不会删除主题
3. 驳回原因会记录在CRP系统中
4. 操作成功后会自动刷新页面数据
5. CRP API调用已封装在 `crp-api.php` 中，支持自动token刷新和重试机制

## 代码优化

### CRP API封装 (crp-api.php)

新增函数 `rejectCrpTopic($topicId, $rejectMsg)`：
- 统一的错误处理
- 自动token管理（通过 `makeCrpApiRequest()`）
- 返回标准化的结果格式
- 支持自动重试机制

优势：
- 代码复用性高
- 易于维护和测试
- 统一的API调用方式
- 自动处理token过期问题
