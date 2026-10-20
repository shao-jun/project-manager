<?php
/**
 * CRP分支同步API接口
 * 用于从CRP系统同步分支数据到本地数据库
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/endpoints.php';

/**
 * 发起HTTP请求
 * @param string $url 请求URL
 * @param string $method 请求方法
 * @param array $headers 请求头
 * @param mixed $data 请求数据
 * @return array
 */
function makeHttpRequest($url, $method = 'GET', $headers = [], $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? json_encode($data) : $data);
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
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
            'data' => null
        ];
    }
    
    $jsonData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'message' => 'JSON解析错误: ' . json_last_error_msg(),
            'data' => null
        ];
    }
    
    return [
        'success' => true,
        'message' => 'HTTP请求成功',
        'data' => $jsonData
    ];
}

/**
 * 同步CRP分支数据
 * @return array 包含success、message、data的数组
 */
function syncCrpBranches() {
    try {
        $db = Database::getInstance();
        
        // 获取CRP分支数据
        $crpUrl = CRP_API . '/branches';
        $response = makeHttpRequest($crpUrl, 'GET');
        
        if (!$response['success']) {
            return [
                'success' => false,
                'message' => 'CRP接口调用失败: ' . $response['message']
            ];
        }
        
        $branchData = $response['data'];
        if (!is_array($branchData)) {
            return [
                'success' => false,
                'message' => 'CRP接口返回数据格式错误'
            ];
        }
        
        $insertedCount = 0;
        $updatedCount = 0;
        $errorCount = 0;
        
        foreach ($branchData as $branch) {
            try {
                // 验证必要字段
                if (!isset($branch['ID']) || !isset($branch['Name'])) {
                    error_log("CRP分支数据缺少必要字段: " . json_encode($branch));
                    $errorCount++;
                    continue;
                }
                
                $crpId = (int)$branch['ID'];
                $name = trim($branch['Name']);
                $description = trim($branch['Description'] ?? '');
                $repoType = trim($branch['RepoType'] ?? '');
                $managers = trim($branch['Managers'] ?? '');
                $isCheck = (int)($branch['IsCheck'] ?? 0);
                
                // 检查记录是否已存在
                $existingRecord = $db->fetch(
                    "SELECT id FROM crp_branch WHERE crp_id = ?", 
                    [$crpId]
                );
                
                if ($existingRecord) {
                    // 更新现有记录
                    $db->execute("
                        UPDATE crp_branch SET 
                            name = ?, 
                            description = ?, 
                            repo_type = ?, 
                            managers = ?, 
                            is_check = ?,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE crp_id = ?
                    ", [$name, $description, $repoType, $managers, $isCheck, $crpId]);
                    $updatedCount++;
                } else {
                    // 插入新记录
                    $db->execute("
                        INSERT INTO crp_branch (crp_id, name, description, repo_type, managers, is_check)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ", [$crpId, $name, $description, $repoType, $managers, $isCheck]);
                    $insertedCount++;
                }
                
            } catch (Exception $e) {
                error_log("处理CRP分支记录异常 [ID: {$branch['ID']}]: " . $e->getMessage());
                $errorCount++;
            }
        }
        
        return [
            'success' => true,
            'message' => "CRP分支同步完成",
            'data' => [
                'total' => count($branchData),
                'inserted' => $insertedCount,
                'updated' => $updatedCount,
                'errors' => $errorCount
            ]
        ];
        
    } catch (Exception $e) {
        error_log("同步CRP分支异常: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '同步失败: ' . $e->getMessage()
        ];
    }
}

/**
 * 获取本地CRP分支数据
 * @param array $params 查询参数
 * @return array
 */
function getCrpBranches($params = []) {
    try {
        $db = Database::getInstance();
        
        $whereConditions = [];
        $whereParams = [];
        
        // 按名称筛选
        if (!empty($params['name'])) {
            $whereConditions[] = "name LIKE ?";
            $whereParams[] = '%' . $params['name'] . '%';
        }
        
        // 按检查标志筛选
        if (isset($params['is_check'])) {
            $whereConditions[] = "is_check = ?";
            $whereParams[] = (int)$params['is_check'];
        }
        
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $sql = "
            SELECT 
                id, crp_id, name, description, repo_type, managers, is_check,
                created_at, updated_at
            FROM crp_branch 
            {$whereClause}
            ORDER BY name ASC
        ";
        
        $branches = $db->fetchAll($sql, $whereParams);
        
        return [
            'success' => true,
            'data' => $branches,
            'total' => count($branches)
        ];
        
    } catch (Exception $e) {
        error_log("获取CRP分支数据异常: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '获取分支数据失败: ' . $e->getMessage()
        ];
    }
}

// 处理API请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'sync':
            // 同步CRP分支数据
            $result = syncCrpBranches();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;
            
        case 'list':
            // 获取本地分支数据
            $params = $_GET;
            unset($params['action']);
            $result = getCrpBranches($params);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => '不支持的操作'
            ], JSON_UNESCAPED_UNICODE);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => '不支持的请求方法'
    ], JSON_UNESCAPED_UNICODE);
}
?> 