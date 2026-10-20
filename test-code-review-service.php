<?php
/**
 * 测试代码审查服务
 * 用于验证后台服务是否正常工作
 */

echo "=== 代码审查服务测试 ===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n\n";

// 测试启动服务
echo "1. 测试启动服务...\n";
$serviceId = uniqid('test_', true);
$command = "php src/api/code-review-service.php " . escapeshellarg($serviceId) . " &";
echo "执行命令: {$command}\n";

$output = [];
$returnCode = 0;
exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ 服务启动命令执行成功\n";
    echo "服务ID: {$serviceId}\n";
    
    // 等待几秒让服务启动
    echo "等待3秒让服务启动...\n";
    sleep(3);
    
    // 检查PID文件
    $pidFile = "src/tmp/code-review-service-{$serviceId}.pid";
    if (file_exists($pidFile)) {
        $pid = trim(file_get_contents($pidFile));
        echo "✅ PID文件存在: {$pidFile}\n";
        echo "进程ID: {$pid}\n";
        
        // 检查进程是否在运行
        $checkCommand = "ps -p {$pid} > /dev/null 2>&1";
        exec($checkCommand, $checkOutput, $checkReturnCode);
        
        if ($checkReturnCode === 0) {
            echo "✅ 进程正在运行\n";
            
            // 等待10秒观察心跳输出
            echo "\n等待10秒观察心跳输出...\n";
            echo "（您应该能在终端看到心跳信息）\n\n";
            sleep(10);
            
            // 停止服务
            echo "\n2. 测试停止服务...\n";
            if (is_numeric($pid)) {
                exec("kill {$pid} 2>/dev/null");
                echo "✅ 发送停止信号到进程 {$pid}\n";
                
                // 等待进程停止
                sleep(2);
                
                // 检查进程是否已停止
                exec($checkCommand, $checkOutput, $checkReturnCode);
                if ($checkReturnCode !== 0) {
                    echo "✅ 进程已成功停止\n";
                } else {
                    echo "⚠️  进程可能仍在运行\n";
                }
            }
            
            // 清理PID文件
            if (file_exists($pidFile)) {
                unlink($pidFile);
                echo "✅ PID文件已清理\n";
            }
        } else {
            echo "❌ 进程未在运行\n";
        }
    } else {
        echo "❌ PID文件不存在\n";
    }
} else {
    echo "❌ 服务启动失败\n";
}

echo "\n=== 测试完成 ===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n";
?>