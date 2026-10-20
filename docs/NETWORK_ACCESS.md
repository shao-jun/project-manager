# 网络访问配置指南

本文档说明如何配置研发自动化管理平台，使其可以通过IP地址访问。

## 🌐 通过IP访问

### 快速启动（推荐）

使用项目提供的启动脚本：

```bash
./start_server.sh
```

启动脚本会：
- 自动检测并停止已运行的服务
- 获取本机IP地址
- 显示所有可用的访问地址
- 启动绑定到 `0.0.0.0:8000` 的服务器

### 手动启动

```bash
# 启动服务器，绑定到所有网络接口
php -S 0.0.0.0:8000 -t public
```

### 访问方式

启动后，可以通过以下方式访问：

- **本地访问**: `http://localhost:8000`
- **回环地址**: `http://127.0.0.1:8000`
- **网络IP**: `http://你的IP地址:8000`

例如：
- `http://你的服务器IP:8000`
- `http://你的服务器IP:8000`

### 查看本机IP地址

```bash
# Linux/macOS
hostname -I | awk '{print $1}'

# 或者
ifconfig | grep 'inet ' | grep -v '127.0.0.1' | awk '{print $2}' | head -1

# Windows
ipconfig | findstr "IPv4"
```

## 🔒 安全注意事项

当绑定到 `0.0.0.0` 时，服务器会监听所有网络接口，这意味着：

1. **局域网访问**: 同一局域网内的设备可以访问
2. **外网访问**: 如果有公网IP，外网也可能访问（根据防火墙配置）

### 安全建议

1. **开发环境**: 仅在开发和测试环境使用
2. **防火墙**: 配置防火墙规则，限制访问来源
3. **生产环境**: 生产环境请使用 Apache/Nginx 等专业Web服务器

## 🐳 Docker部署

如果使用Docker，配置已经支持通过IP访问：

```bash
# 启动Docker服务
docker-compose up -d

# 访问地址
# http://localhost  (默认80端口)
# http://你的IP地址
```

## 🔧 故障排除

### 无法通过IP访问

1. **检查服务绑定地址**:
   ```bash
   ps aux | grep "php -S"
   ```
   确保显示 `0.0.0.0:8000` 而不是 `localhost:8000`

2. **检查防火墙**:
   ```bash
   # Ubuntu/Debian
   sudo ufw status
   sudo ufw allow 8000
   
   # CentOS/RHEL
   sudo firewall-cmd --list-ports
   sudo firewall-cmd --add-port=8000/tcp --permanent
   sudo firewall-cmd --reload
   ```

3. **检查端口占用**:
   ```bash
   netstat -tlnp | grep :8000
   ```

4. **测试连接**:
   ```bash
   curl -I http://你的IP地址:8000
   ```

### 常见错误

- **Address already in use**: 端口已被占用，使用不同端口或停止占用进程
- **Permission denied**: 可能需要管理员权限启动某些端口
- **Connection refused**: 检查防火墙设置或服务状态

## 📱 移动设备访问

配置完成后，移动设备也可以通过IP地址访问：

1. 确保移动设备与服务器在同一局域网
2. 在移动设备浏览器中输入: `http://服务器IP:8000`

## 🔄 重启服务

```bash
# 停止服务
pkill -f "php -S 0.0.0.0:8000"

# 重新启动
./start_server.sh
```

## 📝 配置记录

记录您的配置信息：

- 服务器IP: `_____________`
- 访问端口: `8000`
- 访问URL: `http://服务器IP:8000`
- 启动时间: `_____________` 