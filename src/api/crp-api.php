<?php
// CRP API函数库 - 专门负责调用CRP接口并返回数据

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/endpoints.php';

// 通用的token刷新函数
function refreshCrpToken() {
    global $db;
    
    $crpConfig = $db->fetch("SELECT token_url, a.username, b.password FROM website_configs a left join users b on a.username=b.username WHERE a.name = 'CRP' AND a.is_active = 1");
    
    if (!$crpConfig || empty($crpConfig['username']) || empty($crpConfig['password']) || empty($crpConfig['token_url'])) {
        throw new Exception('CRP认证配置不完整，无法刷新token');
    }
    
    $authUrl = $crpConfig['token_url'];
    $authData = json_encode([
        'username' => $crpConfig['username'],
        'password' => $crpConfig['password']
    ]);
    
    // 注意：日志中不要输出密码等敏感信息
    error_log("尝试刷新CRP token: {$authUrl}");
    
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
        error_log("CRP token刷新网络错误: {$authError}");
        throw new Exception('token刷新网络请求失败: ' . $authError);
    }
    
    if ($authHttpCode !== 200) {
        error_log("CRP token刷新失败，HTTP状态码: {$authHttpCode}, 响应: {$authResponse}");
        throw new Exception('token刷新失败，HTTP状态码: ' . $authHttpCode);
    }
    
    $authResponseData = json_decode($authResponse, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("CRP token刷新响应JSON解析错误: {$authResponse}");
        throw new Exception('token刷新响应JSON格式错误');
    }
    
    // CRP API返回的token字段可能是"Token"（大写）或"token"（小写）
    $newToken = null;
    if (isset($authResponseData['Token'])) {
        $newToken = $authResponseData['Token'];
    } elseif (isset($authResponseData['token'])) {
        $newToken = $authResponseData['token'];
    } else {
        error_log("CRP token刷新响应格式错误: {$authResponse}");
        throw new Exception('响应中未找到Token字段');
    }
    $db->execute("UPDATE website_configs SET token = ?, token_obtained_at = CURRENT_TIMESTAMP WHERE name = 'CRP'", [$newToken]);
    
    error_log("CRP token刷新成功");
    return $newToken;
}

// 获取有效的CRP token
function getValidCrpToken() {
    global $db;
    
    $crpConfig = $db->fetch("SELECT token, token_url, username, password FROM website_configs WHERE name = 'CRP' AND is_active = 1");
    
    if (!$crpConfig) {
        throw new Exception('未找到CRP配置');
    }
    
    $jwtToken = null;
    
    // 检查当前token是否有效
    if (!empty($crpConfig['token'])) {
        $tokenParts = explode('.', $crpConfig['token']);
        if (count($tokenParts) === 3) {
            $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);
            if ($payload && isset($payload['exp'])) {
                $currentTime = time();
                // 提前5分钟过期，确保token在使用过程中不会过期
                if ($payload['exp'] > ($currentTime + 300)) {
                    $jwtToken = $crpConfig['token'];
                    error_log("使用现有有效token，剩余时间: " . round(($payload['exp'] - $currentTime) / 60, 1) . " 分钟");
                } else {
                    error_log("Token即将过期或已过期，过期时间: " . date('Y-m-d H:i:s', $payload['exp']) . ", 当前时间: " . date('Y-m-d H:i:s', $currentTime));
                }
            }
        }
    }
    
    // 如果没有有效token，刷新它
    if (!$jwtToken) {
        $jwtToken = refreshCrpToken();
    }
    
    return $jwtToken;
}

