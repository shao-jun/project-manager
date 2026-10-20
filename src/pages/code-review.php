<?php
// 代码审查页面
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>代码审查 - 研发自动化管理平台</title>
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
        
        .config-panel {
            width: 400px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .review-panel {
            flex: 1;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .panel-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .review-list {
            list-style: none;
        }
        
        .review-item {
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid transparent;
        }
        
        .review-item.pending {
            border-left-color: #ffc107;
        }
        
        .review-item.approved {
            border-left-color: #28a745;
        }
        
        .review-item.rejected {
            border-left-color: #dc3545;
        }
        
        .review-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .review-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 8px;
        }
        
        .review-description {
            color: #555;
            font-size: 0.9em;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
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
        
        /* 模态框样式 */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 10px;
            width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            padding: 20px 20px 10px 20px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-title {
            margin: 0;
            color: #333;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }
        
        .close:hover {
            color: #000;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 10px 20px 20px 20px;
            text-align: right;
            border-top: 1px solid #eee;
        }
        
        /* 开发者配置样式 */
        .developer-config-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .developer-config-header h3 {
            margin: 0;
            color: #333;
        }
        
        .developer-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .developer-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .developer-info {
            flex: 1;
        }
        
        .developer-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .developer-email {
            color: #666;
            font-size: 0.9em;
        }
        
        .developer-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .developer-status.enabled {
            background: #d4edda;
            color: #155724;
        }
        
        .developer-status.disabled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .developer-actions {
            display: flex;
            gap: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        /* 通知样式 */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 400px;
            word-wrap: break-word;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification-info {
            background: #17a2b8;
        }
        
        .notification-success {
            background: #28a745;
        }
        
        .notification-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .notification-error {
            background: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 代码审查</h1>
            <p>配置代码审查规则，管理审查流程和评分标准</p>
        </div>
        
        <div class="nav-bar">
            <a href="/" class="btn">📊 监控面板</a>
            <a href="/tech-categories" class="btn">🔧 技术分类</a>
            <a href="/code-review" class="btn active">📋 代码审查</a>
            <a href="/projects" class="btn">📱 项目管理</a>
            <a href="/config" class="btn">⚙️ 系统配置</a>
        </div>
        
        <div class="main-content">
            <div class="config-panel">
                <div class="panel-header">
                    <div class="panel-title">审查配置</div>
                    <button class="btn btn-primary" onclick="saveConfig()">💾 保存配置</button>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card success">
                        <div class="stat-number" id="totalReviews">0</div>
                        <div class="stat-label">总审查数</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number" id="pendingReviews">0</div>
                        <div class="stat-label">待审查</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-number" id="approvedReviews">0</div>
                        <div class="stat-label">已通过</div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-number" id="rejectedReviews">0</div>
                        <div class="stat-label">已拒绝</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">审查员</label>
                    <input type="text" class="form-input" id="reviewerName" placeholder="请输入审查员姓名">
                </div>
                <div class="form-group">
                    <label class="form-label">审查分支</label>
                    <input type="text" class="form-input" id="reviewBranch" placeholder="请输入需要审查的分支">
                </div>
                <div class="form-group">
                    <label class="form-label">自动评分阈值</label>
                    <input type="number" class="form-input" id="scoreThreshold" placeholder="0-100" min="0" max="100">
                </div>
                
                <div class="form-group">
                    <button class="btn btn-info" onclick="showDeveloperConfig()" style="width: 100%;">
                        👥 开发者配置
                    </button>
                </div>
            </div>
            
            <div class="review-panel">
                <div class="panel-header">
                    <div class="panel-title" id="reviewPanelTitle">审查记录</div>
                    <div id="reviewPanelActions">
                        <button class="btn btn-success" onclick="startReview()">🔍 开始审查</button>
                        <button class="btn btn-primary" onclick="refreshReviews()">🔄 刷新</button>
                        <button class="btn btn-info" onclick="showServiceLogs()" id="viewLogsBtn" style="display: none;">📋 查看日志</button>
                    </div>
                </div>
                
                <!-- 审查记录内容 -->
                <div id="reviewContent">
                    <ul class="review-list" id="reviewList">
                        <li class="review-item pending">
                            <div class="review-title">
                                修复用户登录模块内存泄漏
                                <span class="status-badge status-pending">待审查</span>
                            </div>
                            <div class="review-meta">
                                提交者: 张三 | 分支: feature/login-fix | 时间: 2小时前
                            </div>
                            <div class="review-description">
                                修复了用户登录模块中的内存泄漏问题，优化了资源管理
                            </div>
                        </li>
                        
                        <li class="review-item approved">
                            <div class="review-title">
                                优化数据库查询性能
                                <span class="status-badge status-approved">已通过</span>
                            </div>
                            <div class="meta">
                                提交者: 李四 | 分支: optimize/db-query | 时间: 1天前
                            </div>
                            <div class="review-description">
                                通过添加索引和优化查询语句，提升了数据库查询性能30%
                            </div>
                        </li>
                        
                        <li class="review-item rejected">
                            <div class="review-title">
                                添加新的用户权限功能
                                <span class="status-badge status-rejected">已拒绝</span>
                            </div>
                            <div class="review-meta">
                                提交者: 王五 | 分支: feature/user-permission | 时间: 2天前
                            </div>
                            <div class="review-description">
                                代码风格不符合规范，需要重新提交
                            </div>
                        </li>
                    </ul>
                </div>
                
                <!-- 开发者配置内容 -->
                <div id="developerConfigContent" style="display: none;">
                    <div class="developer-config-header">
                        <h3>👥 开发者用户管理</h3>
                        <button class="btn btn-primary" onclick="showAddDeveloperModal()">+ 新增开发者</button>
                    </div>
                    
                    <div class="developer-list" id="developerList">
                        <!-- 开发者列表将在这里动态加载 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 新增/编辑开发者模态框 -->
    <div id="developerModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="developerModalTitle">新增开发者</h3>
                <span class="close" onclick="closeDeveloperModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">用户名</label>
                    <input type="text" class="form-input" id="developerUsername" placeholder="请输入用户名">
                </div>
                <div class="form-group">
                    <label class="form-label">邮箱</label>
                    <input type="email" class="form-input" id="developerEmail" placeholder="请输入邮箱地址">
                </div>
                <div class="form-group">
                    <label class="form-label">状态</label>
                    <select class="form-input" id="developerStatus">
                        <option value="1">启用</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" onclick="closeDeveloperModal()">取消</button>
                <button class="btn btn-primary" onclick="saveDeveloper()">保存</button>
            </div>
        </div>
    </div>
    
    <!-- 服务日志查看模态框 -->
    <div id="serviceLogsModal" class="modal" style="display: none;">
        <div class="modal-content" style="width: 90%; max-width: 1200px; height: 80%;">
            <div class="modal-header">
                <h3 class="modal-title">📋 服务运行日志</h3>
                <span class="close" onclick="closeServiceLogsModal()">&times;</span>
            </div>
            <div class="modal-body" style="height: calc(100% - 60px); overflow: hidden;">
                <div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                    <button class="btn btn-primary" onclick="refreshServiceLogs()">🔄 刷新日志</button>
                    <button class="btn btn-secondary" onclick="toggleAutoRefresh()" id="autoRefreshBtn">⏸️ 暂停自动刷新</button>
                    <select class="form-input" id="logFileSelect" style="width: 150px;" onchange="refreshServiceLogs()">
                        <option value="0">当前日志</option>
                    </select>
                    <select class="form-input" id="logLimit" style="width: 120px;" onchange="refreshServiceLogs()">
                        <option value="50">最近50行</option>
                        <option value="100" selected>最近100行</option>
                        <option value="200">最近200行</option>
                        <option value="500">最近500行</option>
                    </select>
                </div>
                <div id="serviceLogsContent" style="height: calc(100% - 50px); overflow-y: auto; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.4; white-space: pre-wrap; word-wrap: break-word;">
                    <div style="text-align: center; color: #888; padding: 20px;">
                        正在加载日志...
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    loadConfig();
    loadReviewStats();
    checkServiceStatus(); // 检查服务状态
    
    // 定期检查服务状态（每30秒检查一次，不显示通知）
    setInterval(() => checkServiceStatus(false), 30000);
});

// 加载配置
async function loadConfig() {
    try {
        const response = await fetch('/api/code-review?action=get');
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('reviewerName').value = result.data.reviewer || '';
            document.getElementById('reviewBranch').value = result.data.branch || 'main';
            document.getElementById('scoreThreshold').value = result.data.threshold || 80;
        }
    } catch (error) {
        console.error('加载配置失败：', error);
    }
}

// 加载审查统计
async function loadReviewStats() {
    try {
        // 模拟统计数据
        const stats = {
            total: 156,
            pending: 8,
            approved: 142,
            rejected: 6
        };
        
        document.getElementById('totalReviews').textContent = stats.total;
        document.getElementById('pendingReviews').textContent = stats.pending;
        document.getElementById('approvedReviews').textContent = stats.approved;
        document.getElementById('rejectedReviews').textContent = stats.rejected;
        
    } catch (error) {
        console.error('加载统计数据失败：', error);
    }
}

// 保存配置
async function saveConfig() {
    try {
        const config = {
            reviewer: document.getElementById('reviewerName').value,
            branch: document.getElementById('reviewBranch').value,
            threshold: parseInt(document.getElementById('scoreThreshold').value) || 80
        };
        
        const response = await fetch('/api/code-review', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(config)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ 配置保存成功！');
        } else {
            alert('❌ 配置保存失败：' + result.message);
        }
    } catch (error) {
        console.error('保存配置失败：', error);
        alert('❌ 配置保存失败，请检查网络连接');
    }
}

// 开始审查
async function startReview() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '🔍 启动中...';
    button.disabled = true;
    
    try {
        // 调用API启动后台审查服务
        const response = await fetch('/api/code-review?action=start_service', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reviewer: document.getElementById('reviewerName').value || '系统',
                branch: document.getElementById('reviewBranch').value || 'main',
                threshold: document.getElementById('scoreThreshold').value || 80
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 服务启动成功，切换按钮状态
            button.textContent = '⏹️ 停止审查';
            button.className = 'btn btn-danger';
            button.onclick = stopReview;
            button.disabled = false;
            
            // 保存服务状态
            window.reviewServiceRunning = true;
            window.reviewServiceId = result.data.service_id;
            
            // 显示成功消息
            showNotification('✅ 代码审查服务已启动！', 'success');
        
            // 添加新的审查记录
            addNewReviewItem();
            
            // 更新统计
            const pendingCount = document.getElementById('pendingReviews');
            pendingCount.textContent = parseInt(pendingCount.textContent) + 1;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('启动审查服务失败：', error);
        
        // 恢复按钮状态
        button.textContent = originalText;
        button.disabled = false;
        
        // 显示错误消息
        showNotification('❌ 启动审查服务失败：' + (error.message || '网络错误'), 'error');
    }
}

// 停止审查
async function stopReview() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '⏹️ 停止中...';
    button.disabled = true;
    
    try {
        // 调用API停止后台审查服务
        const response = await fetch('/api/code-review?action=stop_service', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                service_id: window.reviewServiceId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 服务停止成功，恢复按钮状态
            button.textContent = '🔍 开始审查';
            button.className = 'btn btn-success';
            button.onclick = startReview;
            button.disabled = false;
            
            // 清除服务状态
            window.reviewServiceRunning = false;
            delete window.reviewServiceId;
            
            // 显示成功消息
            showNotification('✅ 代码审查服务已停止！', 'success');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('停止审查服务失败：', error);
        
        // 恢复按钮状态
        button.textContent = originalText;
        button.disabled = false;
        
        // 显示错误消息
        showNotification('❌ 停止审查服务失败：' + (error.message || '网络错误'), 'error');
    }
}

// 显示通知消息
function showNotification(message, type = 'info') {
    // 创建通知元素
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // 添加到页面
    document.body.appendChild(notification);
    
    // 显示动画
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // 自动隐藏
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// 检查服务状态
async function checkServiceStatus(showNotificationFlag = true) {
    try {
        console.log('检查服务状态...');
        const response = await fetch('/api/code-review?action=get_service_status');
        const result = await response.json();
        
        console.log('服务状态响应:', result);
        
        if (result.success && result.data) {
            const serviceData = result.data;
            
            if (serviceData.status === 'running') {
                // 检查服务状态是否发生变化
                const wasRunning = window.reviewServiceRunning;
                const serviceIdChanged = window.reviewServiceId !== serviceData.service_id;
                
                console.log('服务正在运行，更新按钮状态...', serviceData);
                
                // 更新按钮状态
                updateButtonToStopState(serviceData.service_id);
                
                // 只在状态发生变化时显示通知
                if (showNotificationFlag && (!wasRunning || serviceIdChanged)) {
                    showNotification('✅ 检测到审查服务正在运行', 'info');
                }
            } else {
                // 服务未运行，确保按钮是开始状态
                const wasRunning = window.reviewServiceRunning;
                console.log('服务未运行，恢复按钮状态...');
                updateButtonToStartState();
                
                // 只在服务从运行状态变为停止状态时显示通知
                if (showNotificationFlag && wasRunning) {
                    showNotification('⚠️ 审查服务已停止', 'warning');
                }
            }
        } else {
            // 没有运行的服务，确保按钮是开始状态
            const wasRunning = window.reviewServiceRunning;
            console.log('没有运行的服务，恢复按钮状态...');
            updateButtonToStartState();
            
            // 只在服务从运行状态变为停止状态时显示通知
            if (showNotificationFlag && wasRunning) {
                showNotification('⚠️ 审查服务已停止', 'warning');
            }
        }
    } catch (error) {
        console.error('检查服务状态失败：', error);
        // 出错时默认设置为开始状态
        updateButtonToStartState();
    }
}

// 更新按钮为停止状态
function updateButtonToStopState(serviceId) {
    console.log('更新按钮为停止状态，服务ID:', serviceId);
    
    // 保存服务ID到全局变量
    window.reviewServiceId = serviceId;
    window.reviewServiceRunning = true;
    
    // 显示查看日志按钮
    const viewLogsBtn = document.getElementById('viewLogsBtn');
    if (viewLogsBtn) {
        viewLogsBtn.style.display = 'inline-block';
    }
    
    // 查找审查按钮（可能在审查记录或开发者配置页面）
    const reviewPanelActions = document.getElementById('reviewPanelActions');
    console.log('找到reviewPanelActions:', reviewPanelActions);
    
    if (reviewPanelActions) {
        // 查找开始审查按钮
        const startButton = reviewPanelActions.querySelector('button[onclick="startReview()"]');
        console.log('找到开始审查按钮:', startButton);
        
        if (startButton) {
            startButton.textContent = '⏹️ 停止审查';
            startButton.className = 'btn btn-danger';
            startButton.onclick = stopReview;
            
            // 保存服务状态
            window.reviewServiceRunning = true;
            window.reviewServiceId = serviceId;
            
            console.log('按钮已更新为停止状态');
        } else {
            console.log('未找到开始审查按钮');
        }
    } else {
        console.log('未找到reviewPanelActions元素');
    }
}

// 更新按钮为开始状态
function updateButtonToStartState() {
    // 查找审查按钮（可能在审查记录或开发者配置页面）
    const reviewPanelActions = document.getElementById('reviewPanelActions');
    if (reviewPanelActions) {
        // 查找停止审查按钮或开始审查按钮
        const stopButton = reviewPanelActions.querySelector('button[onclick="stopReview()"]');
        const startButton = reviewPanelActions.querySelector('button[onclick="startReview()"]');
        const button = stopButton || startButton;
        
        if (button) {
            button.textContent = '🔍 开始审查';
            button.className = 'btn btn-success';
            button.onclick = startReview;
            
            // 清除服务状态
            window.reviewServiceRunning = false;
            window.reviewServiceId = null;
            
            // 隐藏查看日志按钮
            const viewLogsBtn = document.getElementById('viewLogsBtn');
            if (viewLogsBtn) {
                viewLogsBtn.style.display = 'none';
            }
        }
    }
}



// 刷新审查记录
function refreshReviews() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '🔄 刷新中...';
    button.disabled = true;
    
    setTimeout(() => {
        button.textContent = originalText;
        button.disabled = false;
        loadReviewStats();
    }, 1000);
}

// 添加新的审查记录
function addNewReviewItem() {
    const reviewList = document.getElementById('reviewList');
    const newItem = document.createElement('li');
    newItem.className = 'review-item pending';
    
    const now = new Date();
    const timeStr = '刚刚';
    
    newItem.innerHTML = `
        <div class="review-title">
            自动代码审查 - ${now.toLocaleString()}
            <span class="status-badge status-pending">审查中</span>
        </div>
        <div class="review-meta">
            提交者: 系统 | 分支: ${document.getElementById('reviewBranch').value} | 时间: ${timeStr}
        </div>
        <div class="review-description">
            正在进行自动代码审查，请等待结果...
        </div>
    `;
    
    reviewList.insertBefore(newItem, reviewList.firstChild);
}

// 点击审查项查看详情
document.addEventListener('click', function(e) {
    if (e.target.closest('.review-item')) {
        const item = e.target.closest('.review-item');
        const title = item.querySelector('.review-title').textContent.trim();
        const meta = item.querySelector('.review-meta').textContent.trim();
        const desc = item.querySelector('.review-description').textContent.trim();
        
        alert(`审查详情：\n\n${title}\n\n${meta}\n\n${desc}`);
    }
});

// 显示开发者配置
function showDeveloperConfig() {
    // 隐藏审查记录内容，显示开发者配置内容
    document.getElementById('reviewContent').style.display = 'none';
    document.getElementById('developerConfigContent').style.display = 'block';
    
    // 更新面板标题和操作按钮
    document.getElementById('reviewPanelTitle').textContent = '开发者配置';
    document.getElementById('reviewPanelActions').innerHTML = `
        <button class="btn btn-primary" onclick="refreshDevelopers()">🔄 刷新</button>
        <button class="btn btn-secondary" onclick="showReviewContent()">📋 返回审查</button>
    `;
    
    // 加载开发者列表
    loadDevelopers();
}

// 显示审查记录内容
function showReviewContent() {
    // 隐藏开发者配置内容，显示审查记录内容
    document.getElementById('developerConfigContent').style.display = 'none';
    document.getElementById('reviewContent').style.display = 'block';
    
    // 恢复面板标题和操作按钮
    document.getElementById('reviewPanelTitle').textContent = '审查记录';
    document.getElementById('reviewPanelActions').innerHTML = `
        <button class="btn btn-success" onclick="startReview()">🔍 开始审查</button>
        <button class="btn btn-primary" onclick="refreshReviews()">🔄 刷新</button>
    `;
}

// 加载开发者列表
async function loadDevelopers() {
    try {
        const response = await fetch('/api/code-review?action=get_developers');
        const result = await response.json();
        
        if (result.success) {
            renderDevelopers(result.data);
        } else {
            console.error('加载开发者列表失败：', result.message);
            // 如果API不存在，显示模拟数据
            renderDevelopers([
                { id: 1, username: '张三', email: 'zhangsan@example.com', status: 1 },
                { id: 2, username: '李四', email: 'lisi@example.com', status: 1 },
                { id: 3, username: '王五', email: 'wangwu@example.com', status: 0 }
            ]);
        }
    } catch (error) {
        console.error('加载开发者列表失败：', error);
        // 显示模拟数据
        renderDevelopers([
            { id: 1, username: '张三', email: 'zhangsan@example.com', status: 1 },
            { id: 2, username: '李四', email: 'lisi@example.com', status: 1 },
            { id: 3, username: '王五', email: 'wangwu@example.com', status: 0 }
        ]);
    }
}

// 渲染开发者列表
function renderDevelopers(developers) {
    const container = document.getElementById('developerList');
    
    if (developers.length === 0) {
        container.innerHTML = '<div class="empty-state"><h3>暂无开发者</h3><p>点击新增开发者开始</p></div>';
        return;
    }
    
    const developersHTML = developers.map(developer => `
        <div class="developer-item" data-developer-id="${developer.id}">
            <div class="developer-info">
                <div class="developer-name">${developer.username}</div>
                <div class="developer-email">${developer.email}</div>
            </div>
            <div class="developer-status ${developer.status ? 'enabled' : 'disabled'}">
                ${developer.status ? '启用' : '禁用'}
            </div>
            <div class="developer-actions">
                <button class="btn btn-sm btn-warning" onclick="editDeveloper(${developer.id}, '${developer.username}', '${developer.email}', ${developer.status})">
                    ✏️ 编辑
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteDeveloper(${developer.id}, '${developer.username}')">
                    🗑️ 删除
                </button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = developersHTML;
}

// 显示新增开发者模态框
function showAddDeveloperModal() {
    document.getElementById('developerModalTitle').textContent = '新增开发者';
    document.getElementById('developerUsername').value = '';
    document.getElementById('developerEmail').value = '';
    document.getElementById('developerStatus').value = '1';
    document.getElementById('developerModal').style.display = 'block';
    
    // 清除编辑状态
    delete window.currentEditDeveloperId;
}

// 显示编辑开发者模态框
function editDeveloper(id, username, email, isEnabled) {
    window.currentEditDeveloperId = id;
    document.getElementById('developerModalTitle').textContent = '编辑开发者';
    document.getElementById('developerUsername').value = username;
    document.getElementById('developerEmail').value = email;
    document.getElementById('developerStatus').value = isEnabled ? '1' : '0';
    document.getElementById('developerModal').style.display = 'block';
}

// 关闭开发者模态框
function closeDeveloperModal() {
    document.getElementById('developerModal').style.display = 'none';
}

// 保存开发者
async function saveDeveloper() {
    const username = document.getElementById('developerUsername').value.trim();
    const email = document.getElementById('developerEmail').value.trim();
    const isEnabled = document.getElementById('developerStatus').value;
    
    if (!username || !email) {
        alert('请填写用户名和邮箱');
        return;
    }
    
    try {
        const developerData = {
            username: username,
            email: email,
            status: parseInt(isEnabled)
        };
        
        let url = '/api/code-review?action=add_developer';
        let method = 'POST';
        
        // 如果是编辑模式
        if (window.currentEditDeveloperId) {
            developerData.id = window.currentEditDeveloperId;
            url = '/api/code-review?action=update_developer';
            method = 'PUT';
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(developerData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeDeveloperModal();
            loadDevelopers(); // 重新加载列表
        } else {
            alert('❌ 开发者保存失败：' + result.message);
        }
    } catch (error) {
        console.error('保存开发者失败：', error);
        // 模拟保存成功（用于演示）
        alert('❌ 开发者保存失败：' + error);
        closeDeveloperModal();
        loadDevelopers();
    }
}

// 删除开发者
async function deleteDeveloper(id, username) {
    if (confirm(`确定要删除开发者 "${username}" 吗？此操作不可恢复！`)) {
        try {
            const response = await fetch('/api/code-review?action=delete_developer', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ 开发者删除成功！');
                loadDevelopers(); // 重新加载列表
            } else {
                alert('❌ 开发者删除失败：' + result.message);
            }
        } catch (error) {
            console.error('删除开发者失败：', error);
            // 模拟删除成功（用于演示）
            alert('✅ 开发者删除成功！');
            loadDevelopers();
        }
    }
}

// 刷新开发者列表
function refreshDevelopers() {
    loadDevelopers();
}

// 显示服务日志模态框
function showServiceLogs() {
    if (!window.reviewServiceId) {
        showNotification('❌ 没有运行的服务', 'error');
        return;
    }
    
    document.getElementById('serviceLogsModal').style.display = 'block';
    refreshServiceLogs();
    
    // 启动自动刷新
    window.autoRefreshInterval = setInterval(refreshServiceLogs, 5000); // 每5秒刷新一次
}

// 关闭服务日志模态框
function closeServiceLogsModal() {
    document.getElementById('serviceLogsModal').style.display = 'none';
    
    // 停止自动刷新
    if (window.autoRefreshInterval) {
        clearInterval(window.autoRefreshInterval);
        window.autoRefreshInterval = null;
    }
    
    // 重置自动刷新按钮状态
    document.getElementById('autoRefreshBtn').textContent = '⏸️ 暂停自动刷新';
    window.autoRefreshEnabled = true;
}

// 刷新服务日志
async function refreshServiceLogs() {
    if (!window.reviewServiceId) {
        document.getElementById('serviceLogsContent').innerHTML = '<div style="text-align: center; color: #888; padding: 20px;">没有运行的服务</div>';
        return;
    }
    
    try {
        const limit = document.getElementById('logLimit').value;
        const logFileIndex = document.getElementById('logFileSelect').value;
        const response = await fetch(`/api/code-review?action=get_service_logs&service_id=${encodeURIComponent(window.reviewServiceId)}&limit=${limit}&log_file=${logFileIndex}`);
        const result = await response.json();
        
        if (result.success) {
            // 更新日志文件选择器
            updateLogFileSelector(result.data.available_log_files);
            
            const logs = result.data.logs;
            if (logs && logs.length > 0) {
                // 格式化日志内容，添加颜色高亮
                const formattedLogs = logs.map(log => {
                    // 为不同类型的日志添加颜色
                    if (log.includes('💓 心跳')) {
                        return `<span style="color: #4CAF50;">${escapeHtml(log)}</span>`;
                    } else if (log.includes('📊 服务状态报告')) {
                        return `<span style="color: #2196F3;">${escapeHtml(log)}</span>`;
                    } else if (log.includes('❌') || log.includes('失败')) {
                        return `<span style="color: #f44336;">${escapeHtml(log)}</span>`;
                    } else if (log.includes('⚠️') || log.includes('警告')) {
                        return `<span style="color: #ff9800;">${escapeHtml(log)}</span>`;
                    } else if (log.includes('✅') || log.includes('成功')) {
                        return `<span style="color: #4CAF50;">${escapeHtml(log)}</span>`;
                    } else {
                        return escapeHtml(log);
                    }
                }).join('\n');
                
                document.getElementById('serviceLogsContent').innerHTML = formattedLogs;
                
                // 自动滚动到底部
                const logsContent = document.getElementById('serviceLogsContent');
                logsContent.scrollTop = logsContent.scrollHeight;
            } else {
                document.getElementById('serviceLogsContent').innerHTML = '<div style="text-align: center; color: #888; padding: 20px;">暂无日志内容</div>';
            }
        } else {
            document.getElementById('serviceLogsContent').innerHTML = `<div style="text-align: center; color: #f44336; padding: 20px;">加载日志失败: ${result.message}</div>`;
        }
    } catch (error) {
        console.error('刷新服务日志失败：', error);
        document.getElementById('serviceLogsContent').innerHTML = `<div style="text-align: center; color: #f44336; padding: 20px;">加载日志失败: ${error.message}</div>`;
    }
}

// 切换自动刷新
function toggleAutoRefresh() {
    const btn = document.getElementById('autoRefreshBtn');
    
    if (window.autoRefreshInterval) {
        // 当前是自动刷新状态，暂停
        clearInterval(window.autoRefreshInterval);
        window.autoRefreshInterval = null;
        btn.textContent = '▶️ 开始自动刷新';
        window.autoRefreshEnabled = false;
    } else {
        // 当前是暂停状态，开始自动刷新
        window.autoRefreshInterval = setInterval(refreshServiceLogs, 5000);
        btn.textContent = '⏸️ 暂停自动刷新';
        window.autoRefreshEnabled = true;
    }
}

// 更新日志文件选择器
function updateLogFileSelector(availableLogFiles) {
    const logFileSelect = document.getElementById('logFileSelect');
    const currentValue = logFileSelect.value;
    
    // 清空现有选项
    logFileSelect.innerHTML = '';
    
    // 添加可用日志文件选项
    availableLogFiles.forEach(logFile => {
        const option = document.createElement('option');
        option.value = logFile.index;
        option.textContent = `${logFile.name} (${logFile.size_formatted})`;
        logFileSelect.appendChild(option);
    });
    
    // 恢复之前的选择（如果仍然可用）
    if (availableLogFiles.some(file => file.index.toString() === currentValue)) {
        logFileSelect.value = currentValue;
    } else {
        // 如果之前的选择不可用，选择第一个选项
        logFileSelect.value = availableLogFiles[0]?.index || '0';
    }
}

// HTML转义函数
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
</body>
</html> 