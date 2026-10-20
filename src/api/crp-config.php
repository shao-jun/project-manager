<?php
require_once __DIR__ . '/../lib/endpoints.php';
/**
 * CRP配置管理API
 * 用于读取和保存CRP系统的配置信息
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 读取：默认配置 + 本地私有配置（crp_config.php 已合并 local）
$configFile = __DIR__ . '/../config/crp_config.php';
// 保存：JWT token 等敏感项写入本地私有文件（已被 .gitignore 忽略）
$localConfigFile = __DIR__ . '/../config/crp_config.local.php';

/**
 * 读取配置文件
 */
function readConfig($configFile) {
    if (!file_exists($configFile)) {
        return [
            'jwt_token' => '',
            'base_url' => CRP_API,
            'timeout' => 30,
            'verify_ssl' => false,
            'default_topic' => [
                'TpcType' => 'public',
                'Type' => 'test',
                'Owner' => getenv('CRP_DEFAULT_OWNER') ?: 'owner1;owner2',
                'RepoType' => 'deb'
            ]
        ];
    }
    
    return require $configFile;
}

/**
 * 保存配置文件
 */
function saveConfig($configFile, $config) {
    $content = "<?php\n/**\n * CRP API 本地私有配置（请勿提交，已在 .gitignore 中忽略）\n * 由 CRP 配置页面自动生成\n */\n\nreturn " . var_export($config, true) . ";\n";
    
    return file_put_contents($configFile, $content) !== false;
}

/**
 * 测试CRP连接
 */
function testCrpConnection($jwtToken) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, CRP_API . '/branches');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $jwtToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false || !empty($error)) {
        return [
            'success' => false,
            'message' => 'HTTP请求失败: ' . $error
        ];
    }
    
    if ($httpCode === 200) {
        return [
            'success' => true,
            'message' => 'JWT Token有效，连接成功'
        ];
    } else {
        $jsonData = json_decode($response, true);
        $message = $jsonData['message'] ?? "HTTP错误: $httpCode";
        
        return [
            'success' => false,
            'message' => $message
        ];
    }
}

// 处理请求
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // 读取配置
        $config = readConfig($configFile);
        
        echo json_encode([
            'success' => true,
            'message' => '配置读取成功',
            'data' => $config
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('无效的JSON数据');
        }
        
        if (isset($input['action']) && $input['action'] === 'test') {
            // 测试连接
            $jwtToken = $input['jwt_token'] ?? '';
            
            if (!$jwtToken) {
                throw new Exception('JWT Token不能为空');
            }
            
            $result = testCrpConnection($jwtToken);
            
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
            
        } else {
            // 保存配置
            $config = [
                'jwt_token' => $input['jwt_token'] ?? '',
                'base_url' => $input['base_url'] ?? CRP_API,
                'timeout' => (int)($input['timeout'] ?? 30),
                'verify_ssl' => (bool)($input['verify_ssl'] ?? false),
                'default_topic' => $input['default_topic'] ?? [
                    'TpcType' => 'public',
                    'Type' => 'test',
                    'Owner' => getenv('CRP_DEFAULT_OWNER') ?: 'owner1;owner2',
                    'RepoType' => 'deb'
                ]
            ];
            
            if (!$config['jwt_token']) {
                throw new Exception('JWT Token不能为空');
            }
            
            if (saveConfig($localConfigFile, $config)) {
                echo json_encode([
                    'success' => true,
                    'message' => '配置保存成功'
                ]);
            } else {
                throw new Exception('配置文件写入失败，请检查文件权限');
            }
        }
        
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => '不支持的请求方法'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 