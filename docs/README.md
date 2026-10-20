# 研发自动化管理平台

## 项目概述

这是一个现代化的研发自动化管理平台，旨在提高开发团队的协作效率和项目管理能力。平台提供了技术分类管理、项目管理、代码审查、系统配置和监控等功能。

## 项目结构

```
localhost/
├── docs/                    # 文档目录
│   ├── README.md           # 项目说明
│   ├── ARCHITECTURE.md     # 架构设计文档
│   └── DEPLOYMENT.md       # 部署文档
├── src/                    # 源代码目录
│   ├── api/               # API接口
│   ├── pages/             # 页面文件
│   ├── config/            # 配置文件
│   ├── database/          # 数据库文件
│   └── assets/            # 静态资源
│       ├── css/           # 样式文件
│       └── js/            # JavaScript文件
├── public/                # 公共访问目录
│   └── index.php          # 主入口文件
├── docker-compose.yml     # Docker配置
├── composer.json          # PHP依赖
└── package.json           # Node.js依赖
```

## 主要功能

### 1. 技术分类管理
- 创建和管理技术分类
- 为每个分类添加模块
- 模块版本管理和状态跟踪

### 2. 项目管理
- 项目创建和配置
- 项目状态监控
- 项目依赖管理

### 3. 代码审查
- 自动评分配置
- 代码质量检查
- 审查流程管理

### 4. 系统配置
- 用户凭证管理
- 网站配置管理
- Token自动刷新

### 5. 监控面板
- 系统状态监控
- 性能指标展示
- 实时数据更新

## 技术栈

- **后端**: PHP 8.0+
- **数据库**: MySQL 8.0+
- **前端**: HTML5, CSS3, JavaScript (ES6+)
- **UI框架**: Bootstrap 5
- **图标**: Font Awesome 6
- **容器化**: Docker & Docker Compose

## 快速开始

1. 克隆项目
2. 配置数据库
3. 运行数据库迁移
4. 启动开发服务器

详细步骤请参考 [部署文档](DEPLOYMENT.md)

## 开发指南

请参考 [架构设计文档](ARCHITECTURE.md) 了解系统架构和开发规范。

## 许可证

MIT License 