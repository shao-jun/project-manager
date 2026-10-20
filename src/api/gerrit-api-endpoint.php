<?php
// Gerrit API HTTP入口文件

require_once __DIR__ . '/gerrit-api.php'; // Include the Gerrit API function library

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$db = Database::getInstance(); // Database instance is needed by the functions
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'branch_from_commit') {
                $commit = $_GET['commit'] ?? null;
                $result = getBranchFromCommit($commit, $db);
                echo json_encode($result);
            } elseif ($action === 'commit_detail') {
                $commit = $_GET['commit'] ?? null;
                $result = getCommitDetail($commit, $db);
                echo json_encode($result);
            } elseif ($action === 'project_info') {
                $projectName = $_GET['project_name'] ?? null;
                $result = getProjectInfo($projectName, $db);
                echo json_encode($result);
            } elseif ($action === 'project_branches') {
                $projectName = $_GET['project_name'] ?? null;
                $result = getProjectBranches($projectName, $db);
                echo json_encode($result);
            } elseif ($action === 'branch_latest_commit') {
                $projectName = $_GET['project_name'] ?? null;
                $branchName = $_GET['branch_name'] ?? null;
                $result = getBranchLatestCommit($projectName, $branchName, $db);
                echo json_encode($result);
            } elseif ($action === 'search_changes') {
                $queryParams = $_GET;
                unset($queryParams['action']); // 移除action参数
                $result = searchChanges($queryParams, $db);
                echo json_encode($result);
            } elseif ($action === 'project_branch_latest_revision') {
                $moduleName = $_GET['module_name'] ?? null;
                $branchName = $_GET['branch_name'] ?? null;
                $result = getProjectBranchLatestRevision($moduleName, $branchName, $db);
                echo json_encode($result);
            } elseif ($action === 'delivery_test_info') {
                // 获取交付记录的提测信息
                $deliveryId = $_GET['delivery_id'] ?? null;
                $result = getDeliveryTestInfo($deliveryId, $db);
                echo json_encode($result);
            } elseif ($action === 'module_build_test_status') {
                // 获取模块的构建和测试状态
                $moduleId = $_GET['module_id'] ?? null;
                $branchName = $_GET['branch_name'] ?? null;
                $result = getModuleBuildTestStatus($moduleId, $branchName, $db);
                echo json_encode($result);
            } elseif ($action === 'generate_delivery_pms') {
                // 生成交付记录的PMS信息
                $deliveryId = $_GET['delivery_id'] ?? null;
                if (!$deliveryId) {
                    echo json_encode([
                        'success' => false,
                        'message' => '交付记录ID不能为空',
                        'data' => null
                    ]);
                    break;
                }
                
                $result = generateDeliveryPmsInfo($deliveryId, $db);
                echo json_encode($result);
            } elseif ($action === 'clear_delivery_pms') {
                // 清空交付记录的PMS信息
                $deliveryId = $_GET['delivery_id'] ?? null;
                $moduleId = $_GET['module_id'] ?? null;
                $clearManualOnly = isset($_GET['manual_only']) && $_GET['manual_only'] === 'true';
                
                if (!$deliveryId) {
                    echo json_encode([
                        'success' => false,
                        'message' => '交付记录ID不能为空',
                        'data' => null
                    ]);
                    break;
                }
                
                try {
                    if ($clearManualOnly) {
                        // 只清空手动添加的转测单（module_id 为 NULL 或空）
                        $result = $db->execute("DELETE FROM deliver_pms WHERE deliver_id = ? AND (module_id IS NULL OR module_id = '')", [$deliveryId]);
                        $message = '手动转测单已清空';
                    } elseif ($moduleId) {
                        // 清空特定模块的数据
                        $result = $db->execute("DELETE FROM deliver_pms WHERE deliver_id = ? AND module_id = ?", [$deliveryId, $moduleId]);
                        $message = '模块PMS数据已清空';
                    } else {
                        // 清空全部数据
                        $result = $db->execute("DELETE FROM deliver_pms WHERE deliver_id = ?", [$deliveryId]);
                        $message = '所有PMS数据已清空';
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => $message,
                        'data' => null
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => '清空数据失败: ' . $e->getMessage(),
                        'data' => null
                    ]);
                }
            } elseif ($action === 'regenerate_delivery_pms') {
                // 重新生成交付记录的PMS信息（支持模块级别）
                $deliveryId = $_GET['delivery_id'] ?? null;
                $moduleId = $_GET['module_id'] ?? null;
                
                if (!$deliveryId) {
                    echo json_encode([
                        'success' => false,
                        'message' => '交付记录ID不能为空',
                        'data' => null
                    ]);
                    break;
                }
                
                $result = regenerateDeliveryPmsInfo($deliveryId, $moduleId, $db);
                echo json_encode($result);
            } elseif ($action === 'export_test_excel') {
                // 导出提测单Excel
                $deliveryId = $_GET['delivery_id'] ?? null;
                
                if (!$deliveryId) {
                    echo json_encode([
                        'success' => false,
                        'message' => '交付记录ID不能为空',
                        'data' => null
                    ]);
                    break;
                }
                
                $result = exportTestInfoExcel($deliveryId, $db);
                
                if ($result['success']) {
                    // 设置下载headers
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
                    header('Cache-Control: max-age=0');
                    
                    // 输出文件内容
                    readfile($result['filepath']);
                    
                    // 删除临时文件
                    unlink($result['filepath']);
                } else {
                    echo json_encode($result);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '无效的操作',
                    'data' => null
                ]);
            }
            break;

        case 'POST':
            // POST 动作统一从请求体读取
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            // 兼容从查询字符串传入action
            $postAction = $input['action'] ?? ($_POST['action'] ?? ($_GET['action'] ?? ''));
            if ($postAction === 'add_pms_links') {
                // 补充其他转测单（POST）
                $deliveryId = $input['delivery_id'] ?? ($_POST['delivery_id'] ?? null);
                $pmsLinks = $input['pms_links'] ?? ($_POST['pms_links'] ?? []);
                
                if (!$deliveryId) {
                    echo json_encode([
                        'success' => false,
                        'message' => '交付记录ID不能为空',
                        'data' => null
                    ]);
                    break;
                }
                
                if (empty($pmsLinks) || !is_array($pmsLinks)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'PMS链接不能为空',
                        'data' => null
                    ]);
                    break;
                }
                
                $result = addPmsLinksToDelivery($deliveryId, $pmsLinks, $db);
                echo json_encode($result);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '无效的POST操作',
                    'data' => null
                ]);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => '不支持的HTTP方法',
                'data' => null
            ]);
            break;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}
?> 