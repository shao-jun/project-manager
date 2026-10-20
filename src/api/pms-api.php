<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/endpoints.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function getPmsCredentials() {
    global $db;
    $user = $db->fetch("SELECT username, plain_password FROM users WHERE status = 1 LIMIT 1");
    if (!$user || empty($user['username']) || empty($user['plain_password'])) {
        throw new Exception('用户认证配置不完整');
    }
    return ['username' => $user['username'], 'password' => $user['plain_password']];
}

function pmsLogin() {
    $config = getPmsCredentials();
    $baseUrl = PMS_BASE;
    $cookieFile = tempnam(sys_get_temp_dir(), 'pms_');

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl . '/api-getsessionid.json',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    $sessionRtn = curl_exec($ch);
    curl_close($ch);

    $sessionData = json_decode($sessionRtn, true);
    if (!$sessionData || $sessionData['status'] !== 'success') {
        @unlink($cookieFile);
        throw new Exception('获取session失败');
    }
    $sessionInner = json_decode($sessionData['data'], true);
    $sessionId = $sessionInner['sessionID'];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl . '/user-login.json',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'zentaosid' => $sessionId,
            'account' => $config['username'],
            'password' => $config['password'],
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    $loginRtn = curl_exec($ch);
    curl_close($ch);

    $loginData = json_decode($loginRtn, true);
    if (!$loginData || !isset($loginData['user'])) {
        @unlink($cookieFile);
        throw new Exception('登录失败: ' . ($loginData['reason'] ?? '未知错误'));
    }

    return ['sessionId' => $sessionId, 'cookieFile' => $cookieFile];
}

function addPmsCommentSingle($baseUrl, $sessionId, $cookieFile, $type, $id, $content) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl . '/action-comment-' . $type . '-' . $id . '.json',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'zentaosid' => $sessionId,
            't' => 'json',
            'comment' => $content,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    $addRtn = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200;
}

function addPmsComment($type, $id, $content) {
    if (!in_array($type, ['bug', 'task'])) {
        throw new Exception('类型必须是 bug 或 task');
    }
    if (empty($id) || !is_numeric($id)) {
        throw new Exception('单号必须是数字');
    }
    if (empty($content)) {
        throw new Exception('备注内容不能为空');
    }

    $login = pmsLogin();
    $result = addPmsCommentSingle(PMS_BASE, $login['sessionId'], $login['cookieFile'], $type, $id, $content);
    @unlink($login['cookieFile']);

    if (!$result) {
        throw new Exception('添加备注失败');
    }
    return ['success' => true, 'message' => '备注添加成功'];
}

function addPmsCommentsBatch($comments) {
    if (empty($comments) || !is_array($comments)) {
        throw new Exception('备注列表不能为空');
    }

    $login = pmsLogin();
    $baseUrl = PMS_BASE;
    $results = [];

    foreach ($comments as $item) {
        $type = $item['type'] ?? '';
        $id = $item['id'] ?? '';
        $content = $item['content'] ?? '';

        if (!in_array($type, ['bug', 'task']) || !is_numeric($id) || empty($content)) {
            $results[] = ['type' => $type, 'id' => $id, 'success' => false, 'message' => '参数无效'];
            continue;
        }

        $success = addPmsCommentSingle($baseUrl, $login['sessionId'], $login['cookieFile'], $type, $id, $content);
        $results[] = ['type' => $type, 'id' => $id, 'success' => $success, 'message' => $success ? '成功' : '失败'];
    }

    @unlink($login['cookieFile']);
    return $results;
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

try {
    if ($requestMethod === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception('无效的JSON数据');
        }
        $action = $input['action'] ?? '';
        if ($action === 'addComment') {
            $result = addPmsComment($input['type'] ?? '', $input['id'] ?? '', $input['content'] ?? '');
            echo json_encode($result);
        } else if ($action === 'addCommentsBatch') {
            $results = addPmsCommentsBatch($input['comments'] ?? []);
            echo json_encode(['success' => true, 'results' => $results]);
        } else {
            throw new Exception('不支持的操作');
        }
    } else {
        throw new Exception('不支持的请求方法');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>