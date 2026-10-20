<?php
// Shuttle API函数库 - 专门负责调用Shuttle接口并返回数据

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/endpoints.php';

// 通用的token刷新函数
function refreshShuttleToken() {
    global $db;
    
    $shuttleConfig = $db->fetch("SELECT token_url, username, password FROM website_configs WHERE name = 'Shuttle' AND is_active = 1");
    
    if (!$shuttleConfig || empty($shuttleConfig['username']) || empty($shuttleConfig['password']) || empty($shuttleConfig['token_url'])) {
        throw new Exception('Shuttle认证配置不完整，无法刷新token');
    }
    
    $authUrl = $shuttleConfig['token_url'];
    $authData = json_encode([
        'username' => $shuttleConfig['username'],
        'password' => $shuttleConfig['password']
    ]);
    
    error_log("尝试从 {$authUrl} 刷新Shuttle token");
    
    $authCh = curl_init();
    curl_setopt($authCh, CURLOPT_URL, $authUrl);
    curl_setopt($authCh, CURLOPT_POST, true);
    curl_setopt($authCh, CURLOPT_POSTFIELDS, $authData);
    curl_setopt($authCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($authCh, CURLOPT_TIMEOUT, 15);
    curl_setopt($authCh, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($authCh, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($authData)
    ]);
    curl_setopt($authCh, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; ProjectManager/1.0)');
    
    $authResponse = curl_exec($authCh);
    $authHttpCode = curl_getinfo($authCh, CURLINFO_HTTP_CODE);
    $authError = curl_error($authCh);
    curl_close($authCh);
    
    if ($authError) {
        error_log("Shuttle token刷新网络错误: {$authError}");
        throw new Exception('token刷新网络请求失败: ' . $authError);
    }
    
    if ($authHttpCode !== 200) {
        error_log("Shuttle token刷新失败，HTTP状态码: {$authHttpCode}, 响应: {$authResponse}");
        throw new Exception('token刷新失败，HTTP状态码: ' . $authHttpCode);
    }
    
    $authData = json_decode($authResponse, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($authData['token'])) {
        error_log("Shuttle token刷新响应格式错误: {$authResponse}");
        throw new Exception('token刷新返回数据格式错误');
    }
    
    $jwtToken = $authData['token'];
    
    // 更新数据库中的token
    $db->execute("UPDATE website_configs SET token = ?, token_obtained_at = NOW() WHERE name = 'Shuttle'", [$jwtToken]);
    
    error_log("Shuttle token刷新成功");
    return $jwtToken;
}

// 获取有效的Shuttle token
function getValidShuttleToken() {
    global $db;
    
    $shuttleConfig = $db->fetch("SELECT token, token_url, username, password FROM website_configs WHERE name = 'Shuttle' AND is_active = 1");
    
    if (!$shuttleConfig) {
        throw new Exception('未找到Shuttle配置');
    }
    
    // 临时：如果有配置的token就直接使用，否则返回一个测试token
    if (!empty($shuttleConfig['token'])) {
        return $shuttleConfig['token'];
    }
    
    // 如果没有token，暂时返回一个测试token或者尝试刷新
    // TODO: 需要根据Shuttle平台的实际认证方式来修改
    error_log("Shuttle: 使用临时token或需要配置正确的认证方式");
    throw new Exception('请先配置有效的Shuttle认证token。如果Shuttle平台不需要预先认证，请联系管理员更新API实现。');
}

// 通用的Shuttle API请求函数，支持自动token刷新和重试
function makeShuttleApiRequest($url, $method = 'POST', $data = null, $maxRetries = 2) {
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
        try {
            $jwtToken = getValidShuttleToken();
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; ProjectManager/1.0)');
            
            $headers = [
                'Authorization: Bearer ' . $jwtToken,
                'Content-Type: application/json'
            ];
            
            if ($method === 'POST' && $data) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $headers[] = 'Content-Length: ' . strlen($data);
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception('网络请求失败: ' . $error);
            }
            
            // 如果是认证相关错误，尝试刷新token重试
            if (($httpCode === 401 || $httpCode === 400) && $retryCount < ($maxRetries - 1)) {
                error_log("Shuttle API返回认证错误 {$httpCode}，尝试刷新token重试 (重试次数: " . ($retryCount + 1) . ")");
                
                try {
                    // 强制刷新token
                    refreshShuttleToken();
                    $retryCount++;
                    continue; // 重试请求
                } catch (Exception $e) {
                    error_log("Token刷新失败: " . $e->getMessage());
                    throw new Exception('认证失败且无法刷新token: ' . $e->getMessage());
                }
            }
            
            if ($httpCode !== 200) {
                throw new Exception('Shuttle接口返回错误，HTTP状态码: ' . $httpCode . ', 响应: ' . $response);
            }
            
            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Shuttle接口返回的数据格式错误');
            }
            
            return [
                'success' => true,
                'data' => $responseData,
                'http_code' => $httpCode
            ];
            
        } catch (Exception $e) {
            if ($retryCount >= ($maxRetries - 1)) {
                throw $e;
            }
            $retryCount++;
            error_log("Shuttle API请求失败，准备重试: " . $e->getMessage());
        }
    }
}

// 刷新打包状态 - 根据taskid获取最新的构建状态
function refreshBuildStatus($taskId) {
    if (empty($taskId)) {
        throw new Exception('taskId参数不能为空');
    }
    
    $apiUrl = SHUTTLE_API . '/shuttle/task/list';
    $requestData = json_encode([
        'params' => [
            'taskid' => $taskId
        ],
        'pageSize' => 1,
        'current' => 1
    ]);
    
    error_log("刷新构建状态 - taskId: {$taskId}");
    
    try {
        $response = makeShuttleApiRequest($apiUrl, 'POST', $requestData);
        
        if (!$response['success'] || !isset($response['data']['tasks']) || empty($response['data']['tasks'])) {
            throw new Exception('未找到对应的构建任务');
        }
        
        $task = $response['data']['tasks'][0];
        $buildStatus = $task['status'] ?? null;
        
        if (!$buildStatus) {
            throw new Exception('无法获取构建状态');
        }
        
        error_log("构建状态刷新成功 - taskId: {$taskId}, status: {$buildStatus}");
        
        return [
            'success' => true,
            'status' => $buildStatus,
            'task_info' => $task
        ];
        
    } catch (Exception $e) {
        error_log("刷新构建状态失败 - taskId: {$taskId}, error: " . $e->getMessage());
        throw $e;
    }
}
?>