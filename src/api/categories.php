<?php
// 技术分类API接口

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
$path = $_SERVER['REQUEST_URI'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'categories' || $action === '') {
                // 获取所有技术分类
                $categories = $db->fetchAll("
                    SELECT c.*, COUNT(m.id) as module_count 
                    FROM tech_categories c 
                    LEFT JOIN modules m ON c.id = m.category_id 
                    GROUP BY c.id 
                    ORDER BY c.name
                ");
                
                echo json_encode([
                    'success' => true,
                    'data' => $categories
                ]);
            } elseif ($action === 'modules') {
                // 获取指定分类的模块
                $categoryId = $_GET['category_id'] ?? null;
                $projectId = $_GET['project_id'] ?? null;

                if ($categoryId) {
                    $modules = $db->fetchAll("
                        SELECT m.*,
                               (SELECT GROUP_CONCAT(m2.name ORDER BY m2.name SEPARATOR ', ')
                                  FROM modules m2
                                  WHERE FIND_IN_SET(m2.id, m.dependencies) > 0
                               ) as dependencies_names,
                               c.name as category_name
                        FROM modules m
                        JOIN tech_categories c ON m.category_id = c.id
                        WHERE m.category_id = ?
                        ORDER BY m.name
                    ", [$categoryId]);
                } else if($projectId) {
                    $modules = $db->fetchAll("
                        SELECT m.*,
                               (SELECT GROUP_CONCAT(m2.name ORDER BY m2.name SEPARATOR ', ')
                                  FROM modules m2
                                  WHERE FIND_IN_SET(m2.id, m.dependencies) > 0
                               ) as dependencies_names,
                               c.name as category_name
                        FROM modules m
                        JOIN tech_categories c ON m.category_id = c.id
                        left JOIN project_modules p ON m.id = p.module_id and p.project_id=?
                        where p.project_id is null
                        ORDER BY c.name, m.name
                    ", [$projectId]);
                } else {
                    $modules = $db->fetchAll("
                        SELECT m.*,
                               (SELECT GROUP_CONCAT(m2.name ORDER BY m2.name SEPARATOR ', ')
                                  FROM modules m2
                                  WHERE FIND_IN_SET(m2.id, m.dependencies) > 0
                               ) as dependencies_names,
                               c.name as category_name
                        FROM modules m
                        JOIN tech_categories c ON m.category_id = c.id
                        ORDER BY c.name, m.name
                    ");
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $modules
                ]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['name']) && !isset($input['category_id'])) {
                // 创建新分类
                $name = $input['name'] ?? '';
                $description = $input['description'] ?? '';
                
                if (empty($name)) {
                    throw new Exception('分类名称不能为空');
                }
                
                $db->execute("
                    INSERT INTO tech_categories (name, description) 
                    VALUES (?, ?)
                ", [$name, $description]);
                
                $categoryId = $db->lastInsertId();
                
                echo json_encode([
                    'success' => true,
                    'message' => '分类创建成功',
                    'data' => ['id' => $categoryId]
                ]);
                
            } else {
                // 创建新模块
                $moduleId = $input['id'] ?? 0;
                $categoryId = $input['category_id'] ?? 0;
                $name = $input['name'] ?? '';
                $repoUrl = $input['repo_url'] ?? '';
                $mainBranch = $input['main_branch'] ?? 'main';
                $currentVersion = $input['current_version'] ?? 'v1.0.0';
                $status = $input['status'] ?? 'active';
                $buildType = $input['buildType'] ?? '0';
                
                if (empty($moduleId) || empty($name) || empty($repoUrl)) {
                    throw new Exception('模块ID、模块名称和仓库地址不能为空');
                }
                
                if (!is_numeric($moduleId) || $moduleId <= 0) {
                    throw new Exception('模块ID必须是大于0的数字');
                }
                
                // 检查模块ID是否已存在
                $existingModule = $db->fetch("SELECT id FROM modules WHERE id = ?", [$moduleId]);
                if ($existingModule) {
                    throw new Exception('模块ID已存在，请使用其他ID');
                }
                
                // 获取别名
                $alias = $data['alias'] ?? null;
                $packageName = $data['package_name'] ?? null;
                
                $db->execute("
                    INSERT INTO modules (id, category_id, name, alias, package_name, repo_url, main_branch, current_version, status, build_type) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [$moduleId, $categoryId, $name, $alias, $packageName, $repoUrl, $mainBranch, $currentVersion, $status, $buildType]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '模块创建成功',
                    'data' => ['id' => $moduleId]
                ]);
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            // 从URL路径中提取ID
            $pathParts = explode('/', trim($path, '/'));
            $moduleId = end($pathParts);
            
            if (is_numeric($moduleId)) {
                // 更新模块
                $categoryId = $input['category_id'] ?? 0;
                $name = $input['name'] ?? '';
                $repoUrl = $input['repo_url'] ?? '';
                $mainBranch = $input['main_branch'] ?? '';
                $currentVersion = $input['current_version'] ?? '';
                $status = $input['status'] ?? 'active';
                $aliasName = $input['alias'] ?? '';
                $packageName = $input['package_name'] ?? '';

                // 构建更新语句
                $updateFields = [];
                $updateValues = [];

                if (!empty($name)) {
                    $updateFields[] = 'name = ?';
                    $updateValues[] = $name;
                }
                // 处理别名字段（允许为空）
                if (!empty($aliasName)) {
                    $updateFields[] = 'alias = ?';
                    $updateValues[] = $aliasName;
                }
                // 处理包名字段（允许为空）
                if (!empty($packageName)) {
                    $updateFields[] = 'package_name = ?';
                    $updateValues[] = $packageName;
                }
                if (!empty($repoUrl)) {
                    $updateFields[] = 'repo_url = ?';
                    $updateValues[] = $repoUrl;
                }
                if (!empty($mainBranch)) {
                    $updateFields[] = 'main_branch = ?';
                    $updateValues[] = $mainBranch;
                }
                if (!empty($currentVersion)) {
                    $updateFields[] = 'current_version = ?';
                    $updateValues[] = $currentVersion;
                }
                if ($categoryId > 0) {
                    $updateFields[] = 'category_id = ?';
                    $updateValues[] = $categoryId;
                }

                // 处理依赖字段（区分"未提供"和"显式清空"）
                if (array_key_exists('dependencies', $input)) {
                    $updateFields[] = 'dependencies = ?';
                    $updateValues[] = empty($input['dependencies']) ? null : $input['dependencies'];
                }

                // 免得被瞎改
                // $updateFields[] = 'build_type = ?';
                // $updateValues[] = $buildType;

                if (!empty($updateFields)) {
                    $updateValues[] = $moduleId;
                    $sql = "UPDATE modules SET " . implode(', ', $updateFields) . " WHERE id = ?";
                    $db->execute($sql, $updateValues);
                }

                echo json_encode([
                    'success' => true,
                    'message' => '模块更新成功'
                ]);
            } else {
                throw new Exception('无效的模块ID');
            }
            break;
            
        case 'DELETE':
            // 从URL路径中提取ID
            $pathParts = explode('/', trim($path, '/'));
            $moduleId = end($pathParts);
            
            if (is_numeric($moduleId)) {
                // 删除模块
                $db->execute("DELETE FROM modules WHERE id = ?", [$moduleId]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '模块删除成功'
                ]);
            } else {
                throw new Exception('无效的模块ID');
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
