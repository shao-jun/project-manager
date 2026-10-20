<?php
// 系统配置页面
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统配置 - 研发自动化管理平台</title>
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
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .main-content {
            display: flex;
            gap: 20px;
            height: calc(100vh - 200px);
        }
        
        .config-nav {
            width: 300px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .config-panel {
            flex: 1;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-y: auto;
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
        
        .config-menu {
            list-style: none;
        }
        
        .config-menu-item {
            padding: 12px 15px;
            margin-bottom: 5px;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .config-menu-item:hover {
            background: #e9ecef;
        }
        
        .config-menu-item.active {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }
        
        .config-menu-item-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
        
        .config-menu-item-desc {
            color: #666;
            font-size: 0.85em;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
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
            min-height: 80px;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background: white;
        }
        
        .form-select:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .config-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .config-section:last-child {
            border-bottom: none;
        }
        
        .config-section-title {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        
        .config-section-desc {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .website-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .website-table th,
        .website-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .website-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .website-table tr:hover {
            background: #f8f9fa;
        }
        
        .website-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .website-input:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .token-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
        }
        
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        /* 同步按钮：进行中态 */
        .btn:disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }

        .sync-spinner {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 4px;
            vertical-align: -1px;
            border: 2px solid rgba(255, 255, 255, 0.45);
            border-top-color: #fff;
            border-radius: 50%;
            animation: sync-spin 0.7s linear infinite;
        }

        @keyframes sync-spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ 系统配置</h1>
            <p>管理用户凭据和网站配置</p>
        </div>
        
        <div class="nav-bar">
            <a href="/" class="btn">📊 监控面板</a>
            <a href="/tech-categories" class="btn">🔧 技术分类</a>
            <a href="/code-review" class="btn">📋 代码审查</a>
            <a href="/projects" class="btn">📱 项目管理</a>
            <a href="/config" class="btn active">⚙️ 系统配置</a>
        </div>
        
        <div class="main-content">
            <div class="config-nav">
                <div class="panel-header">
                    <div class="panel-title">配置分类</div>
                </div>
                
                <ul class="config-menu">
                    <li class="config-menu-item active" data-config="user">
                        <div class="config-menu-item-title">用户凭据</div>
                        <div class="config-menu-item-desc">管理API访问的用户名和密码</div>
                    </li>
                    <li class="config-menu-item" data-config="websites">
                        <div class="config-menu-item-title">网站配置</div>
                        <div class="config-menu-item-desc">管理网站列表和Token配置</div>
                    </li>
                </ul>
            </div>
            
            <div class="config-panel">
                <div class="panel-header">
                    <div class="panel-title">配置详情 - <span id="currentConfigTitle">用户凭据</span></div>
                </div>
                
                <div id="alertContainer"></div>
                
                <div id="configContent">
                    <!-- 用户凭据配置 -->
                    <div class="config-content" id="userConfig">
                        <div class="config-section">
                            <div class="config-section-title">API用户凭据</div>
                            <div class="config-section-desc">设置用于API访问的用户名和密码，这些凭据将用于获取Token</div>
                            
                            <div class="form-group">
                                <label class="form-label">用户名</label>
                                <input type="text" class="form-input" id="username" placeholder="请输入用户名">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">密码</label>
                                <input type="password" class="form-input" id="password" placeholder="请输入密码">
                            </div>
                            
                            <div class="form-group">
                                <button class="btn btn-primary" onclick="saveUserCredentials()">💾 保存用户凭据</button>
                            </div>
                        </div>
                        
                        <div class="config-section">
                            <div class="config-section-title">当前用户信息</div>
                            <div class="config-section-desc">显示当前保存的用户凭据信息</div>
                            
                            <div id="userInfo">
                                <div class="empty-state">
                                    <h3>暂无用户信息</h3>
                                    <p>请先设置用户凭据</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 网站配置 -->
                    <div class="config-content" id="websitesConfig" style="display: none;">
                        <div class="config-section">
                            <div class="config-section-title">网站配置管理</div>
                            <div class="config-section-desc">管理需要访问的网站列表，包括主页地址和Token获取地址</div>
                            
                            <div class="form-group">
                                <button class="btn btn-success" onclick="showAddWebsiteModal()">➕ 新增网站</button>
                            </div>
                            
                            <div id="websitesList">
                                <div class="empty-state">
                                    <h3>暂无网站配置</h3>
                                    <p>请添加网站配置</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 新增网站模态框 -->
    <div id="addWebsiteModal" class="modal" style="display: none;">
        <div class="modal-content" style="background-color: white; margin: 15% auto; padding: 20px; border-radius: 10px; width: 500px; max-width: 90%;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
                <div class="modal-title" style="font-size: 1.2em; font-weight: bold; color: #333;">新增网站配置</div>
                <span class="close" onclick="closeAddWebsiteModal()" style="font-size: 28px; font-weight: bold; cursor: pointer; color: #aaa;">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">网站名称</label>
                    <input type="text" class="form-input" id="newWebsiteName" placeholder="请输入网站名称">
                </div>
                <div class="form-group">
                    <label class="form-label">主页地址</label>
                    <input type="text" class="form-input" id="newWebsiteHomepage" placeholder="请输入网站主页地址">
                </div>
                <div class="form-group">
                    <label class="form-label">Token获取地址</label>
                    <input type="text" class="form-input" id="newWebsiteTokenUrl" placeholder="请输入Token获取API地址">
                </div>
            </div>
            <div class="modal-footer" style="text-align: right; margin-top: 20px;">
                <button class="btn" onclick="closeAddWebsiteModal()">取消</button>
                <button class="btn btn-primary" onclick="addWebsite()">保存</button>
            </div>
        </div>
    </div>

<script>
let currentConfig = 'user';

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    setupConfigMenu();
    loadUserInfo();
    loadWebsites();
});

// 设置配置菜单事件
function setupConfigMenu() {
    document.querySelectorAll('.config-menu-item').forEach(item => {
        item.addEventListener('click', function() {
            const configType = this.dataset.config;
            selectConfig(configType);
        });
    });
}

// 选择配置类型
function selectConfig(configType) {
    currentConfig = configType;
    
    // 更新菜单选中状态
    document.querySelectorAll('.config-menu-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-config="${configType}"]`).classList.add('active');
    
    // 隐藏所有配置内容
    document.querySelectorAll('.config-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // 显示当前配置内容
    document.getElementById(`${configType}Config`).style.display = 'block';
    
    // 更新标题
    const titles = {
        user: '用户凭据',
        websites: '网站配置'
    };
    document.getElementById('currentConfigTitle').textContent = titles[configType];
}

// 显示提示信息
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alertContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alertDiv);
    
    // 3秒后自动消失
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// 加载用户信息
async function loadUserInfo() {
    try {
        const response = await fetch('/api/config?action=user');
        const result = await response.json();
        
        const userInfoDiv = document.getElementById('userInfo');
        
        if (result.success && result.data) {
            const user = result.data;
            userInfoDiv.innerHTML = `
                <div class="form-group">
                    <label class="form-label">当前用户名</label>
                    <input type="text" class="form-input" value="${user.username}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">创建时间</label>
                    <input type="text" class="form-input" value="${user.created_at}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">最后更新</label>
                    <input type="text" class="form-input" value="${user.updated_at}" readonly>
                </div>
            `;
        } else {
            userInfoDiv.innerHTML = `
                <div class="empty-state">
                    <h3>暂无用户信息</h3>
                    <p>请先设置用户凭据</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('加载用户信息失败：', error);
        showAlert('加载用户信息失败', 'danger');
    }
}

// 保存用户凭据
async function saveUserCredentials() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    
    if (!username || !password) {
        showAlert('用户名和密码不能为空', 'danger');
        return;
    }
    
    try {
        const response = await fetch('/api/config', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_user',
                username: username,
                password: password
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('✅ 用户凭据保存成功！');
            // 清空输入框
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            // 重新加载用户信息
            loadUserInfo();
        } else {
            showAlert('❌ 保存失败：' + result.message, 'danger');
        }
    } catch (error) {
        console.error('保存用户凭据失败：', error);
        showAlert('❌ 保存失败，请检查网络连接', 'danger');
    }
}

// 加载网站列表
async function loadWebsites() {
    try {
        const response = await fetch('/api/config?action=websites');
        const result = await response.json();
        
        const websitesListDiv = document.getElementById('websitesList');
        
        if (result.success && result.data && result.data.length > 0) {
            const websites = result.data;
            let tableHTML = `
                <table class="website-table">
                    <thead>
                        <tr>
                            <th>网站名称</th>
                            <th>主页地址</th>
                            <th>Token状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            websites.forEach(website => {
                const hasToken = website.token && website.token.trim() !== '';
                const tokenTime = website.token_obtained_at ? new Date(website.token_obtained_at).toLocaleString() : '';
                const syncBtn = website.name === 'CRP' ? `
                    <button class="btn btn-primary sync-btn" onclick="syncBranches(${website.id}, this)" style="padding: 5px 10px; font-size: 12px; margin-right: 5px;">
                        同步分支
                    </button>
                ` : '';

                tableHTML += `
                    <tr>
                        <td>${website.name}</td>
                        <td>
                            <input type="text" class="website-input" value="${website.homepage}"
                                   onblur="updateWebsite(${website.id}, 'homepage', this.value)">
                        </td>
                        <td>
                            <span class="status-badge ${hasToken ? 'status-active' : 'status-inactive'}">
                                ${hasToken ? '已获取' : '未获取'}
                            </span>
                            ${hasToken ? `<div class="token-info">获取时间: ${tokenTime}</div>` : ''}
                        </td>
                        <td>
                            ${syncBtn}
                            <button class="btn btn-warning" onclick="refreshToken(${website.id})" style="padding: 5px 10px; font-size: 12px; margin-right: 5px;">
                                🔄 刷新Token
                            </button>
                            <button class="btn btn-danger" onclick="deleteWebsite(${website.id})" style="padding: 5px 10px; font-size: 12px;">
                                删除
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            tableHTML += `
                    </tbody>
                </table>
            `;
            
            websitesListDiv.innerHTML = tableHTML;
        } else {
            websitesListDiv.innerHTML = `
                <div class="empty-state">
                    <h3>暂无网站配置</h3>
                    <p>请添加网站配置</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('加载网站列表失败：', error);
        showAlert('加载网站列表失败', 'danger');
    }
}

// 显示新增网站模态框
function showAddWebsiteModal() {
    document.getElementById('addWebsiteModal').style.display = 'block';
}

// 关闭新增网站模态框
function closeAddWebsiteModal() {
    document.getElementById('addWebsiteModal').style.display = 'none';
    // 清空输入框
    document.getElementById('newWebsiteName').value = '';
    document.getElementById('newWebsiteHomepage').value = '';
    document.getElementById('newWebsiteTokenUrl').value = '';
}

// 新增网站
async function addWebsite() {
    const name = document.getElementById('newWebsiteName').value.trim();
    const homepage = document.getElementById('newWebsiteHomepage').value.trim();
    const tokenUrl = document.getElementById('newWebsiteTokenUrl').value.trim();
    
    if (!name || !homepage || !tokenUrl) {
        showAlert('网站名称、主页地址和Token地址不能为空', 'danger');
        return;
    }
    
    try {
        const response = await fetch('/api/config', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_website',
                name: name,
                homepage: homepage,
                token_url: tokenUrl
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('✅ 网站配置添加成功！');
            closeAddWebsiteModal();
            loadWebsites();
        } else {
            showAlert('❌ 添加失败：' + result.message, 'danger');
        }
    } catch (error) {
        console.error('添加网站失败：', error);
        showAlert('❌ 添加失败，请检查网络连接', 'danger');
    }
}

// 更新网站配置
async function updateWebsite(websiteId, field, value) {
    try {
        const response = await fetch('/api/config', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_website',
                website_id: websiteId,
                field: field,
                value: value
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('✅ 网站配置更新成功！');
        } else {
            showAlert('❌ 更新失败：' + result.message, 'danger');
        }
    } catch (error) {
        console.error('更新网站配置失败：', error);
        showAlert('❌ 更新失败，请检查网络连接', 'danger');
    }
}

// 刷新Token
async function refreshToken(websiteId) {
    try {
        const response = await fetch('/api/config', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'refresh_token',
                website_id: websiteId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('✅ Token刷新成功！');
            loadWebsites();
        } else {
            showAlert('❌ Token刷新失败：' + result.message, 'danger');
        }
    } catch (error) {
        console.error('刷新Token失败：', error);
        showAlert('❌ Token刷新失败，请检查网络连接', 'danger');
    }
}

// 同步CRP分支
// 正在同步中的网站ID，防止连续点击重复触发
const syncingBranchIds = new Set();

async function syncBranches(websiteId, btn) {
    if (syncingBranchIds.has(websiteId)) {
        showAlert('正在同步分支，请稍候...', 'warning');
        return;
    }
    syncingBranchIds.add(websiteId);

    const originalHTML = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="sync-spinner"></span> 同步中...';
    }
    showAlert('正在同步分支...', 'warning');

    try {
        const response = await fetch('/api/config', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'sync_branches',
                website_id: websiteId
            })
        });

        const result = await response.json();

        if (result.success) {
            showAlert('✅ ' + result.message);
        } else {
            showAlert('❌ 同步分支失败：' + result.message, 'danger');
        }
    } catch (error) {
        console.error('同步分支失败：', error);
        showAlert('❌ 同步分支失败，请检查网络连接', 'danger');
    } finally {
        // 无论成功失败都恢复按钮，避免卡在“同步中”
        syncingBranchIds.delete(websiteId);
        if (btn && btn.isConnected) {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    }
}

// 删除网站
async function deleteWebsite(websiteId) {
    if (!confirm('确定要删除这个网站配置吗？')) {
        return;
    }
    
    try {
        const response = await fetch('/api/config', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_website',
                website_id: websiteId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('✅ 网站配置删除成功！');
            loadWebsites();
        } else {
            showAlert('❌ 删除失败：' + result.message, 'danger');
        }
    } catch (error) {
        console.error('删除网站失败：', error);
        showAlert('❌ 删除失败，请检查网络连接', 'danger');
    }
}
</script>
</body>
</html> 