// 通用的CRP API请求函数，支持自动token刷新和重试
function makeCrpApiRequest($url, $method = 'GET', $data = null, $maxRetries = 2) {
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
        try {
            $jwtToken = getValidCrpToken();
            
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
                // 如果 $data 是数组，转换为 JSON 字符串
                $postData = is_array($data) ? json_encode($data) : $data;
                error_log("CRP API POST 数据: " . $postData);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                $headers[] = 'Content-Length: ' . strlen($postData);
            }
            
            if ($method === 'PUT' && $data) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                $putData = is_array($data) ? json_encode($data) : $data;
                error_log("CRP API PUT 数据: " . $putData);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $putData);
                $headers[] = 'Content-Length: ' . strlen($putData);
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
                error_log("CRP API返回认证错误 {$httpCode}，尝试刷新token重试 (重试次数: " . ($retryCount + 1) . ")");
                
                try {
                    // 强制刷新token
                    refreshCrpToken();
                    $retryCount++;
                    continue; // 重试请求
                } catch (Exception $e) {
                    error_log("Token刷新失败: " . $e->getMessage());
                    throw new Exception('认证失败且无法刷新token: ' . $e->getMessage());
                }
            }
            
            if ($httpCode !== 200) {
                throw new Exception('CRP接口返回错误，HTTP状态码: ' . $httpCode . ', 响应: ' . $response);
            }
            
            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('CRP接口返回的数据格式错误');
            }
            
            return [
                'success' => true,
                'data' => $responseData,
                'debug_info' => [
                    'url' => $url,
                    'http_code' => $httpCode,
                    'retry_count' => $retryCount
                ]
            ];
            
        } catch (Exception $e) {
            // 如果是网络错误或非认证错误，不重试
            if (!in_array($httpCode ?? 0, [400, 401]) || $retryCount >= ($maxRetries - 1)) {
                throw $e;
            }
            $retryCount++;
        }
    }
    
    throw new Exception('CRP API请求失败，已达到最大重试次数');
}

// 定义CRP API函数
function getCrpReleases($topicId) {
    if (!$topicId) {
        throw new Exception('主题ID不能为空');
    }
    
    $crpUrl = CRP_API . "/topics/{$topicId}/releases";
    return makeCrpApiRequest($crpUrl);
}

function getCrpTopicDetail($topicId) {
    if (!$topicId) {
        throw new Exception('主题ID不能为空');
    }
    
    $topicUrl = CRP_API . "/topics/{$topicId}?id={$topicId}";
    return makeCrpApiRequest($topicUrl);
}

// 获取主题中一个模块的信息
function getCrpTopicItemDetail($itemId) {
    if (!$itemId) {
        throw new Exception('itemId不能为空');
    }
    
    $itemUrl = CRP_API . "/topic_releases/{$itemId}";
    return makeCrpApiRequest($itemUrl);
}

function getCrpTopicUrls($topicId, $branchId) {
    if (!$topicId) {
        throw new Exception('主题ID不能为空');
    }
    
    if (empty($branchId)) {
        throw new Exception('分支ID不能为空');
    }
    
    $repoUrl = CRP_API . "/topic_urls";
    $repoData = json_encode([
        'branchID' => intval($branchId),
        'topicID' => intval($topicId)
    ]);
       
    try {
        $responseData = makeCrpApiRequest($repoUrl, 'POST', $repoData);
        // error_log("CRP仓库地址返回值 - ".json_encode($responseData));
        return $responseData;
    } catch (Exception $e) {
        // 如果仓库地址接口失败，返回模拟数据（仅用于兼容性）
        error_log("CRP仓库地址接口失败: " . $e->getMessage());
        return [
            'success' => true,
            'data' => [
                'RepoUrls' => [GERRIT_BASE . "/test/repo-" . $topicId]
            ],
            'debug_info' => [
                'url' => $repoUrl,
                'http_code' => 0,
                'error' => $e->getMessage(),
                'fallback_used' => true
            ]
        ];
    }
}

