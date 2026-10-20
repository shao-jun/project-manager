#!/bin/bash

# 研发自动化管理平台启动脚本

echo "==================================="
echo "研发自动化管理平台"
echo "==================================="

# 检查是否有运行中的服务
if pgrep -f "php -S 0.0.0.0:8000" > /dev/null; then
    echo "⚠️  检测到已有服务在运行，正在停止..."
    pkill -f "php -S 0.0.0.0:8000"
    sleep 2
fi

# 获取本机IP地址
IP_ADDRESS=$(hostname -I | awk '{print $1}')
if [ -z "$IP_ADDRESS" ]; then
    IP_ADDRESS=$(ifconfig | grep 'inet ' | grep -v '127.0.0.1' | awk '{print $2}' | head -1)
fi

echo "🚀 启动PHP开发服务器..."
echo "📍 绑定地址: 0.0.0.0:8000"
echo "🌐 访问地址:"
echo "   - 本地: http://localhost:8000"
echo "   - 本地: http://127.0.0.1:8000"
if [ ! -z "$IP_ADDRESS" ]; then
    echo "   - 网络: http://$IP_ADDRESS:8000"
fi
echo ""
echo "💡 提示: 按 Ctrl+C 停止服务器"
echo "日志查看：journalctl -u rnd-automation-platform.service -f"
echo "==================================="

# 启动服务器（使用 index.php 作为路由器）
# 重定向 stderr 到 stdout，确保所有日志都被捕获
# 设置 PHP 错误日志输出到 stderr，并启用详细日志
# 禁用输出缓冲，确保日志实时输出
php -d error_reporting=E_ALL \
    -d display_errors=1 \
    -d log_errors=1 \
    -d error_log=/dev/stderr \
    -d output_buffering=0 \
    -d implicit_flush=1 \
    -S 0.0.0.0:8000 -t public public/index.php 2>&1
