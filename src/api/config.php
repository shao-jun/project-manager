<?php
// 系统配置API接口

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/crp-api.php';
require_once __DIR__ . '/../lib/endpoints.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'user') {
                // 获取用户凭据信息
                $user = $db->fetchOne("SELECT username, created_at, updated_at FROM users ORDER BY id DESC LIMIT 1");
                
                echo json_encode([
                    'success' => true,
                    'data' => $user
                ]);
            } elseif ($action === 'websites') {
                // 获取网站配置
                $websites = $db->fetchAll("
                    SELECT id, name, homepage, token, token_url, token_obtained_at, is_active 
                    FROM website_configs 
                    WHERE is_active = 1 
                    ORDER BY name
                ");
                
                echo json_encode([
                    'success' => true,
                    'data' => $websites
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '无效的操作'
                ]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['action'])) {
                echo json_encode([
                    'success' => false,
                    'message' => '无效的请求数据'
                ]);
                break;
            }
            
            switch ($input['action']) {
                case 'save_user':
                    // 保存用户凭据
                    if (!isset($input['username']) || !isset($input['password'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => '用户名和密码不能为空'
                        ]);
                        break;
                    }
                    
                    $username = $input['username'];
                    $password = getCRPPassword($input['password']);
                    $plainPassword = $input['password']; // 保存明文密码用于API调用
                    
                    // 检查是否已存在用户
                    $existingUser = $db->fetchOne("SELECT id FROM users WHERE username = '?'", [$username]);
                    
                    if ($existingUser) {
                        // 更新现有用户
                        $result = $db->execute("
                            UPDATE users SET 
                                password = ?, 
                                plain_password = ?,
                                updated_at = CURRENT_TIMESTAMP 
                            WHERE username = ?
                        ", [$password, $plainPassword, $username]);
                    } else {
                        // 创建新用户
                        $result = $db->execute("
                            INSERT INTO users (username, password, plain_password) 
                            VALUES (?, ?, ?)
                        ", [$username, $password, $plainPassword]);
                    }
                    
                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? '用户凭据保存成功' : '用户凭据保存失败'
                    ]);
                    break;
                    
                case 'add_website':
                    // 新增网站配置
                    if (!isset($input['name']) || !isset($input['homepage']) || !isset($input['token_url'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => '网站名称、主页地址和Token地址不能为空'
                        ]);
                        break;
                    }
                    
                    $result = $db->execute("
                        INSERT INTO website_configs (name, homepage, token_url) 
                        VALUES (?, ?, ?)
                    ", [$input['name'], $input['homepage'], $input['token_url']]);
                    
                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? '网站配置添加成功' : '网站配置添加失败'
                    ]);
                    break;
                    
                case 'update_website':
                    // 更新网站配置
                    if (!isset($input['website_id']) || !isset($input['field']) || !isset($input['value'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => '参数不完整'
                        ]);
                        break;
                    }
                    
                    $allowedFields = ['homepage', 'token_url'];
                    if (!in_array($input['field'], $allowedFields)) {
                        echo json_encode([
                            'success' => false,
                            'message' => '不允许更新的字段'
                        ]);
                        break;
                    }
                    
                    $result = $db->execute("
                        UPDATE website_configs SET 
                            {$input['field']} = ?, 
                            updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?
                    ", [$input['value'], $input['website_id']]);
                    
                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? '网站配置更新成功' : '网站配置更新失败'
                    ]);
                    break;
                    
                case 'delete_website':
                    // 删除网站配置
                    if (!isset($input['website_id'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => '网站ID不能为空'
                        ]);
                        break;
                    }
                    
                    $result = $db->execute("
                        UPDATE website_configs SET 
                            is_active = 0, 
                            updated_at = CURRENT_TIMESTAMP 
                        WHERE id = ?
                    ", [$input['website_id']]);
                    
                    echo json_encode([
                        'success' => $result,
                        'message' => $result ? '网站配置删除成功' : '网站配置删除失败'
                    ]);
                    break;
                    
                case 'refresh_token':
                    // 刷新Token
                    if (!isset($input['website_id'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => '网站ID不能为空'
                        ]);
                        break;
                    }
                    
                    // 获取网站配置和用户凭据
                    $website = $db->fetchOne("SELECT * FROM website_configs WHERE id = ?", [$input['website_id']]);
                    $user = $db->fetchOne("SELECT username, plain_password FROM users ORDER BY id DESC LIMIT 1");
                    
                    if (!$website) {
                        echo json_encode([
                            'success' => false,
                            'message' => '网站配置不存在'
                        ]);
                        break;
                    }
                    
                    if (!$user) {
                        echo json_encode([
                            'success' => false,
                            'message' => '用户凭据未设置'
                        ]);
                        break;
                    }
                    
                    if (!$user['plain_password']) {
                        echo json_encode([
                            'success' => false,
                            'message' => '用户密码未设置，请重新保存用户凭据'
                        ]);
                        break;
                    }
                    
                    // 调用Token获取API
                    $tokenResult = getTokenFromAPI($website['token_url'], $user['username'], $user['plain_password']);
                    
                    if ($tokenResult['success']) {
                        // 更新Token到数据库
                        $updateResult = $db->execute("
                            UPDATE website_configs SET 
                                token = ?, 
                                token_obtained_at = CURRENT_TIMESTAMP,
                                updated_at = CURRENT_TIMESTAMP 
                            WHERE id = ?
                        ", [$tokenResult['token'], $input['website_id']]);
                        
                        echo json_encode([
                            'success' => $updateResult,
                            'message' => $updateResult ? 'Token刷新成功' : 'Token保存失败'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Token获取失败: ' . $tokenResult['message']
                        ]);
                    }
                    break;
                    
                case 'sync_branches':
                    if (!isset($input['website_id'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => '网站ID不能为空'
                        ]);
                        break;
                    }

                    $website = $db->fetch("SELECT name FROM website_configs WHERE id = ?", [$input['website_id']]);
                    if (!$website || $website['name'] !== 'CRP') {
                        echo json_encode([
                            'success' => false,
                            'message' => '仅CRP网站支持分支同步'
                        ]);
                        break;
                    }

                    try {
                        $result = makeCrpApiRequest(CRP_API . '/branches', 'GET');
                        $branchData = $result['data'];

                        if (!is_array($branchData)) {
                            echo json_encode([
                                'success' => false,
                                'message' => 'CRP接口返回数据格式错误'
                            ]);
                            break;
                        }

                        // 获取本地已有的crp_id列表
                        $existingRows = $db->fetchAll("SELECT crp_id FROM crp_branch");
                        $filterIds = array_map(function($row) { return (int)$row['crp_id']; }, $existingRows);

                        // 筛选出新增的分支（本地不存在的）
                        $newBranches = array_filter($branchData, function($item) use ($filterIds) {
                            return isset($item['ID']) && !in_array((int)$item['ID'], $filterIds);
                        });

                        $insertedCount = 0;
                        foreach ($newBranches as $branch) {
                            if (!isset($branch['ID']) || !isset($branch['Name'])) {
                                continue;
                            }
                            $db->execute("
                                INSERT INTO crp_branch (crp_id, name, description, repo_type, managers, is_check)
                                VALUES (?, ?, ?, ?, ?, ?)
                            ", [
                                (int)$branch['ID'],
                                trim($branch['Name']),
                                trim($branch['Description'] ?? ''),
                                trim($branch['RepoType'] ?? ''),
                                trim($branch['Managers'] ?? ''),
                                (int)($branch['IsCheck'] ?? 0)
                            ]);
                            $insertedCount++;
                        }

                        echo json_encode([
                            'success' => true,
                            'message' => "同步完成，新增{$insertedCount}个分支",
                            'data' => [
                                'total' => count($branchData),
                                'existing' => count($filterIds),
                                'inserted' => $insertedCount
                            ]
                        ]);
                    } catch (Exception $e) {
                        echo json_encode([
                            'success' => false,
                            'message' => '同步分支失败: ' . $e->getMessage()
                        ]);
                    }
                    break;

                default:
                    echo json_encode([
                        'success' => false,
                        'message' => '无效的操作'
                    ]);
                    break;
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => '不支持的请求方法'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '服务器错误: ' . $e->getMessage()
    ]);
}

