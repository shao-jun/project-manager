<?php
// 项目管理页面 - 只保留项目列表
require_once __DIR__ . '/header.php';

ob_start();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>项目管理 - 研发自动化管理平台</title>
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
            align-items: center;
        }
        
        .search-box {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .search-btn {
            padding: 8px 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
        
        .main-content {
            display: flex;
            gap: 20px;
            height: calc(100vh - 200px);
        }
        
        .filter-panel {
            width: 400px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .project-panel {
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
        
        .filter-section {
            margin-bottom: 30px;
        }
        
        .filter-title {
            font-size: 1em;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .filter-group {
            margin-bottom: 20px;
        }
        
        .filter-label {
            display: block;
            font-size: 0.9em;
            color: #666;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .filter-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9em;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .action-section {
            margin-top: 30px;
        }
        
        .action-section .btn {
            width: 100%;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .project-list {
            list-style: none;
            max-height: calc(100vh - 340px);
            overflow-y: auto;
            padding-right: 5px;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
                height: auto;
            }
            
            .filter-panel {
                width: 100%;
                margin-bottom: 20px;
            }
            
            .filter-section, .action-section {
                margin-bottom: 20px;
            }
            
            .filter-title {
                font-size: 0.9em;
                margin-bottom: 10px;
            }
            
            .project-list {
                max-height: calc(100vh - 420px);
            }
        }
        
        /* 自定义滚动条样式 */
        .project-list::-webkit-scrollbar {
            width: 8px;
        }
        
        .project-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .project-list::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .project-list::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        .project-item {
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .project-item:hover {
            background: #e9ecef;
            border-left-color: #667eea;
            transform: translateX(5px);
        }
        
        .project-name {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .project-desc {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        
        .project-status {
            font-size: 0.8em;
            color: #28a745;
            font-weight: bold;
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
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #eee;
            text-align: right;
        }
        
        .modal-footer .btn {
            margin-left: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        /* 分支选择器样式 */
        .branch-select-container {
            position: relative;
        }
        
        .branch-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 350px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .branch-dropdown.show {
            display: block;
        }
        
        .branch-option {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
            font-size: 0.8em;
            line-height: 0.8;
        }
        
        .branch-option:hover {
            background-color: #e9ecef;
        }
        
        .branch-option.active {
            background-color: #e9ecef;
            font-weight: 500;
        }
        
        .branch-option:last-child {
            border-bottom: none;
        }
        
        .branch-name {
            font-weight: 500;
            color: #333;
        }
        
        .branch-description {
            font-size: 0.875rem;
            color: #666;
            margin-top: 4px;
        }
        
        .branch-no-results {
            padding: 12px 16px;
            color: #666;
            font-style: italic;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 项目管理</h1>
            <p>管理项目开发流程，跟踪模块状态，自动化交付管理</p>
        </div>
        
        <div class="nav-bar">
            <a href="/" class="btn">📊 监控面板</a>
            <a href="/tech-categories" class="btn">🔧 技术分类</a>
            <a href="/code-review" class="btn">📋 代码审查</a>
            <a href="/projects" class="btn active">📱 项目管理</a>
            <a href="/config" class="btn">⚙️ 系统配置</a>
            <div class="search-box">
                <input type="text" id="topicIdSearch" placeholder="输入Topic ID、版本号、hash值或仓库地址搜索项目" class="search-input" oninput="onTopicSearchInput()">
            </div>
        </div>
        
        <div class="main-content">
            <!-- 左侧筛选面板 -->
            <div class="filter-panel">
                <div class="panel-header">
                    <div class="panel-title">项目筛选</div>
                </div>
                
                <div class="filter-section">
                    <div class="filter-title">📂 分类筛选</div>
                    <div class="filter-group">
                        <label class="filter-label">项目分类</label>
                        <select class="filter-select" id="projectTypeFilter" onchange="filterByProjectType()">
                            <option value="">全部项目</option>
                            <!-- 分类选项将由JavaScript动态加载 -->
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">项目经理</label>
                        <select class="filter-select" id="projectManagerFilter" onchange="filterByProjectManager()">
                            <option value="">全部项目经理</option>
                            <!-- 项目经理选项将由JavaScript动态加载 -->
                        </select>
                    </div>
                </div>
                
                <div class="action-section">
                    <div class="filter-title">🛠️ 项目操作</div>
                    <button class="btn btn-primary" onclick="showAddProjectModal()">+ 新建项目</button>
                </div>
            </div>
            
            <!-- 右侧项目列表 -->
            <div class="project-panel">
                <div class="panel-header">
                    <div class="panel-title">项目列表</div>
                </div>
                
                <ul class="project-list" id="projectList">
                    <!-- 项目列表将由JavaScript动态加载 -->
                </ul>
            </div>
        </div>
    </div>

    <!-- 新建项目模态框 -->
    <div id="projectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">新建项目</div>
                <span class="close" onclick="closeProjectModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">项目名称</label>
                    <input type="text" class="form-input" id="newProjectName" placeholder="请输入项目名称">
                </div>
                <div class="form-group">
                    <label class="form-label">技术分类</label>
                    <select class="form-select" id="newProjectCategory">
                        <option value="">请选择技术分类</option>
                        <!-- 分类选项将由JavaScript动态加载 -->
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">项目分类</label>
                    <select class="form-select" id="newProjectType">
                        <option value="">请选择项目分类</option>
                        <!-- 项目分类选项将由JavaScript动态加载 -->
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">打包分支</label>
                    <div class="branch-select-container">
                        <input type="text" class="form-input" id="newProjectBuildBranch" placeholder="请输入或选择打包分支" autocomplete="off">
                        <div class="branch-dropdown" id="newProjectBranchDropdown"></div>
                </div>
                </div>
                <div class="form-group">
                    <label class="form-label">项目描述</label>
                    <textarea class="form-textarea" id="newProjectDescription" placeholder="请输入项目描述"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeProjectModal()">取消</button>
                <button class="btn btn-primary" onclick="createProject()">创建</button>
            </div>
        </div>
    </div>

<script>
// 全局变量
let branchData = [];
let newProjectSelectedBranchIndex = -1;
let newProjectSelectedBranchData = null;
let projectManagerData = [];
let projectManagerSelectedIndex = -1;

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    loadProjects();
    loadBranchData();
    loadProjectTypes();
    loadProjectManagers();
});

// 加载项目列表
async function loadProjects(projectType = '', topicId = '', search = '') {
    try {
        let url = '/api/projects';
        const params = new URLSearchParams();
        
        if (projectType) {
            params.append('project_type', projectType);
        }
        
        if (topicId) {
            params.append('topic_id', topicId);
        }
        
        if (search) {
            params.append('search', search);
        }
        
        if (params.toString()) {
            url += `?${params.toString()}`;
        }
        
        const response = await fetch(url);
        const result = await response.json();
        
        const projectList = document.getElementById('projectList');
        
        if (result.success) {
            const projects = result.data;
            renderProjectList(projects);
        } else {
            projectList.innerHTML = '<div class="empty-state"><h3>加载失败</h3><p>' + result.message + '</p></div>';
        }
    } catch (error) {
        console.error('加载项目列表失败：', error);
        const projectList = document.getElementById('projectList');
        projectList.innerHTML = '<div class="empty-state"><h3>加载失败</h3><p>网络连接异常</p></div>';
    }
}

// 按Topic ID、版本号、hash值或仓库地址搜索
// 输入防抖：粘贴/输入长串仓库地址时避免每个字符都请求一次
let topicSearchTimer = null;
function onTopicSearchInput() {
    clearTimeout(topicSearchTimer);
    const searchValue = document.getElementById('topicIdSearch').value.trim();
    // 清空时立即恢复全部列表
    if (!searchValue) {
        searchByTopicId();
        return;
    }
    topicSearchTimer = setTimeout(searchByTopicId, 300);
}

function searchByTopicId() {
    clearTimeout(topicSearchTimer);
    const searchValue = document.getElementById('topicIdSearch').value.trim();
    if (searchValue) {
        loadProjects('', '', searchValue);
    } else {
        // 如果搜索框为空，重新加载所有项目
        loadProjects();
    }
}

// 渲染项目列表
function renderProjectList(projects) {
    const projectList = document.getElementById('projectList');
    
    if (projects.length === 0) {
        projectList.innerHTML = '<div class="empty-state"><h3>暂无项目</h3><p>点击新建项目开始</p></div>';
        return;
    }
    
    projectList.innerHTML = projects.map((project, index) => `
        <li class="project-item" data-project="${project.id}" onclick="openProjectDetail(${project.id})">
            <div class="project-name">${project.name}</div>
            <div class="project-desc">${project.description || '暂无描述'}</div>
            <div class="project-status">${project.status || '正在开发'}</div>
        </li>
    `).join('');
}

// 打开项目详情页面
function openProjectDetail(projectId) {
    // 在新标签页中打开项目详情
    window.open(`/project-detail?id=${projectId}`, '_blank');
}

// 加载项目分类选项
async function loadProjectTypes() {
    try {
        const response = await fetch('/api/projects?action=project_types');
        const result = await response.json();
        
        const typeFilter = document.getElementById('projectTypeFilter');
        
        if (result.success) {
            const projectTypes = result.data;
            
            // 清空现有选项（保留"全部项目"）
            typeFilter.innerHTML = '<option value="">全部项目</option>';
            
            // 添加分类选项
            projectTypes.forEach(typeObj => {
                const option = document.createElement('option');
                option.value = typeObj.project_type;
                option.textContent = typeObj.project_type;
                typeFilter.appendChild(option);
            });
        }
    } catch (error) {
        console.error('加载项目分类失败：', error);
    }
}

// 按项目分类筛选
function filterByProjectType() {
    const projectType = document.getElementById('projectTypeFilter').value;
    
    // 当选择项目分类时，清空项目经理筛选
    if (projectType) {
        document.getElementById('projectManagerFilter').value = '';
    }
    
    loadProjects(projectType);
}

// 按项目经理筛选
function filterByProjectManager() {
    const managerName = document.getElementById('projectManagerFilter').value;
    
    // 当选择项目经理时，清空项目分类筛选
    if (managerName) {
        document.getElementById('projectTypeFilter').value = '';
    }
    
    filterProjectsByManager(managerName);
}

// 根据项目经理筛选项目
async function filterProjectsByManager(managerName) {
    try {
        // 获取所有项目
        const response = await fetch('/api/projects');
        const result = await response.json();
        
        if (result.success) {
            const allProjects = result.data;
            
            // 根据项目经理过滤项目（如果选择了项目经理）
            const filteredProjects = managerName ? 
                allProjects.filter(project => project.pm === managerName) :
                allProjects;
            
            // 渲染过滤后的项目列表
            renderProjectList(filteredProjects);
        } else {
            console.error('筛选项目失败:', result.message);
        }
    } catch (error) {
        console.error('筛选项目异常:', error);
    }
}

// 显示新建项目模态框
function showAddProjectModal() {
    document.getElementById('projectModal').style.display = 'block';
    loadCategories(); // 加载技术分类选项
    loadNewProjectTypes(); // 加载项目分类选项
    initNewProjectBranchSelector(); // 初始化分支选择器
}

// 关闭新建项目模态框
function closeProjectModal() {
    document.getElementById('projectModal').style.display = 'none';
    // 清空表单
    document.getElementById('newProjectName').value = '';
    document.getElementById('newProjectCategory').value = '';
    document.getElementById('newProjectType').value = '';
    document.getElementById('newProjectDescription').value = '';
    document.getElementById('newProjectBuildBranch').value = '';
    // 重置分支选择数据
    newProjectSelectedBranchData = null;
}

// 加载技术分类
async function loadCategories() {
    try {
        const response = await fetch('/api/categories');
        const result = await response.json();
        
        const categorySelect = document.getElementById('newProjectCategory');
        
        if (result.success) {
            const categories = result.data;
            categorySelect.innerHTML = '<option value="">请选择技术分类</option>' + 
                categories.map(category => `<option value="${category.id}">${category.name}</option>`).join('');
        }
    } catch (error) {
        console.error('加载技术分类失败：', error);
    }
}

// 加载新建项目的项目分类选项
async function loadNewProjectTypes() {
    try {
        const response = await fetch('/api/projects?action=project_types');
        const result = await response.json();
        
        const typeSelect = document.getElementById('newProjectType');
        
        if (result.success) {
            const projectTypes = result.data;
            
            // 清空现有选项
            typeSelect.innerHTML = '<option value="">请选择项目分类</option>';
            
            // 添加分类选项
            projectTypes.forEach(typeObj => {
                const option = document.createElement('option');
                option.value = typeObj.project_type;
                option.textContent = typeObj.project_type;
                typeSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('加载项目分类失败：', error);
    }
}

// 创建项目
async function createProject() {
    const name = document.getElementById('newProjectName').value.trim();
    const categoryId = document.getElementById('newProjectCategory').value;
    const projectType = document.getElementById('newProjectType').value.trim();
    const description = document.getElementById('newProjectDescription').value.trim();
    const buildBranch = document.getElementById('newProjectBuildBranch').value.trim();
    
    if (!name) {
        alert('请输入项目名称');
        return;
    }
    
    if (!categoryId) {
        alert('请选择技术分类');
        return;
    }
    
    // 准备分支信息
    let branchInfo = {};
    
    // 如果选择了分支，添加CRP分支ID和名称
    if (newProjectSelectedBranchData) {
        branchInfo.crp_branch_id = newProjectSelectedBranchData.crp_id;
        branchInfo.crp_branch_name = newProjectSelectedBranchData.name;
    } else if (buildBranch) {
        // 如果只是手动输入了分支名称，只保存名称
        branchInfo.crp_branch_name = buildBranch;
    }
    
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                category_id: categoryId,
                project_type: projectType,
                description: description,
                ...branchInfo
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeProjectModal();
            alert('项目创建成功');
            // 保持当前筛选状态刷新项目列表
            const currentFilter = document.getElementById('projectTypeFilter').value;
            loadProjects(currentFilter);
        } else {
            alert('创建项目失败：' + result.message);
        }
    } catch (error) {
        console.error('创建项目失败：', error);
        alert('创建项目失败，请检查网络连接');
    }
}

// 页面点击事件处理
// 记录mousedown事件的目标，用于区分拖拽选择和真正的点击
let projectModalMouseDownTarget = null;

window.addEventListener('mousedown', function(event) {
    projectModalMouseDownTarget = event.target;
});

window.addEventListener('click', function(event) {
    const projectModal = document.getElementById('projectModal');
    
    // 只有当mousedown和click都在模态框背景上时才关闭
    // 这样可以防止拖拽选择时误关闭模态框
    if (event.target === projectModal && projectModalMouseDownTarget === projectModal) {
        closeProjectModal();
    }
});

// 加载分支数据
async function loadBranchData() {
    try {
        const response = await fetch('/api/crp-sync?action=list');
        const result = await response.json();
        
        if (result.success) {
            branchData = result.data;
        } else {
            console.error('加载分支数据失败:', result.message);
        }
    } catch (error) {
        console.error('加载分支数据异常:', error);
    }
}

// 初始化新项目分支选择器
function initNewProjectBranchSelector() {
    const branchInput = document.getElementById('newProjectBuildBranch');
    const branchDropdown = document.getElementById('newProjectBranchDropdown');
    
    if (!branchInput || !branchDropdown) return;
    
    // 输入框事件
    branchInput.addEventListener('input', function() {
        const query = this.value.trim();
        filterAndShowNewProjectBranches(query);
    });
    
    branchInput.addEventListener('focus', function() {
        const query = this.value.trim();
        filterAndShowNewProjectBranches(query);
    });
    
    branchInput.addEventListener('keydown', function(e) {
        const options = branchDropdown.querySelectorAll('.branch-option');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            newProjectSelectedBranchIndex = Math.min(newProjectSelectedBranchIndex + 1, options.length - 1);
            updateNewProjectBranchSelection();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            newProjectSelectedBranchIndex = Math.max(newProjectSelectedBranchIndex - 1, -1);
            updateNewProjectBranchSelection();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (newProjectSelectedBranchIndex >= 0 && newProjectSelectedBranchIndex < options.length) {
                selectNewProjectBranchByIndex(newProjectSelectedBranchIndex);
            }
        } else if (e.key === 'Escape') {
            hideNewProjectBranchDropdown();
        }
    });
    
    // 点击外部关闭下拉框
    document.addEventListener('click', function(e) {
        if (!branchInput.contains(e.target) && !branchDropdown.contains(e.target)) {
            hideNewProjectBranchDropdown();
        }
    });
}

// 过滤并显示新项目分支
function filterAndShowNewProjectBranches(query) {
    const branchDropdown = document.getElementById('newProjectBranchDropdown');
    newProjectSelectedBranchIndex = -1;
    
    if (!query) {
        // 显示所有分支（限制前20个）
        const limitedBranches = branchData.slice(0, 20);
        renderNewProjectBranchOptions(limitedBranches);
    } else {
        // 按名称过滤分支
        const filtered = branchData.filter(branch => 
            branch.name.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 20);
        renderNewProjectBranchOptions(filtered);
    }
    
    branchDropdown.classList.add('show');
}

// 渲染新项目分支选项
function renderNewProjectBranchOptions(branches) {
    const branchDropdown = document.getElementById('newProjectBranchDropdown');
    
    if (branches.length === 0) {
        branchDropdown.innerHTML = '<div class="branch-no-results">未找到匹配的分支</div>';
        return;
    }
    
    branchDropdown.innerHTML = branches.map((branch, index) => `
        <div class="branch-option" data-index="${index}" onclick="selectNewProjectBranchByIndex(${index})">
            <div class="branch-name">${branch.name}</div>
            ${branch.description ? `<div class="branch-description">${branch.description}</div>` : ''}
        </div>
    `).join('');
}

// 更新新项目分支选择状态
function updateNewProjectBranchSelection() {
    const options = document.querySelectorAll('#newProjectBranchDropdown .branch-option');
    options.forEach((option, index) => {
        option.classList.toggle('active', index === newProjectSelectedBranchIndex);
    });
    
    // 滚动到选中项
    if (newProjectSelectedBranchIndex >= 0 && newProjectSelectedBranchIndex < options.length) {
        options[newProjectSelectedBranchIndex].scrollIntoView({
            block: 'nearest'
        });
    }
}

// 按索引选择新项目分支
function selectNewProjectBranchByIndex(index) {
    const branchDropdown = document.getElementById('newProjectBranchDropdown');
    const options = branchDropdown.querySelectorAll('.branch-option');
    
    if (index >= 0 && index < options.length) {
        selectNewProjectBranch(options[index], index);
    }
}

// 选择新项目分支
function selectNewProjectBranch(optionElement, dataIndex = null) {
    const branchInput = document.getElementById('newProjectBuildBranch');
    const branchName = optionElement.querySelector('.branch-name').textContent;
    
    // 如果没有提供数据索引，从元素属性中获取
    if (dataIndex === null) {
        dataIndex = parseInt(optionElement.getAttribute('data-index'));
    }
    
    // 从当前过滤的分支中找到对应的完整数据
    let filteredBranches = branchData;
    const query = branchInput.value.trim();
    if (query) {
        filteredBranches = branchData.filter(branch => 
            branch.name.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 20);
    } else {
        filteredBranches = branchData.slice(0, 20);
    }
    
    // 保存选中分支的完整数据
    if (dataIndex >= 0 && dataIndex < filteredBranches.length) {
        newProjectSelectedBranchData = filteredBranches[dataIndex];
        console.log('新项目选中分支:', newProjectSelectedBranchData);
    }
    
    branchInput.value = branchName;
    hideNewProjectBranchDropdown();
}

// 隐藏新项目分支下拉框
function hideNewProjectBranchDropdown() {
    const branchDropdown = document.getElementById('newProjectBranchDropdown');
    if (branchDropdown) {
    branchDropdown.classList.remove('show');
    }
    newProjectSelectedBranchIndex = -1;
}

// 加载项目经理数据
async function loadProjectManagers() {
    try {
        const response = await fetch('/api/projects?action=project_managers');
        const result = await response.json();
        
        const managerFilter = document.getElementById('projectManagerFilter');
        
        if (result.success) {
            projectManagerData = result.data;
            
            // 清空现有选项（保留"全部项目经理"）
            managerFilter.innerHTML = '<option value="">全部项目经理</option>';
            
            // 对项目经理进行排序（按字母顺序）
            const sortedManagers = projectManagerData
                .filter(manager => manager.pm) // 过滤掉空值
                .sort((a, b) => a.pm.localeCompare(b.pm, 'zh-CN')); // 中文排序
            
            // 添加排序后的项目经理选项
            sortedManagers.forEach(manager => {
                const option = document.createElement('option');
                option.value = manager.pm;
                option.textContent = manager.pm;
                managerFilter.appendChild(option);
            });
        } else {
            console.error('加载项目经理数据失败:', result.message);
        }
    } catch (error) {
        console.error('加载项目经理数据异常:', error);
    }
}
</script>
</body>
</html>

<?php
ob_end_flush();
?>
