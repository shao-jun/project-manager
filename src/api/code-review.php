<?php
// 代码审查配置API接口

// 抑制错误输出，确保只输出JSON
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once __DIR__ . '/../config/database.php';

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
            if ($action === 'get') {
                // 获取代码审查配置
                $config = $db->fetch("SELECT * FROM code_review_configs ORDER BY id DESC LIMIT 1");
                
                if (!$config) {
                    // 如果没有配置，返回默认配置
                    $config = [
                        'auto_scoring_enabled' => 1,
                        'cherry_pick_score' => 2,
                        'no_conflict_score' => 2,
                        'scoring_trigger' => 'cherry_pick_and_no_conflict',
                        'required_file_types' => '.js,.php,.py,.java,.cpp,.c,.h,.hpp',
                        'skip_review_dirs' => 'node_modules,vendor,dist,build',
                        'min_reviewers' => 2,
                        'force_review_large_files' => 1
                    ];
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $config
                ]);
            } elseif ($action === 'get_developers') {
                // 获取开发者用户列表
                $developers = $db->fetchAll("SELECT * FROM develope_users ORDER BY created_at DESC");
                
                echo json_encode([
                    'success' => true,
                    'data' => $developers
                ]);
            } elseif ($action === 'get_service_status') {
                // 获取当前运行的服务状态
                $runningService = $db->fetch("
                    SELECT service_id, reviewer, branch, threshold, status, started_at 
                    FROM code_review_services 
                    WHERE status = 'running' 
                    ORDER BY started_at DESC 
                    LIMIT 1
                ");
                
                if ($runningService) {
                    echo json_encode([
                        'success' => true,
                        'data' => $runningService
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'data' => null
                    ]);
                }
            } elseif ($action === 'get_service_logs') {
                // 获取后台服务的日志
                $serviceId = $_GET['service_id'] ?? '';
                
                if (empty($serviceId)) {
                    echo json_encode([
                        'success' => false,
                        'message' => '缺少服务ID参数'
                    ]);
                    break;
                }
                
                $logFile = __DIR__ . "/../tmp/code-review-service-{$serviceId}.log";
                $logFileIndex = $_GET['log_file'] ?? '0'; // 0表示当前日志，1,2,3表示历史日志
                
                // 确定要读取的日志文件
                if ($logFileIndex === '0') {
                    $targetLogFile = $logFile;
                } else {
                    $targetLogFile = $logFile . '.' . $logFileIndex;
                }
                
                if (!file_exists($targetLogFile)) {
                    echo json_encode([
                        'success' => false,
                        'message' => '日志文件不存在'
                    ]);
                    break;
                }
                
                // 读取日志文件内容
                $logContent = file_get_contents($targetLogFile);
                $lines = explode("\n", $logContent);
                
                // 获取最后N行日志（默认100行）
                $limit = intval($_GET['limit'] ?? 100);
                $lastLines = array_slice($lines, -$limit);
                
                // 过滤空行
                $lastLines = array_filter($lastLines, function($line) {
                    return !empty(trim($line));
                });
                
                // 检查可用的日志文件
                $availableLogFiles = [];
                for ($i = 0; $i <= 3; $i++) {
                    $checkFile = ($i === 0) ? $logFile : $logFile . '.' . $i;
                    if (file_exists($checkFile)) {
                        $fileSize = filesize($checkFile);
                        $availableLogFiles[] = [
                            'index' => $i,
                            'name' => $i === 0 ? '当前日志' : "历史日志 {$i}",
                            'size' => $fileSize,
                            'size_formatted' => formatFileSize($fileSize)
                        ];
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'service_id' => $serviceId,
                        'log_file' => $targetLogFile,
                        'log_file_index' => $logFileIndex,
                        'total_lines' => count($lines),
                        'returned_lines' => count($lastLines),
                        'available_log_files' => $availableLogFiles,
                        'logs' => array_values($lastLines)
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '无效的操作'
                ]);
            }
            break;
            
        case 'POST':
            if ($action === 'add_developer') {
                // 新增开发者用户
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || !isset($input['username']) || !isset($input['email'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => '缺少必要字段：用户名和邮箱'
                    ]);
                    break;
                }
                
                // 检查用户名和邮箱是否已存在
                $existingUser = $db->fetch("SELECT id FROM develope_users WHERE username = ? OR email = ?", [$input['username'], $input['email']]);
                if ($existingUser) {
                    echo json_encode([
                        'success' => false,
                        'message' => '用户名或邮箱已存在'
                    ]);
                    break;
                }
                
                $result = $db->execute("
                    INSERT INTO develope_users (username, email, status) 
                    VALUES (?, ?, ?)
                ", [
                    $input['username'],
                    $input['email'],
                    $input['status'] ?? 1
                ]);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => '开发者添加成功'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => '开发者添加失败'
                    ]);
                }
            } elseif ($action === 'start_service') {
                // 启动代码审查服务
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    echo json_encode([
                        'success' => false,
                        'message' => '无效的请求数据'
                    ]);
                    break;
                }
                
                try {
                    // 生成唯一的服务ID
                    $serviceId = uniqid('review_', true);
                    
                    // 启动后台审查服务进程
                    // 使用nohup让服务在后台运行，避免阻塞
                    // 将日志输出到文件，方便主服务读取
                    $logFile = __DIR__ . "/../tmp/code-review-service-{$serviceId}.log";
                    $command = "nohup php " . __DIR__ . "/code-review-service.php " . escapeshellarg($serviceId) . " > " . escapeshellarg($logFile) . " 2>&1 &";
                    $output = [];
                    $returnCode = 0;
                    
                    exec($command, $output, $returnCode);

                    if ($returnCode === 0) {
                        // 记录服务启动信息到数据库
                        $db->execute("
                            INSERT INTO code_review_services (
                                service_id, reviewer, branch, threshold, status, started_at
                            ) VALUES (?, ?, ?, ?, 'running', CURRENT_TIMESTAMP)
                        ", [
                            $serviceId,
                            $input['reviewer'] ?? '系统',
                            $input['branch'] ?? 'main',
                            $input['threshold'] ?? 80
                        ]);
                        

                        echo json_encode([
                            'success' => true,
                            'message' => '代码审查服务启动成功',
                            'data' => [
                                'service_id' => $serviceId,
                                'status' => 'running'
                            ]
                        ]);
                    } else {
                        throw new Exception('启动后台进程失败');
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => '启动服务失败：' . $e->getMessage()
                    ]);
                }
            } elseif ($action === 'stop_service') {
                // 停止代码审查服务
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || !isset($input['service_id'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => '缺少服务ID'
                    ]);
                    break;
                }
                
                try {
                    // 停止后台审查服务进程
                    $pidFile = __DIR__ . "/../tmp/code-review-service-{$input['service_id']}.pid";
                    
                    if (file_exists($pidFile)) {
                        $pid = trim(file_get_contents($pidFile));
                        if ($pid && is_numeric($pid)) {
                            // 终止进程
                            exec("kill $pid 2>/dev/null");
                        }
                        // 删除PID文件
                        unlink($pidFile);
                    }
                    
                    // 更新服务状态到数据库
                    $db->execute("
                        UPDATE code_review_services SET 
                            status = 'stopped', 
                            stopped_at = CURRENT_TIMESTAMP
                        WHERE service_id = ?
                    ", [$input['service_id']]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => '代码审查服务停止成功',
                        'data' => [
                            'service_id' => $input['service_id'],
                            'status' => 'stopped'
                        ]
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => '停止服务失败：' . $e->getMessage()
                    ]);
                }
            } else {
                // 保存或更新代码审查配置
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input) {
                    echo json_encode([
                        'success' => false,
                        'message' => '无效的请求数据'
                    ]);
                    break;
                }
                
                // 验证必要字段
                $requiredFields = ['auto_scoring_enabled', 'cherry_pick_score', 'no_conflict_score', 'scoring_trigger', 'min_reviewers', 'force_review_large_files'];
                foreach ($requiredFields as $field) {
                    if (!isset($input[$field])) {
                        echo json_encode([
                            'success' => false,
                            'message' => "缺少必要字段: $field"
                        ]);
                        break 2;
                    }
                }
                
                // 检查是否已存在配置
                $existingConfig = $db->fetch("SELECT id FROM code_review_configs ORDER BY id DESC LIMIT 1");
                
                if ($existingConfig) {
                    // 更新现有配置
                    $result = $db->execute("
                        UPDATE code_review_configs SET 
                            auto_scoring_enabled = ?,
                            cherry_pick_score = ?,
                            no_conflict_score = ?,
                            scoring_trigger = ?,
                            required_file_types = ?,
                            skip_review_dirs = ?,
                            min_reviewers = ?,
                            force_review_large_files = ?,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ", [
                        $input['auto_scoring_enabled'],
                        $input['cherry_pick_score'],
                        $input['no_conflict_score'],
                        $input['scoring_trigger'],
                        $input['required_file_types'] ?? '',
                        $input['skip_review_dirs'] ?? '',
                        $input['min_reviewers'],
                        $input['force_review_large_files'],
                        $existingConfig['id']
                    ]);
                } else {
                    // 创建新配置
                    $result = $db->execute("
                        INSERT INTO code_review_configs (
                            auto_scoring_enabled, cherry_pick_score, no_conflict_score, 
                            scoring_trigger, required_file_types, skip_review_dirs, 
                            min_reviewers, force_review_large_files
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ", [
                        $input['auto_scoring_enabled'],
                        $input['cherry_pick_score'],
                        $input['no_conflict_score'],
                        $input['scoring_trigger'],
                        $input['required_file_types'] ?? '',
                        $input['skip_review_dirs'] ?? '',
                        $input['min_reviewers'],
                        $input['force_review_large_files']
                    ]);
                }
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => '配置保存成功'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => '配置保存失败'
                    ]);
                }
            }
            break;
            
        case 'PUT':
            if ($action === 'update_developer') {
                // 更新开发者用户
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || !isset($input['id']) || !isset($input['username']) || !isset($input['email'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => '缺少必要字段：ID、用户名和邮箱'
                    ]);
                    break;
                }
                
                // 检查用户名和邮箱是否已被其他用户使用
                $existingUser = $db->fetch("SELECT id FROM develope_users WHERE (username = ? OR email = ?) AND id != ?", [$input['username'], $input['email'], $input['id']]);
                if ($existingUser) {
                    echo json_encode([
                        'success' => false,
                        'message' => '用户名或邮箱已被其他用户使用'
                    ]);
                    break;
                }
                
                $result = $db->execute("
                    UPDATE develope_users SET 
                        username = ?, 
                        email = ?, 
                        status = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ", [
                    $input['username'],
                    $input['email'],
                    $input['status'] ?? 1,
                    $input['id']
                ]);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => '开发者更新成功'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => '开发者更新失败'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '无效的操作'
                ]);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete_developer') {
                // 删除开发者用户
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || !isset($input['id'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => '缺少必要字段：ID'
                    ]);
                    break;
                }
                
                $result = $db->execute("DELETE FROM develope_users WHERE id = ?", [$input['id']]);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => '开发者删除成功'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => '开发者删除失败'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '无效的操作'
                ]);
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

/**
 * 格式化文件大小
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// 清理输出缓冲区，确保只输出JSON
ob_end_flush();