// 从API获取Token的函数
function getTokenFromAPI($tokenUrl, $username, $password) {
    try {
        $postData = json_encode([
            'userName' => $username,
            'password' => $password
        ]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $postData,
                'timeout' => 30
            ]
        ]);
        
        $response = file_get_contents($tokenUrl, false, $context);
        
        if ($response === false) {
            return [
                'success' => false,
                'message' => '无法连接到Token服务'
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if (isset($responseData['token'])) {
            return [
                'success' => true,
                'token' => $responseData['token']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Token响应格式错误'
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => '获取Token时发生错误: ' . $e->getMessage()
        ];
    }
}

// CRP密码处理函数
function getCRPPassword($password) {
    // RSA公钥 PEM 格式字符串
    $pubKeyPem = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCkA9WqirWQII3D8/M9UG8X8ybQ
Ou+cPSNTgR9b4HenJ7A5zSfkXZnetb5q6MmKTJLGCl9MSsHveQPHmLGDG+xw2MlB
w3Yefd/jJ1Cg8pP69wlHRX+wiyh5p8KY55ehFNsQLm3kDGXgVJdtrZn/MiBOlCtE
fe9YvvT0lqy2BtBpaQIDAQAB
-----END PUBLIC KEY-----";
    
    // 检查密码是否为空
    if (empty($password)) {
        return false;
    }
    
    // 加载公钥
    $publicKey = openssl_pkey_get_public($pubKeyPem);
    if (!$publicKey) {
        throw new Exception('无法加载RSA公钥');
    }
    
    // 用公钥加密密码
    $encrypted = '';
    $success = openssl_public_encrypt($password, $encrypted, $publicKey);
    
    // 释放公钥资源
    openssl_pkey_free($publicKey);
    
    if (!$success) {
        throw new Exception('RSA加密失败');
    }
    
    // 转换为base64编码
    $cipherBase64 = base64_encode($encrypted);
    
    return $cipherBase64;
}