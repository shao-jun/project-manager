<?php
require_once __DIR__ . '/../lib/endpoints.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRP配置 - 项目管理系统</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <style>
        .config-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .config-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .config-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .config-header p {
            color: #666;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.1);
        }
        
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            min-height: 80px;
            resize: vertical;
        }
        
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .btn {
            background: #007cba;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background: #005a87;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .status-message {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }
        
        .status-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .nav-bar {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .nav-bar a {
            text-decoration: none;
            margin: 0 10px;
            padding: 8px 16px;
            background: #f8f9fa;
            color: #333;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        
        .nav-bar a:hover {
            background: #e9ecef;
        }
        
        .nav-bar a.active {
            background: #007cba;
            color: white;
        }
    </style>
</head>
<body>
    <div class="config-container">
        <div class="nav-bar">
            <a href="/">📊 监控面板</a>
            <a href="/projects">📱 项目管理</a>
            <a href="/config">⚙️ 系统配置</a>
            <a href="/crp-config" class="active">🔑 CRP配置</a>
        </div>
        
        <div class="config-header">
            <h1>🔑 CRP API 配置</h1>
            <p>配置CRP系统的JWT认证token和相关设置</p>
        </div>
        
        <div id="statusMessage" class="status-message"></div>
        
        <form id="crpConfigForm">
            <div class="form-group">
                <label class="form-label">JWT Token *</label>
                <textarea class="form-textarea" id="jwtToken" placeholder="请输入CRP系统的JWT认证token..." required></textarea>
                <div class="help-text">从CRP系统管理员处获取，或通过CRP登录API获取有效的JWT token</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">CRP API 基础URL</label>
                <input type="url" class="form-input" id="baseUrl" placeholder="https://crp.example.com/api">
                <div class="help-text">CRP系统API的基础地址，一般无需修改</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">请求超时时间（秒）</label>
                <input type="number" class="form-input" id="timeout" min="5" max="300" placeholder="30">
                <div class="help-text">API请求的超时时间，建议保持默认值30秒</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">默认主题Owner</label>
                <input type="text" class="form-input" id="owner" placeholder="owner1;owner2">
                <div class="help-text">创建主题时的默认负责人，多个用分号分隔</div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <button type="button" class="btn" onclick="saveConfig()">保存配置</button>
                <button type="button" class="btn btn-secondary" onclick="testConnection()">测试连接</button>
                <button type="button" class="btn btn-secondary" onclick="loadConfig()">重新加载</button>
            </div>
        </form>
    </div>

    <script>
        // 页面加载时自动加载配置
        document.addEventListener('DOMContentLoaded', function() {
            loadConfig();
        });
        
        // 加载配置
        async function loadConfig() {
            try {
                const response = await fetch('/api/crp-config');
                const result = await response.json();
                
                if (result.success) {
                    const config = result.data;
                    document.getElementById('jwtToken').value = config.jwt_token || '';
                    document.getElementById('baseUrl').value = config.base_url || window.APP_ENDPOINTS.crp_api;
                    document.getElementById('timeout').value = config.timeout || 30;
                    document.getElementById('owner').value = config.default_topic?.Owner || 'owner1;owner2';
                    
                    showMessage('配置加载成功', 'success');
                } else {
                    showMessage('加载配置失败：' + result.message, 'error');
                }
            } catch (error) {
                console.error('加载配置失败：', error);
                showMessage('加载配置失败，请检查网络连接', 'error');
            }
        }
        
        // 保存配置
        async function saveConfig() {
            const config = {
                jwt_token: document.getElementById('jwtToken').value.trim(),
                base_url: document.getElementById('baseUrl').value.trim() || window.APP_ENDPOINTS.crp_api,
                timeout: parseInt(document.getElementById('timeout').value) || 30,
                default_topic: {
                    TpcType: 'public',
                    Type: 'test',
                    Owner: document.getElementById('owner').value.trim() || 'owner1;owner2',
                    RepoType: 'deb'
                }
            };
            
            if (!config.jwt_token) {
                showMessage('请输入JWT Token', 'error');
                return;
            }
            
            try {
                const response = await fetch('/api/crp-config', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(config)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('配置保存成功！', 'success');
                } else {
                    showMessage('保存失败：' + result.message, 'error');
                }
            } catch (error) {
                console.error('保存配置失败：', error);
                showMessage('保存失败，请检查网络连接', 'error');
            }
        }
        
        // 测试连接
        async function testConnection() {
            const jwtToken = document.getElementById('jwtToken').value.trim();
            if (!jwtToken) {
                showMessage('请先输入JWT Token', 'error');
                return;
            }
            
            try {
                showMessage('正在测试连接...', 'success');
                
                const response = await fetch('/api/crp-config', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'test',
                        jwt_token: jwtToken
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('🎉 连接测试成功！JWT Token有效', 'success');
                } else {
                    showMessage('❌ 连接测试失败：' + result.message, 'error');
                }
            } catch (error) {
                console.error('测试连接失败：', error);
                showMessage('测试失败，请检查网络连接', 'error');
            }
        }
        
        // 显示消息
        function showMessage(message, type) {
            const messageEl = document.getElementById('statusMessage');
            messageEl.textContent = message;
            messageEl.className = `status-message status-${type}`;
            messageEl.style.display = 'block';
            
            // 3秒后自动隐藏成功消息
            if (type === 'success') {
                setTimeout(() => {
                    messageEl.style.display = 'none';
                }, 3000);
            }
        }
    </script>
</body>
</html> 