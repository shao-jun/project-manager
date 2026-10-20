<?php
/**
 * 公共头部组件
 * 包含统一的页面结构、样式和脚本引用
 */

function renderPageHeader($title, $description, $currentPage = '') {
    return "
<!DOCTYPE html>
<html lang=\"zh-CN\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>$title - 研发自动化管理平台</title>
    <link rel=\"stylesheet\" href=\"/src/assets/css/common.css\">
</head>
<body>
    <div class=\"container\">
        <div class=\"header\">
            <h1>$title</h1>
            <p>$description</p>
        </div>
        
        <div class=\"nav-bar\">
            <a href=\"/\" class=\"btn" . ($currentPage === '/' ? ' active' : '') . "\">📊 监控面板</a>
            <a href=\"/tech-categories\" class=\"btn" . ($currentPage === '/tech-categories' ? ' active' : '') . "\">🔧 技术分类</a>
            <a href=\"/code-review\" class=\"btn" . ($currentPage === '/code-review' ? ' active' : '') . "\">📋 代码审查</a>
            <a href=\"/projects\" class=\"btn" . ($currentPage === '/projects' ? ' active' : '') . "\">📱 项目管理</a>
            <a href=\"/config\" class=\"btn" . ($currentPage === '/config' ? ' active' : '') . "\">⚙️ 系统配置</a>
        </div>
    ";
}

function renderPageFooter() {
    return "
    </div>
    <script src=\"/src/assets/js/common.js\"></script>
</body>
</html>
    ";
}

// 渲染完整页面的辅助函数
function renderPage($title, $description, $currentPage, $content) {
    echo renderPageHeader($title, $description, $currentPage);
    echo $content;
    echo renderPageFooter();
}
?> 