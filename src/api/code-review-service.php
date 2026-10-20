<?php
/**
 * 代码审查后台服务
 * 每10秒输出一次心跳，显示服务运行状态
 */

// 获取服务ID参数
$serviceId = $argv[1] ?? 'unknown';

// 设置进程标题
if (function_exists('cli_set_process_title')) {
    cli_set_process_title("CodeReviewService-{$serviceId}");
}

// 创建PID文件
$pidFile = __DIR__ . "/../tmp/code-review-service-{$serviceId}.pid";
$tmpDir = dirname($pidFile);

// 确保tmp目录存在
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0755, true);
}

// 写入PID文件
file_put_contents($pidFile, getmypid());

// 注册信号处理器，用于优雅关闭
function signalHandler($signal) {
    global $serviceId, $pidFile;
    
    switch ($signal) {
        case SIGTERM:
        case SIGINT:
            echo "[" . date('Y-m-d H:i:s') . "] 代码审查服务 {$serviceId} 收到停止信号，正在关闭...\n";
            
            // 删除PID文件
            if (file_exists($pidFile)) {
                unlink($pidFile);
            }
            
            // 更新数据库状态
            try {
                require_once __DIR__ . '/../config/database.php';
                $db = Database::getInstance();
                $db->execute("
                    UPDATE code_review_services SET 
                        status = 'stopped', 
                        stopped_at = CURRENT_TIMESTAMP
                    WHERE service_id = ?
                ", [$serviceId]);
            } catch (Exception $e) {
                echo "[" . date('Y-m-d H:i:s') . "] 更新数据库状态失败: " . $e->getMessage() . "\n";
            }
            
            echo "[" . date('Y-m-d H:i:s') . "] 代码审查服务 {$serviceId} 已停止\n";
            exit(0);
            break;
    }
}

// 注册信号处理器
pcntl_signal(SIGTERM, 'signalHandler');
pcntl_signal(SIGINT, 'signalHandler');

// 服务启动信息
echo "[" . date('Y-m-d H:i:s') . "] 代码审查服务 {$serviceId} 已启动 (PID: " . getmypid() . ")\n";
echo "[" . date('Y-m-d H:i:s') . "] 服务将每10秒输出一次心跳\n";
echo "[" . date('Y-m-d H:i:s') . "] 按 Ctrl+C 停止服务\n\n";

// 日志轮转配置
$logFile = __DIR__ . "/../tmp/code-review-service-{$serviceId}.log";
$maxLogSize = 5 * 1024 * 1024; // 5MB
$maxLogFiles = 3; // 保留3个历史日志文件

// 心跳计数器
$heartbeatCount = 0;

/**
 * 日志轮转函数
 */
function rotateLogFile($logFile, $maxLogSize, $maxLogFiles) {
    if (!file_exists($logFile)) {
        return;
    }
    
    $fileSize = filesize($logFile);
    if ($fileSize < $maxLogSize) {
        return; // 文件大小未超过限制，不需要轮转
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] 📄 日志文件大小超过限制 ({$fileSize} bytes)，开始轮转...\n";
    
    // 删除最旧的日志文件
    $oldestLogFile = $logFile . '.' . $maxLogFiles;
    if (file_exists($oldestLogFile)) {
        unlink($oldestLogFile);
    }
    
    // 移动现有的历史日志文件
    for ($i = $maxLogFiles - 1; $i >= 1; $i--) {
        $currentFile = $logFile . '.' . $i;
        $nextFile = $logFile . '.' . ($i + 1);
        
        if (file_exists($currentFile)) {
            rename($currentFile, $nextFile);
        }
    }
    
    // 移动当前日志文件为 .1
    rename($logFile, $logFile . '.1');
    
    echo "[" . date('Y-m-d H:i:s') . "] ✅ 日志轮转完成\n";
}

// 主循环
while (true) {
    // 处理信号
    pcntl_signal_dispatch();
    
    // 增加心跳计数
    $heartbeatCount++;
    
    // 输出心跳信息
    $timestamp = date('Y-m-d H:i:s');
    $memoryUsage = memory_get_usage(true);
    $memoryUsageMB = round($memoryUsage / 1024 / 1024, 2);
    
    echo "[{$timestamp}] 💓 心跳 #{$heartbeatCount} - 服务ID: {$serviceId} | 内存使用: {$memoryUsageMB}MB | PID: " . getmypid() . "\n";
    
    // 每50次心跳（约8分钟）输出一次详细状态
    if ($heartbeatCount % 50 === 0) {
        echo "[{$timestamp}] 📊 服务状态报告:\n";
        echo "  - 运行时间: " . formatUptime($heartbeatCount * 10) . "\n";
        echo "  - 心跳次数: {$heartbeatCount}\n";
        echo "  - 内存使用: {$memoryUsageMB}MB\n";
        echo "  - 服务ID: {$serviceId}\n";
        echo "  - 进程ID: " . getmypid() . "\n\n";
    }
    
    // 检查数据库连接状态（每100次心跳检查一次）
    if ($heartbeatCount % 100 === 0) {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::getInstance();
            $result = $db->fetch("SELECT status FROM code_review_services WHERE service_id = ?", [$serviceId]);
            
            if ($result && $result['status'] !== 'running') {
                echo "[{$timestamp}] ⚠️  数据库状态显示服务已停止，正在退出...\n";
                break;
            }
        } catch (Exception $e) {
            echo "[{$timestamp}] ❌ 数据库连接检查失败: " . $e->getMessage() . "\n";
        }
    }
    
    // 检查日志轮转（每200次心跳检查一次，约33分钟）
    if ($heartbeatCount % 200 === 0) {
        rotateLogFile($logFile, $maxLogSize, $maxLogFiles);
    }
    
    // 等待10秒
    sleep(10);
}

// 清理工作
echo "[" . date('Y-m-d H:i:s') . "] 代码审查服务 {$serviceId} 正在清理资源...\n";

// 删除PID文件
if (file_exists($pidFile)) {
    unlink($pidFile);
}

echo "[" . date('Y-m-d H:i:s') . "] 代码审查服务 {$serviceId} 已完全停止\n";

/**
 * 格式化运行时间
 */
function formatUptime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return "{$hours}小时{$minutes}分钟{$secs}秒";
    } elseif ($minutes > 0) {
        return "{$minutes}分钟{$secs}秒";
    } else {
        return "{$secs}秒";
    }
}
?>