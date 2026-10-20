<?php
/**
 * 数据库升级脚本
 * 用于执行数据库迁移文件
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 加载数据库配置
$config = require __DIR__ . '/../config/database.php';

try {
    // 连接数据库
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    echo "数据库连接成功！\n";
    
    // 获取所有迁移文件
    $migrationsDir = __DIR__ . '/migrations';
    $migrationFiles = glob($migrationsDir . '/*.sql');
    
    if (empty($migrationFiles)) {
        echo "没有找到迁移文件。\n";
        exit(0);
    }
    
    // 按文件名排序
    sort($migrationFiles);
    
    echo "找到 " . count($migrationFiles) . " 个迁移文件：\n";
    
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        echo "执行迁移: $filename ... ";
        
        try {
            // 读取SQL文件内容
            $sql = file_get_contents($file);
            
            if (empty($sql)) {
                echo "跳过（文件为空）\n";
                continue;
            }
            
            // 执行SQL
            $pdo->exec($sql);
            echo "成功\n";
            
        } catch (PDOException $e) {
            echo "失败: " . $e->getMessage() . "\n";
            // 继续执行其他迁移，不中断
        }
    }
    
    echo "\n数据库升级完成！\n";
    
} catch (PDOException $e) {
    echo "数据库连接失败: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "升级失败: " . $e->getMessage() . "\n";
    exit(1);
}
?> 