// 放弃CRP构建
function abandonCrpBuild($buildItemId) {
    if (!$buildItemId) {
        throw new Exception('构建项ID不能为空');
    }
    
    try {
        // 获取有效的JWT token
        $jwtToken = getValidCrpToken();
        
        $url = CRP_API . "/topic_releases/{$buildItemId}";
        
        // 发送DELETE请求
        $context = stream_context_create([
            'http' => [
                'method' => 'DELETE',
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $jwtToken,
                    'User-Agent: Mozilla/5.0 (compatible; ProjectManager/1.0)'
                ],
                'timeout' => 30,
                'ignore_errors' => true
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        // 获取HTTP响应头信息
        $httpResponseHeader = $http_response_header ?? [];
        $statusLine = $httpResponseHeader[0] ?? '';
        
        if ($response === false) {
            $error = error_get_last();
            throw new Exception('无法连接到CRP服务器: ' . ($error['message'] ?? '未知错误'));
        }
        
        // 记录响应信息
        error_log("CRP放弃构建响应状态: " . $statusLine);
        error_log("CRP放弃构建响应: " . $response);
        
        // 检查HTTP状态码
        if (strpos($statusLine, '200') !== false) {
            // 尝试解析JSON响应
            $responseData = json_decode($response, true);
            
            if ($responseData && isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'message' => '构建已成功放弃',
                    'data' => $responseData
                ];
            } else {
                // 如果JSON解析失败或状态不是success，检查是否是直接的ID值
                if (is_numeric($response) && $response > 0) {
                    return [
                        'success' => true,
                        'message' => '构建已成功放弃',
                        'data' => ['ID' => intval($response)]
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'CRP API返回错误状态: ' . ($responseData['message'] ?? $response)
                ];
            }
        } else {
            throw new Exception('CRP API返回错误，HTTP状态码: ' . $statusLine . ', 响应: ' . $response);
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// 驳回CRP主题
function rejectCrpTopic($topicId, $rejectMsg) {
    if (!$topicId) {
        throw new Exception('主题ID不能为空');
    }
    
    if (empty($rejectMsg)) {
        throw new Exception('驳回原因不能为空');
    }
    
    try {
        // 第一步：获取workflow信息
        $workflowInfoUrl = CRP_API . "/topics/{$topicId}/workflow/info";
        $workflowResult = makeCrpApiRequest($workflowInfoUrl, 'GET');
        
        if (!$workflowResult['success']) {
            throw new Exception('获取workflow信息失败: ' . $workflowResult['message']);
        }
        
        $workflowData = $workflowResult['data'];
        if (!isset($workflowData['ID'])) {
            throw new Exception('workflow信息中未找到ID字段');
        }
        
        $workflowId = $workflowData['ID'];

        // 第二步：调用驳回接口
        $rejectUrl = CRP_API . '/topics/workflow/reject';
        $rejectData = [
            'id' => intval($workflowId),
            'topicid' => intval($topicId),
            'msg' => $rejectMsg
        ];
        
        // 记录调试信息
        error_log("驳回主题请求数据: " . json_encode($rejectData));
        
        $rejectResult = makeCrpApiRequest($rejectUrl, 'POST', $rejectData);
        
        if (!$rejectResult['success']) {
            throw new Exception('驳回主题失败: ' . $rejectResult['message']);
        }
        
        $rejectResponseData = $rejectResult['data'];
        if (!isset($rejectResponseData['status']) || $rejectResponseData['status'] !== 'success') {
            throw new Exception('驳回主题失败: ' . ($rejectResponseData['message'] ?? '未知错误'));
        }
        
        return [
            'success' => true,
            'message' => '主题驳回成功',
            'data' => [
                'workflow_id' => $workflowId,
                'topic_id' => $topicId
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// 修改CRP主题名称
function modifyCrpName($topicId, $topicName, $branchId) {
    if (!$topicId) {
        throw new Exception('主题ID不能为空');
    }
    
    if (empty($topicName)) {
        throw new Exception('主题名称不能为空');
    }
    
    if (empty($branchId)) {
        throw new Exception('分支ID不能为空');
    }
    
    $topicUrl = CRP_API . "/topics/{$topicId}";
    
    $requestData = [
        'ID' => intval($topicId),
        'Name' => $topicName,
        'NameB64' => base64_encode($topicName),
        'TpcType' => 'public',
        'Type' => 'test',
        'Description' => $topicName,
        'BranchID' => intval($branchId),
        'SyncID' => 0,
        'State' => 0,
        'Owner' => null,
        'Abandoned' => false,
        'Completed' => false,
        'Members' => null,
        'RepoType' => 'deb',
        'CopyId' => null
    ];
    
    error_log("修改CRP主题名称请求数据: " . json_encode($requestData));
    
    try {
        $result = makeCrpApiRequest($topicUrl, 'PUT', $requestData);
        
        return [
            'success' => true,
            'message' => '主题名称修改成功',
            'data' => $result['data']
        ];
    } catch (Exception $e) {
        error_log("修改CRP主题名称失败: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
} 