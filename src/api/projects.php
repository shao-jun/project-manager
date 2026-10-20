<?php
// 项目API接口

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/build-functions.php';
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
$path = $_SERVER['REQUEST_URI'];

try {
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? '';
            
            if ($action === 'modules') {
                // 获取项目的模块配置
                $projectId = $_GET['project_id'] ?? null;
                $filterType = $_GET['filter_type'] ?? 'packaged'; // 新的过滤参数
                
                if (!$projectId) {
                    throw new Exception('项目ID不能为空');
                }
                
                // 根据过滤类型构建查询条件
                $whereConditions = ['pm.project_id = ?', 'm.id is not null'];
                $params = [$projectId];
                
                switch ($filterType) {
                    case 'packaged':
                        // 已打包：有build_id的模块
                        $whereConditions[] = 'pm.build_id is not null';
                        break;
                    case 'updated':
                        // 有更新：状态为"有更新"的模块
                        $whereConditions[] = "pm.status = '有更新'";
                        break;
                    case 'refreshed':
                        // 已更新：状态为"已更新"的模块
                        $whereConditions[] = "pm.status = '已更新'";
                        break;
                    case 'unknown':
                        // 未知：状态为"未知"的模块
                        $whereConditions[] = "pm.status = '未知'";
                        break;
                    case 'all':
                        // 所有：不添加额外条件
                        break;
                    default:
                        // 默认显示已打包模块
                        $whereConditions[] = 'pm.build_id is not null';
                        break;
                }
                
                $whereClause = implode(' AND ', $whereConditions);
                
                $modules = $db->fetchAll("
                    SELECT pm.id, m.name, m.alias, m.build_type,
                    pm.module_branch as project_branch, pm.release_version as project_version,
                    pm.status as project_module_status,
                    pm.build_cur_tag, pm.build_tag_info, pm.build_commit, pm.branch_commit, pm.build_id, pm.branch_tag,pm.branch_tag_info,pm.build_crp_name,
                    CONCAT(m.repo_url, '/+log/refs/heads/', COALESCE(pm.module_branch, m.main_branch)) as code_url,
                    CASE 
                        WHEN pm.branch_commit IS NOT NULL AND pm.branch_commit != '' 
                        THEN CONCAT(m.repo_url, '/+log/', pm.branch_commit)
                        ELSE NULL 
                    END as branch_commit_url,
                    CASE 
                        WHEN pm.build_commit IS NOT NULL AND pm.build_commit != '' 
                        THEN CONCAT(m.repo_url, '/+log/', pm.build_commit)
                        ELSE NULL 
                    END as build_commit_url
                    FROM project_modules pm
                    LEFT JOIN modules m ON pm.module_id = m.id
                    WHERE {$whereClause}
                    ORDER BY pm.status, pm.build_id DESC
                ", $params);
                
                echo json_encode([
                    'success' => true,
                    'data' => $modules
                ]);
                
            } elseif ($action === 'detail') {
                // 获取单个项目详情
                $projectId = $_GET['project_id'] ?? null;
                
                if (!$projectId) {
                    throw new Exception('项目ID不能为空');
                }
                
                $project = $db->fetch("
                    SELECT 
                        p.*
                    FROM projects p
                    WHERE p.id = ?
                ", [$projectId]);
                
                if (!$project) {
                    throw new Exception('项目不存在');
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $project
                ]);
                
            } elseif ($action === 'goals') {
                // 获取项目目标
                $projectId = $_GET['project_id'] ?? null;
                
                if (!$projectId) {
                    throw new Exception('项目ID不能为空');
                }
                
                $project = $db->fetch("
                    SELECT goals FROM projects 
                    WHERE id = ?
                ", [$projectId]);
                
                // 将goals字段的文本转换为数组格式
                $goals = [];
                if ($project && !empty($project['goals'])) {
                    $goalLines = explode("\n", trim($project['goals']));
                    foreach ($goalLines as $index => $line) {
                        if (!empty(trim($line))) {
                            $goals[] = [
                                'id' => $index + 1,
                                'goal_name' => trim($line),
                                'goal_description' => '',
                                'priority' => 'medium',
                                'created_at' => date('Y-m-d H:i:s')
                            ];
                        }
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $goals
                ]);
                
            } elseif ($action === 'deliveries') {
                // 获取项目交付记录
                $projectId = $_GET['project_id'] ?? null;
                $archive = $_GET['archive'] ?? null;

                if (!$projectId) {
                    throw new Exception('项目ID不能为空');
                }
                
                // 构建查询条件
                $whereConditions = ['project_id = ?'];
                $params = [$projectId];
                
                if ($archive !== null) {
                    $whereConditions[] = 'archive = ?';
                    $params[] = $archive;
                }
                
                $whereClause = implode(' AND ', $whereConditions);
                
                $deliveries = $db->fetchAll("
                    SELECT 
                        id,
                        project_id,
                        crp_id,
                        crp_name,
                        pms_id,
                        repo_addr,
                        crp_addr,
                        crp_state,
                        crp_locked,
                        archive,
                        created_at,
                        updated_at
                    FROM project_deliver_record 
                    WHERE {$whereClause}
                    ORDER BY archive, id DESC, crp_id DESC
                ", $params);
                
                echo json_encode([
                    'success' => true,
                    'data' => $deliveries
                ]);
                
            } elseif ($action === 'delivery_modules') {
                // 获取交付记录的模块详情
                $deliveryId = $_GET['delivery_id'] ?? null;
                
                if (!$deliveryId) {
                    throw new Exception('交付记录ID不能为空');
                }
                
                $modules = $db->fetchAll("
                    SELECT 
                        dp.*,
                        m.name as module_name,
                        m.repo_url
                    FROM deliver_package dp
                    JOIN modules m ON dp.module_id = m.id
                    WHERE dp.delivery_record_id = ? and dp.build_state != '已放弃'
                    ORDER BY dp.created_at ASC
                ", [$deliveryId]);
                
                echo json_encode([
                    'success' => true,
                    'data' => $modules
                ]);
                
            } elseif ($action === 'delivery_detail') {
                // 获取单个交付记录详情
                $deliveryId = $_GET['delivery_id'] ?? null;
                
                if (!$deliveryId) {
                    throw new Exception('交付记录ID不能为空');
                }
                
                $delivery = $db->fetch("
                    SELECT 
                        id,
                        project_id,
                        crp_id,
                        crp_name,
                        pms_id,
                        repo_addr,
                        crp_addr,
                        crp_state,
                        created_at,
                        updated_at
                    FROM project_deliver_record 
                    WHERE id = ?
                ", [$deliveryId]);
                
                if (!$delivery) {
                    throw new Exception('交付记录不存在');
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $delivery
                ]);
                
            } elseif ($action === 'delivery_package_info') {
                // 获取交付记录的打包信息
                $deliveryId = $_GET['delivery_id'] ?? null;
                if (!$deliveryId) {
                    throw new Exception('交付记录ID不能为空');
                }
                $packageInfo = $db->fetchAll("
                    SELECT 
                        dp.*,
                        m.name as module_name,
                        m.alias,
                        m.repo_url,
                        CONCAT(m.repo_url, '/+log/', dp.build_commit) as build_commit_url,
                        CONCAT(m.repo_url, '/+log/', dp.base_commit) as base_commit_url
                    FROM deliver_package dp
                    LEFT JOIN modules m ON dp.module_id = m.id
                    WHERE dp.delivery_record_id = ?
                    ORDER BY dp.build_state,dp.build_id DESC
                ", [$deliveryId]);
                echo json_encode(['success' => true, 'data' => $packageInfo]);
                
            } elseif ($action === 'package_detail') {
                // 获取单个包的详细信息
                $packageId = $_GET['package_id'] ?? null;
                if (!$packageId) {
                    throw new Exception('包ID不能为空');
                }
                
                $packageDetail = $db->fetch("SELECT * FROM deliver_package WHERE id = ?", [$packageId]);
                if (!$packageDetail) {
                    throw new Exception('包信息不存在');
                }
                
                echo json_encode(['success' => true, 'data' => $packageDetail]);
                
            } elseif ($action === 'delivery_pms_data') {
                // 获取交付记录的PMS数据
                $deliveryId = $_GET['delivery_id'] ?? null;
                if (!$deliveryId) {
                    throw new Exception('交付记录ID不能为空');
                }
                
                $pmsData = $db->fetchAll("
                    SELECT 
                        dp.*,
                        m.name as module_name
                    FROM deliver_pms dp
                    LEFT JOIN modules m ON dp.module_id = m.id
                    WHERE dp.deliver_id = ?
                    ORDER BY dp.created_at DESC
                ", [$deliveryId]);
                
                echo json_encode(['success' => true, 'data' => $pmsData]);
                
            } elseif ($action === 'pms_modules_summary') {
                // 获取PMS链接和模块信息汇总，用于添加备注
                $deliveryId = $_GET['delivery_id'] ?? null;
                if (!$deliveryId) {
                    throw new Exception('交付记录ID不能为空');
                }
                
                // 获取所有PMS记录
                $pmsData = $db->fetchAll("
                            SELECT 
                                dp.id,
                                dp.module_id,
                                dp.pms_url,
                                dp.remark_status,
                                m.name as module_name
                            FROM deliver_pms dp
                            LEFT JOIN modules m ON dp.module_id = m.id
                            WHERE dp.deliver_id = ? AND (dp.remark_status = 0 OR dp.remark_status IS NULL)
                        ", [$deliveryId]);
                        
                        // 按PMS链接分组，汇总模块信息
                        $pmsGroups = [];
                        $pmsRecordIds = []; // 记录 deliver_pms 的 ID
                        foreach ($pmsData as $pms) {
                            // 解析pms_url（可能包含多个用空格分隔的链接）
                            $urls = explode(' ', $pms['pms_url']);
                            foreach ($urls as $url) {
                                $url = trim($url);
                                if (empty($url)) continue;
                                
                                if (!isset($pmsGroups[$url])) {
                                    $pmsGroups[$url] = [
                                        'pms_url' => $url,
                                        'module_ids' => [],
                                        'modules' => [],
                                        'dp_ids' => []
                                    ];
                                }
                                
                                // 添加模块ID（去重）
                                if (!empty($pms['module_id']) && !in_array($pms['module_id'], $pmsGroups[$url]['module_ids'])) {
                                    $pmsGroups[$url]['module_ids'][] = $pms['module_id'];
                                }
                                
                                // 记录 deliver_pms 的 ID
                                if (!empty($pms['id']) && !in_array($pms['id'], $pmsGroups[$url]['dp_ids'])) {
                                    $pmsGroups[$url]['dp_ids'][] = $pms['id'];
                                }
                            }
                        }
                
                // 获取每个模块的详细信息
                foreach ($pmsGroups as $url => &$group) {
                    if (empty($group['module_ids'])) continue;
                    
                    $placeholders = str_repeat('?,', count($group['module_ids']) - 1) . '?';
                    $modules = $db->fetchAll("
                        SELECT 
                            m.id as module_id,
                            m.name as module_name,
                            m.repo_url,
                            dp.build_branch,
                            dp.cur_tag as build_version,
                            dp.build_commit
                        FROM deliver_package dp
                        LEFT JOIN modules m ON dp.module_id = m.id
                        WHERE dp.delivery_record_id = ? AND dp.module_id IN ($placeholders)
                    ", array_merge([$deliveryId], $group['module_ids']));
                    
                    $group['modules'] = $modules;
                }
                
                echo json_encode(['success' => true, 'data' => array_values($pmsGroups)]);
                
            } elseif ($action === 'delivery_modules') {
                // 获取交付记录的所有模块信息
                $deliveryId = $_GET['delivery_id'] ?? null;
                if (!$deliveryId) {
                    throw new Exception('交付记录ID不能为空');
                }
                
                $modules = $db->fetchAll("
                    SELECT 
                        dp.module_id,
                        m.name as module_name,
                        dp.build_commit,
                        dp.build_branch,
                        dp.build_state
                    FROM deliver_package dp
                    LEFT JOIN modules m ON dp.module_id = m.id
                    WHERE dp.delivery_record_id = ?
                    ORDER BY m.name
                ", [$deliveryId]);
                
                echo json_encode(['success' => true, 'data' => $modules]);
                
            } elseif ($action === 'refresh_build_tags') {
                // 刷新项目的build_cur_tag和build_commit字段
                $projectId = $_GET['project_id'] ?? null;
                
                if (!$projectId) {
                    throw new Exception('项目ID不能为空');
                }
                
                $db->beginTransaction();
                try {
                    // 获取项目的所有模块，包括模块名称
                    $modules = $db->fetchAll("
                        SELECT pm.id, pm.module_id, pm.module_branch, m.name as module_name, m.build_type, pm.build_tag_info, pm.tag_commit 
                        FROM project_modules pm
                        JOIN modules m ON pm.module_id = m.id
                        WHERE pm.project_id = ?
                    ", [$projectId]);
                    
                    $updatedCount = 0;
                    $errorCount = 0;
                    $branchCommitUpdatedCount = 0;
                    
                    // 引入Gerrit API函数
                    require_once __DIR__ . '/gerrit-api.php';
                    
                    foreach ($modules as $module) {
                        // 从交付记录中获取该模块的最新构建信息
                        // tag_flag用来手动降低权重，避免版本排序错误
                        $latestPackage = $db->fetch("
                            SELECT dp.cur_tag, dp.build_commit, dp.build_id, dp.build_branch, pdr.crp_name
                            FROM deliver_package dp
                            JOIN project_deliver_record pdr ON dp.delivery_record_id = pdr.id
                            WHERE dp.module_id = ? AND pdr.project_id = ? and pdr.type != '2'
                                AND dp.cur_tag IS NOT NULL AND dp.cur_tag != '' and dp.build_state != '已放弃'
                            	ORDER BY 
                                CAST(SUBSTRING_INDEX(dp.cur_tag, '.', 1) AS UNSIGNED) DESC,
                                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(dp.cur_tag, '.', 2), '.', -1) AS UNSIGNED) DESC,
                                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(dp.cur_tag, '.', 3), '.', -1) AS UNSIGNED) DESC,
                                CAST(IF(dp.cur_tag LIKE '%.%.%.%', SUBSTRING_INDEX(SUBSTRING_INDEX(dp.cur_tag, '.', 4), '.', -1), 0) AS UNSIGNED) DESC,
                                CAST(IF(dp.cur_tag LIKE '%.%.%.%.%', SUBSTRING_INDEX(SUBSTRING_INDEX(dp.cur_tag, '.', 5), '.', -1), 0) AS UNSIGNED) DESC,
                                dp.cur_tag DESC
                                LIMIT 1
                        ", [$module['module_id'], $projectId]);
                        
                        $branchCommit = null;
                        
                        // 获取分支最新提交
                        if (!empty($module['module_name']) && !empty($module['module_branch'])) {
                            $branchResult = getProjectBranchLatestRevision($module['module_name'], $module['module_branch'], $db);
                            if ($branchResult['success'] && isset($branchResult['data']['revision'])) {
                                $branchCommit = $branchResult['data']['revision'];
                                $branchCommitUpdatedCount++;
                            } else {
                                error_log("获取分支最新提交失败: 模块={$module['module_name']}, 分支={$module['module_branch']}, 错误={$branchResult['message']}");
                            }
                        }

                        // 获取最新构建的tag信息
                        $tagInfo = null;
                        if ($module['build_type'] === '1') {
                            $tagInfo = $latestPackage['cur_tag'];
                        }

                        // hash没变就不再获取tag信息
                        $tagCommit = null;
                        if ($module['tag_commit'] === $latestPackage['build_commit'] ) {
                            $tagInfo = $module['build_tag_info'];
                            $tagCommit = $module['tag_commit'];
                        }
                                

                        if ($tagInfo==null && !empty($module['module_name']) && !empty($latestPackage['cur_tag']) && substr($module['module_branch'],0,7) !='project') {
                            $tagResult = getTagInfo($module['module_name'], $latestPackage['cur_tag'], $db);
                            if ($tagResult['success'] && isset($tagResult['data']['object'])) {
                                $tagInfo = $tagResult['data']['message'];
                                $tagCommit = $tagResult['data']['object'];
                                if ($latestPackage['build_commit'] != $tagCommit) {
                                    error_log("Tag信息与commit不匹配: 模块={$module['module_name']}, 
                                    tag={$latestPackage['cur_tag']}, 
                                    build_commit={$latestPackage['build_commit']},
                                    tag_commit={$tagCommit}, 
                                    ");

                                    // $tagInfo = "Tag信息与commit不匹配";
                                } else {
                                    error_log("Tag信息:{$tagInfo},TagCommit:{$tagCommit}");
                                    if(empty($tagInfo)) {
                                        error_log("Tag信息为空，给一个占位符");
                                        $tagInfo = '-';
                                    }
                                }
                                 
                            } else {
                                error_log("获取tag失败: 模块={$module['module_name']}, tag={$latestPackage['cur_tag']}, 错误={$tagResult['message']}");
                            }
                        }
                       
                        
                        if ($latestPackage) {
                            // 准备更新字段和值
                            $updateFields = ['build_cur_tag = ?', 'build_commit = ?', 'build_id = ?', 'branch_commit = ?', 'build_tag_info = ?', 'tag_commit = ?', 'updated_at = CURRENT_TIMESTAMP'];
                            $updateValues = [$latestPackage['cur_tag'], $latestPackage['build_commit'], $latestPackage['build_id'], $branchCommit, $tagInfo, $tagCommit];
                            
                            // 如果有build_branch，同时更新module_branch
                            if (!empty($latestPackage['build_branch'])) {
                                // 在updated_at之前插入module_branch字段
                                array_splice($updateFields, -1, 0, 'module_branch = ?');
                                $updateValues[] = $latestPackage['build_branch'];
                            }
                            
                            $updateFields[] = 'build_crp_name = ?';
                            $updateValues[] = $latestPackage['crp_name'];

                            // 添加WHERE条件的参数
                            $updateValues[] = $module['id'];
                            
                            // 更新project_modules表中的build_cur_tag、build_commit、build_id、branch_commit和module_branch
                            $db->execute("
                                UPDATE project_modules 
                                SET " . implode(', ', $updateFields) . "
                                WHERE id = ?
                            ", $updateValues);
                            
                            // 更新模块状态
                            updateModuleStatus($module['id'], $branchCommit, $latestPackage['build_commit'], $db);
                            
                            $updatedCount++;
                        } elseif ($branchCommit) {
                            // 即使没有package信息，也要更新branch_commit
                            $db->execute("
                                UPDATE project_modules 
                                SET branch_commit = ?, updated_at = CURRENT_TIMESTAMP
                                WHERE id = ?
                            ", [$branchCommit, $module['id']]);
                            
                            // 更新模块状态（没有build_commit时）
                            updateModuleStatus($module['id'], $branchCommit, null, $db);
                        }
                    }
                    
                    $db->commit();
                    
                    echo json_encode([
                        'success' => true,
                        'message' => "成功更新{$updatedCount}个模块的构建标签和分支信息，{$branchCommitUpdatedCount}个模块的分支提交",
                        'data' => [
                            'updated_count' => $updatedCount,
                            'error_count' => $errorCount,
                            'branch_commit_updated_count' => $branchCommitUpdatedCount
                        ]
                    ]);
                    
                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }
                
            } elseif ($action === 'update_code_commits') {
                // 根据代码分支获取最新提交，更新build_commit字段
                $projectId = $_GET['project_id'] ?? null;
                $moduleIds = $_GET['module_ids'] ?? null;
                
                if (!$projectId) {
                    throw new Exception('项目ID不能为空');
                }
                
                $db->beginTransaction();
                try {
                    // 构建查询条件
                    $whereClause = "pm.project_id = ? AND pm.module_branch IS NOT NULL AND pm.module_branch != ''";
                    $params = [$projectId];
                    
                    // 如果指定了模块ID，只更新指定的模块
                    if ($moduleIds) {
                        $moduleIdArray = array_map('intval', explode(',', $moduleIds));
                        $placeholders = str_repeat('?,', count($moduleIdArray) - 1) . '?';
                        $whereClause .= " AND pm.id IN ($placeholders)";
                        $params = array_merge($params, $moduleIdArray);
                    }
                    
                    // 获取项目的模块，包括模块名称和分支信息
                    $modules = $db->fetchAll("
                        SELECT pm.id, pm.module_id, pm.module_branch, pm.branch_tag, pm.build_commit, pm.branch_commit, m.name as module_name, m.build_type
                        FROM project_modules pm
                        JOIN modules m ON pm.module_id = m.id
                        WHERE $whereClause
                    ", $params);
                    
                    $updatedCount = 0;
                    $errorCount = 0;
                    
                    // 引入Gerrit API函数
                    require_once __DIR__ . '/gerrit-api.php';
                    
                    foreach ($modules as $module) {
                        try {
                            // 获取分支最新提交
                            $branchResult = getProjectBranchLatestRevision($module['module_name'], $module['module_branch'], $db);
                            
                            if ($branchResult['success'] && isset($branchResult['data']['revision'])) {
                                $branchCommit = $branchResult['data']['revision'];
                                $branchTag = null;
                                $branchTagInfo = null;

                                // 检查是否需要更新branch_commit字段
                                if ($module['branch_commit'] !== $branchCommit || $module['build_type'] == 0) {
                                    
                                    // 查询当前分支的最新tag
                                    if (substr($module['module_branch'],0,7) !='project') {
                                        $commitResult = queryBranchTag($module['module_name'], $branchCommit, $module['module_branch'], $db);
                                        if ($commitResult['success']) {
                                            $branchTag = $commitResult['data']['tag'];
                                            $branchTagInfo = $commitResult['data']['tagInfo'];
                                        } else {
                                            $branchTag = null;
                                        }
                                    }
                                    
                                    // 更新branch_commit 和 branch_tag字段
                                    $db->execute("
                                        UPDATE project_modules 
                                        SET branch_commit = ?, branch_tag = ?, branch_tag_info = ?, updated_at = CURRENT_TIMESTAMP
                                        WHERE id = ?
                                    ", [$branchCommit, $branchTag, $branchTagInfo, $module['id']]);
                                    
                                    // 更新模块状态
                                    updateModuleStatus($module['id'], $branchCommit, $module['build_commit'], $db);

                                    $updatedCount++;
                                }
                            } else {
                                error_log("获取分支最新提交失败: 模块={$module['module_name']}, 分支={$module['module_branch']}, 错误={$branchResult['message']}");
                                $errorCount++;
                            }
                        } catch (Exception $e) {
                            error_log("更新模块代码提交失败: 模块={$module['module_name']}, 错误={$e->getMessage()}");
                            $errorCount++;
                        }
                    }
                    
                    $db->commit();
                    
                    $scopeText = $moduleIds ? '勾选的' : '所有';
                    echo json_encode([
                        'success' => true,
                        'message' => "成功更新{$scopeText}{$updatedCount}个模块的代码提交，{$errorCount}个模块更新失败",
                        'data' => [
                            'updated_count' => $updatedCount,
                            'error_count' => $errorCount
                        ]
                    ]);
                    
                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }
                
            } elseif ($action === 'item_detail') {

                $itemId = $_GET['build_itemid'] ?? null;
                $packageId = $_GET['packageId'] ?? null;

                // 引入CRP API函数
                require_once __DIR__ . '/crp-api.php';

                $result = getCrpTopicItemDetail($itemId);
                if ($result['success']) {
                    $itemData = $result['data'];
                    // 更新数据库中的构建状态
                    $buildStatus = $itemData['BuildState']['state'];
                    $buildVersion = $itemData['Tag'];
                    $buildId = $itemData['BuildID'];
                    // 更新数据库
                    $db->execute(
                        "UPDATE deliver_package SET build_state = ? ,cur_tag = ?, build_id = ? WHERE id = ?",
                        [$buildStatus, $buildVersion, $buildId, $packageId]
                    );

                    echo json_encode([
                        'success' => true,
                        'message' => '构建状态刷新成功',
                        'data' => [
                            'build_state' => $buildStatus,
                            'cur_tag' => $buildVersion,
                            'build_id' => $buildId,
                            'build_commit' => $itemData['Commit'] ?? null,
                            'build_log' => $itemData['BuildLog'] ?? null
                        ]
                    ]);
                } else {
                    throw new Exception('刷新构建状态失败');
                }

            } elseif ($action === 'abandon_build') {
                // 放弃构建
                error_log("开始处理abandon_build请求，参数: " . json_encode($_GET));
                
                $buildItemId = $_GET['build_itemid'] ?? null;
                $packageId = $_GET['packageId'] ?? null;

                if (!$buildItemId) {
                    throw new Exception('构建项ID不能为空');
                }
                
                error_log("构建项ID: {$buildItemId}");
                
                try {
                    // 引入CRP API函数
                    require_once __DIR__ . '/crp-api.php';
                    
                    error_log("开始调用abandonCrpBuild函数");
                    
                    // 调用CRP API放弃构建
                    $result = abandonCrpBuild($buildItemId);
                    
                    error_log("abandonCrpBuild返回结果: " . json_encode($result));
                    
                    if ($result['success']) {

                        // 更新数据库
                        if ($packageId)
                            $db->execute("UPDATE deliver_package SET build_state = '已放弃' WHERE id = ?",[$packageId]);

                        echo json_encode([
                            'success' => true,
                            'message' => '构建已成功放弃',
                            'data' => [
                                'build_state' => 'abandoned'
                            ]
                        ]);
                    } else {
                        throw new Exception('放弃构建失败：' . $result['message']);
                    }
                } catch (Exception $e) {
                    error_log("abandon_build异常: " . $e->getMessage());
                    throw new Exception('放弃构建失败：' . $e->getMessage());
                }

            } elseif ($action === 'crp_releases') {
                // 获取CRP主题发布信息并同步到数据库
                $projectId = $_GET['project_id'] ?? null;
                $deliveryId = $_GET['delivery_id'] ?? null;
                
                if (!$projectId) {
                    throw new Exception('项目ID不能为空');
                }
                
                // 如果提供了delivery_id，则同步到deliver_package表
                $syncToDeliverPackage = !empty($deliveryId);
                
                // 获取项目的crp_topicid
                $project = $db->fetch("SELECT * FROM projects WHERE id = ?", [$projectId]);
                
                if (!$project) {
                    throw new Exception('项目不存在');
                }
                
                // 从交付记录获取主题ID
                $deliveryRecord = $db->fetch("SELECT crp_id,type FROM project_deliver_record WHERE id = ?", [$deliveryId]);
                
                if (!$deliveryRecord || empty($deliveryRecord['crp_id'])) {
                    throw new Exception('交付记录不存在或未配置主题ID');
                }
                
                // 使用交付记录中的crp_id作为主题ID
                $crpTopicId = $deliveryRecord['crp_id'];
                $crpAddr = CRP_BASE . "/#/detail/detail?id={$crpTopicId}";
                
                // 引入CRP API函数
                require_once __DIR__ . '/crp-api.php';
                
                // 调用CRP API获取发布信息
                $crpReleasesResult = getCrpReleases($crpTopicId);
                if (!$crpReleasesResult['success']) {
                    throw new Exception('CRP API返回错误: ' . $crpReleasesResult['message']);
                }
                
                $crpData = $crpReleasesResult['data'];
                $debugInfo = $crpReleasesResult['debug_info'] ?? [];
                
                // 调用CRP API获取主题详情
                $crpTopicDetailResult = getCrpTopicDetail($crpTopicId);
                $topicName = null;
                $crpState = null;
                $crpLocked = null;
                
                if ($crpTopicDetailResult['success'] && isset($crpTopicDetailResult['data'])) {
                    $topicData = $crpTopicDetailResult['data'];
                    if (isset($topicData['Name'])) {
                        $topicName = $topicData['Name'];
                    }
                    if (isset($topicData['Type'])) {
                        $crpState = $topicData['Type'];
                    }
                    if (isset($topicData['Lock'])) {
                        $crpLocked = $topicData['Lock'];
                    }
                }
                
                // 检查项目是否配置了CRP分支ID
                if (empty($project['crp_branch_id'])) {
                    throw new Exception('项目未配置打包分支，请在项目设置中配置CRP分支');
                }
                
                $projectCrpBranchId = $project['crp_branch_id'];
                
                // 记录使用的分支ID，帮助调试
                error_log("项目 {$projectId} 使用CRP分支ID: {$projectCrpBranchId}");
                
                // 调用CRP API获取仓库地址，使用项目的实际分支ID
                $crpTopicUrlsResult = getCrpTopicUrls($crpTopicId, $projectCrpBranchId);
                $repoAddr = null;
                
                if ($crpTopicUrlsResult['success'] && isset($crpTopicUrlsResult['data']['RepoUrls'])) {
                    $repoUrls = $crpTopicUrlsResult['data']['RepoUrls'];
                    if (is_array($repoUrls) && !empty($repoUrls)) {
                        $repoAddr = implode(';', $repoUrls);
                    } elseif (is_string($repoUrls)) {
                        $repoAddr = $repoUrls;
                    }
                } else {
                    $repoAddr = "";
                }
                
                $db->beginTransaction();
                try {
                    $processedCount = 0;
                    $updatedCount = 0;
                    $insertedCount = 0;
                    $deliverPackageCount = 0;
                    $foundModuleIds = [];
                    
                    // 如果同步到deliver_package表，直接使用delivery_id作为delivery_record_id
                    $deliveryRecordId = null;
                    if ($syncToDeliverPackage) {
                        $deliveryRecordId = $deliveryId;
                    }
                    
                    // 更新交付记录的主题名称、状态、仓库地址和主题地址
                    if ($syncToDeliverPackage && ($topicName || $crpState || $repoAddr || $crpAddr)) {
                        $updateFields = [];
                        $updateValues = [];
                        
                        if ($topicName && $deliveryRecord['type'] == 0) {
                            $updateFields[] = 'crp_name = ?';
                            $updateValues[] = $topicName;
                        }
                        
                        if ($crpState) {
                            $updateFields[] = 'crp_state = ?';
                            $updateValues[] = $crpState;
                        }
                        
                        if ($repoAddr) {
                            $updateFields[] = 'repo_addr = ?';
                            $updateValues[] = $repoAddr;
                        }
                        
                        if ($crpAddr) {
                            $updateFields[] = 'crp_addr = ?';
                            $updateValues[] = $crpAddr;
                        }
                        
                        if ($crpLocked) {
                            $updateFields[] = 'crp_locked = ?';
                            $updateValues[] = $crpLocked;
                        }

                        if (!empty($updateFields)) {
                            $updateValues[] = $deliveryRecordId;
                            $db->execute("UPDATE project_deliver_record SET " . implode(', ', $updateFields) . " WHERE id = ?", $updateValues);
                        }
                    }
                    
                    foreach ($crpData as $crpItem) {
                        $moduleId = $crpItem['ProjectID'] ?? null;
                        if (!$moduleId) continue;
                        $foundModuleIds[] = $moduleId;
                        $buildModuleName = $crpItem['ProjectName'] ?? null;
                        $buildCurTag = $crpItem['Tag'] ?? null;
                        $buildCommit = $crpItem['Commit'] ?? null;
                        $buildId = $crpItem['BuildID'] ?? null;
                        $buildState = $crpItem['BuildState']['state'] ?? null;
                        $buildLog = $crpItem['BuildState']['log'] ?? null;
                        $buildItemId = $crpItem['ID'];

                        
                        // 只同步到deliver_package表（如果提供了delivery_id）
                        if ($syncToDeliverPackage && $deliveryRecordId) {
                            // 获取分支信息
                            $buildBranch = null;
                            if (!empty($buildCommit) && $buildCommit != 'uploadcrp') {
                                // 引入Gerrit API函数
                                require_once __DIR__ . '/gerrit-api.php';
                                
                                $branchResult = getBranchFromCommit($buildCommit, $db);
                                if ($branchResult['success'] && isset($branchResult['data']['branch'])) {
                                    $buildBranch = $branchResult['data']['branch'];
                                }
                            }
                            
                            // 检查是否已存在该模块的记录
                            $existingRecord = $db->fetch("SELECT id,build_commit,build_branch FROM deliver_package WHERE delivery_record_id = ? AND module_id = ?", [$deliveryRecordId, $moduleId]);
                            
                            if ($existingRecord) {
                                // 更新现有记录
                                if ($buildCommit === 'uploadcrp') {
                                    // 如果是上传的就不更新build_commit，采用上一次同步的数据
                                    $buildCommit = $existingRecord['build_commit'];
                                    $buildBranch = $existingRecord['build_branch'];
                                }
                                $db->execute("
                                    UPDATE deliver_package SET 
                                        cur_tag = ?, build_id = ?, build_itemid = ?,
                                        build_commit = ?, build_state = ?, build_log = ?, build_module_name = ?, build_branch = ?,
                                        updated_at = CURRENT_TIMESTAMP
                                    WHERE delivery_record_id = ? AND module_id = ?
                                ", [$buildCurTag, $buildId, $buildItemId, $buildCommit, $buildState, $buildLog, $buildModuleName, $buildBranch, $deliveryRecordId, $moduleId]);
                            } else if ($deliveryRecord['type'] == 0) {
                                // 只有原始的交付记录才同步所有数据，第二次交付只更新已有的数据
                                // 从项目模块中获取：基础版本，基础提交
                                $projectModuleRecord = $db->fetch("SELECT build_cur_tag,build_commit FROM project_modules WHERE project_id = ? AND module_id = ?", [$projectId, $moduleId]);
                                $baseTag = $projectModuleRecord['build_cur_tag'];
                                $baseCommit = $projectModuleRecord['build_commit'];

                                // 插入新记录
                                $db->execute("
                                    INSERT INTO deliver_package (delivery_record_id, module_id, cur_tag, build_id, build_itemid, build_commit, build_state, build_log, build_module_name, build_branch, base_tag, base_commit)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ", [$deliveryRecordId, $moduleId, $buildCurTag, $buildId, $buildItemId, $buildCommit, $buildState, $buildLog, $buildModuleName, $buildBranch, $baseTag, $baseCommit]);
                                $insertedCount++;
                            }
                            $deliverPackageCount++;
                            $updatedCount++;
                        }
                        
                        $processedCount++;
                    }
                    
                    // 处理deliver_package中不存在于CRP接口返回的数据
                    $abandonedCount = 0;
                    if ($syncToDeliverPackage && $deliveryRecordId) {
                        if (count($foundModuleIds) > 0) {
                            // 如果CRP接口返回了数据，将不在返回列表中的模块状态设为'已放弃'
                            $inStr = implode(',', array_map('intval', $foundModuleIds));
                            $db->execute("
                                UPDATE deliver_package SET 
                                    build_state = '已放弃',
                                    updated_at = CURRENT_TIMESTAMP
                                WHERE delivery_record_id = ? AND module_id NOT IN ($inStr)
                                  AND (build_state IS NULL OR build_state != '已放弃')
                            ", [$deliveryRecordId]);
                            $abandonedCount = $db->fetch("
                                SELECT COUNT(*) as cnt FROM deliver_package 
                                WHERE delivery_record_id = ? AND module_id NOT IN ($inStr) 
                                AND build_state = '已放弃'
                            ", [$deliveryRecordId])['cnt'];
                        } else {
                            // 如果CRP接口返回空数据，将所有模块状态设为'已放弃'
                            $db->execute("
                                UPDATE deliver_package SET 
                                    build_state = '已放弃',
                                    updated_at = CURRENT_TIMESTAMP
                                WHERE delivery_record_id = ?
                                  AND (build_state IS NULL OR build_state != '已放弃')
                            ", [$deliveryRecordId]);
                            $abandonedCount = $db->fetch("
                                SELECT COUNT(*) as cnt FROM deliver_package 
                                WHERE delivery_record_id = ? AND build_state = '已放弃'
                            ", [$deliveryRecordId])['cnt'];
                        }
                    }

                    // 根据deliver_package同步project_modules表（只同步有build_id的模块）
                    if ($syncToDeliverPackage && $deliveryRecordId) {
                        $projectModuleUpdatedCount = 0;
                        
                        // 引入Gerrit API函数（如果还没有引入）
                        if (!function_exists('getProjectBranchLatestRevision')) {
                            require_once __DIR__ . '/gerrit-api.php';
                        }
                        
                        // 获取当前交付记录中有build_id的模块数据
                        $packagedModules = $db->fetchAll("
                            SELECT dp.module_id, dp.cur_tag, dp.build_commit, dp.build_id, dp.build_branch,
                                   pm.id as project_module_id, pm.module_branch, m.name as module_name
                            FROM deliver_package dp
                            JOIN project_modules pm ON dp.module_id = pm.module_id AND pm.project_id = ?
                            JOIN modules m ON pm.module_id = m.id
                            WHERE dp.delivery_record_id = ? 
                              AND dp.build_id IS NOT NULL 
                              AND dp.cur_tag IS NOT NULL 
                              AND dp.cur_tag != ''
                        ", [$projectId, $deliveryRecordId]);
                        
                        foreach ($packagedModules as $packagedModule) {
                            $branchCommit = null;
                            
                            // 获取分支最新提交
                            if (!empty($packagedModule['module_name']) && !empty($packagedModule['module_branch'])) {
                                $branchResult = getProjectBranchLatestRevision($packagedModule['module_name'], $packagedModule['module_branch'], $db);
                                if ($branchResult['success'] && isset($branchResult['data']['revision'])) {
                                    $branchCommit = $branchResult['data']['revision'];
                                }
                            }
                            
                            // 准备更新字段和值
                            $updateFields = ['build_cur_tag = ?', 'build_commit = ?', 'build_id = ?', 'branch_commit = ?', 'updated_at = CURRENT_TIMESTAMP'];
                            $updateValues = [$packagedModule['cur_tag'], $packagedModule['build_commit'], $packagedModule['build_id'], $branchCommit];
                            
                            // 如果有build_branch，同时更新module_branch
                            if (!empty($packagedModule['build_branch'])) {
                                // 在updated_at之前插入module_branch字段
                                array_splice($updateFields, -1, 0, 'module_branch = ?');
                                $updateValues[] = $packagedModule['build_branch'];
                            }
                            
                            // 添加WHERE条件的参数
                            $updateValues[] = $packagedModule['project_module_id'];
                            
                            // 更新project_modules表中的构建相关字段
                            $db->execute("
                                UPDATE project_modules 
                                SET " . implode(', ', $updateFields) . "
                                WHERE id = ?
                            ", $updateValues);
                            
                            // 更新模块状态（只对同步了的模块进行状态判断）
                            updateModuleStatus($packagedModule['project_module_id'], $branchCommit, $packagedModule['build_commit'], $db);
                            
                            $projectModuleUpdatedCount++;
                        }
                    }

                    $db->commit();
                    
                    if ($syncToDeliverPackage) {
                        $message = "交付记录同步完成：处理{$processedCount}个模块，更新{$updatedCount}个，插入{$insertedCount}个";
                        if ($abandonedCount > 0) {
                            $message .= "，设置{$abandonedCount}个多余模块状态为已放弃";
                        }
                    } else {
                        $message = "CRP数据获取完成：处理{$processedCount}个模块";
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'crp_data' => $crpData,
                            'sync_summary' => [
                                'processed' => $processedCount,
                                'updated' => $updatedCount,
                                'inserted' => $insertedCount,
                                'deliver_package' => $deliverPackageCount,
                                'abandoned' => $abandonedCount
                            ],
                            'debug_info' => $debugInfo
                        ],
                        'message' => $message
                    ]);
                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }
                
            } elseif ($action === 'project_types') {
                // 获取所有项目分类
                $projectTypes = $db->fetchAll("
                    SELECT DISTINCT project_type 
                    FROM projects 
                    WHERE project_type IS NOT NULL AND project_type != '' 
                    ORDER BY project_type
                ");
                
                echo json_encode([
                    'success' => true,
                    'data' => $projectTypes
                ]);
                
            } elseif ($action === 'project_managers') {
                // 获取所有项目经理
                $projectManagers = $db->fetchAll("
                    SELECT DISTINCT pm 
                    FROM projects 
                    WHERE pm IS NOT NULL AND pm != '' 
                    ORDER BY pm
                ");
                
                echo json_encode([
                    'success' => true,
                    'data' => $projectManagers
                ]);
                
            } elseif ($action === 'recent_activities') {
                // 获取最近动态
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 20;

                $result = getRecentActivities($db, $page, $limit);
                echo json_encode([
                    'success' => true,
                    'data' => $result['data'],
                    'page' => $result['page'],
                    'limit' => $result['limit'],
                    'total' => $result['total']
                ]);

            } elseif ($action === 'task_queue') {
                // 获取任务队列状态
                $updateStatus = $_GET['update_status'] ?? null;

                $result = getTaskQueue($db, $updateStatus);
                echo json_encode([
                    'success' => true,
                    'data' => $result
                ]);

            } elseif ($action === 'abandon_task') {
                // 放弃任务
                $taskId = $_GET['task_id'] ?? null;

                if (!$taskId) {
                    throw new Exception('任务ID不能为空');
                }

                $db->execute("UPDATE build_queue SET status = 3 WHERE id = ?", [$taskId]);
                $record = $existingRecord = $db->fetch("SELECT deliver_package_id FROM build_queue WHERE id = ?", [$taskId]);
                if ($record) {
                    $db->execute("UPDATE deliver_package SET build_state = '------' WHERE id = ? and build_state = '排队中'", [$record['deliver_package_id']]);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => '任务已放弃'
                ]);

            } else {
                // 获取所有项目，支持按project_type和搜索筛选（支持topicid、版本号、hash值）
                $projectType = $_GET['project_type'] ?? '';
                $topicId = $_GET['topic_id'] ?? '';
                $search = $_GET['search'] ?? '';
                
                $whereClause = '';
                $params = [];
                
                // 如果指定了search参数，同时模糊搜索topicid、版本号、hash值和仓库地址
                if (!empty($search)) {
                    $searchPattern = "%{$search}%";
                    $whereClause = 'WHERE EXISTS (
                        SELECT 1 FROM project_deliver_record r 
                        WHERE r.project_id = p.id AND (
                            (
                                r.type = 0 AND (
                                    r.crp_id LIKE ?
                                    OR r.id IN (
                                        SELECT dp.delivery_record_id FROM deliver_package dp 
                                        WHERE dp.cur_tag LIKE ? OR dp.build_commit LIKE ?
                                    )
                                )
                            )
                            OR r.repo_addr LIKE ?
                        )
                    )';
                    $params = [$searchPattern, $searchPattern, $searchPattern, $searchPattern];
                } else if (!empty($topicId)) {
                    // 兼容旧的topicId参数
                    $whereClause = 'WHERE EXISTS (
                        SELECT 1 FROM project_deliver_record r 
                        WHERE r.project_id = p.id AND r.crp_id = ? AND r.type = 0
                    )';
                    $params[] = $topicId;
                } else if (!empty($projectType)) {
                    $whereClause = 'WHERE p.project_type = ?';
                    $params[] = $projectType;
                }
                
                $projects = $db->fetchAll("
                    SELECT p.*, latest_record.record_time
                    FROM projects p
                    LEFT JOIN (
                        SELECT project_id, MAX(updated_at) as record_time
                        FROM project_deliver_record
                        GROUP BY project_id
                    ) latest_record ON p.id = latest_record.project_id
                    {$whereClause}
                    ORDER BY COALESCE(latest_record.record_time, p.updated_at) DESC
                ", $params);

                echo json_encode([
                    'success' => true,
                    'data' => $projects
                ]);
            }
            break;
            
        case 'POST':
            // 检查是否是表单提交
            if (isset($_POST['action'])) {
                $action = $_POST['action'];
                
                if ($action === 'update_package_field') {
                    // 更新包字段
                    $packageId = $_POST['package_id'] ?? null;
                    $fieldName = $_POST['field_name'] ?? null;
                    $fieldValue = $_POST['field_value'] ?? null;
                    
                    if (!$packageId || !$fieldName) {
                        throw new Exception('包ID和字段名不能为空');
                    }
                    
                    // 验证字段名是否允许编辑
                    $allowedFields = ['base_tag', 'base_commit', 'build_commit'];
                    if (!in_array($fieldName, $allowedFields)) {
                        throw new Exception('不允许编辑该字段');
                    }
                    
                    // 更新字段
                    $sql = "UPDATE deliver_package SET {$fieldName} = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                    $success = $db->execute($sql, [$fieldValue, $packageId]);
                    
                    if ($success) {
                        echo json_encode([
                            'success' => true,
                            'message' => '字段更新成功'
                        ]);
                    } else {
                        throw new Exception('更新失败');
                    }
                    break;
                }
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['action'])) {
                switch ($input['action']) {
                    case 'update_module':
                        // 更新项目模块配置
                        $projectId = $input['project_id'] ?? 0;
                        $moduleId = $input['module_id'] ?? 0;
                        $projectBranch = $input['project_branch'] ?? null;
                        $projectVersion = $input['project_version'] ?? null;
                        $status = $input['status'] ?? '未知';
                        
                        if (!$projectId || !$moduleId) {
                            throw new Exception('项目ID和模块ID不能为空');
                        }
                        
                        // 使用 ON DUPLICATE KEY UPDATE 语法
                        $db->execute("
                            INSERT INTO project_modules (project_id, module_id, module_branch, release_version, status)
                            VALUES (?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                                module_branch = VALUES(module_branch),
                                release_version = VALUES(release_version),
                                status = VALUES(status)
                        ", [$projectId, $moduleId, $projectBranch, $projectVersion, $status]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => '项目模块配置更新成功'
                        ]);
                        break;
                        
                    case 'update_module_branch':
                        // 更新模块分支
                        $moduleId = $input['module_id'] ?? 0;
                        $branch = $input['branch'] ?? '';
                        
                        if (!$moduleId) {
                            throw new Exception('模块ID不能为空');
                        }
                        
                        // 首先检查模块是否存在
                        $module = $db->fetch("SELECT id FROM project_modules WHERE id = ?", [$moduleId]);
                        if (!$module) {
                            throw new Exception('模块不存在');
                        }
                        
                        // 更新模块分支
                        $db->execute("
                            UPDATE project_modules 
                            SET module_branch = ?, updated_at = CURRENT_TIMESTAMP
                            WHERE id = ?
                        ", [$branch, $moduleId]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => '分支更新成功'
                        ]);
                        break;
                        
                    case 'update_module_version':
                        // 更新模块系统版本
                        $moduleId = $input['module_id'] ?? 0;
                        $version = $input['version'] ?? '';
                        
                        if (!$moduleId) {
                            throw new Exception('模块ID不能为空');
                        }
                        
                        // 首先检查模块是否存在
                        $module = $db->fetch("SELECT id FROM project_modules WHERE id = ?", [$moduleId]);
                        if (!$module) {
                            throw new Exception('模块不存在');
                        }
                        
                        // 更新模块系统版本
                        $db->execute("
                            UPDATE project_modules 
                            SET release_version = ?, updated_at = CURRENT_TIMESTAMP
                            WHERE id = ?
                        ", [$version, $moduleId]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => '系统版本更新成功'
                        ]);
                        break;
                        
                    case 'update_module_commit':
                        // 更新模块打包提交
                        $moduleId = $input['module_id'] ?? 0;
                        $commit = $input['commit'] ?? '';
                        
                        if (!$moduleId) {
                            throw new Exception('模块ID不能为空');
                        }
                        
                        // 首先检查模块是否存在并获取当前的分支提交
                        $module = $db->fetch("SELECT id, branch_commit FROM project_modules WHERE id = ?", [$moduleId]);
                        if (!$module) {
                            throw new Exception('模块不存在');
                        }
                        
                        // 更新模块打包提交
                        $db->execute("
                            UPDATE project_modules 
                            SET build_commit = ?, updated_at = CURRENT_TIMESTAMP
                            WHERE id = ?
                        ", [$commit, $moduleId]);
                        
                        // 更新模块状态
                        updateModuleStatus($moduleId, $module['branch_commit'], $commit, $db);
                        
                        // 获取更新后的状态
                        $updatedModule = $db->fetch("SELECT status FROM project_modules WHERE id = ?", [$moduleId]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => '打包提交更新成功',
                            'data' => [
                                'new_status' => $updatedModule['status']
                            ]
                        ]);
                        break;
                        
                    // tag信息列已设置为只读，不再需要更新功能
                        
                        
                    case 'add_goal':
                        // 添加项目目标
                        $projectId = $input['project_id'] ?? 0;
                        $goalName = $input['goal_name'] ?? '';
                        $goalDescription = $input['goal_description'] ?? '';
                        
                        if (!$projectId || !$goalName) {
                            throw new Exception('项目ID和目标名称不能为空');
                        }
                        
                        // 获取当前项目的goals
                        $project = $db->fetch("SELECT goals FROM projects WHERE id = ?", [$projectId]);
                        $currentGoals = $project['goals'] ?? '';
                        
                        // 构建新的目标文本
                        $newGoal = $goalName;
                        if (!empty($goalDescription)) {
                            $newGoal .= ': ' . $goalDescription;
                        }
                        
                        // 添加到现有目标中
                        $updatedGoals = $currentGoals;
                        if (!empty($updatedGoals)) {
                            $updatedGoals .= "\n";
                        }
                        $updatedGoals .= $newGoal;
                        
                        // 更新项目表
                        $db->execute("
                            UPDATE projects SET goals = ? WHERE id = ?
                        ", [$updatedGoals, $projectId]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => '项目目标添加成功'
                        ]);
                        break;
                        

                        
                    case 'add_delivery':
                        // 添加交付记录
                        $projectId = $input['project_id'] ?? 0;
                        $topicId = $input['topic_id'] ?? '';
                        $topicName = $input['topic_name'] ?? '';
                        $deliveryDate = $input['delivery_date'] ?? date('Y-m-d');
                        $repositoryUrl = $input['repository_url'] ?? '';
                        $description = $input['description'] ?? '';
                        
                        if (!$projectId) {
                            throw new Exception('项目ID不能为空');
                        }
                        
                        if (!$topicName) {
                            throw new Exception('主题名称不能为空');
                        }
                        
                        // 生成随机的PMS_id
                        // $pmsId = 'PMS_' . date('Ymd') . '_' . strtoupper(substr(md5(uniqid()), 0, 8));
                        $pmsId ='没什么用';

                        // 验证主题是否已经交付过
                        $deliveryType = 0;
                        if ($db->fetch("SELECT id FROM project_deliver_record WHERE crp_id = ?", [$topicId])) {
                            $deliveryType = 1;
                        }

                        $db->execute("
                            INSERT INTO project_deliver_record (project_id, crp_id, crp_name, pms_id, repo_addr, crp_addr, crp_state, delivery_date, description ,type)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ", [$projectId, $topicId, $topicName, $pmsId, $repositoryUrl, CRP_BASE . '/#/detail/detail?id='.$topicId, 'pending', $deliveryDate, $description, $deliveryType]);
                        
                        $deliveryId = $db->lastInsertId();
                        
                        echo json_encode([
                            'success' => true,
                            'message' => '交付记录添加成功',
                            'data' => [
                                'id' => $deliveryId,
                                'topic_id' => $topicId,
                                'topic_name' => $topicName
                            ]
                        ]);
                        break;
                        
                    case 'update_delivery':
                        // 更新交付记录
                        $deliveryId = $input['delivery_id'] ?? 0;
                        $crpId = $input['crp_id'] ?? '';
                        $crpName = $input['crp_name'] ?? '';
                        $onlyNameChanged = $input['only_name_changed'] ?? false;
                        
                        if (!$deliveryId) {
                            throw new Exception('交付记录ID不能为空');
                        }
                        
                        $db->execute("
                            UPDATE project_deliver_record 
                            SET crp_id = ?, crp_name = ?, updated_at = CURRENT_TIMESTAMP
                            WHERE id = ?
                        ", [$crpId, $crpName, $deliveryId]);
                        
                        $response = [
                            'success' => true,
                            'message' => '交付记录更新成功'
                        ];
                        
                        // 如果仅修改了主题名称且主题ID不为空，调用CRP API同步修改主题名称
                        if ($onlyNameChanged && !empty($crpId)) {
                            require_once __DIR__ . '/crp-api.php';
                            
                            // 获取交付记录所属项目的分支ID和类型
                            $deliveryRecord = $db->fetch("
                                SELECT pdr.project_id, p.crp_branch_id, pdr.type 
                                FROM project_deliver_record pdr
                                LEFT JOIN projects p ON pdr.project_id = p.id
                                WHERE pdr.id = ?
                            ", [$deliveryId]);
                            
                            if ($deliveryRecord && !empty($deliveryRecord['crp_branch_id']) && $deliveryRecord['type'] == 0) {
                                $branchId = $deliveryRecord['crp_branch_id'];
                                error_log("调用CRP API修改主题名称 - 主题ID: {$crpId}, 名称: {$crpName}, 分支ID: {$branchId}");
                                
                                $crpResult = modifyCrpName($crpId, $crpName, $branchId);
                                $response['crp_sync_result'] = $crpResult;
                                
                                if ($crpResult['success']) {
                                    error_log("CRP主题名称修改成功");
                                } else {
                                    error_log("CRP主题名称修改失败: " . $crpResult['message']);
                                }
                            }
                        }
                        
                        echo json_encode($response);
                        break;
                        
                    case 'delete_delivery':
                        // 删除交付记录及其关联数据
                        $deliveryId = $input['delivery_id'] ?? 0;
                        
                        if (!$deliveryId) {
                            throw new Exception('交付记录ID不能为空');
                        }
                        
                        // 验证交付记录是否存在
                        $deliveryRecord = $db->fetch("SELECT id FROM project_deliver_record WHERE id = ?", [$deliveryId]);
                        if (!$deliveryRecord) {
                            throw new Exception('交付记录不存在');
                        }
                        
                        $db->beginTransaction();
                        try {
                            // 删除关联的deliver_package记录
                            $packageDeletedCount = $db->execute("DELETE FROM deliver_package WHERE delivery_record_id = ?", [$deliveryId]);
                            
                            // 删除关联的deliver_pms记录
                            $pmsDeletedCount = $db->execute("DELETE FROM deliver_pms WHERE deliver_id = ?", [$deliveryId]);
                            
                            // 删除交付记录本身
                            $deliveryDeletedCount = $db->execute("DELETE FROM project_deliver_record WHERE id = ?", [$deliveryId]);
                            
                            $db->commit();
                            
                            echo json_encode([
                                'success' => true,
                                'message' => "交付记录删除成功",
                                'data' => [
                                    'delivery_deleted' => $deliveryDeletedCount,
                                    'packages_deleted' => $packageDeletedCount,
                                    'pms_deleted' => $pmsDeletedCount
                                ]
                            ]);
                        } catch (Exception $e) {
                            $db->rollback();
                            throw new Exception('删除交付记录失败：' . $e->getMessage());
                        }
                        break;
                        
                    case 'delete_modules':
                        // 删除选中的项目模块
                        $projectId = $input['project_id'] ?? null;
                        $moduleIds = $input['module_ids'] ?? [];
                        
                        if (!$projectId || empty($moduleIds)) {
                            throw new Exception('项目ID和模块ID列表不能为空');
                        }
                        
                        // 删除选中的模块
                        $placeholders = str_repeat('?,', count($moduleIds) - 1) . '?';
                        $sql = "DELETE FROM project_modules WHERE id IN ({$placeholders}) AND project_id = ?";
                        $params = array_merge($moduleIds, [$projectId]);
                        
                        // 打印SQL语句和参数用于调试
                        error_log("删除模块SQL: " . $sql);
                        error_log("删除模块参数: " . json_encode($params));
                        
                        $success = $db->execute($sql, $params);
                        
                        if ($success) {
                            echo json_encode([
                                'success' => true,
                                'message' => '模块删除成功',
                                'data' => [
                                    'deleted_count' => count($moduleIds)
                                ]
                            ]);
                        } else {
                            throw new Exception('删除失败');
                        }
                        break;
                    case 'add_modules':
                        $projectId = $input['project_id'] ?? null;
                        $moduleIds = $input['module_ids'] ?? [];
                        
                        if (!$projectId || empty($moduleIds)) {
                            throw new Exception('项目ID和模块ID列表不能为空');
                        }
                        
                        // 批量插入模块到项目
                        $db->beginTransaction();
                        try {
                            foreach ($moduleIds as $moduleId) {
                                // 获取模块的main_branch
                                $module = $db->fetch("SELECT main_branch FROM modules WHERE id = ?", [$moduleId]);
                                $moduleBranch = $module ? $module['main_branch'] : null;
                                
                                $db->execute("
                                    INSERT INTO project_modules (project_id, module_id, module_branch, status)
                                    VALUES (?, ?, ?, '未知')
                                ", [$projectId, $moduleId, $moduleBranch]);
                            }
                            $db->commit();
                            $success = true;
                        } catch (Exception $e) {
                            $db->rollback();
                            $success = false;
                            throw $e;
                        }
                        
                        if ($success) {
                            echo json_encode([
                                'success' => true,
                                'message' => '模块添加成功',
                                'data' => [
                                    'deleted_count' => count($moduleIds)
                                ]
                            ]);
                        } else {
                            throw new Exception('模块添加失败');
                        }
                        break;
                    case 'create_tag':
                        // 为模块创建tag
                        $moduleId = $input['module_id'] ?? 0;
                        
                        if (!$moduleId) {
                            throw new Exception('模块ID不能为空');
                        }
                        
                        // 验证模块是否存在
                        $module = $db->fetch("SELECT m.name,m.module_name_id, pm.build_commit,pm.build_cur_tag,pdr.crp_name FROM project_modules pm 
                        left join deliver_package dp on pm.build_id=dp.build_id 
                        left join project_deliver_record pdr on pdr.id=dp.delivery_record_id 
                        left join modules m on m.id = pm.module_id WHERE pm.id = ?", [$moduleId]);
                        if (!$module) {
                            throw new Exception('模块不存在');
                        }
                        
                        if (empty($module['crp_name'])) {
                            error_log("SELECT m.name,m.module_name_id, pm.build_commit,pm.build_cur_tag,pdr.crp_name FROM project_modules pm left join deliver_package dp on pm.build_id=dp.build_id left join project_deliver_record pdr on pdr.id=dp.delivery_record_id left join modules m on m.id = pm.module_id WHERE pm.id = $moduleId");

                            throw new Exception('主题名称获取失败，同步主题后再试');
     
                        }
                        
                        // 调用协作平台API创建tag
                        $result = createTagInPlatform($module['name'], $module['module_name_id'], $module['build_cur_tag'], $module['build_commit'], $module['crp_name']);
                        
                        if ($result['success']) {
                            echo json_encode([
                                'success' => true,
                                'message' => 'Tag创建成功',
                                'data' => $result['data']
                            ]);
                        } else {
                            throw new Exception('Tag创建失败: ' . $result['message']);
                        }
                        break;
                        
                    case 'crp_reject':
                        // 驳回CRP主题
                        $deliveryId = $input['delivery_id'] ?? null;
                        $topicId = $input['topic_id'] ?? null;
                        $rejectMsg = $input['msg'] ?? '';
                        
                        if (!$deliveryId || !$topicId) {
                            throw new Exception('交付记录ID和主题ID不能为空');
                        }
                        
                        if (empty($rejectMsg)) {
                            throw new Exception('驳回原因不能为空');
                        }
                        
                        // 引入CRP API函数
                        require_once __DIR__ . '/crp-api.php';
                        
                        // 调用CRP API驳回主题
                        $rejectResult = rejectCrpTopic($topicId, $rejectMsg);
                        
                        if (!$rejectResult['success']) {
                            throw new Exception($rejectResult['message']);
                        }
                        
                        // 更新数据库中的crp_locked字段为0
                        $db->execute("UPDATE project_deliver_record SET crp_locked = 0 WHERE id = ?", [$deliveryId]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => '主题驳回成功',
                            'data' => $rejectResult['data']
                        ]);
                        break;
                        
                    case 'update_delivery_archive':
                        // 更新交付记录的归档状态
                        $deliveryId = $input['delivery_id'] ?? null;
                        $archive = $input['archive'] ?? 0;
                        
                        if (!$deliveryId) {
                            throw new Exception('交付记录ID不能为空');
                        }
                        
                        // 验证归档状态值
                        if (!in_array($archive, [0, 1])) {
                            throw new Exception('归档状态值无效');
                        }
                        
                        // 更新归档状态
                        $db->execute("
                            UPDATE project_deliver_record 
                            SET archive = ?, updated_at = CURRENT_TIMESTAMP
                            WHERE id = ?
                        ", [$archive, $deliveryId]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => $archive == 1 ? '已归档' : '已取消归档'
                        ]);
                        break;
                        
                    case 'add_pms_remarks':
                        // 给PMS工单添加备注
                        $deliveryId = $input['delivery_id'] ?? null;
                        $repoAddr = $input['repo_addr'] ?? '';

                        error_log("==== add_pms_remarks 开始 ==== delivery_id: {$deliveryId}");

                        if (!$deliveryId) {
                            throw new Exception('交付记录ID不能为空');
                        }
                        
                        if (empty($repoAddr)) {
                            throw new Exception('仓库地址为空，请先同步仓库地址');
                        }

                        // 获取所有PMS记录 - 只查询未备注的
                        $startTime = microtime(true);
                        $pmsData = $db->fetchAll("
                            SELECT 
                                dp.id,
                                dp.module_id,
                                dp.pms_url,
                                m.name as module_name
                            FROM deliver_pms dp
                            LEFT JOIN modules m ON dp.module_id = m.id
                            WHERE dp.deliver_id = ? AND (dp.remark_status = 0 OR dp.remark_status IS NULL)
                        ", [$deliveryId]);
                        
                        $fetchPmsDuration = round((microtime(true) - $startTime) * 1000, 2);
                        error_log("步骤1 - 获取PMS记录: {$fetchPmsDuration}ms, 记录数: " . count($pmsData));
                        
                        // 按 deliver_pms 记录ID分组，每个记录单独处理
                        $pmsGroups = [];
                        foreach ($pmsData as $pms) {
                            $pmsId = $pms['id'];
                            $moduleId = $pms['module_id'];
                            
                            if (empty($moduleId)) continue;
                            
                            // 从 deliver_package 查询该模块的打包信息
                            $modules = $db->fetchAll("
                                SELECT 
                                    m.id as module_id,
                                    m.name as module_name,
                                    dp.build_branch,
                                    dp.cur_tag as build_version,
                                    dp.build_commit
                                FROM deliver_package dp
                                LEFT JOIN modules m ON dp.module_id = m.id
                                WHERE dp.delivery_record_id = ? AND dp.module_id = ?
                            ", [$deliveryId, $moduleId]);
                            
                            // 解析 pms_url（可能包含多个用空格分隔的链接）
                            $urls = explode(' ', $pms['pms_url']);
                            $pmsGroups[$pmsId] = [
                                'pms_urls' => array_filter(array_map('trim', $urls)),
                                'modules' => $modules,
                                'dp_id' => $pmsId
                            ];
                        }
                        
                        error_log("步骤2 - 获取模块详情完成，PMS组数: " . count($pmsGroups));

                        // 调用PMS API添加备注
                        $successCount = 0;
                        $failCount = 0;
                        $results = [];
                        $startTime = microtime(true);
                        $apiCallCount = 0;
                        
                        foreach ($pmsGroups as $pmsId => $group) {
                            if (empty($group['modules'])) continue;
                            
                            $urls = $group['pms_urls'];
                            $apiCallCount++;
                            
                            // 构建备注内容
                            $remarkContent = buildPmsRemarkContent($repoAddr, $group['modules']);
                            
                            // 每个URL都需要调用PMS API
                            $urlSuccessCount = 0;
                            $urlFailCount = 0;
                            
                            foreach ($urls as $url) {
                                if (empty($url)) continue;
                                
                                $apiStartTime = microtime(true);
                                $result = addRemarkToPms($url, $remarkContent);
                                
                                $apiDuration = round((microtime(true) - $apiStartTime) * 1000, 2);
                                error_log("步骤3 - PMS API调用 #{$apiCallCount}: {$apiDuration}ms, URL: {$url}, 结果: " . ($result['success'] ? '成功' : '失败: ' . ($result['message'] ?? '')));
                                
                                if ($result['success']) {
                                    $urlSuccessCount++;
                                    $successCount++;
                                } else {
                                    $urlFailCount++;
                                    $failCount++;
                                }
                                
                                $results[] = [
                                    'pms_url' => $url,
                                    'success' => $result['success'],
                                    'message' => $result['message'] ?? ''
                                ];
                            }
                            
                            // 只要有一个URL成功，就更新 remark_status 为 1
                            if ($urlSuccessCount > 0 && !empty($group['dp_id'])) {
                                error_log("更新 remark_status: dp_id=" . $group['dp_id'] . ", 成功URL数: $urlSuccessCount");
                                $db->execute("UPDATE deliver_pms SET remark_status = 1 WHERE id = ?", [$group['dp_id']]);
                            }
                        }
                        
                        $totalPmsDuration = round((microtime(true) - $startTime) * 1000, 2);
                        error_log("==== add_pms_remarks 完成 ==== 总耗时: {$totalPmsDuration}ms, API调用次数: {$apiCallCount}, 成功: {$successCount}, 失败: {$failCount}");
                        
                        echo json_encode([
                            'success' => true,
                            'message' => "备注添加完成：成功{$successCount}个，失败{$failCount}个",
                            'data' => [
                                'success_count' => $successCount,
                                'fail_count' => $failCount,
                                'results' => $results
                            ]
                        ]);
                        break;
                        
                    case 'update_remark_status':
                        // 更新备注状态
                        $pmsId = $input['pms_id'] ?? null;
                        $status = $input['status'] ?? null;
                        
                        if (!$pmsId) {
                            throw new Exception('PMS记录ID不能为空');
                        }
                        
                        if ($status === null) {
                            throw new Exception('状态不能为空');
                        }
                        
                        $db->execute("UPDATE deliver_pms SET remark_status = ? WHERE id = ?", [$status, $pmsId]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => '备注状态已更新'
                        ]);
                        break;
                    
                    case 'fetch_baseline_versions':
                        // 获取基线版本
                        $projectId = $input['project_id'] ?? null;
                        $baselineName = $input['baseline_name'] ?? null;
                        
                        if (!$projectId || !$baselineName) {
                            throw new Exception('项目ID和基线名称不能为空');
                        }
                        
                        // 转换基线名称格式: eagle-1075 -> eagle/1075
                        $append = $input['append'] ?? false;
                        $repoPath = str_replace('-', '/', $baselineName);
                        $packagesUrls = [
                            POOLS_BASE . "/desktop-professional/dists/{$repoPath}/main/binary-amd64/Packages",
                            POOLS_BASE . "/desktop-professional/dists/{$repoPath}/non-free/binary-amd64/Packages"
                        ];

                        // append模式：额外获取append仓库地址
                        if ($append) {
                            $packagesUrls[] = POOLS_BASE . "/desktop-professional/dists/{$repoPath}append/main/binary-amd64/Packages";
                            $packagesUrls[] = POOLS_BASE . "/desktop-professional/dists/{$repoPath}append/non-free/binary-amd64/Packages";
                        }
                        
                        // 获取项目模块
                        $modules = $db->fetchAll("
                            SELECT pm.id, pm.module_id, m.name, m.package_name 
                            FROM project_modules pm 
                            LEFT JOIN modules m ON pm.module_id = m.id 
                            WHERE pm.project_id = ?
                        ", [$projectId]);
                        
                        if (empty($modules)) {
                            throw new Exception('项目没有模块');
                        }
                        
                        // 获取所有Packages文件内容并合并
                        $packageVersions = [];
                        
                        foreach ($packagesUrls as $packagesUrl) {
                            $packagesContent = @file_get_contents($packagesUrl);
                            if ($packagesContent === false) {
                                continue; // 跳过无法获取的仓库
                            }
                            
                            // 解析包的版本
                            $blocks = explode("\n\n", $packagesContent);
                            foreach ($blocks as $block) {
                                $lines = explode("\n", $block);
                                $pkgName = null;
                                $version = null;
                                
                                foreach ($lines as $line) {
                                    if (strpos($line, 'Package: ') === 0) {
                                        $pkgName = substr($line, 9);
                                    }
                                    if (strpos($line, 'Version: ') === 0) {
                                        $version = substr($line, 9);
                                    }
                                }
                                
                                if ($pkgName && $version) {
                                    $packageVersions[$pkgName] = $version;
                                }
                            }
                        }
                        
                        if (empty($packageVersions)) {
                            throw new Exception("无法获取任何Packages文件");
                        }
                        
                        // 更新模块版本
                        $updatedCount = 0;
                        $notFoundCount = 0;
                        
                        foreach ($modules as $module) {
                            // 优先使用package_name，如果为空则使用name
                            $lookupName = !empty($module['package_name']) ? $module['package_name'] : $module['name'];
                            
                            if (isset($packageVersions[$lookupName])) {
                                $version = $packageVersions[$lookupName];
                                $db->execute("
                                    UPDATE project_modules 
                                    SET release_version = ?, updated_at = CURRENT_TIMESTAMP 
                                    WHERE id = ?
                                ", [$version, $module['id']]);
                                $updatedCount++;
                            } else {
                                $notFoundCount++;
                            }
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'message' => "基线版本获取完成：更新{$updatedCount}个，未找到{$notFoundCount}个",
                            'data' => [
                                'updated' => $updatedCount,
                                'not_found' => $notFoundCount,
                                'total_modules' => count($modules)
                            ]
                        ]);
                        break;
                        
                    default:
                        throw new Exception('未知的操作类型');
                }
            } else {
                // 创建新项目
                $name = $input['name'] ?? '';
                $categoryId = $input['category_id'] ?? null;
                $projectType = $input['project_type'] ?? null;
                $description = $input['description'] ?? '';
                $status = $input['status'] ?? 'running';
                $crpBranchId = $input['crp_branch_id'] ?? null;
                $crpBranchName = $input['crp_branch_name'] ?? null;
                $systemBaselineId = $input['system_baseline_id'] ?? null;
                $systemBaselineName = $input['system_baseline_name'] ?? null;
                
                if (empty($name)) {
                    throw new Exception('创建新项目：项目名称不能为空');
                }
                
                if (empty($categoryId)) {
                    throw new Exception('创建新项目：技术分类不能为空');
                }
                
                $db->beginTransaction();
                
                try {
                    // 检查技术分类是否存在
                    $category = $db->fetch("SELECT id FROM tech_categories WHERE id = ?", [$categoryId]);
                    if (!$category) {
                        throw new Exception('指定的技术分类不存在');
                    }
                    
                    // 创建项目（包含新的分支字段和项目分类）
                    $db->execute("
                        INSERT INTO projects (name, description, status, project_type, build_arches, crp_branch_id, crp_branch_name, system_baseline_id, system_baseline_name)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ", [$name, $description, $status, $projectType, '', $crpBranchId, $crpBranchName, $systemBaselineId, $systemBaselineName]);
                    
                    $projectId = $db->lastInsertId();
                    
                    // 获取该分类下的所有模块
                    $modules = $db->fetchAll("SELECT id FROM modules WHERE category_id = ? AND status = 'active'", [$categoryId]);
                    
                    // 将所有模块添加到project_modules表中
                    $moduleCount = 0;
                    foreach ($modules as $module) {
                        // 获取模块的main_branch
                        $moduleInfo = $db->fetch("SELECT main_branch FROM modules WHERE id = ?", [$module['id']]);
                        $moduleBranch = $moduleInfo ? $moduleInfo['main_branch'] : null;
                        
                        $db->execute("
                            INSERT INTO project_modules (project_id, module_id, module_branch, status)
                            VALUES (?, ?, ?, '未知')
                        ", [$projectId, $module['id'], $moduleBranch]);
                        $moduleCount++;
                    }
                    
                    $db->commit();
                    
                    echo json_encode([
                        'success' => true,
                        'message' => "项目创建成功，已自动添加{$moduleCount}个模块",
                        'data' => ['id' => $projectId, 'module_count' => $moduleCount]
                    ]);
                    
                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (strpos($path, '/projects/') !== false && strpos($path, '/sync') !== false) {
                // 同步项目模块到主线
                $projectId = $input['project_id'] ?? 0;
                
                if (!$projectId) {
                    throw new Exception('项目ID不能为空');
                }
                
                $db->execute("
                    UPDATE project_modules pm
                    JOIN modules m ON pm.module_id = m.id
                    SET pm.project_branch = m.main_branch,
                        pm.project_version = m.current_version
                    WHERE pm.project_id = ?
                ", [$projectId]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '项目模块已同步到主线'
                ]);
                
            } elseif (strpos($path, '/projects/') !== false) {
                // 更新项目
                $projectId = basename($path);
                $name = $input['name'] ?? '';
                $description = $input['description'] ?? '';
                $status = $input['status'] ?? 'running';
                $projectType = $input['project_type'] ?? null;
                $buildArches = $input['build_arches'] ?? null;
                $crpBranchId = $input['crp_branch_id'] ?? null;
                $crpBranchName = $input['crp_branch_name'] ?? null;
                $systemBaselineId = $input['system_baseline_id'] ?? null;
                $systemBaselineName = $input['system_baseline_name'] ?? null;
                
                if (empty($name)) {
                    throw new Exception('更新项目：项目名称不能为空');
                }
                
                // 构建更新语句，只更新提供的字段
                $updateFields = [];
                $updateValues = [];
                
                $updateFields[] = 'name = ?';
                $updateValues[] = $name;
                
                if (isset($input['description'])) {
                    $updateFields[] = 'description = ?';
                    $updateValues[] = $description;
                }
                
                if (isset($input['status'])) {
                    $updateFields[] = 'status = ?';
                    $updateValues[] = $status;
                }
                
                if (isset($input['project_type'])) {
                    $updateFields[] = 'project_type = ?';
                    $updateValues[] = $projectType;
                }
                
                if (isset($input['build_arches'])) {
                    $updateFields[] = 'build_arches = ?';
                    $updateValues[] = $buildArches;
                }
                
                if (isset($input['crp_branch_id'])) {
                    $updateFields[] = 'crp_branch_id = ?';
                    $updateValues[] = $crpBranchId;
                }
                
                if (isset($input['crp_branch_name'])) {
                    $updateFields[] = 'crp_branch_name = ?';
                    $updateValues[] = $crpBranchName;
                }
                
                if (isset($input['system_baseline_id'])) {
                    $updateFields[] = 'system_baseline_id = ?';
                    $updateValues[] = $systemBaselineId;
                }
                
                if (isset($input['system_baseline_name'])) {
                    $updateFields[] = 'system_baseline_name = ?';
                    $updateValues[] = $systemBaselineName;
                }
                
                if (isset($input['pm'])) {
                    $updateFields[] = 'pm = ?';
                    $updateValues[] = $input['pm'];
                }
                
                $updateValues[] = $projectId;
                $sql = "UPDATE projects SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $db->execute($sql, $updateValues);
                
                                        echo json_encode([
                            'success' => true,
                            'message' => '项目更新成功'
                        ]);
                    }
                    break;
                    

                    
                case 'DELETE':
            if (strpos($path, '/projects/') !== false) {
                // 删除项目
                $projectId = basename($path);
                
                $db->execute("DELETE FROM projects WHERE id = ?", [$projectId]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '项目删除成功'
                ]);
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

/**
 * 调用协作平台API创建tag
 * @param string $moduleName 模块名称
 * @param string $ckwzmc 明道云创建tag时需要的完整仓库名对应的id
 * @return array
 */
function createTagInPlatform($moduleName,$ckwzmc,$tagname,$taghash,$taginfo) {
    try {
        // 构建请求数据，参考fetchCooperationData的实现
        $requestData = [
            'appKey' => '4c1c7c8f2092d7ac',
            'sign' => 'NzU4YTJlOTZhNjhkOWFhNzZiNjQ1YjFlNjI2ZDI1YjFjNGM4YWQ3YzQwMTc1YTI4Mzk2Y2FiZWYxOTFjYTdkYw==',
            'worksheetId' => '64890f369d15fe3df14274de',
            'triggerWorkflow' => true,
            'controls' => [
                [
                    'controlId' => 'ckwzmc', // 仓库完整名称
                    'value' => $ckwzmc
                ],
                [
                    'controlId' => 'ckmc', // 仓库名称
                    'value' => $moduleName
                ],
                [
                    'controlId' => 'glfhbb_bt_', // 关联发行版本
                    'value' => 'UOS 20 专业版 1070Update4',
                    'valueType' => 1
                ],
                [
                    'controlId' => 'tagmc', // Tag名称
                    'value' => $tagname
                ],
                [
                    'controlId' => 'revisionz', // revision值
                    'value' => $taghash
                ],
                [
                    'controlId' => 'tagmsxx', // Tag描述信息
                    'value' => $taginfo
                ],
                [
                    'controlId' => 'sqyy', // 申请原因
                    'value' => $taginfo
                ],
                [
                    'controlId' => 'shenqingren', // 申请人
                    'value' => '78b6d1ad-f643-41d0-8d6f-e676d7fd23c6'
                ]
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, COOPERATION_API . '/v2/open/worksheet/addRow'); // TODO: 需要确认实际的API端点
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // 增强日志记录
        error_log("协作平台创建Tag API调用 - 模块名称: {$moduleName}");
        error_log("协作平台创建Tag API响应 - HTTP状态码: {$httpCode}, 响应长度: " . ($response));
        
        if ($curlError) {
            error_log("协作平台创建Tag API网络错误: {$curlError}");
            return [
                'success' => false,
                'message' => '网络连接错误: ' . $curlError
            ];
        }
        
        if ($httpCode !== 200) {
            error_log("协作平台创建Tag API请求失败，HTTP状态码: {$httpCode}, 响应: " . substr($response, 0, 500));
            return [
                'success' => false,
                'message' => 'API请求失败，状态码: ' . $httpCode
            ];
        }
        
        $data = json_decode($response, true);
        if (!$data) {
            error_log("协作平台创建Tag API响应格式错误，响应内容: " . substr($response, 0, 1000));
            return [
                'success' => false,
                'message' => 'API响应格式错误'
            ];
        }
        
        // 检查API返回的成功状态
        if (isset($data['success']) && $data['success'] === true) {
            return [
                'success' => true,
                'message' => 'Tag创建成功',
                'data' => [
                    'module_name' => $moduleName,
                    'response' => $data
                ]
            ];
        } else {
            $errorMessage = $data['error_msg'] ?? '未知错误';
            error_log("协作平台创建Tag失败: {$errorMessage}");
            return [
                'success' => false,
                'message' => '创建失败: ' . $errorMessage
            ];
        }
        
    } catch (Exception $e) {
        error_log("调用协作平台创建Tag API异常: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '系统异常: ' . $e->getMessage()
        ];
    }
}



/**
 * 根据branch_commit和build_commit的比较更新模块状态
 * @param int $projectModuleId 项目模块ID
 * @param string|null $branchCommit 分支最新提交
 * @param string|null $buildCommit 构建提交
 * @param Database $db 数据库实例
 */
function updateModuleStatus($projectModuleId, $branchCommit, $buildCommit, $db) {
    $status = '未知';
    
    if ($buildCommit === null) {
        $status = '未知';
    } elseif ($branchCommit === null) {
        $status = '未知';
    } elseif ($branchCommit === $buildCommit) {
        $status = '已更新';
    } else {
        $status = '有更新';
    }
    
    $db->execute("
        UPDATE project_modules 
        SET status = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ", [$status, $projectModuleId]);
}

/**
 * 构建PMS备注内容
 * @param array $modules 模块信息数组
 * @return string 备注内容
 */
function buildPmsRemarkContent($repoAddr, $modules) {
    $content = "<p><strong>【提测信息汇总】</strong></p>\n\n";

    $content .= "<p>仓库地址: {$repoAddr}</p>\n";
    $content .= "<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\">\n";
    $content .= "<thead><tr><th>模块名称</th><th>打包分支</th><th>打包版本</th><th>打包提交</th></tr></thead>\n";
    $content .= "<tbody>\n";

    foreach ($modules as $module) {
        $moduleName = htmlspecialchars($module['module_name'] ?? '-');
        $buildBranch = htmlspecialchars($module['build_branch'] ?? '-');
        $buildVersion = htmlspecialchars($module['build_version'] ?? '-');
        $buildCommit = htmlspecialchars($module['build_commit'] ?? '-');

        $content .= "<tr><td>{$moduleName}</td><td>{$buildBranch}</td><td>{$buildVersion}</td><td>{$buildCommit}</td></tr>\n";
    }

    $content .= "</tbody>\n</table>\n<br>\n";

    return $content;
}

/**
 * 给PMS工单添加备注
 * @param string $pmsUrl PMS链接
 * @param string $remarkContent 备注内容
 * @return array 操作结果
 */
function addRemarkToPms($pmsUrl, $remarkContent) {
    if (empty($pmsUrl) || empty($remarkContent)) {
        return ['success' => false, 'message' => 'PMS链接和备注内容不能为空'];
    }

    if (!preg_match('/\/(bug|task)-view-(\d+)\.html/', $pmsUrl, $matches)) {
        return ['success' => false, 'message' => '无效的PMS链接格式'];
    }
    $type = $matches[1];
    $id = $matches[2];

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $apiUrl = $protocol . '://' . $host . '/api/pms-api';

    // 异步模式 - 发送请求后不等待响应，立即返回成功
    // PMS 会在后台处理请求
    $postData = json_encode([
        'action' => 'addComment',
        'type' => $type,
        'id' => $id,
        'content' => $remarkContent,
    ]);

    // 使用 curl 的异步模式，发送请求后不等待响应
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => false,  // 不等待响应
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 5,             // 短超时，请求发送成功即可
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ]);
    
    curl_exec($ch);
    curl_close($ch);

    // 请求已发送成功，PMS会在后台处理
    return ['success' => true, 'message' => '请求已发送，PMS将异步处理'];
}

/**
 * 批量给PMS工单添加备注（只登录一次，效率更高）
 * @param array $remarks [['pmsUrl' => '...', 'content' => '...'], ...]
 * @return array 操作结果
 */
function addRemarksToPms($remarks) {
    if (empty($remarks) || !is_array($remarks)) {
        return ['success' => false, 'message' => '备注列表不能为空'];
    }

    $comments = [];
    foreach ($remarks as $item) {
        if (!preg_match('/\/(bug|task)-view-(\d+)\.html/', $item['pmsUrl'], $matches)) {
            continue;
        }
        $comments[] = [
            'type' => $matches[1],
            'id' => $matches[2],
            'content' => $item['content'],
        ];
    }

    if (empty($comments)) {
        return ['success' => false, 'message' => '没有有效的PMS链接'];
    }

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $apiUrl = $protocol . '://' . $host . '/api/pms-api';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'action' => 'addCommentsBatch',
            'comments' => $comments,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'message' => '调用pms-api失败: ' . $error];
    }

    return json_decode($response, true) ?: ['success' => false, 'message' => '响应格式错误'];
}

// 获取最近动态数据
function getRecentActivities($db, $page = 1, $limit = 6) {
    $page = max(1, (int)$page);
    $limit = max(1, (int)$limit);
    $offset = ($page - 1) * $limit;
    
    // 计算每个表需要查询的记录数（足够覆盖当前页）
    $fetchLimit = $limit * 3; // 每个表查询足够多的记录
    
    // 查询项目
    $projects = $db->fetchAll("
        SELECT 
            'project' as type,
            p.id as item_id,
            p.name as name,
            p.description,
            p.project_type,
            p.pm,
            p.crp_branch_name,
            p.created_at,
            p.updated_at,
            p.id as project_id
        FROM projects p
        ORDER BY p.created_at DESC
        LIMIT {$fetchLimit}
    ");
    
    // 查询交付记录
    $deliveries = $db->fetchAll("
        SELECT 
            'delivery' as type,
            d.id as item_id,
            d.crp_name as name,
            d.crp_id,
            d.crp_addr,
            d.repo_addr,
            d.created_at,
            d.updated_at,
            d.project_id
        FROM project_deliver_record d
        ORDER BY d.created_at DESC
        LIMIT {$fetchLimit}
    ");
    
    // 查询包
    $packages = $db->fetchAll("
        SELECT 
            'package' as type,
            pkg.id as item_id,
            m.name as module_name,
            pkg.build_branch,
            pkg.cur_tag as build_version,
            pkg.build_commit,
            pkg.build_state,
            pkg.build_id,
            pkg.build_itemid,
            pkg.created_at,
            pkg.updated_at,
            pdr.project_id,
            pdr.crp_name as topic_name,
            pdr.crp_id as topic_id,
            p.name as project_name
        FROM deliver_package pkg
        LEFT JOIN modules m ON pkg.module_id = m.id
        LEFT JOIN project_deliver_record pdr ON pkg.delivery_record_id = pdr.id
        LEFT JOIN projects p ON pdr.project_id = p.id
        ORDER BY pkg.created_at DESC
        LIMIT {$fetchLimit}
    ");
    
    // 获取每个表的总记录数
    $projectCount = $db->fetch("SELECT COUNT(*) as total FROM projects")['total'];
    $deliveryCount = $db->fetch("SELECT COUNT(*) as total FROM project_deliver_record")['total'];
    $packageCount = $db->fetch("SELECT COUNT(*) as total FROM deliver_package")['total'];
    $total = $projectCount + $deliveryCount + $packageCount;
    
    // 合并数据
    $allData = array_merge($projects, $deliveries, $packages);
    
    // 按 created_at 倒序排序
    usort($allData, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // 分页
    $data = array_slice($allData, $offset, $limit);
    
    return [
        'data' => $data,
        'page' => $page,
        'limit' => $limit,
        'total' => $total
    ];
}

// 获取任务队列状态 0排队,1进行,2完成,3放弃
function getTaskQueue($db, $updateStatus) {

    if ($updateStatus == '1') {
        error_log("更新已经编译完成的任务状态");

        // 更新已完成的任务状态
        $db->execute("
            UPDATE build_queue bq
            INNER JOIN deliver_package dp ON bq.deliver_package_id = dp.id
            SET bq.status = 2
            WHERE bq.status = 1
            AND dp.build_state IN ('已放弃','UPLOAD_CRP','UPLOAD_OK')
        ");
    }

    // 检查排队的任务是否可以开始
    checkWaitingTask($db);

    $tasks = $db->fetchAll("
        SELECT * FROM (
            -- status 0, 1 全部记录
            (SELECT
                bq.id as task_id,
                bq.status as task_status,
                bq.deliver_package_id,
                bq.build_json,
                bq.created_at as start_time,
                bq.updated_at as queue_time,
                p.name as project_name,
                dr.crp_name as deliver_name,
                dp.cur_tag as build_version,
                dp.build_state as build_state,
                dp.build_itemid,
                m.name as module_name
            FROM build_queue bq
            INNER JOIN deliver_package dp
                ON bq.deliver_package_id = dp.id
            INNER JOIN project_deliver_record dr
                ON dp.delivery_record_id = dr.id
            INNER JOIN projects p
                ON dr.project_id = p.id
            INNER JOIN modules m
                ON dp.module_id = m.id
            WHERE bq.status IN (0, 1))

            UNION ALL

            -- status 2, 3 只取5条
            (SELECT
                bq.id as task_id,
                bq.status as task_status,
                bq.deliver_package_id,
                bq.build_json,
                bq.created_at as start_time,
                bq.updated_at as queue_time,
                p.name as project_name,
                dr.crp_name as deliver_name,
                dp.cur_tag as build_version,
                dp.build_state as build_state,
                dp.build_itemid,
                m.name as module_name
            FROM build_queue bq
            INNER JOIN deliver_package dp
                ON bq.deliver_package_id = dp.id
            INNER JOIN project_deliver_record dr
                ON dp.delivery_record_id = dr.id
            INNER JOIN projects p
                ON dr.project_id = p.id
            INNER JOIN modules m
                ON dp.module_id = m.id
            WHERE bq.status IN (2, 3)
            ORDER BY bq.updated_at DESC
            LIMIT 5)
        ) AS combined
        ORDER BY
            CASE task_status
                WHEN 0 THEN 1
                WHEN 1 THEN 2
                ELSE 3
            END,
            queue_time DESC
    ");

    return $tasks;
}

// 检查排队的任务是否可以开始
function checkWaitingTask($db) {
    $tasks = $db->fetchAll("SELECT q.*, p.delivery_record_id FROM build_queue q LEFT JOIN deliver_package p ON q.deliver_package_id = p.id WHERE status = 0 AND deliver_package_id != 0");

    foreach ($tasks as $task) {
        // 查询依赖模块的打包状态，检查是否有未完成的依赖
        $uncompleted = $db->fetch("
            SELECT dp.build_module_name,dp.build_state
            FROM deliver_package dp
            JOIN deliver_package dp2 ON dp.delivery_record_id = dp2.delivery_record_id
            JOIN modules m ON m.id = dp2.module_id
            WHERE dp2.id = ?
            AND FIND_IN_SET(dp.module_id, m.dependencies)
            AND dp.build_state NOT IN ('已放弃','UPLOAD_CRP','UPLOAD_OK')
            LIMIT 1", [$task['deliver_package_id']]);

        // 当依赖都已完成（查询为空）时，执行打包
        if (!$uncompleted) {
            $params = json_decode($task['build_json'], true);
            if (!$params) {
                error_log("build_json 解析失败: " . $task['build_json']);
                continue;
            }

            $deliveryRecordId = $task['delivery_record_id'];
            $module = $params['module'];
            $buildItemId = $params['buildItemId'];
            $version = $params['version'];
            $hash = $params['hash'];

            error_log("开始执行排队任务 - deliver_package_id: {$task['deliver_package_id']}, module: {$module['module_name']}");

            $buildResult = callCrpBuildApi($params['topicId'], $buildItemId, $module, $version, $hash);

            if ($buildResult['success']) {
                $buildHash = $hash ?: ($module['branch_commit'] ?? '');
                $newBuildItemId = getBuildItemId($buildResult, $buildItemId);

                // 更新 deliver_package 记录
                $buildPackageId = updateBuildRecord($db, $deliveryRecordId, $module, $version, $buildHash, $newBuildItemId);

                // 更新任务队列
                if ($buildPackageId > 0) {
                    updateTaskQueue($db, $buildPackageId);
                }

                // 更新项目模块的打包信息
                $db->execute("
                    UPDATE project_modules
                    SET build_cur_tag = ?, build_commit = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?", [$version, $buildHash, $module['module_id']]);

                error_log("排队任务打包成功 - deliver_package_id: {$task['deliver_package_id']}");
            } else {
                error_log("排队任务打包失败 - deliver_package_id: {$task['deliver_package_id']}, error: " . ($buildResult['message'] ?? '未知错误'));
            }

            // 更新任务状态为进行中
            $db->execute("UPDATE build_queue SET status = 1 WHERE id = ?", [$task['id']]);
        } else {
            error_log("排队等待：依赖没有打完，{$uncompleted['build_module_name']} -- {$uncompleted['build_state']}");
        }
    }
}