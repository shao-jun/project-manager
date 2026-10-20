<?php
// 打包API接口

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/build-functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';

            if ($action === 'build_package') {
                // 参数验证
                $required = ['module_id' => '模块ID', 'topic_id' => '主题ID', 'delivery_record_id' => '交付记录ID', 'version' => '版本号'];
                foreach ($required as $field => $label) {
                    if (empty($input[$field])) {
                        throw new Exception("{$label}不能为空");
                    }
                }

                $moduleId = $input['module_id'];
                $topicId = $input['topic_id'];
                $deliveryRecordId = $input['delivery_record_id'];
                $version = $input['version'];
                $hash = $input['hash'] ?? '';

                // 获取模块信息
                $module = $db->fetch("
                    SELECT pm.*, m.name as module_name, p.crp_branch_id, p.crp_branch_name, p.build_arches, m.build_type
                    FROM project_modules pm
                    LEFT JOIN modules m ON pm.module_id = m.id
                    LEFT JOIN projects p ON pm.project_id = p.id
                    WHERE pm.id = ?", [$moduleId]);

                if (!$module) {
                    throw new Exception('模块不存在');
                }

                // 获取或复用 build_itemid
                $buildItemid = getBuildItemidForPackage($db, $deliveryRecordId, $topicId, $module['module_id']);

                // 检查打包依赖
                $dependsOk = checkBuildDepends($db, $module['module_id'], $deliveryRecordId);

                if ($dependsOk) {
                    // 立即打包
                    $result = callCrpBuildApi($topicId, $buildItemid, $module, $version, $hash);

                    if (!$result['success']) {
                        throw new Exception('CRP打包API调用失败：' . $result['message']);
                    }

                    executeBuildSuccess($db, $deliveryRecordId, $module, $version, $hash, $result, $buildItemid, $moduleId);

                    echo json_encode([
                        'success' => true,
                        'message' => '打包请求已提交',
                        'data' => $result['data']
                    ]);
                } else {
                    // 加入排队
                    queueBuildTask($db, $deliveryRecordId, $topicId, $buildItemid, $module, $version, $hash);

                    echo json_encode([
                        'success' => true,
                        'message' => '打包任务已加入队列，等待依赖完成'
                    ]);
                }

            } else {
                throw new Exception('未知的操作类型');
            }
            break;

        default:
            throw new Exception('不支持的请求方法');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 获取打包用的 build_itemid（优先复用已有的）
function getBuildItemidForPackage($db, $deliveryRecordId, $topicId, $moduleId) {
    // 先查当前交付记录
    $packageItem = $db->fetch("
        SELECT dp.build_itemid
        FROM deliver_package dp
        LEFT JOIN project_deliver_record pdr ON dp.delivery_record_id = pdr.id
        WHERE dp.delivery_record_id = ? AND dp.module_id = ?", [$deliveryRecordId, $moduleId]);

    if ($packageItem && $packageItem['build_itemid'] > 0) {
        return $packageItem['build_itemid'];
    }

    // 再查同一主题下的其他交付记录
    $packageItem = $db->fetch("
        SELECT dp.build_itemid
        FROM deliver_package dp
        LEFT JOIN project_deliver_record pdr ON dp.delivery_record_id = pdr.id
        WHERE pdr.crp_id = ? AND dp.module_id = ? AND dp.build_itemid > 0
        ORDER BY dp.created_at DESC
        LIMIT 1", [$topicId, $moduleId]);

    $buildItemid = $packageItem ? $packageItem['build_itemid'] : 0;
    error_log("当前主题是否打过，打过就复用：{$buildItemid}");

    return $buildItemid;
}

// 执行打包成功后的更新操作
function executeBuildSuccess($db, $deliveryRecordId, $module, $version, $hash, $result, $oldBuildItemid, $moduleId) {
    $buildHash = $hash ?: ($module['branch_commit'] ?? '');
    $buildItemid = getBuildItemId($result, $oldBuildItemid);

    // 更新 deliver_package 记录
    $buildPackageId = updateBuildRecord($db, $deliveryRecordId, $module, $version, $buildHash, $buildItemid);

    // 更新任务队列
    if ($buildPackageId > 0) {
        updateTaskQueue($db, $buildPackageId);
    }

    // 更新项目模块的打包信息
    $db->execute("
        UPDATE project_modules
        SET build_cur_tag = ?, build_commit = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?", [$version, $buildHash, $moduleId]);
}

// 将打包任务加入队列
function queueBuildTask($db, $deliveryRecordId, $topicId, $buildItemid, $module, $version, $hash) {
    $buildParams = json_encode([
        'topicId' => $topicId,
        'buildItemId' => $buildItemid,
        'version' => $version,
        'hash' => $hash,
        'module' => [
            'crp_branch_id' => $module['crp_branch_id'],
            'module_id' => $module['module_id'],
            'module_name' => $module['module_name'],
            'build_type' => $module['build_type'],
            'branch_commit' => $module['branch_commit'],
            'module_branch' => $module['module_branch'],
            'build_arches' => $module['build_arches']
        ]
    ]);

    $buildHash = $hash ?: ($module['branch_commit'] ?? '');

    $buildPackageId = updateBuildRecord($db, $deliveryRecordId, $module, $version, $buildHash, $buildItemid, '排队中');

    updateTaskQueue($db, $buildPackageId, $buildParams, 0);
}
?>