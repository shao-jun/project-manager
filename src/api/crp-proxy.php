<?php
/**
 * CRP API代理接口
 * 用于代理调用CRP系统的API，解决跨域问题
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/crp-api.php';

// 加载CRP配置（备用静态配置）
$crpConfig = require_once __DIR__ . '/../config/crp_config.php';

// header('Content-Type: application/json; charset=utf-8');
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

/**
 * 发起HTTP请求到CRP API
 */
function makeCrpRequest($url, $method = 'GET', $data = null, $crpConfig = null) {
    // 初始化数据库连接（用于获取动态token）
    global $db;
    if (!isset($db)) {
        $db = Database::getInstance();
    }
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $crpConfig['timeout'] ?? 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $crpConfig['verify_ssl'] ?? false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $crpConfig['verify_ssl'] ?? false ? 2 : 0);
    
    // 设置HTTP头，包含JWT认证
    $headers = ['Content-Type: application/json'];
    
    // 优先使用数据库中的动态token，回退到静态配置
    $jwtToken = null;
    try {
        // 尝试获取数据库中的有效token
        $jwtToken = getValidCrpToken();
        error_log("使用数据库动态token进行CRP API认证");
    } catch (Exception $e) {
        // 如果数据库token不可用，使用静态配置
        error_log("数据库token不可用，使用静态配置token: " . $e->getMessage());
        $jwtToken = $crpConfig['jwt_token'] ?? null;
    }
    
    // 添加JWT认证头
    if (!empty($jwtToken)) {
        $headers[] = 'Authorization: Bearer ' . $jwtToken;
    } else {
        error_log("警告：没有可用的JWT token进行CRP API认证");
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Length: ' . strlen($data);
        }
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false || !empty($error)) {
        return [
            'success' => false,
            'message' => 'HTTP请求失败: ' . $error,
            'data' => null
        ];
    }
    
    if ($httpCode >= 400) {
        return [
            'success' => false,
            'message' => 'HTTP错误: ' . $httpCode,
            'data' => null,
            'response' => $response
        ];
    }
    
    $jsonData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'JSON解析错误: ' . json_last_error_msg(),
            'data' => null,
            'response' => $response
        ];
    }
    
    return [
        'success' => true,
        'message' => 'CRP API调用成功',
        'data' => $jsonData
    ];
}

// 处理API请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create_topic':
                // 创建CRP主题
                $projectId = $input['project_id'] ?? 0;
                $topicName = $input['topic_name'] ?? '';
                
                if (!$projectId || !$topicName) {
                    throw new Exception('项目ID和主题名称不能为空');
                }
                
                // 获取项目的CRP分支信息
                $db = Database::getInstance();
                $project = $db->fetch("SELECT crp_branch_id, crp_branch_name FROM projects WHERE id = ?", [$projectId]);
                
                if (!$project) {
                    throw new Exception('项目不存在');
                }
                
                $crpBranchId = $project['crp_branch_id'];
                $crpBranchName = $project['crp_branch_name'];
                
                if (!$crpBranchId || !$crpBranchName) {
                    throw new Exception('项目未设置CRP分支，请先在项目设置中选择打包分支');
                }
                
                // 检查JWT配置
                if (empty($crpConfig['jwt_token'])) {
                    throw new Exception('CRP API需要JWT认证，请配置jwt_token。您可以在src/config/crp_config.php中设置JWT token');
                }
                
                // 调用CRP API创建主题
                $crpUrl = "{$crpConfig['base_url']}/branches/{$crpBranchId}/topics";
                $defaultTopic = $crpConfig['default_topic'];
                
                $topicData = [
                    "ID" => null,
                    "Name" => $topicName,
                    "TpcType" => $defaultTopic['TpcType'],
                    "Description" => $topicName,
                    "Type" => $defaultTopic['Type'],
                    "NameB64" => null,
                    "BranchID" => (int)$crpBranchId,
                    "SyncID" => 0,
                    "State" => 0,
                    "Owner" => $defaultTopic['Owner'],
                    "Abandoned" => false,
                    "Completed" => false,
                    "Members" => null,
                    "RepoType" => $defaultTopic['RepoType'],
                    "CopyId" => null
                ];
                
                $result = makeCrpRequest($crpUrl, 'POST', json_encode($topicData), $crpConfig);
                
                if ($result['success']) {
                    $topicInfo = $result['data'];
                    
                    // 记录调试信息
                    error_log("CRP API返回的原始数据: " . json_encode($topicInfo, JSON_UNESCAPED_UNICODE));
                    
                    $topicId = null;
                    
                    // 情况1：CRP API直接返回主题ID（数字）
                    if (is_numeric($topicInfo)) {
                        $topicId = (string)$topicInfo;
                        error_log("CRP API直接返回主题ID: {$topicId}");
                    }
                    // 情况2：返回的是数组/对象，需要查找ID字段
                    elseif (is_array($topicInfo)) {
                        $possibleIdFields = ['ID', 'id', 'topicId', 'topic_id', 'TopicID', 'TpcID'];
                        
                        // 在顶层查找ID字段
                        foreach ($possibleIdFields as $field) {
                            if (isset($topicInfo[$field]) && !empty($topicInfo[$field])) {
                                $topicId = $topicInfo[$field];
                                error_log("找到主题ID字段: {$field} = {$topicId}");
                                break;
                            }
                        }
                        
                        // 如果是嵌套结构，尝试在子对象中查找
                        if (!$topicId) {
                            foreach ($topicInfo as $key => $value) {
                                if (is_array($value)) {
                                    foreach ($possibleIdFields as $field) {
                                        if (isset($value[$field]) && !empty($value[$field])) {
                                            $topicId = $value[$field];
                                            error_log("在子对象 {$key} 中找到主题ID字段: {$field} = {$topicId}");
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    if ($topicId) {
                        echo json_encode([
                            'success' => true,
                            'message' => '主题创建成功',
                            'data' => [
                                'topic_id' => $topicId,
                                'topic_name' => $topicName,
                                'crp_branch_id' => $crpBranchId,
                                'crp_branch_name' => $crpBranchName
                            ]
                        ]);
                    } else {
                        // 构建调试信息，避免array_keys错误
                        $debugInfo = [
                            'returned_data' => $topicInfo,
                            'data_type' => gettype($topicInfo)
                        ];
                        
                        if (is_array($topicInfo)) {
                            $debugInfo['data_structure'] = array_keys($topicInfo);
                            $debugInfo['searched_fields'] = $possibleIdFields ?? [];
                        }
                        
                        echo json_encode([
                            'success' => false,
                            'message' => 'CRP API返回数据中未找到主题ID',
                            'debug' => $debugInfo
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'CRP API调用失败: ' . $result['message'],
                        'debug' => $result
                    ]);
                }
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => '不支持的操作'
                ]);
                break;
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => '不支持的请求方法'
    ]);
}
?> 