<?php
// 技术分类管理页面
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>技术分类管理 - 研发自动化管理平台</title>
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
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .main-content {
            display: flex;
            gap: 20px;
            height: calc(100vh - 200px);
        }
        
        .categories-panel {
            width: 400px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .modules-panel {
            flex: 1;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-height: 0;
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
        
        .category-list {
            list-style: none;
        }
        
        .category-item {
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .category-item:hover {
            background: #e9ecef;
        }
        
        .category-item.active {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }
        
        .category-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .category-desc {
            color: #666;
            font-size: 0.9em;
        }
        
        .category-stats {
            color: #999;
            font-size: 0.8em;
            margin-top: 5px;
        }
        
        .module-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .module-table th,
        .module-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .module-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .module-table tr:hover {
            background: #f8f9fa;
        }
        
        .module-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .module-input:focus {
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
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
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
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }
        
        .modal-content {
            background-color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .modal-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }
        
        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        
        .close:hover {
            color: #000;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        
        /* 通知样式 */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 300px;
            max-width: 500px;
        }
        
        .notification.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .notification.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .notification.warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .notification.info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
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
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .notification.hiding {
            animation: slideOut 0.3s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 技术分类管理</h1>
            <p>管理DDE、窗管、DTK等技术分类及其包含的模块配置</p>
        </div>
        
        <div class="nav-bar">
            <a href="/" class="btn">📊 监控面板</a>
            <a href="/tech-categories" class="btn active">🔧 技术分类</a>
            <a href="/code-review" class="btn">📋 代码审查</a>
            <a href="/projects" class="btn">📱 项目管理</a>
            <a href="/config" class="btn">⚙️ 系统配置</a>
        </div>
        
        <div class="main-content">
            <div class="categories-panel">
                <div class="panel-header">
                    <div class="panel-title">技术分类</div>
                    <button class="btn btn-primary" id="addCategoryBtn">+ 新增分类</button>
                </div>
                
                <ul class="category-list" id="categoryList">
                    <li class="category-item active" data-category="DDE V20">
                        <div class="category-name">DDE V20</div>
                        <div class="category-desc">深度桌面环境V20相关模块</div>
                        <div class="category-stats">55个模块</div>
                    </li>
                    <li class="category-item" data-category="窗管">
                        <div class="category-name">窗管</div>
                        <div class="category-desc">窗口管理系统模块</div>
                        <div class="category-stats">3个模块</div>
                    </li>
                    <li class="category-item" data-category="DTK">
                        <div class="category-name">DTK</div>
                        <div class="category-desc">开发工具包模块</div>
                        <div class="category-stats">4个模块</div>
                    </li>
                </ul>
            </div>
            
            <div class="modules-panel">
                <div class="panel-header">
                    <div class="panel-title">模块配置 - <span id="currentCategory">DDE V20</span></div>
                    <div>
                        <button class="btn btn-success" id="addModuleBtn">+ 新增模块</button>
                    </div>
                </div>
                
                <div id="moduleContent">
                    <table class="module-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>模块名称 / 别名</th>
                                <th>包名</th>
                                <th>代码地址</th>
                                <th>主线分支</th>
                                <th>打包方式</th>
                                <th>技术分类</th>
                                <th>打包依赖</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="moduleTableBody">
                            <!-- 动态内容 -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 新增分类模态框 -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">新增技术分类</div>
                <span class="close" onclick="closeCategoryModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">分类名称</label>
                    <input type="text" class="form-input" id="categoryName" placeholder="请输入分类名称">
                </div>
                <div class="form-group">
                    <label class="form-label">分类描述</label>
                    <input type="text" class="form-input" id="categoryDesc" placeholder="请输入分类描述">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeCategoryModal()">取消</button>
                <button class="btn btn-primary" onclick="saveCategoryModal()">保存</button>
            </div>
        </div>
    </div>

    <!-- 新增模块模态框 -->
    <div id="moduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">新增模块</div>
                <span class="close" onclick="closeModuleModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">模块ID</label>
                    <input type="number" class="form-input" id="moduleId" placeholder="请输入模块ID（数字）">
                </div>
                <div class="form-group">
                    <label class="form-label">模块名称</label>
                    <input type="text" class="form-input" id="moduleName" placeholder="请输入模块名称">
                </div>
                <div class="form-group">
                    <label class="form-label">别名（可选）</label>
                    <input type="text" class="form-input" id="moduleAlias" placeholder="请输入模块别名">
                </div>
                <div class="form-group">
                    <label class="form-label">代码地址</label>
                    <input type="text" class="form-input" id="moduleRepo" placeholder="请输入Git仓库地址">
                </div>
                <div class="form-group">
                    <label class="form-label">主线分支</label>
                    <input type="text" class="form-input" id="moduleBranch" placeholder="请输入主线分支名（如：main, master）">
                </div>
                <div class="form-group">
                    <label class="form-label">打包方式</label>
                    <select class="form-input" id="moduleBuildType">
                        <option value="deb" selected>deb</option>
                        <option value="changelog">changelog</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeModuleModal()">取消</button>
                <button class="btn btn-primary" onclick="saveModuleModal()">保存</button>
            </div>
        </div>
    </div>

    <!-- 选择依赖模态框 -->
    <div id="dependenciesModal" class="modal">
        <div class="modal-content" style="width: 700px;">
            <div class="modal-header">
                <div class="modal-title">选择打包依赖 - <span id="dependencyModuleTitle"></span></div>
                <span class="close" onclick="closeDependenciesModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">当前选中：<span id="selectedDependenciesText" style="color: #007bff;"></span></label>
                </div>
                <div class="form-group">
                    <label class="form-label">可选择的依赖模块（当前技术分类）</label>
                    <div id="dependenciesList" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                        <!-- 动态加载模块列表 -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeDependenciesModal()">取消</button>
                <button class="btn btn-primary" onclick="saveDependencies()">保存</button>
            </div>
        </div>
    </div>

<script>
// 从数据库获取的数据
let techCategories = {};
let currentCategory = 'DDE V20';
let currentEditingModuleId = null;
let selectedDependencies = [];

// 通知函数
function notify(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' ? '✓' : 
                 type === 'error' ? '✕' : 
                 type === 'warning' ? '⚠' : 'ℹ';
    
    notification.innerHTML = `
        <span style="font-size: 20px; font-weight: bold;">${icon}</span>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // 3秒后自动消失
    setTimeout(() => {
        notification.classList.add('hiding');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
});

// 从数据库加载分类数据
async function loadCategories() {
    try {
        const response = await fetch('/api/categories?action=categories');
        const result = await response.json();
        
        if (result.success) {
            const categoryList = document.getElementById('categoryList');
            categoryList.innerHTML = '';
            
            result.data.forEach((category, index) => {
                techCategories[category.name] = {
                    id: category.id,
                    name: category.name,
                    desc: category.description,
                    moduleCount: category.module_count
                };
                
                const categoryItem = document.createElement('li');
                categoryItem.className = 'category-item' + (index === 0 ? ' active' : '');
                categoryItem.dataset.category = category.name;
                categoryItem.innerHTML = `
                    <div class="category-name">${category.name}</div>
                    <div class="category-desc">${category.description}</div>
                    <div class="category-stats">${category.module_count}个模块</div>
                `;
                
                categoryItem.addEventListener('click', function() {
                    document.querySelectorAll('.category-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    currentCategory = this.dataset.category;
                    document.getElementById('currentCategory').textContent = currentCategory;
                    loadModules(currentCategory);
                });
                
                categoryList.appendChild(categoryItem);
            });
            
            // 加载第一个分类的模块
            if (result.data.length > 0) {
                currentCategory = result.data[0].name;
                document.getElementById('currentCategory').textContent = currentCategory;
                loadModules(currentCategory);
            }
        } else {
            notify('加载分类失败：' + result.message, 'error');
        }
    } catch (error) {
        console.error('加载分类失败：', error);
        notify('加载分类失败，请检查网络连接', 'error');
    }
}

// 从数据库加载模块数据
async function loadModules(categoryName) {
    try {
        const categoryId = techCategories[categoryName]?.id;
        if (!categoryId) return;
        
        const response = await fetch(`/api/categories?action=modules&category_id=${categoryId}`);
        const result = await response.json();
        
        if (result.success) {
            const tbody = document.getElementById('moduleTableBody');
            tbody.innerHTML = '';
            
            // 获取所有技术分类用于下拉框
            const categoriesResponse = await fetch('/api/categories?action=categories');
            const categoriesResult = await categoriesResponse.json();
            const allCategories = categoriesResult.success ? categoriesResult.data : [];
            
            result.data.forEach((module, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <input type="text" class="module-input" value="${module.id}" readonly style="background: #f5f5f5; cursor: not-allowed; width: 60px; text-align: center;">
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <input type="text" class="module-input" value="${module.name}" data-field="name" data-id="${module.id}" placeholder="模块名称">
                            <input type="text" class="module-input" value="${module.alias || ''}" data-field="alias" data-id="${module.id}" placeholder="别名（可选）" style="font-size: 0.9em; color: #666;">
                        </div>
                    </td>
                    <td><input type="text" class="module-input" value="${module.package_name || ''}" data-field="package_name" data-id="${module.id}" placeholder="包名"></td>
                    <td><input type="text" class="module-input" value="${module.repo_url}" data-field="repo_url" data-id="${module.id}"></td>
                    <td><input type="text" class="module-input" value="${module.main_branch}" data-field="main_branch" data-id="${module.id}"></td>
                    <td>
                        <select class="module-input" data-field="build_type" data-id="${module.id}" disabled>
                            <option value="0" ${module.build_type === '0' ? 'selected' : ''}>deb</option>
                            <option value="1" ${module.build_type === '1' ? 'selected' : ''}>changelog</option>
                        </select>
                    </td>
                    <td>
                        <select class="module-input" data-field="category_id" data-id="${module.id}" onchange="handleCategoryChange(${module.id}, this.value)">
                            ${allCategories.map(cat =>
                                `<option value="${cat.id}" ${cat.id == module.category_id ? 'selected' : ''}>${cat.name}</option>`
                            ).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="module-input" id="dependencies_${module.id}" value="${module.dependencies_names || ''}" readonly placeholder="点击选择依赖" style="cursor: pointer; background: #f8f9fa;" onclick="openDependenciesModal(${module.id}, '${module.name}')">
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-warning" onclick="saveModule(${module.id})" style="padding: 5px 10px; font-size: 12px;">保存</button>
                            <button class="btn btn-danger" onclick="deleteModule(${module.id})" style="padding: 5px 10px; font-size: 12px;">删除</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        } else {
            notify('加载模块失败：' + result.message, 'error');
        }
    } catch (error) {
        console.error('加载模块失败：', error);
        notify('加载模块失败，请检查网络连接', 'error');
    }
}

// 新增分类
document.getElementById('addCategoryBtn').addEventListener('click', function() {
    document.getElementById('categoryModal').style.display = 'block';
});

// 关闭分类模态框
function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDesc').value = '';
}

// 保存分类
async function saveCategoryModal() {
    const name = document.getElementById('categoryName').value.trim();
    const desc = document.getElementById('categoryDesc').value.trim();
    
    if (!name) {
        notify('请输入分类名称', 'warning');
        return;
    }
    
    if (techCategories[name]) {
        notify('分类名称已存在', 'warning');
        return;
    }
    
    try {
        const response = await fetch('/api/categories', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                description: desc
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeCategoryModal();
            notify('分类创建成功', 'success');
            loadCategories(); // 重新加载分类列表
        } else {
            notify('创建分类失败：' + result.message, 'error');
        }
    } catch (error) {
        console.error('创建分类失败：', error);
        notify('创建分类失败，请检查网络连接', 'error');
    }
}

// 新增模块
document.getElementById('addModuleBtn').addEventListener('click', function() {
    document.getElementById('moduleModal').style.display = 'block';
});

// 关闭模块模态框
function closeModuleModal() {
    document.getElementById('moduleModal').style.display = 'none';
    document.getElementById('moduleId').value = '';
    document.getElementById('moduleName').value = '';
    document.getElementById('moduleAlias').value = '';
    document.getElementById('moduleRepo').value = 'develop/eagle';
    document.getElementById('moduleBranch').value = '';
    document.getElementById('moduleBuildType').selectedIndex = 0;
}

// 保存模块
async function saveModuleModal() {
    const moduleId = document.getElementById('moduleId').value.trim();
    const name = document.getElementById('moduleName').value.trim();
    const alias = document.getElementById('moduleAlias').value.trim();
    const repo = document.getElementById('moduleRepo').value.trim();
    const branch = document.getElementById('moduleBranch').value.trim();
    const buildType = document.getElementById('moduleBuildType').selectedIndex;
    
    if (!moduleId || !name || !repo || !branch) {
        notify('请填写所有必填字段', 'warning');
        return;
    }
    
    if (isNaN(moduleId) || parseInt(moduleId) <= 0) {
        notify('模块ID必须是大于0的数字', 'warning');
        return;
    }
    
    try {
        const response = await fetch('/api/categories', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: parseInt(moduleId),
                category_id: techCategories[currentCategory].id,
                name: name,
                alias: alias || null,
                repo_url: repo,
                main_branch: branch,
                buildType: buildType,
                status: 'active'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModuleModal();
            notify('模块添加成功', 'success');
            loadModules(currentCategory); // 重新加载模块列表
            loadCategories(); // 重新加载分类列表以更新统计
        } else {
            notify('添加模块失败：' + result.message, 'error');
        }
    } catch (error) {
        console.error('添加模块失败：', error);
        notify('添加模块失败，请检查网络连接', 'error');
    }
}

// 保存模块（点击保存按钮时调用）
async function saveModule(moduleId) {
    // 获取该行的所有输入框
    const inputs = document.querySelectorAll(`.module-input[data-id="${moduleId}"]`);
    const updateData = {};
    
    // 收集所有字段的值
    inputs.forEach(input => {
        if (input.dataset.field === 'category_id') {
            updateData[input.dataset.field] = parseInt(input.value);
        } else {
            updateData[input.dataset.field] = input.value;
        }
    });
    
    try {
        const response = await fetch(`/api/categories/modules/${moduleId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(updateData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            notify('保存成功', 'success');
            // 如果分类发生变化，重新加载分类列表
            if (updateData.category_id && updateData.category_id !== techCategories[currentCategory].id) {
                loadCategories();
            }
        } else {
            notify('保存失败：' + result.message, 'error');
            loadModules(currentCategory); // 重新加载以恢复原值
        }
    } catch (error) {
        console.error('保存失败：', error);
        notify('保存失败，请检查网络连接', 'error');
        loadModules(currentCategory); // 重新加载以恢复原值
    }
}

// 处理技术分类变更
async function handleCategoryChange(moduleId, newCategoryId) {
    if (confirm('确定要更改模块的技术分类吗？')) {
        try {
            const updateData = {
                category_id: parseInt(newCategoryId)
            };
            
            const response = await fetch(`/api/categories/modules/${moduleId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(updateData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                notify('技术分类更新成功', 'success');
                // 重新加载当前分类的模块列表
                loadModules(currentCategory);
                // 重新加载分类列表以更新统计
                loadCategories();
            } else {
                notify('更新技术分类失败：' + result.message, 'error');
                // 重新加载以恢复原值
                loadModules(currentCategory);
            }
        } catch (error) {
            console.error('更新技术分类失败：', error);
            notify('更新技术分类失败，请检查网络连接', 'error');
            // 重新加载以恢复原值
            loadModules(currentCategory);
        }
    } else {
        // 用户取消操作，重新加载以恢复原值
        loadModules(currentCategory);
    }
}

// 删除模块
async function deleteModule(moduleId) {
    if (confirm('确定要删除这个模块吗？')) {
        try {
            const response = await fetch(`/api/categories/modules/${moduleId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                notify('模块删除成功', 'success');
                loadModules(currentCategory); // 重新加载模块列表
                loadCategories(); // 重新加载分类列表以更新统计
            } else {
                notify('删除模块失败：' + result.message, 'error');
            }
        } catch (error) {
            console.error('删除模块失败：', error);
            notify('删除模块失败，请检查网络连接', 'error');
        }
    }
}

// 点击模态框外部关闭
window.onclick = function(event) {
    const categoryModal = document.getElementById('categoryModal');
    const moduleModal = document.getElementById('moduleModal');
    const dependenciesModal = document.getElementById('dependenciesModal');

    if (event.target === categoryModal) {
        closeCategoryModal();
    }
    if (event.target === moduleModal) {
        closeModuleModal();
    }
    if (event.target === dependenciesModal) {
        closeDependenciesModal();
    }
};

// 打开依赖选择模态框
async function openDependenciesModal(moduleId, moduleName) {
    currentEditingModuleId = moduleId;
    selectedDependencies = []; // 清空之前的选择

    document.getElementById('dependencyModuleTitle').textContent = moduleName;
    document.getElementById('selectedDependenciesText').textContent = '无';

    // 获取当前模块的依赖
    try {
        const response = await fetch(`/api/categories?action=modules&category_id=${techCategories[currentCategory].id}`);
        const result = await response.json();

        if (result.success) {
            const currentModule = result.data.find(m => m.id === moduleId);
            if (currentModule && currentModule.dependencies) {
                selectedDependencies = currentModule.dependencies.split(',').map(id => parseInt(id.trim()));
                updateSelectedDependenciesText(result.data);
            }
        }
    } catch (error) {
        console.error('获取模块依赖失败：', error);
    }

    // 加载当前技术分类的所有模块
    await loadAvailableModules();

    document.getElementById('dependenciesModal').style.display = 'block';
}

// 加载可选择的依赖模块
async function loadAvailableModules() {
    try {
        const response = await fetch(`/api/categories?action=modules&category_id=${techCategories[currentCategory].id}`);
        const result = await response.json();

        if (result.success) {
            const dependenciesList = document.getElementById('dependenciesList');
            dependenciesList.innerHTML = '';

            result.data.forEach(module => {
                // 跳过当前模块自己
                if (module.id === currentEditingModuleId) return;

                const div = document.createElement('div');
                div.className = 'dependency-item';
                div.style.cssText = 'padding: 8px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; cursor: pointer;';

                const isChecked = selectedDependencies.includes(module.id);

                div.innerHTML = `
                    <input type="checkbox" id="dep_${module.id}" value="${module.id}" ${isChecked ? 'checked' : ''} style="margin-right: 10px;">
                    <label for="dep_${module.id}" style="cursor: pointer; flex: 1;">
                        ${module.name}
                    </label>
                `;

                div.onclick = function(e) {
                    if (e.target.tagName !== 'INPUT') {
                        const checkbox = document.getElementById(`dep_${module.id}`);
                        checkbox.checked = !checkbox.checked;
                        toggleDependency(module.id, checkbox.checked);
                    }
                };

                const checkbox = div.querySelector('input[type="checkbox"]');
                checkbox.onchange = function() {
                    toggleDependency(module.id, this.checked);
                };

                dependenciesList.appendChild(div);
            });

            updateSelectedDependenciesText(result.data);
        }
    } catch (error) {
        console.error('加载依赖模块失败：', error);
        notify('加载依赖模块失败', 'error');
    }
}

// 切换依赖选择
function toggleDependency(moduleId, checked) {
    if (checked) {
        if (!selectedDependencies.includes(moduleId)) {
            selectedDependencies.push(moduleId);
        }
    } else {
        selectedDependencies = selectedDependencies.filter(id => id !== moduleId);
    }
}

// 更新已选依赖文本
function updateSelectedDependenciesText(allModules) {
    const moduleMap = {};
    allModules.forEach(m => moduleMap[m.id] = m.name);

    const names = selectedDependencies.map(id => {
        return moduleMap[id] || id;
    }).sort((a, b) => a.localeCompare(b)).join(', ');

    document.getElementById('selectedDependenciesText').textContent = names || '无';
}

// 关闭依赖模态框
function closeDependenciesModal() {
    document.getElementById('dependenciesModal').style.display = 'none';
    currentEditingModuleId = null;
    selectedDependencies = [];
}

// 保存依赖
async function saveDependencies() {
    if (!currentEditingModuleId) return;

    const dependenciesStr = selectedDependencies.length > 0 ? selectedDependencies.sort((a, b) => a - b).join(',') : '';

    try {
        const response = await fetch(`/api/categories/modules/${currentEditingModuleId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                dependencies: dependenciesStr
            })
        });

        const result = await response.json();

        if (result.success) {
            notify('依赖保存成功', 'success');
            closeDependenciesModal();
            loadModules(currentCategory); // 重新加载模块列表
        } else {
            notify('保存依赖失败：' + result.message, 'error');
        }
    } catch (error) {
        console.error('保存依赖失败：', error);
        notify('保存依赖失败，请检查网络连接', 'error');
    }
}

// 导出技术分类数据到全局，供其他页面使用
window.techCategories = techCategories;
</script>
</body>
</html>
