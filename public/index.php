<?php
/**
 * 主入口文件
 * 重定向到新的src目录结构
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 记录所有HTTP请求
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
error_log("[$request_method] $request_uri from $client_ip");

// 定义根目录
define('ROOT_PATH', __DIR__ . '/../src');

// 自动加载配置
require_once ROOT_PATH . '/config/database.php';

// 路由处理
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// 移除前导斜杠
$path = ltrim($path, '/');

// 如果路径为空，默认为首页
if (empty($path)) {
    $path = 'index';
}

// 检查是否是静态资源请求
if (strpos($path, 'assets/') === 0) {
    $static_file = ROOT_PATH . '/' . $path;
    
    // 调试信息
    error_log("Static file request: path={$path}, file={$static_file}, exists=" . (file_exists($static_file) ? 'YES' : 'NO'));
    
    if (file_exists($static_file)) {
        // 设置正确的Content-Type
        $extension = pathinfo($static_file, PATHINFO_EXTENSION);
        $content_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon'
        ];
        
        if (isset($content_types[$extension])) {
            header('Content-Type: ' . $content_types[$extension]);
        }
        
        // 设置缓存头
        header('Cache-Control: public, max-age=3600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        
        // 输出文件内容
        readfile($static_file);
        exit;
    } else {
        http_response_code(404);
        echo 'Static file not found: ' . htmlspecialchars($path);
        exit;
    }
}

// 映射路径到文件
$page_mapping = [
    '' => 'monitor.php',
    'index' => 'monitor.php',
    'index.php' => 'monitor.php',
    'monitor' => 'monitor.php',
    'monitor.php' => 'monitor.php',
    'tech-categories' => 'tech-categories.php',
    'tech-categories.php' => 'tech-categories.php',
    'code-review' => 'code-review.php',
    'code-review.php' => 'code-review.php',
    'projects' => 'projects.php',
    'projects.php' => 'projects.php',
    'project-detail' => 'project-detail.php',
    'project-detail.php' => 'project-detail.php',
    'config' => 'config.php',
    'config.php' => 'config.php'
];

// 检查是否是API请求
if (strpos($path, 'api/') === 0) {
    $api_path = substr($path, 4); // 移除 'api/' 前缀
    
    // 处理带有子路径的API请求（如 /api/categories/modules/123）
    $api_parts = explode('/', $api_path);
    $api_name = $api_parts[0];
    
    $api_file = ROOT_PATH . '/api/' . $api_name . '.php';
    
    if (file_exists($api_file)) {
        require_once $api_file;
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API not found: ' . $api_name]);
        exit;
    }
}

// 检查页面映射
if (isset($page_mapping[$path])) {
    $page_file = ROOT_PATH . '/pages/' . $page_mapping[$path];
    
    if (file_exists($page_file)) {
        require_once $page_file;
        exit;
    }
}

// 如果文件不存在，返回404
http_response_code(404);
echo '<h1>404 - 页面未找到</h1>';
echo '<p>请求的页面: ' . htmlspecialchars($path) . '</p>';
echo '<p><a href="/">返回首页</a></p>';
?>
