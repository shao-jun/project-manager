<?php
// Shuttle API端点 - 处理刷新打包状态的请求

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/shuttle-api.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('无效的请求数据');
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'refresh_build_status':
            // 刷新构建状态
            $taskId = $input['task_id'] ?? '';
            $packageId = $input['package_id'] ?? '';
            
            if (empty($taskId)) {
                throw new Exception('缺少task_id参数');
            }
            
            if (empty($packageId)) {
                throw new Exception('缺少package_id参数');
            }
            
            // 获取最新的构建状态
            $result = refreshBuildStatus($taskId);
            
            if ($result['success']) {
                // 更新数据库中的构建状态
                $buildStatus = $result['status'];
                $taskInfo = $result['task_info'];
                
                // 将Shuttle状态映射到本地状态
                $localStatus = mapShuttleStatusToLocal($buildStatus);
                
                // 更新数据库
                global $db;
                $db->execute(
                    "UPDATE deliver_package SET build_state = ? WHERE id = ?", 
                    [$buildStatus, $packageId]
                );
                
                echo json_encode([
                    'success' => true,
                    'message' => '构建状态刷新成功',
                    'data' => [
                        'original_status' => $buildStatus,
                        'local_status' => $localStatus,
                        'task_info' => $taskInfo
                    ]
                ]);
            } else {
                throw new Exception('刷新构建状态失败');
            }
            break;
            
        default:
            throw new Exception('不支持的操作: ' . $action);
    }
    
} catch (Exception $e) {
    error_log('Shuttle API错误: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 将Shuttle状态映射到本地状态
function mapShuttleStatusToLocal($shuttleStatus) {
    $statusMap = [
        'UPLOAD_OK' => 'success',      // 上传成功
        'BUILD_OK' => 'success',       // 构建成功
        'BUILDING' => 'building',      // 构建中
        'BUILD_FAILED' => 'failed',    // 构建失败
        'UPLOAD_FAILED' => 'failed',   // 上传失败
        'PENDING' => 'pending',        // 等待中
        'CANCELED' => 'canceled',      // 已取消
        'TIMEOUT' => 'timeout',        // 超时
    ];
    
    return $statusMap[$shuttleStatus] ?? 'unknown';
}
?>