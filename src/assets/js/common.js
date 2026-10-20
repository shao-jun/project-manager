// 公共JavaScript函数库

// 通用工具函数
const Utils = {
    // 格式化日期
    formatDate: function(date) {
        if (!date) return '';
        const d = new Date(date);
        return d.toLocaleDateString('zh-CN') + ' ' + d.toLocaleTimeString('zh-CN');
    },
    
    // 格式化相对时间
    formatRelativeTime: function(date) {
        if (!date) return '';
        const now = new Date();
        const target = new Date(date);
        const diff = now - target;
        
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        
        if (days > 0) return `${days}天前`;
        if (hours > 0) return `${hours}小时前`;
        if (minutes > 0) return `${minutes}分钟前`;
        return '刚刚';
    },
    
    // 显示加载状态
    showLoading: function(button, loadingText = '加载中...') {
        if (!button) return;
        button.dataset.originalText = button.textContent;
        button.textContent = loadingText;
        button.disabled = true;
    },
    
    // 隐藏加载状态
    hideLoading: function(button) {
        if (!button) return;
        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
    },
    
    // 显示成功消息
    showSuccess: function(message) {
        alert('✅ ' + message);
    },
    
    // 显示错误消息
    showError: function(message) {
        alert('❌ ' + message);
    },
    
    // 显示警告消息
    showWarning: function(message) {
        alert('⚠️ ' + message);
    },
    
    // 确认对话框
    confirm: function(message) {
        return confirm(message);
    },
    
    // 防抖函数
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // 节流函数
    throttle: function(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

// API请求封装
const API = {
    // 基础请求方法
    request: async function(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, finalOptions);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('API请求失败:', error);
            throw error;
        }
    },
    
    // GET请求
    get: function(url, params = {}) {
        const urlParams = new URLSearchParams(params);
        const fullUrl = urlParams.toString() ? `${url}?${urlParams}` : url;
        return this.request(fullUrl);
    },
    
    // POST请求
    post: function(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    },
    
    // PUT请求
    put: function(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    },
    
    // DELETE请求
    delete: function(url) {
        return this.request(url, {
            method: 'DELETE',
        });
    }
};

// 通用页面功能
const PageUtils = {
    // 初始化页面
    init: function() {
        this.setupEventListeners();
        this.loadInitialData();
    },
    
    // 设置事件监听器
    setupEventListeners: function() {
        // 为所有按钮添加点击效果
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
            });
        });
        
        // 为表单输入框添加焦点效果
        document.querySelectorAll('.form-input, .form-textarea, .form-select').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    },
    
    // 加载初始数据
    loadInitialData: function() {
        // 子类可以重写此方法
    },
    
    // 刷新页面数据
    refresh: function() {
        this.loadInitialData();
    }
};

// 导航栏管理
const Navigation = {
    // 设置当前页面
    setActive: function(currentPage) {
        document.querySelectorAll('.nav-bar .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        const currentBtn = document.querySelector(`.nav-bar .btn[href="${currentPage}"]`);
        if (currentBtn) {
            currentBtn.classList.add('active');
        }
    },
    
    // 获取导航HTML
    getNavHTML: function(currentPage) {
        const navItems = [
            { href: '/', icon: '📊', text: '监控面板' },
            { href: '/tech-categories', icon: '🔧', text: '技术分类' },
            { href: '/code-review', icon: '📋', text: '代码审查' },
            { href: '/projects', icon: '📱', text: '项目管理' },
            { href: '/config', icon: '⚙️', text: '系统配置' }
        ];
        
        return navItems.map(item => {
            const activeClass = item.href === currentPage ? ' active' : '';
            return `<a href="${item.href}" class="btn${activeClass}">${item.icon} ${item.text}</a>`;
        }).join('');
    }
};

// 数据表格管理
const TableUtils = {
    // 创建表格
    createTable: function(columns, data, options = {}) {
        const table = document.createElement('table');
        table.className = 'data-table';
        
        // 创建表头
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        
        columns.forEach(col => {
            const th = document.createElement('th');
            th.textContent = col.title;
            if (col.width) th.style.width = col.width;
            headerRow.appendChild(th);
        });
        
        thead.appendChild(headerRow);
        table.appendChild(thead);
        
        // 创建表体
        const tbody = document.createElement('tbody');
        
        data.forEach(row => {
            const tr = document.createElement('tr');
            
            columns.forEach(col => {
                const td = document.createElement('td');
                
                if (col.render) {
                    td.innerHTML = col.render(row[col.key], row);
                } else {
                    td.textContent = row[col.key] || '';
                }
                
                tr.appendChild(td);
            });
            
            tbody.appendChild(tr);
        });
        
        table.appendChild(tbody);
        return table;
    },
    
    // 更新表格数据
    updateTable: function(tableElement, columns, data) {
        const tbody = tableElement.querySelector('tbody');
        tbody.innerHTML = '';
        
        data.forEach(row => {
            const tr = document.createElement('tr');
            
            columns.forEach(col => {
                const td = document.createElement('td');
                
                if (col.render) {
                    td.innerHTML = col.render(row[col.key], row);
                } else {
                    td.textContent = row[col.key] || '';
                }
                
                tr.appendChild(td);
            });
            
            tbody.appendChild(tr);
        });
    }
};

// 模态框管理
const Modal = {
    // 显示模态框
    show: function(title, content, options = {}) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.style.display = 'block';
        
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">${title}</div>
                    <span class="close" onclick="this.closest('.modal').remove()">&times;</span>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
                ${options.footer ? `<div class="modal-footer">${options.footer}</div>` : ''}
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // 点击外部关闭
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
        
        return modal;
    },
    
    // 隐藏模态框
    hide: function(modal) {
        if (modal) {
            modal.remove();
        }
    },
    
    // 确认对话框
    confirm: function(title, message, callback) {
        const footer = `
            <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">取消</button>
            <button class="btn btn-primary" onclick="(${callback.toString()})(); this.closest('.modal').remove()">确定</button>
        `;
        
        this.show(title, `<p>${message}</p>`, { footer });
    }
};

// 存储管理
const Storage = {
    // 设置本地存储
    set: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            console.error('存储失败:', e);
        }
    },
    
    // 获取本地存储
    get: function(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            console.error('读取存储失败:', e);
            return defaultValue;
        }
    },
    
    // 删除本地存储
    remove: function(key) {
        try {
            localStorage.removeItem(key);
        } catch (e) {
            console.error('删除存储失败:', e);
        }
    },
    
    // 清空本地存储
    clear: function() {
        try {
            localStorage.clear();
        } catch (e) {
            console.error('清空存储失败:', e);
        }
    }
};

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    PageUtils.init();
});

// 导出到全局
window.Utils = Utils;
window.API = API;
window.PageUtils = PageUtils;
window.Navigation = Navigation;
window.TableUtils = TableUtils;
window.Modal = Modal;
window.Storage = Storage; 