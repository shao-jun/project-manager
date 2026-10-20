#!/bin/bash

# 研发自动化管理平台服务管理脚本

SERVICE_NAME="rnd-automation-platform.service"

show_status() {
    echo "🔍 检查服务状态..."
    sudo systemctl status $SERVICE_NAME
}

start_service() {
    echo "🚀 启动服务..."
    sudo systemctl start $SERVICE_NAME
    echo "✅ 服务已启动"
    show_status
}

stop_service() {
    echo "🛑 停止服务..."
    sudo systemctl stop $SERVICE_NAME
    echo "✅ 服务已停止"
}

restart_service() {
    echo "🔄 重启服务..."
    sudo systemctl restart $SERVICE_NAME
    echo "✅ 服务已重启"
    show_status
}

enable_autostart() {
    echo "⚙️  启用开机自启动..."
    sudo systemctl enable $SERVICE_NAME
    echo "✅ 开机自启动已启用"
}

disable_autostart() {
    echo "⚙️  禁用开机自启动..."
    sudo systemctl disable $SERVICE_NAME
    echo "✅ 开机自启动已禁用"
}

show_logs() {
    echo "📋 显示服务日志..."
    sudo journalctl -u $SERVICE_NAME -f
}

case "$1" in
    "status")
        show_status
        ;;
    "start")
        start_service
        ;;
    "stop")
        stop_service
        ;;
    "restart")
        restart_service
        ;;
    "enable")
        enable_autostart
        ;;
    "disable")
        disable_autostart
        ;;
    "logs")
        show_logs
        ;;
    *)
        echo "使用方法: $0 {status|start|stop|restart|enable|disable|logs}"
        echo ""
        echo "命令说明:"
        echo "  status  - 查看服务状态"
        echo "  start   - 启动服务"
        echo "  stop    - 停止服务"
        echo "  restart - 重启服务"
        echo "  enable  - 启用开机自启动"
        echo "  disable - 禁用开机自启动"
        echo "  logs    - 查看服务日志"
        echo ""
        echo "当前服务状态:"
        show_status
        ;;
esac
