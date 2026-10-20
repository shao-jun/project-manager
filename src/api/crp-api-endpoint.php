<?php
// CRP API HTTP入口文件

require_once __DIR__ . '/crp-api.php';

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
            if ($action === 'releases') {
                $topicId = $_GET['topic_id'] ?? null;
                $result = getCrpReleases($topicId);
                echo json_encode($result);
            } elseif ($action === 'topic_detail') {
                $topicId = $_GET['topic_id'] ?? null;
                $result = getCrpTopicDetail($topicId);
                echo json_encode($result);
            } elseif ($action === 'topic_urls') {
                $topicId = $_GET['topic_id'] ?? null;
                $branchId = $_GET['branch_id'] ?? null;
                
                if (!$topicId) {
                    throw new Exception('主题ID不能为空');
                }
                
                if (!$branchId) {
                    throw new Exception('分支ID不能为空');
                }
                
                $result = getCrpTopicUrls($topicId, $branchId);
                // echo json_encode($result);
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
                'message' => '不支持的HTTP方法'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 