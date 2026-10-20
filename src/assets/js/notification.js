/**
 * 通用通知系统
 * 用于在页面右上角显示通知消息
 */

/**
 * 显示通知消息
 * @param {string} message - 通知消息内容
 * @param {string} type - 通知类型: 'success', 'error', 'warning', 'info'
 */
function notify(message, type = 'success') {
    console.log('notify 函数被调用:', message, type);
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' ? '✓' : 
                 type === 'error' ? '✕' : 
                 type === 'warning' ? '⚠' : 'ℹ';
    
    notification.innerHTML = `
        <span style="font-size: 20px; font-weight: bold;">${icon}</span>
        <span>${message}</span>
    `;
    
    console.log('通知元素已创建:', notification);
    document.body.appendChild(notification);
    console.log('通知元素已添加到 body');
    
    // 3秒后自动消失
    setTimeout(() => {
        notification.classList.add('hiding');
        setTimeout(() => {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}
