<?php
// 调试API连接

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>API调试信息</h1>";

try {
    echo "<h2>1. 检查文件路径</h2>";
    echo "当前目录: " . __DIR__ . "<br>";
    echo "Database.php路径: " . __DIR__ . '/../classes/Database.php' . "<br>";
    echo "数据库配置路径: " . __DIR__ . '/../config/database.php' . "<br>";
    
    echo "<h2>2. 检查文件是否存在</h2>";
    echo "Database.php存在: " . (file_exists(__DIR__ . '/../classes/Database.php') ? '是' : '否') . "<br>";
    echo "database.php存在: " . (file_exists(__DIR__ . '/../config/database.php') ? '是' : '否') . "<br>";
    
    echo "<h2>3. 尝试包含Database类</h2>";
    require_once __DIR__ . '/../classes/Database.php';
    echo "Database类包含成功<br>";
    
    echo "<h2>4. 尝试连接数据库</h2>";
    $db = Database::getInstance();
    echo "数据库连接成功<br>";
    
    echo "<h2>5. 测试查询</h2>";
    $categories = $db->fetchAll("SELECT * FROM tech_categories ORDER BY name");
    echo "查询结果数量: " . count($categories) . "<br>";
    
    echo "<h2>6. 查询结果</h2>";
    echo "<pre>" . print_r($categories, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h2>错误信息</h2>";
    echo "错误: " . $e->getMessage() . "<br>";
    echo "文件: " . $e->getFile() . "<br>";
    echo "行号: " . $e->getLine() . "<br>";
}
?> 