<?php
require_once __DIR__ . '/../lib/endpoints.php';
// 打包相关公共函数库
// 被 package.php 和 projects.php 共享使用

// 检查依赖模块是否都已入库
function checkBuildDepends($db, $moduleId, $deliveryRecordId) {
    error_log("检查依赖包是否入库");

    // 获取模块的依赖列表
    $moduleData = $db->fetch("SELECT dependencies FROM modules WHERE id = ?", [$moduleId]);
    if (!$moduleData || empty($moduleData['dependencies'])) {
        return true; // 没有依赖，可以打包
    }

    // dependencies 是逗号分隔的字符串，使用 FIND_IN_SET 匹配
    $result = $db->fetch("
        SELECT dp.module_id, dp.build_state
        FROM deliver_package dp
        JOIN modules m ON FIND_IN_SET(dp.module_id, m.dependencies) > 0
        WHERE dp.delivery_record_id = ?
          AND m.id = ?
          AND dp.build_state NOT IN ('已放弃','UPLOAD_CRP','UPLOAD_OK')
    ", [$deliveryRecordId, $moduleId]);

    return !$result; // 没有未完成的依赖，返回 true
}

// 更新任务队列 status 0排队,1进行,2完成,3放弃
function updateTaskQueue($db, $deliver_package_id, $build_json = '', $status = 1) {
    if ($status === 0)
        error_log("新增任务,deliver_package_id:{$deliver_package_id},排队中:{$build_json}");
    else
        error_log("新增任务,deliver_package_id:{$deliver_package_id},status:{$status}");

    // 使用 INSERT ... ON DUPLICATE KEY UPDATE 实现存在则更新，不存在则新增
    $db->execute("
    INSERT INTO build_queue (
        deliver_package_id, build_json, status,
        created_at, updated_at
    ) VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ON DUPLICATE KEY UPDATE
        build_json = VALUES(build_json),
        status = VALUES(status),
        updated_at = CURRENT_TIMESTAMP
    ", [$deliver_package_id, $build_json, $status]);

    error_log("插入/更新 build_queue 记录成功");
}

// 获取返回的build_itemid（第一次打包时）或使用现有的build_itemid
function getBuildItemId($result, $oldBuildItemId) {
    // 调试信息：输出CRP API返回的完整数据
    error_log("CRP打包API返回数据: " . json_encode($result['data'], JSON_UNESCAPED_UNICODE));
    error_log("CRP打包API返回的result结构: " . json_encode($result, JSON_UNESCAPED_UNICODE));
    error_log("检查result['data']['ID']: " . (isset($result['data']['ID']) ? $result['data']['ID'] : '未设置'));

    $buildItemId = 0;
    if (isset($result['data']['ID']) && $result['data']['ID'] > 0) {
        // CRP API返回了build_itemid，优先使用返回的值
        $buildItemId = $result['data']['ID'];
        error_log("使用CRP API返回的buildItemId: {$buildItemId}");

        // 如果现有记录中有build_itemid，且与返回的不同，记录更新信息
        if ($oldBuildItemId > 0 && $oldBuildItemId != $buildItemId) {
            error_log("CRP API返回了新的buildItemId，将更新: {$oldBuildItemId} -> {$buildItemId}");
        }
    } elseif ($oldBuildItemId > 0) {
        // 没有返回新ID，使用现有的build_itemid
        $buildItemId = $oldBuildItemId;
        error_log("使用现有的buildItemId: {$buildItemId}");
    } else {
        error_log("未获取到有效的buildItemId，CRP返回ID: " . ($result['data']['ID'] ?? 'null') . ", 现有ID: " . ($oldBuildItemId ?? 'null'));
    }

    return $buildItemId;
}

// 更新打包记录（deliver_package）
function updateBuildRecord($db, $deliveryRecordId, $module, $version, $buildHash, $buildItemId, $buildStatus = 'APPLYING') {

    $buildPackageId = 0;
    try {
        // 检查是否已存在相同交付记录和模块的记录
        $existingRecord = $db->fetch("
            SELECT id FROM deliver_package
            WHERE delivery_record_id = ? AND module_id = ?
        ", [$deliveryRecordId, $module['module_id']]);

        if ($existingRecord) {
            $buildPackageId = $existingRecord['id'];

            // 记录已存在，更新打包版本和打包hash
            $db->execute("
                UPDATE deliver_package
                SET cur_tag = ?,
                    build_commit = ?,
                    build_itemid = ?,
                    build_branch = ?,
                    build_state = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE delivery_record_id = ? AND module_id = ?
            ", [
                $version,
                $buildHash,
                $buildItemId,
                $module['module_branch'],
                $buildStatus,
                $deliveryRecordId,
                $module['module_id']
            ]);
            error_log("更新deliver_package记录成功 - delivery_record_id: {$deliveryRecordId}, module_id: {$module['module_id']}, buildItemId: {$buildItemId}");
        } else {
            // 记录不存在，插入新记录
            $db->execute("
                INSERT INTO deliver_package (
                    delivery_record_id, module_id, cur_tag, build_itemid,
                    build_commit, build_branch, base_tag, base_commit,build_state,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ", [
                $deliveryRecordId,
                $module['module_id'],
                $version,
                $buildItemId,
                $buildHash,
                $module['module_branch'],
                $module['build_cur_tag'],
                $module['build_commit'],
                $buildStatus
            ]);

            // 获取新增的 ID
            $buildPackageId = $db->lastInsertId();

            error_log("插入deliver_package记录成功 - delivery_record_id: {$deliveryRecordId}, module_id: {$module['module_id']}, buildItemId: {$buildItemId}");
        }

        return $buildPackageId;

    } catch (Exception $e) {
        error_log("操作deliver_package记录失败: " . $e->getMessage());
        // 不抛出异常，因为打包本身是成功的
    }
}

// 调用CRP打包API
function callCrpBuildApi($topicId, $buildItemid, $module, $version, $hash = '') {
    try {
        // 引入CRP API函数库
        require_once __DIR__ . '/crp-api.php';
        $jwtToken = getValidCrpToken();

        $url = CRP_API . "/topics/{$topicId}/new_release";

        // 构建请求数据 - 使用更简单的格式
        $data = [
            "TopicID" => intval($topicId),
            "BranchID" => strval($module['crp_branch_id'] ?? 0),
            "ID" => $buildItemid ?? 0, // topic-item-id
            "ProjectID" => $module['module_id'] ?? 0, // 模块ID
            "ProjectName" => $module['module_name'] ?? '', // 模块名称
            "Tag" => ($module['build_type'] == 1) ? '' : $version, // 用户输入的版本号
            "Commit" => $hash ?: $module['branch_commit'] ?? '', // 优先使用用户输入的hash，否则使用默认的build-commit
            "Custom" => true,
            "Branch" => $module['module_branch'] ?? '', // 项目编译分支
            "Arches" => $module['build_arches'] ?? '', // build-arches，用户选择的架构
            "BaseTag" => null,
            "BuildID" => 0,
            "BuildState" => null,
            "Changelog" => [""],
            "History" => null,
            "ProjectRepoUrl" => null,
            "SlaveNode" => null,
            "TagSuffix" => null,
            "TopicType" => "test",
            "ChangeLogMode" => ($module['build_type'] == 1), // 根据build_type：0=false，1=true
            "RepoType" => "deb"
        ];

        error_log("CRP打包请求数据: " . json_encode($data, JSON_UNESCAPED_UNICODE));

        // 发送HTTP请求
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $jwtToken,
                    'User-Agent: Mozilla/5.0 (compatible; ProjectManager/1.0)'
                ],
                'content' => json_encode($data),
                'timeout' => 30,
                'ignore_errors' => true  // 允许接收错误响应
            ]
        ]);

        $response = file_get_contents($url, false, $context);

        // 获取HTTP响应头信息
        $httpResponseHeader = $http_response_header ?? [];
        $statusLine = $httpResponseHeader[0] ?? '';
        // error_log("CRP打包响应状态: " . $statusLine);

        if ($response === false) {
            $error = error_get_last();
            error_log("CRP打包请求失败: " . ($error['message'] ?? '未知错误'));
            return [
                'success' => false,
                'message' => '无法连接到CRP服务器: ' . ($error['message'] ?? '未知错误')
            ];
        }

        // 记录响应信息
        // error_log("CRP打包响应: " . $response);

        // 检查HTTP状态码
        if (strpos($statusLine, '500') !== false) {
            error_log("CRP服务器内部错误，响应: " . $response);
            return [
                'success' => false,
                'message' => $response
            ];
        }

        if (strpos($statusLine, '400') !== false) {
            error_log("CRP请求格式错误，响应: " . $response);
            return [
                'success' => false,
                'message' => '请求格式错误: ' . $response
            ];
        }

        // 检查是否成功（200或201状态码都是成功）
        if (strpos($statusLine, '200') === false && strpos($statusLine, '201') === false) {
            error_log("CRP API返回非成功状态: " . $statusLine . ", 响应: " . $response);
            return [
                'success' => false,
                'message' => 'CRP API返回错误状态: ' . $statusLine
            ];
        }

        // 尝试解析JSON响应
        $responseData = json_decode($response, true);

        // 如果JSON解析失败，检查是否是直接的ID值
        if ($responseData === null) {
            // 检查响应是否是纯数字（ID值）
            if (is_numeric($response) && $response > 0) {
                error_log("CRP API返回直接ID值: {$response}");
                return [
                    'success' => true,
                    'message' => '打包请求提交成功',
                    'data' => ['ID' => intval($response)]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'CRP API响应格式错误: ' . $response
                ];
            }
        }

        // 如果JSON解析成功，但返回的是纯数字，也需要包装成标准格式
        if (is_numeric($responseData) && $responseData > 0) {
            error_log("CRP API返回JSON格式的数字ID值: {$responseData}");
            return [
                'success' => true,
                'message' => '打包请求提交成功',
                'data' => ['ID' => intval($responseData)]
            ];
        }

        // 检查是否有错误信息
        if (isset($responseData['error']) ||
        isset($responseData['message']) && $responseData['message'] != 'OK') {
            $errorMsg = $responseData['error'] ?? $responseData['message'] ?? '未知错误';
            return [
                'success' => false,
                'message' => 'CRP API错误: ' . $errorMsg
            ];
        }

        return [
            'success' => true,
            'message' => '打包请求提交成功',
            'data' => $responseData
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
