<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

/**
 * 公共HTTP请求函数
 * @param string $url 请求URL
 * @param string $method 请求方法 (GET/POST)
 * @param array $headers 请求头
 * @param array $data POST数据 (JSON格式)
 * @return array 返回结果
 */
function makeHttpRequest($url, $method = 'GET', $headers = [], $data = null) {
    $ch = curl_init();
    
    // 设置基本选项
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // 设置请求方法
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    
    // 设置请求头
    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    $allHeaders = array_merge($defaultHeaders, $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
    
    // 设置POST数据
    if ($method === 'POST' && $data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    // 执行请求
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    // 返回结果
    if ($error) {
        return [
            'success' => false,
            'error' => $error,
            'http_code' => $httpCode
        ];
    }
    
    // 尝试解析JSON响应
    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // 如果不是JSON，返回原始响应
        $responseData = $response;
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'data' => $responseData,
        'raw_response' => $response
    ];
}

// 处理API请求
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => '无效的JSON数据']);
        exit;
    }
    
    $url = $input['url'] ?? '';
    $method = $input['method'] ?? 'GET';
    $headers = $input['headers'] ?? [];
    $data = $input['data'] ?? null;
    
    if (empty($url)) {
        http_response_code(400);
        echo json_encode(['error' => 'URL不能为空']);
        exit;
    }
    
    $result = makeHttpRequest($url, $method, $headers, $data);
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['error' => '只支持POST请求']);
}
?> 