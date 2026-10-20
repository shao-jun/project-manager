#!/bin/bash

# 网络访问检查脚本

echo "==================================="
echo "网络访问检查工具"
echo "==================================="

# 检查PHP服务器是否运行
echo "🔍 检查PHP服务器状态..."
if pgrep -f "php -S 0.0.0.0:8000" > /dev/null; then
    echo "✅ PHP服务器正在运行"
    
    # 获取进程信息
    echo "📊 进程信息:"
    ps aux | grep "php -S 0.0.0.0:8000" | grep -v grep
    echo ""
    
    # 检查端口监听
    echo "🔌 端口监听状态:"
    netstat -tlnp 2>/dev/null | grep :8000 || ss -tlnp 2>/dev/null | grep :8000
    echo ""
    
    # 获取本机IP地址
    echo "🌐 网络接口和IP地址:"
    IP_ADDRESSES=$(hostname -I 2>/dev/null || ifconfig 2>/dev/null | grep 'inet ' | grep -v '127.0.0.1' | awk '{print $2}' | tr '\n' ' ')
    
    if [ ! -z "$IP_ADDRESSES" ]; then
        for ip in $IP_ADDRESSES; do
            # 清理IP地址格式（移除可能的前缀）
            clean_ip=$(echo $ip | sed 's/addr://')
            echo "  📍 $clean_ip"
        done
    else
        echo "  ⚠️  无法获取IP地址"
    fi
    echo ""
    
    # 测试本地访问
    echo "🧪 测试访问连通性:"
    
    # 测试 localhost
    echo -n "  localhost:8000 ... "
    if curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 http://localhost:8000 | grep -q "200\|302"; then
        echo "✅ 可访问"
    else
        echo "❌ 不可访问"
    fi
    
    # 测试 127.0.0.1
    echo -n "  127.0.0.1:8000 ... "
    if curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 http://127.0.0.1:8000 | grep -q "200\|302"; then
        echo "✅ 可访问"
    else
        echo "❌ 不可访问"
    fi
    
    # 测试网络IP
    if [ ! -z "$IP_ADDRESSES" ]; then
        for ip in $IP_ADDRESSES; do
            clean_ip=$(echo $ip | sed 's/addr://')
            # 跳过localhost地址
            if [[ "$clean_ip" != "127.0.0.1" && "$clean_ip" != "::1" ]]; then
                echo -n "  $clean_ip:8000 ... "
                if curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 http://$clean_ip:8000 | grep -q "200\|302"; then
                    echo "✅ 可访问"
                else
                    echo "❌ 不可访问"
                fi
            fi
        done
    fi
    
    echo ""
    echo "🎉 检查完成！"
    echo ""
    echo "📋 访问地址列表:"
    echo "  - http://localhost:8000"
    echo "  - http://127.0.0.1:8000"
    if [ ! -z "$IP_ADDRESSES" ]; then
        for ip in $IP_ADDRESSES; do
            clean_ip=$(echo $ip | sed 's/addr://')
            if [[ "$clean_ip" != "127.0.0.1" && "$clean_ip" != "::1" ]]; then
                echo "  - http://$clean_ip:8000"
            fi
        done
    fi
    
else
    echo "❌ PHP服务器未运行"
    echo ""
    echo "🚀 启动建议:"
    echo "  ./start_server.sh"
    echo "  或"
    echo "  php -S 0.0.0.0:8000 -t public"
fi

echo "===================================" 