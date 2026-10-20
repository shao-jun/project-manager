# 部署文档

## 环境要求

### 系统要求
- **操作系统**: Linux (Ubuntu 20.04+), macOS 10.15+, Windows 10+
- **PHP**: 8.0 或更高版本
- **MySQL**: 8.0 或更高版本
- **Web服务器**: Apache 2.4+ 或 Nginx 1.18+
- **内存**: 最少 2GB RAM
- **磁盘空间**: 最少 1GB 可用空间

### PHP扩展要求
```bash
# 必需的PHP扩展
- pdo
- pdo_mysql
- json
- curl
- mbstring
- openssl
```

## 快速部署

### 1. 克隆项目
```bash
git clone <repository-url>
cd localhost
```

### 2. 安装依赖
```bash
# 安装PHP依赖（如果有）
composer install

# 安装Node.js依赖（如果有）
npm install
```

### 3. 配置数据库
```bash
# 创建数据库
mysql -u root -p
CREATE DATABASE dev_automation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. 导入数据库结构
```bash
# 导入初始化脚本
mysql -u root -p dev_automation < src/database/init.sql

# 导入配置表
mysql -u root -p dev_automation < src/database/config_tables.sql

# 如果是升级已有数据库，执行迁移脚本
mysql -u root -p dev_automation < src/database/migrations/001_add_website_config_credentials.sql
```

### 5. 配置数据库连接
编辑 `src/config/database.php` 文件：
```php
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'dev_automation',
    'username' => 'your_username',
    'password' => 'your_password',
    // ... 其他配置
];
```

### 6. 启动开发服务器
```bash
# 使用PHP内置服务器
php -S localhost:8000 -t public

# 或使用Apache/Nginx
# 配置Web服务器指向 public/ 目录
```

### 7. 访问应用
打开浏览器访问：`http://localhost:8000`

## 生产环境部署

### 1. 使用Docker部署

#### 创建Dockerfile
```dockerfile
FROM php:8.1-apache

# 安装PHP扩展
RUN docker-php-ext-install pdo pdo_mysql

# 复制项目文件
COPY . /var/www/html/

# 设置权限
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/

# 配置Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
```

#### 使用Docker Compose
```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "80:80"
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=dev_automation
      - DB_USER=root
      - DB_PASS=password

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: dev_automation
    volumes:
      - mysql_data:/var/lib/mysql
      - ./src/database/init.sql:/docker-entrypoint-initdb.d/init.sql

volumes:
  mysql_data:
```

启动服务：
```bash
docker-compose up -d
```

### 2. 使用Apache部署

#### 配置虚拟主机
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/localhost/public
    
    <Directory /path/to/localhost/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

#### 启用重写模块
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 3. 使用Nginx部署

#### 配置Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/localhost/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 环境配置

### 1. 开发环境配置

创建 `.env` 文件：
```env
# 数据库配置
DB_HOST=localhost
DB_PORT=3306
DB_NAME=dev_automation
DB_USER=root
DB_PASS=

# 应用配置
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# 安全配置
APP_KEY=your-secret-key
```

### 2. 生产环境配置

```env
# 数据库配置
DB_HOST=localhost
DB_PORT=3306
DB_NAME=dev_automation
DB_USER=prod_user
DB_PASS=strong_password

# 应用配置
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# 安全配置
APP_KEY=production-secret-key
```

## 数据库迁移

### 1. 运行迁移脚本

#### 方法一：使用PHP升级脚本（推荐）
```bash
# 自动执行所有迁移文件
php src/database/upgrade.php
```

#### 方法二：手动执行SQL文件
```bash
# 手动运行特定迁移
mysql -u root -p dev_automation < src/database/migrations/001_add_website_config_credentials.sql

# 或者批量运行所有迁移文件
for file in src/database/migrations/*.sql; do
    echo "执行迁移: $file"
    mysql -u root -p dev_automation < "$file"
done
```

### 2. 回滚迁移
```bash
# 回滚到指定版本
php src/database/migrate.php --rollback=001
```

## 安全配置

### 1. 文件权限设置
```bash
# 设置正确的文件权限
chmod 755 public/
chmod 644 public/index.php
chmod 600 src/config/database.php
chmod -R 755 src/assets/
```

### 2. 防火墙配置
```bash
# 只允许必要端口
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

### 3. SSL证书配置
```bash
# 使用Let's Encrypt
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

## 性能优化

### 1. PHP优化
编辑 `php.ini`：
```ini
# 内存限制
memory_limit = 256M

# 执行时间
max_execution_time = 30

# 上传文件大小
upload_max_filesize = 10M
post_max_size = 10M

# OPcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
```

### 2. MySQL优化
编辑 `my.cnf`：
```ini
[mysqld]
# 缓冲池大小
innodb_buffer_pool_size = 1G

# 查询缓存
query_cache_size = 64M
query_cache_type = 1

# 连接数
max_connections = 200
```

### 3. Web服务器优化

#### Apache优化
```apache
# 启用压缩
LoadModule deflate_module modules/mod_deflate.so
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

#### Nginx优化
```nginx
# 启用gzip压缩
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

# 静态文件缓存
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## 监控和日志

### 1. 日志配置
```bash
# 创建日志目录
mkdir -p /var/log/dev-automation

# 配置日志轮转
sudo nano /etc/logrotate.d/dev-automation
```

### 2. 监控脚本
```bash
#!/bin/bash
# 健康检查脚本
curl -f http://localhost/health || exit 1
```

### 3. 备份策略
```bash
#!/bin/bash
# 数据库备份脚本
mysqldump -u root -p dev_automation > backup_$(date +%Y%m%d_%H%M%S).sql
```

## 故障排除

### 1. 常见问题

#### 数据库连接失败
```bash
# 检查MySQL服务状态
sudo systemctl status mysql

# 检查数据库配置
php -r "require 'src/config/database.php'; print_r(require 'src/config/database.php');"
```

#### 权限问题
```bash
# 检查文件权限
ls -la public/
ls -la src/config/

# 修复权限
chown -R www-data:www-data /path/to/localhost
```

#### 500错误
```bash
# 检查错误日志
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log

# 检查PHP错误日志
tail -f /var/log/php_errors.log
```

### 2. 调试模式
```php
// 在开发环境中启用调试
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### 3. 性能分析
```bash
# 使用Xdebug进行性能分析
# 或使用内置的性能分析工具
php -d memory_limit=512M -d max_execution_time=60 script.php
```

## 更新和维护

### 1. 代码更新
```bash
# 拉取最新代码
git pull origin main

# 更新依赖
composer install --no-dev --optimize-autoloader
npm install --production

# 运行数据库迁移
php src/database/migrate.php
```

### 2. 数据库备份
```bash
# 创建备份
mysqldump -u root -p dev_automation > backup.sql

# 恢复备份
mysql -u root -p dev_automation < backup.sql
```

### 3. 系统更新
```bash
# 更新系统包
sudo apt update && sudo apt upgrade

# 重启服务
sudo systemctl restart apache2
sudo systemctl restart mysql
```

## 支持

如果遇到部署问题，请：

1. 检查错误日志
2. 确认环境要求
3. 验证配置文件
4. 联系技术支持

更多信息请参考：
- [架构设计文档](ARCHITECTURE.md)
- [项目说明文档](README.md) 