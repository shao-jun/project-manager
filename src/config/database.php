<?php
/**
 * 数据库配置
 *
 * 敏感信息不要写在这里（本文件会提交到仓库）。
 * 优先级：src/config/database.local.php > 环境变量 > 默认值
 *
 * 本地/内网部署时新建 src/config/database.local.php（已 gitignore）：
 *
 *   return [
 *       'host'     => '127.0.0.1',
 *       'port'     => 3306,
 *       'database' => 'dev_automation',
 *       'username' => 'dev_user',
 *       'password' => '你的密码',
 *   ];
 */

$config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => (int)(getenv('DB_PORT') ?: 3306),
    'database' => getenv('DB_NAME') ?: 'dev_automation',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

// 本地私有配置覆盖（不存在时使用上面的默认值）
$localConfigFile = __DIR__ . '/database.local.php';
if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
    if (is_array($localConfig)) {
        foreach ($localConfig as $key => $value) {
            if (array_key_exists($key, $config)) {
                $config[$key] = $value;
            }
        }
    }
}

// 数据库连接类
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct($config) {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            global $config;
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    // 兼容别名：等同于 fetch()
    public function fetchOne($sql, $params = []) {
        return $this->fetch($sql, $params);
    }
    
    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollback() {
        return $this->pdo->rollback();
    }
}

// 创建全局数据库实例
$db = Database::getInstance();

return $config; 