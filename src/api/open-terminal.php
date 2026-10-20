<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$terminal = '/usr/bin/deepin-terminal';
$logCmd = 'journalctl -u rnd-automation-platform.service -f';

// 动态获取当前用户的环境变量
$uid = posix_getuid();
$display = getenv('DISPLAY') ?: ':0';
$dbusAddress = getenv('DBUS_SESSION_BUS_ADDRESS') ?: "unix:path=/run/user/{$uid}/bus";

$env = "DISPLAY={$display} DBUS_SESSION_BUS_ADDRESS={$dbusAddress}";
exec("{$env} nohup {$terminal} -e '{$logCmd}' > /dev/null 2>&1 &", $output, $returnCode);

echo json_encode([
    'success' => $returnCode === 0,
    'message' => $returnCode === 0 ? '终端已打开' : '打开终端失败',
    'debug' => [
        'uid' => $uid,
        'display' => $display,
        'dbus' => $dbusAddress
    ]
]);