# 架构设计文档

## 系统架构概述

研发自动化管理平台采用现代化的Web应用架构，基于PHP后端和前端分离的设计模式，提供RESTful API接口和响应式用户界面。

## 整体架构

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   前端界面      │    │   API接口层     │    │   数据库层      │
│   (Pages)       │◄──►│   (API)         │◄──►│   (MySQL)       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   静态资源      │    │   配置管理      │    │   数据迁移      │
│   (Assets)      │    │   (Config)      │    │   (Migrations)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 目录结构设计

### 1. 源代码目录 (`src/`)

```
src/
├── api/                    # API接口层
│   ├── categories.php      # 技术分类API
│   ├── projects.php        # 项目管理API
│   ├── code-review.php     # 代码审查API
│   ├── website_configs.php # 网站配置API
│   ├── config.php          # 系统配置API
│   └── http_client.php     # HTTP客户端
├── pages/                  # 页面层
│   ├── header.php          # 通用头部
│   ├── index.php           # 首页
│   ├── tech-categories.php # 技术分类页面
│   ├── projects.php        # 项目管理页面
│   ├── code-review.php     # 代码审查页面
│   ├── config.php          # 系统配置页面
│   └── monitor.php         # 监控面板页面
├── config/                 # 配置层
│   └── database.php        # 数据库配置
├── database/               # 数据库层
│   ├── init.sql            # 初始化脚本
│   ├── config_tables.sql   # 配置表结构
│   └── migrations/         # 数据迁移
└── assets/                 # 静态资源
    ├── css/                # 样式文件
    └── js/                 # JavaScript文件
```

### 2. 公共访问目录 (`public/`)

```
public/
└── index.php              # 主入口文件（路由分发）
```

## 核心组件设计

### 1. 数据库连接层

**文件**: `src/config/database.php`

```php
class Database {
    private static $instance = null;
    private $pdo;
  
    // 单例模式
    public static function getInstance();
  
    // 数据库操作方法
    public function fetchAll($sql, $params = []);
    public function fetch($sql, $params = []);
    public function execute($sql, $params = []);
    public function lastInsertId();
}
```

**设计原则**:

- 单例模式确保全局唯一连接
- PDO预处理语句防止SQL注入
- 统一的错误处理机制

### 2. API接口层

**设计模式**: RESTful API

**统一响应格式**:

```json
{
    "success": true/false,
    "data": {...},
    "message": "操作结果描述",
    "error": "错误信息（可选）"
}
```

**HTTP状态码**:

- 200: 成功
- 400: 请求错误
- 404: 资源不存在
- 500: 服务器错误

### 3. 页面层

**设计模式**: 模板化页面

**通用组件**:

- `header.php`: 统一的页面头部
- 响应式布局设计
- Bootstrap 5 UI框架

## 数据库设计

### 核心表结构

#### 1. 技术分类表 (`tech_categories`)

```sql
CREATE TABLE tech_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 2. 模块表 (`modules`)

```sql
CREATE TABLE modules (
    pk_id INT PRIMARY KEY AUTO_INCREMENT,
    id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    repo_url VARCHAR(500),
    main_branch VARCHAR(50) DEFAULT 'main',
    current_version VARCHAR(50),
    status ENUM('active', 'inactive', 'deprecated') DEFAULT 'active',
    build_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES tech_categories(id)
);
```

#### 3. 用户表 (`users`)

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 4. 网站配置表 (`website_configs`)

```sql
CREATE TABLE website_configs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    homepage VARCHAR(500),
    token_url VARCHAR(500),
    username VARCHAR(100),
    password VARCHAR(255),
    password_encrypted VARCHAR(255),
    token TEXT,
    token_obtained_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 5. 代码审查配置表 (`code_review_configs`)

```sql
CREATE TABLE code_review_configs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    auto_score_enabled BOOLEAN DEFAULT FALSE,
    score_threshold INT DEFAULT 80,
    review_criteria JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 安全设计

### 1. 数据安全

- 密码使用 `password_hash()` 加密存储
- 敏感数据在API响应中自动过滤
- SQL注入防护（PDO预处理语句）

### 2. 访问控制

- CORS头部配置
- 输入数据验证和过滤
- 错误信息不暴露系统内部结构

### 3. 配置安全

- 数据库配置独立管理
- 敏感配置不提交到版本控制
- 环境变量支持

## 性能优化

### 1. 数据库优化

- 索引优化
- 查询语句优化
- 连接池管理

### 2. 前端优化

- 静态资源压缩
- CDN资源引用
- 响应式图片加载

### 3. 缓存策略

- 浏览器缓存
- API响应缓存
- 数据库查询缓存

## 扩展性设计

### 1. 模块化架构

- 功能模块独立
- 接口标准化
- 插件化设计

### 2. 配置驱动

- 数据库配置化
- 功能开关配置
- 主题样式配置

### 3. API版本控制

- 向后兼容
- 版本号管理
- 废弃接口处理

## 开发规范

### 1. 代码规范

- PSR-4 自动加载规范
- PSR-12 代码风格规范
- 统一的命名约定

### 2. 文档规范

- API文档自动生成
- 代码注释规范
- 变更日志维护

### 3. 测试规范

- 单元测试覆盖
- 集成测试
- 性能测试

## 部署架构

### 1. 开发环境

- PHP内置服务器
- 本地MySQL数据库
- 文件系统存储

### 2. 生产环境

- Nginx/Apache Web服务器
- MySQL主从复制
- Redis缓存
- 负载均衡

### 3. 容器化部署

- Docker容器化
- Docker Compose编排
- 环境隔离

## 监控和日志

### 1. 系统监控

- 性能指标监控
- 错误日志记录
- 用户行为分析

### 2. 日志管理

- 结构化日志
- 日志级别控制
- 日志轮转

### 3. 告警机制

- 异常告警
- 性能告警
- 容量告警
