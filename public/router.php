<?php
/**
 * PHP内置服务器路由器
 * 处理静态资源和动态页面的路由
 */

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// 移除前导斜杠
$path = ltrim($path, '/');

// 检查是否是静态资源请求
if (strpos($path, 'assets/') === 0) {
    $static_file = __DIR__ . '/../src/' . $path;
    
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
        return true;
    } else {
        // 文件不存在，返回404
        http_response_code(404);
        echo 'Static file not found: ' . htmlspecialchars($path);
        return true;
    }
}

// 处理项目详情页面路由
if ($path === 'project-detail') {
    $detail_file = __DIR__ . '/../src/pages/project-detail.php';
    
    if (file_exists($detail_file)) {
        // 包含项目详情页面
        require_once $detail_file;
        return true;
    } else {
        // 文件不存在，返回404
        http_response_code(404);
        echo 'Project detail page not found';
        return true;
    }
}

// 处理CRP配置页面路由
if ($path === 'crp-config') {
    $config_file = __DIR__ . '/../src/pages/crp-config.php';
    
    if (file_exists($config_file)) {
        // 包含CRP配置页面
        require_once $config_file;
        return true;
    } else {
        // 文件不存在，返回404
        http_response_code(404);
        echo 'CRP config page not found';
        return true;
    }
}

// 对于其他请求，使用index.php处理
require_once __DIR__ . '/index.php';
return true;
?> 