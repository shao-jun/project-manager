<?php
// 监控面板页面
require_once __DIR__ . '/../lib/endpoints.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>监控面板 - 研发自动化管理平台</title>
    <script>window.APP_ENDPOINTS = <?php echo app_endpoints_json(); ?>;</script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .nav-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a67d8;
        }
        
        .btn.active {
            background: #4c51bf;
        }
        
        .btn-primary {
            background: #007bff;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .main-content {
            display: flex;
            gap: 20px;
            height: calc(100vh - 200px);
        }
        
        .stats-panel {
            width: 400px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .monitor-panel {
            flex: 1;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .tab-container {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 12px 24px;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            color: #666;
            transition: all 0.3s;
        }

        .tab-btn:hover {
            color: #333;
            background: #f5f5f5;
        }

        .tab-btn.active {
            color: #007bff;
            border-bottom-color: #007bff;
        }

        .tab-content {
            display: none;
            flex: 1;
            overflow: hidden;
            flex-direction: column;
        }

        .tab-content.active {
            display: flex;
        }

        .tab-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-shrink: 0;
        }

        .tab-title {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
        }

        .refresh-config {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .refresh-config label {
            font-size: 0.85em;
            color: #666;
        }

        .refresh-config input {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 60px;
            text-align: center;
        }

        .refresh-config input:focus {
            outline: none;
            border-color: #007bff;
        }

        .refresh-status {
            font-size: 0.75em;
            color: #999;
            margin-left: 5px;
        }

        .refresh-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #28a745;
            margin-right: 5px;
            animation: pulse 2s infinite;
        }

        .refresh-indicator.refreshing {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .task-queue-section {
            flex: 1;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #fafafa;
            padding: 10px;
        }

        .task-queue-item {
            padding: 10px 12px;
            margin-bottom: 8px;
            background: #fff4e6;
            border-radius: 6px;
            border-left: 4px solid #0aa83aff;
            font-size: 0.85em;
        }

        .task-queue-item.processing {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }

        .task-queue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .task-queue-actions {
            display: flex;
            gap: 5px;
        }

        .task-queue-actions button {
            padding: 3px 8px;
            font-size: 0.85em;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .task-queue-actions .abandon-btn {
            background: #dc3545;
            color: white;
        }

        .task-queue-actions .abandon-btn:hover {
            background: #c82333;
        }

        .task-queue-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7em;
            font-weight: bold;
            color: white;
            min-width: 50px;
            text-align: center;
        }

        .task-queue-status.queued {
            background: #ff9800;
        }

        .task-queue-status.processing {
            background: #2196f3;
        }

        .task-queue-status.completed {
            background: white;
            border: 1px solid #28a745;
            color: #28a745;
        }

        .task-queue-status.abandoned {
            background: #9e9e9e;
        }

        .task-queue-info {
            color: #666;
            font-size: 0.75em;
        }

        .task-queue-empty {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 0.85em;
        }

        .panel-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        
        .stat-card.success {
            border-left-color: #28a745;
        }
        
        .stat-card.warning {
            border-left-color: #ffc107;
        }
        
        .stat-card.danger {
            border-left-color: #dc3545;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9em;
        }
        
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            padding: 12px 15px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid transparent;
            min-height: 80px;
            display: flex;
            align-items: center;
        }
        
        .activity-item.info {
            border-left-color: #17a2b8;
        }
        
        .activity-item.success {
            border-left-color: #28a745;
        }
        
        .activity-item.warning {
            border-left-color: #ffc107;
        }
        
        .activity-item.danger {
            border-left-color: #dc3545;
        }
        
        .activity-time {
            color: #999;
            font-size: 0.8em;
            margin-bottom: 5px;
        }
        
        .activity-content {
            color: #333;
            font-size: 0.9em;
        }
        
        .chart-container {
            width: 100%;
            height: 300px;
            margin-bottom: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
        }
        
        .system-status {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .status-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .status-card.warning {
            border-left-color: #ffc107;
        }
        
        .status-card.danger {
            border-left-color: #dc3545;
        }
        
        .status-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .status-value {
            color: #666;
            font-size: 0.9em;
        }
        
        .global-refresh-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        
        .global-refresh-btn:hover {
            background: #0056b3;
            transform: scale(1.1);
        }
        
        .global-refresh-btn.spinning {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
        }
        
        .activity-list-container {
            flex: 1;
            overflow-y: auto;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .pagination button {
            padding: 8px 16px;
            border: 1px solid #007bff;
            background: white;
            color: #007bff;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .pagination button:disabled {
            border-color: #ccc;
            color: #ccc;
            cursor: not-allowed;
        }
        
        .pagination button:hover:not(:disabled) {
            background: #007bff;
            color: white;
        }
        
        .activity-item.package-item {
            border-left-color: #17a2b8;
        }
        
        .activity-item.project-item {
            border-left-color: #6f42c1;
        }
        
        .activity-item.delivery-item {
            border-left-color: #28a745;
        }
        
        .activity-row {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
        }
        
        .activity-type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7em;
            font-weight: bold;
            color: white;
            min-width: 50px;
            text-align: center;
        }
        
        .activity-type-badge.project {
            background: #6f42c1;
        }
        
        .activity-type-badge.delivery {
            background: #28a745;
        }
        
        .activity-type-badge.package {
            background: #17a2b8;
        }
        
        .activity-name {
            font-weight: 600;
            font-size: 0.95em;
            color: #333;
            min-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .activity-row-main {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            overflow: hidden;
        }
        
        .activity-fields {
            display: flex;
            flex: 1;
            gap: 12px;
            font-size: 0.8em;
            flex-wrap: wrap;
        }
        
        .activity-field {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .activity-field-label {
            color: #888;
            white-space: nowrap;
        }
        
        .activity-field-value {
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .activity-field-value a {
            color: #007bff;
            text-decoration: none;
        }
        
        .activity-field-value a:hover {
            text-decoration: underline;
        }
        
        .activity-time {
            color: #999;
            font-size: 0.75em;
            white-space: nowrap;
            min-width: 140px;
            text-align: right;
        }
        
        .activity-actions {
            display: flex;
            gap: 6px;
            min-width: 100px;
            justify-content: flex-end;
        }
        
        .activity-actions button {
            padding: 4px 8px;
            font-size: 0.75em;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .activity-actions .refresh-btn {
            background: #007bff;
            color: white;
        }
        
        .activity-actions .refresh-btn:hover {
            background: #0056b3;
        }
        
        .activity-actions button.view-btn {
            background: #28a745;
            color: white;
        }
        
        .activity-actions button.view-btn:hover {
            background: #1e7e34;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 监控面板</h1>
            <p>实时监控系统状态、项目进展和代码审查情况</p>
        </div>
        
        <div class="nav-bar">
            <a href="/" class="btn active">📊 监控面板</a>
            <a href="/tech-categories" class="btn">🔧 技术分类</a>
            <a href="/code-review" class="btn">📋 代码审查</a>
            <a href="/projects" class="btn">📱 项目管理</a>
            <a href="/config" class="btn">⚙️ 系统配置</a>
        </div>
        
        <div class="main-content">
            <div class="stats-panel">
                <div class="panel-header">
                    <div class="panel-title">系统状态</div>
                    <button class="btn btn-primary" onclick="openLogs()">📋 查看日志</button>
                </div>

                <div class="system-status">

                </div>

                <div class="panel-header">
                    <div class="panel-title">最近活动</div>
                </div>

                <ul class="activity-list" id="activityList">
                </ul>
            </div>
            
            <div class="monitor-panel">
                <!-- Tab 切换按钮 -->
                <div class="tab-container">
                    <button class="tab-btn active" onclick="switchTab('taskQueue')">任务队列</button>
                    <button class="tab-btn" onclick="switchTab('recentActivity')">最近动态</button>
                </div>

                <!-- 任务队列 Tab -->
                <div id="taskQueueTab" class="tab-content active">
                    <div class="tab-header">
                        <div class="tab-title">任务列表</div>
                        <div class="refresh-config">
                            <label>刷新间隔(分钟):</label>
                            <input type="number" id="refreshIntervalInput" value="3" min="1" max="30" onchange="updateRefreshInterval()">
                            <span class="refresh-status" id="refreshStatus"><span class="refresh-indicator"></span>(每10分钟刷新)</span>
                        </div>
                    </div>

                    <div class="task-queue-section">
                        <div id="taskQueueList">
                            <div class="task-queue-empty">加载中...</div>
                        </div>
                    </div>
                </div>

                <!-- 最近动态 Tab -->
                <div id="recentActivityTab" class="tab-content">
                    <div class="tab-header">
                        <div class="tab-title">动态列表</div>
                    </div>

                    <div class="activity-list-container">
                        <ul class="activity-list" id="recentActivityList">
                        </ul>
                    </div>

                    <div class="pagination">
                        <button id="prevPage" onclick="changePage(-1)" disabled>上一页</button>
                        <span id="pageInfo">第 1 页</span>
                        <button id="nextPage" onclick="changePage(1)">下一页</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
let refreshInterval;
let taskRefreshTimer;
const defaultRefreshInterval = 10;

// 当前激活的 tab
let currentTab = 'taskQueue';

// Tab 切换功能
function switchTab(tabName) {
    currentTab = tabName;

    // 更新按钮状态
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');

    // 更新内容显示
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });

    if (tabName === 'taskQueue') {
        document.getElementById('taskQueueTab').classList.add('active');
    } else if (tabName === 'recentActivity') {
        document.getElementById('recentActivityTab').classList.add('active');
        // 切换到最近动态时刷新数据
        loadRecentActivities(currentPage);
    }
}

// 打开日志
async function openLogs() {
    try {
        const response = await fetch('/api/open-terminal');
        const result = await response.json();
        
        if (!result.success) {
            alert('打开终端失败: ' + result.message);
        }
    } catch (error) {
        console.error('打开日志失败:', error);
        alert('打开日志失败');
    }
}

// 最近动态相关变量
let currentPage = 1;
let totalItems = 0;
const itemsPerPage = 6;

// 加载最近动态数据
async function loadRecentActivities(page = 1) {
    try {
        const response = await fetch(`/api/projects?action=recent_activities&page=${page}&limit=${itemsPerPage}`);
        const result = await response.json();
        
        if (result.success) {
            const activities = result.data;
            currentPage = result.page;
            totalItems = result.total;
            
            renderRecentActivities(activities);
            updatePagination();
        }
    } catch (error) {
        console.error('加载最近动态失败:', error);
    }
}

// 渲染最近动态列表
function renderRecentActivities(activities) {
    const list = document.getElementById('recentActivityList');
    
    if (activities.length === 0) {
        list.innerHTML = '<li class="empty-state"><h3>暂无动态</h3></li>';
        return;
    }
    
    list.innerHTML = activities.map(activity => {
        const date = new Date(activity.created_at);
        const timeStr = date.toLocaleString('zh-CN');
        
        if (activity.type === 'project') {
            return `
                <li class="activity-item project-item" onclick="openProjectDetail(${activity.project_id})">
                    <div class="activity-row">
                        <span class="activity-type-badge project">项目</span>
                        <span class="activity-name" title="${activity.name}">${activity.name}</span>
                        <div class="activity-row-main">
                            <div class="activity-fields">
                                ${activity.project_type ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">分类:</span>
                                    <span class="activity-field-value">${activity.project_type}</span>
                                </div>` : ''}
                                ${activity.pm ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">经理:</span>
                                    <span class="activity-field-value">${activity.pm}</span>
                                </div>` : ''}
                            </div>
                            <div class="activity-fields">
                                ${activity.crp_branch_name ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">分支:</span>
                                    <span class="activity-field-value">${activity.crp_branch_name}</span>
                                </div>` : ''}
                            </div>
                        </div>
                        <span class="activity-time">${timeStr}</span>
                    </div>
                </li>
            `;
        } else if (activity.type === 'delivery') {
            return `
                <li class="activity-item delivery-item" onclick="openProjectDetail(${activity.project_id})">
                    <div class="activity-row">
                        <span class="activity-type-badge delivery">交付</span>
                        <span class="activity-name" title="${activity.name || '未命名'}">${activity.name || '未命名'}</span>
                        <div class="activity-row-main">
                            <div class="activity-fields">
                                ${activity.crp_id ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">主题ID:</span>
                                    <span class="activity-field-value">${activity.crp_id}</span>
                                </div>` : ''}
                                ${activity.crp_addr ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">主题地址:</span>
                                    <span class="activity-field-value"><a href="${activity.crp_addr}" target="_blank" onclick="event.stopPropagation()">查看</a></span>
                                </div>` : ''}
                            </div>
                            <div class="activity-fields">
                                ${activity.repo_addr ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">仓库地址:</span>
                                    <span class="activity-field-value"><a href="${activity.repo_addr}" target="_blank" onclick="event.stopPropagation()">查看</a></span>
                                </div>` : ''}
                            </div>
                        </div>
                        <span class="activity-time">${timeStr}</span>
                    </div>
                </li>
            `;
        } else if (activity.type === 'package') {
            return `
                <li class="activity-item package-item">
                    <div class="activity-row">
                        <span class="activity-type-badge package">包</span>
                        <span class="activity-name" title="${activity.module_name || '未知模块'}">${activity.module_name || '未知模块'}</span>
                        <div class="activity-row-main">
                            <div class="activity-fields">
                                ${activity.project_name ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">项目:</span>
                                    <span class="activity-field-value">${activity.project_name}</span>
                                </div>` : ''}
                                ${activity.topic_id ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">主题ID:</span>
                                    <span class="activity-field-value">${activity.topic_id}</span>
                                </div>` : ''}
                                ${activity.topic_name ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">主题名称:</span>
                                    <span class="activity-field-value">${activity.topic_name}</span>
                                </div>` : ''}
                            </div>
                            <div class="activity-fields">
                                ${activity.build_branch ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">分支:</span>
                                    <span class="activity-field-value">${activity.build_branch}</span>
                                </div>` : ''}
                                ${activity.build_version ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">版本:</span>
                                    <span class="activity-field-value">${activity.build_version}</span>
                                </div>` : ''}
                                ${activity.build_commit ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">提交:</span>
                                    <span class="activity-field-value">${activity.build_commit.substring(0, 10)}</span>
                                </div>` : ''}
                                ${activity.build_state ? `
                                <div class="activity-field">
                                    <span class="activity-field-label">状态:</span>
                                    <span class="activity-field-value">${activity.build_state}</span>
                                </div>` : ''}
                            </div>
                        </div>
                        <span class="activity-time">${timeStr}</span>
                        <div class="activity-actions">
                            <button class="refresh-btn" onclick="event.stopPropagation(); refreshBuildStatus(${activity.item_id}, ${activity.build_itemid})">刷新</button>
                            ${activity.build_id ? `<button class="view-btn" onclick="event.stopPropagation(); openBuildRecord('${activity.build_id}')">查看</button>` : ''}
                        </div>
                    </div>
                </li>
            `;
        }
    }).join('');
}

// 更新分页信息
function updatePagination() {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    document.getElementById('pageInfo').textContent = `第 ${currentPage} 页 / 共 ${totalPages} 页`;
    
    document.getElementById('prevPage').disabled = currentPage <= 1;
    document.getElementById('nextPage').disabled = currentPage >= totalPages;
}

// 切换页码
function changePage(delta) {
    const newPage = currentPage + delta;
    if (newPage >= 1) {
        loadRecentActivities(newPage);
    }
}

// 打开项目详情
function openProjectDetail(projectId) {
    window.open(`/project-detail?id=${projectId}`, '_blank');
}

// 打开打包记录
function openBuildRecord(buildItemId) {
    window.open(`${window.APP_ENDPOINTS.shuttle}/#/tasks/task?taskid=${buildItemId}`, '_blank');
}

// 显示通知
function notify(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 24px;
        border-radius: 8px;
        color: white;
        font-size: 14px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// 刷新构建状态和相关信息
async function refreshBuildStatus(packageId, buildItemid) {
    if (!buildItemid) {
        notify('刷新失败：缺少必要参数', 'error');
        return;
    }
    
    try {
        const response = await fetch(`/api/projects?action=item_detail&build_itemid=${buildItemid}&packageId=${packageId}`);
        const result = await response.json();
        
        if (result.success) {
            // 刷新成功后重新加载当前页
            loadRecentActivities(currentPage);
            notify(`构建信息已更新: ${result.data.build_state || '状态更新'}`, 'success');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('刷新构建信息失败：', error);
        notify('刷新构建信息失败：' + error.message, 'error');
    }
}

// === 任务队列相关功能 ===

// 更新刷新间隔
function updateRefreshInterval() {
    const input = document.getElementById('refreshIntervalInput');
    let seconds = parseInt(input.value);

    if (isNaN(seconds) || seconds < 3) {
        seconds = 3;
        input.value = 3;
    } else if (seconds > 300) {
        seconds = 300;
        input.value = 300;
    }

    // 停止当前定时器
    if (taskRefreshTimer) {
        clearInterval(taskRefreshTimer);
    }

    // 重新设置定时器
    startTaskQueueRefresh(seconds);
}

// 启动任务队列刷新
function startTaskQueueRefresh(interval = defaultRefreshInterval) {
    if (taskRefreshTimer) {
        clearInterval(taskRefreshTimer);
    }

    loadTaskQueue();
    taskRefreshTimer = setInterval(loadTaskQueue, interval * 1000 * 60);
    updateRefreshStatus(interval);
}

// 更新刷新状态显示
function updateRefreshStatus(interval) {
    const statusEl = document.getElementById('refreshStatus');
    if (statusEl) {
        statusEl.innerHTML = `<span class="refresh-indicator"></span>(每${interval}分钟刷新)`;
    }
}

// 加载任务队列数据
async function loadTaskQueue() {
    console.log(`[${new Date().toLocaleString('zh-CN')}] 定时器触发: 刷新任务队列`);

    const queueList = document.getElementById('taskQueueList');

    try {
        // 先获取任务队列
        const response = await fetch('/api/projects?action=task_queue&update_status=0');
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || '获取任务队列失败');
        }

        const tasks = result.data || [];

        // 刷新所有处理中任务的构建状态
        const processingTasks = tasks.filter(t => t.task_status == 1 && t.build_itemid);
        if (processingTasks.length > 0) {

            console.log(`[${new Date().toLocaleString('zh-CN')}] 刷新构建状态`);

            await Promise.all(processingTasks.map(task =>
                fetch(`/api/projects?action=item_detail&build_itemid=${task.build_itemid}&packageId=${task.deliver_package_id}`)
                    .catch(e => console.log('刷新构建状态失败:', e))
            ));

            console.log(`[${new Date().toLocaleString('zh-CN')}] 重新加载任务列表`);
            // 刷新完成后重新获取任务队列
            const refreshResponse = await fetch('/api/projects?action=task_queue&update_status=1');
            const refreshResult = await refreshResponse.json();
            if (refreshResult.success) {
                renderTaskQueue(refreshResult.data || []);
                return;
            }
        }

        renderTaskQueue(tasks);

    } catch (error) {
        console.error('加载任务队列失败:', error);
        queueList.innerHTML = '<div class="task-queue-empty">加载失败: ' + escapeHtml(error.message) + '</div>';
    }
}

// 渲染任务队列
function renderTaskQueue(tasks) {
    const queueList = document.getElementById('taskQueueList');

    if (tasks.length === 0) {
        queueList.innerHTML = '<div class="task-queue-empty">暂无任务</div>';
        return;
    }

    queueList.innerHTML = tasks.map(task => {
        // 状态: 0排队, 1进行, 2完成, 3放弃
        const statusMap = {
            0: { text: '排队中', class: '', label: 'queued' },
            1: { text: '进行中', class: 'processing', label: 'processing' },
            2: { text: '已完成', class: '', label: 'completed' },
            3: { text: '已放弃', class: '', label: 'abandoned' }
        };
        const status = statusMap[task.task_status] || statusMap[0];
        const statusClass = status.class;
        const statusText = status.text;
        const statusLabel = status.label;

        let timeInfo = '';
        let buildStateInfo = '';

        if (task.task_status == 1) {
            // 进行中：显示已处理时长
            const startTime = new Date(task.queue_time || task.start_time);
            const elapsed = Math.floor((Date.now() - startTime.getTime()) / 1000);
            timeInfo = `<div class="task-queue-info">已处理: ${formatDuration(elapsed)}</div>`;
        } else if (task.task_status == 0) {
            // 排队中：显示等待时长
            const queueTime = new Date(task.queue_time);
            const elapsed = Math.floor((Date.now() - queueTime.getTime()) / 1000);
            timeInfo = `<div class="task-queue-info">等待时长: ${formatDuration(elapsed)}</div>`;
        }

        if (task.build_state) {
            buildStateInfo = `<div class="task-queue-info">构建状态: ${escapeHtml(task.build_state)}</div>`;
        }

        // 解析任务名称
        let taskName = '';
        if (task.module_name && task.project_name) {
            taskName = `${escapeHtml(task.project_name)} - ${escapeHtml(task.module_name)}`;
            if (task.deliver_name) {
                taskName += ` (${escapeHtml(task.deliver_name)})`;
            }
        } else {
            taskName = `任务 #${escapeHtml(task.task_id)}`;
        }

        return `
            <div class="task-queue-item ${statusClass}">
                <div class="task-queue-header">
                    <div>
                        <span class="task-queue-status ${statusLabel}">${statusText}</span>
                        <strong>${taskName}</strong>
                    </div>
                    <div class="task-queue-actions">
                        ${task.task_status <= 1 ? `<button class="abandon-btn" onclick="abandonTask(${task.task_id})">放弃</button>` : ''}
                    </div>
                </div>
                <div class="task-queue-info">任务ID: ${escapeHtml(task.task_id)}</div>
                ${timeInfo}
                ${buildStateInfo}
            </div>
        `;
    }).join('');
}

// 放弃任务
async function abandonTask(taskId) {
    if (!confirm('确定要放弃该任务吗？')) {
        return;
    }

    try {
        const response = await fetch(`/api/projects?action=abandon_task&task_id=${taskId}`);
        const result = await response.json();

        if (result.success) {
            notify('任务已放弃', 'success');
            loadTaskQueue();
        } else {
            throw new Error(result.message || '放弃任务失败');
        }
    } catch (error) {
        console.error('放弃任务失败:', error);
        notify('放弃任务失败: ' + error.message, 'error');
    }
}

// HTML转义函数（防止XSS）
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// 格式化时间显示
function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);

    if (hours > 0) {
        return `${hours}小时${minutes}分钟`;
    } else if (minutes > 0) {
        return `${minutes}分钟`;
    } else {
        return `${seconds}秒`;
    }
}

// 页面加载完成后加载数据
document.addEventListener('DOMContentLoaded', function() {
    // 加载左侧最近活动
    loadRecentActivities(1);
    // 启动任务队列刷新（默认显示任务队列 tab）
    startTaskQueueRefresh(defaultRefreshInterval);
});
</script>
</body>
</html> 