<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 只require一次数据库配置
$dbConfig = require __DIR__ . '/../config/database.php';
try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '数据库连接失败: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// 引入HTTP客户端
require_once 'http_client.php';

/**
 * 获取所有网站配置
 */
function getWebsiteConfigs() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, homepage, token, token_url, username, password, 
                   password_encrypted, token_obtained_at, is_active, created_at, updated_at
            FROM website_configs 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 隐藏密码字段，只返回加密版本
        foreach ($configs as &$config) {
            unset($config['password']); // 不返回明文密码
        }
        
        return ['success' => true, 'data' => $configs];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => '数据库查询失败: ' . $e->getMessage()];
    }
}

/**
 * 获取单个网站配置
 */
function getWebsiteConfig($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, homepage, token, token_url, username, password, 
                   password_encrypted, token_obtained_at, is_active, created_at, updated_at
            FROM website_configs 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$config) {
            return ['success' => false, 'error' => '配置不存在'];
        }
        
        // 隐藏明文密码
        unset($config['password']);
        
        return ['success' => true, 'data' => $config];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => '数据库查询失败: ' . $e->getMessage()];
    }
}

/**
 * 创建网站配置
 */
function createWebsiteConfig($data) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO website_configs (name, homepage, token_url, username, password, password_encrypted, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $passwordEncrypted = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->execute([
            $data['name'],
            $data['homepage'],
            $data['token_url'],
            $data['username'] ?? null,
            $data['password'] ?? null,
            $passwordEncrypted,
            $data['is_active'] ?? 1
        ]);
        
        return ['success' => true, 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => '创建配置失败: ' . $e->getMessage()];
    }
}

/**
 * 更新网站配置
 */
function updateWebsiteConfig($id, $data) {
    global $pdo;
    
    try {
        $updateFields = [];
        $params = [];
        
        if (isset($data['name'])) {
            $updateFields[] = 'name = ?';
            $params[] = $data['name'];
        }
        if (isset($data['homepage'])) {
            $updateFields[] = 'homepage = ?';
            $params[] = $data['homepage'];
        }
        if (isset($data['token_url'])) {
            $updateFields[] = 'token_url = ?';
            $params[] = $data['token_url'];
        }
        if (isset($data['username'])) {
            $updateFields[] = 'username = ?';
            $params[] = $data['username'];
        }
        if (isset($data['password'])) {
            $updateFields[] = 'password = ?';
            $updateFields[] = 'password_encrypted = ?';
            $params[] = $data['password'];
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['is_active'])) {
            $updateFields[] = 'is_active = ?';
            $params[] = $data['is_active'];
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'error' => '没有要更新的字段'];
        }
        
        $params[] = $id;
        $sql = "UPDATE website_configs SET " . implode(', ', $updateFields) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'error' => '配置不存在或没有变化'];
        }
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => '更新配置失败: ' . $e->getMessage()];
    }
}

/**
 * 删除网站配置
 */
function deleteWebsiteConfig($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM website_configs WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'error' => '配置不存在'];
        }
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => '删除配置失败: ' . $e->getMessage()];
    }
}

/**
 * 获取token
 */
function getToken($id) {
    global $pdo;
    
    try {
        // 获取配置信息
        $stmt = $pdo->prepare("
            SELECT name, token_url, username, password
            FROM website_configs 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$config) {
            return ['success' => false, 'error' => '配置不存在'];
        }
        
        if (empty($config['username']) || empty($config['password'])) {
            return ['success' => false, 'error' => '用户名或密码未设置'];
        }
        
        // 调用HTTP客户端获取token
        $requestData = [
            'url' => $config['token_url'],
            'method' => 'POST',
            'data' => [
                'username' => $config['username'],
                'password' => $config['password']
            ]
        ];
        
        $result = makeHttpRequest(
            $requestData['url'],
            $requestData['method'],
            [],
            $requestData['data']
        );
        
        if ($result['success']) {
            // 更新数据库中的token
            $token = $result['data']['token'] ?? $result['data']['access_token'] ?? $result['raw_response'];
            $updateStmt = $pdo->prepare("
                UPDATE website_configs 
                SET token = ?, token_obtained_at = NOW() 
                WHERE id = ?
            ");
            $updateStmt->execute([$token, $id]);
            
            return [
                'success' => true,
                'data' => [
                    'token' => $token,
                    'config_name' => $config['name'],
                    'obtained_at' => date('Y-m-d H:i:s')
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => '获取token失败: ' . ($result['error'] ?? '未知错误'),
                'http_code' => $result['http_code'] ?? null
            ];
        }
        
    } catch (PDOException $e) {
        return ['success' => false, 'error' => '数据库操作失败: ' . $e->getMessage()];
    }
}

// 处理请求
$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim($path, '/'));

// 获取最后一个路径段作为ID
$id = null;
if (count($pathParts) > 0) {
    $lastPart = end($pathParts);
    if (is_numeric($lastPart)) {
        $id = (int)$lastPart;
    }
}

// 检查是否是token获取请求
$isTokenRequest = false;
if (strpos($path, '/token') !== false) {
    $isTokenRequest = true;
    $id = (int)basename(str_replace('/token', '', $path));
}

try {
    switch ($requestMethod) {
        case 'GET':
            if ($id) {
                if ($isTokenRequest) {
                    $result = getToken($id);
                } else {
                    $result = getWebsiteConfig($id);
                }
            } else {
                $result = getWebsiteConfigs();
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if ($isTokenRequest) {
                $result = getToken($id);
            } else {
                $result = createWebsiteConfig($input);
            }
            break;
            
        case 'PUT':
            if (!$id) {
                $result = ['success' => false, 'error' => '缺少ID参数'];
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $result = updateWebsiteConfig($id, $input);
            break;
            
        case 'DELETE':
            if (!$id) {
                $result = ['success' => false, 'error' => '缺少ID参数'];
                break;
            }
            $result = deleteWebsiteConfig($id);
            break;
            
        default:
            $result = ['success' => false, 'error' => '不支持的请求方法'];
    }
    
    if (!$result['success']) {
        http_response_code(400);
    }
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '服务器内部错误: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?> 