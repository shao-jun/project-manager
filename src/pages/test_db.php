<?php
// 数据库连接测试页面

require_once '../classes/Database.php';

echo "<h1>数据库连接测试</h1>";

try {
    $db = Database::getInstance();
    
    echo "<h2>✅ 数据库连接成功</h2>";
    
    // 测试技术分类
    $categories = $db->fetchAll("SELECT * FROM tech_categories ORDER BY name");
    echo "<h3>技术分类 (" . count($categories) . "个):</h3>";
    foreach ($categories as $category) {
        echo "<p>- {$category['name']}: {$category['description']}</p>";
    }
    
    // 测试模块数量
    $moduleCounts = $db->fetchAll("
        SELECT c.name, COUNT(m.id) as module_count 
        FROM tech_categories c 
        LEFT JOIN modules m ON c.id = m.category_id 
        GROUP BY c.id 
        ORDER BY c.name
    ");
    echo "<h3>模块统计:</h3>";
    foreach ($moduleCounts as $count) {
        echo "<p>- {$count['name']}: {$count['module_count']}个模块</p>";
    }
    
    // 测试项目
    $projects = $db->fetchAll("
        SELECT p.*, c.name as category_name 
        FROM projects p 
        JOIN tech_categories c ON p.category_id = c.id 
        ORDER BY p.name
    ");
    echo "<h3>项目 (" . count($projects) . "个):</h3>";
    foreach ($projects as $project) {
        echo "<p>- {$project['name']} ({$project['category_name']}) - {$project['status']}</p>";
    }
    
    echo "<h2>🎉 所有测试通过！</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ 数据库连接失败</h2>";
    echo "<p>错误信息: " . $e->getMessage() . "</p>";
}
?> 