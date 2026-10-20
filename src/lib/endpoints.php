<?php
/**
 * 地址配置读取辅助函数
 *
 * 用法：
 *   app_endpoint('crp')                     -> https://crp.example.com
 *   app_endpoint('crp', '/api/topics/1')    -> https://crp.example.com/api/topics/1
 *   CRP_BASE / CRP_API / GERRIT_BASE ...    -> 便捷常量
 */

if (!function_exists('app_endpoints')) {
    function app_endpoints(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = require __DIR__ . '/../config/endpoints.php';
        }
        return $cache;
    }
}

if (!function_exists('app_endpoint')) {
    function app_endpoint(string $key, string $path = ''): string
    {
        $all  = app_endpoints();
        $base = rtrim($all[$key] ?? '', '/');
        return $base . $path;
    }
}

// 前端可用的地址表（注入到页面里的 window.APP_ENDPOINTS）
if (!function_exists('app_endpoints_json')) {
    function app_endpoints_json(): string
    {
        return json_encode(
            app_endpoints(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
    }
}

// 便捷常量
$__app_constants = [
    'CRP_BASE'   => 'crp',
    'GERRIT_BASE' => 'gerrit',
    'PMS_BASE'   => 'pms',
    'SHUTTLE_BASE' => 'shuttle',
    'POOLS_BASE' => 'pools',
];
foreach ($__app_constants as $__name => $__key) {
    if (!defined($__name)) {
        define($__name, app_endpoint($__key));
    }
}
if (!defined('CRP_API')) {
    define('CRP_API', app_endpoint('crp', '/api'));
}
if (!defined('SHUTTLE_API')) {
    define('SHUTTLE_API', app_endpoint('shuttle', '/api'));
}
if (!defined('COOPERATION_API')) {
    define('COOPERATION_API', app_endpoint('cooperation', '/api'));
}
unset($__app_constants, $__name, $__key);
