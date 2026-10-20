/**
 * 研发自动化管理平台 - 通用JavaScript
 */

// 全局配置
const APP_CONFIG = {
    API_BASE_URL: '/api',
    DEBUG: true
};

// 工具函数
const Utils = {
    // 显示消息
    showMessage: function(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };
        
        const alertHtml = `
            <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // 在页面顶部显示消息
        const container = document.querySelector('.container');
        if (container) {
            container.insertAdjacentHTML('afterbegin', alertHtml);
            
            // 3秒后自动消失
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 3000);
        }
    },
    
    // 格式化日期
    formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('zh-CN');
    },
    
    // 验证表单
    validateForm: function(formData) {
        const errors = [];
        
        for (let [key, value] of formData.entries()) {
            if (!value || value.trim() === '') {
                errors.push(`${key} 不能为空`);
            }
        }
        
        return errors;
    },
    
    // 加载状态
    showLoading: function(element) {
        if (element) {
            element.innerHTML = '<span class="loading"></span> 加载中...';
            element.disabled = true;
        }
    },
    
    hideLoading: function(element, originalText) {
        if (element) {
            element.innerHTML = originalText || '提交';
            element.disabled = false;
        }
    }
};

// API请求类
class ApiClient {
    constructor(baseUrl = APP_CONFIG.API_BASE_URL) {
        this.baseUrl = baseUrl;
    }
    
    // 通用请求方法
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}/${endpoint}`;
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };
        
        const config = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || '请求失败');
            }
            
            return data;
        } catch (error) {
            console.error('API请求错误:', error);
            throw error;
        }
    }
    
    // GET请求
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        
        return this.request(url, { method: 'GET' });
    }
    
    // POST请求
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    // PUT请求
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    // DELETE请求
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
}

// 全局API客户端实例
const api = new ApiClient();

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    // 初始化工具提示
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // 初始化弹出框
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // 自动隐藏消息
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    });
    
    // 表单验证
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const formData = new FormData(form);
            const errors = Utils.validateForm(formData);
            
            if (errors.length > 0) {
                e.preventDefault();
                Utils.showMessage(errors.join('<br>'), 'error');
            }
        });
    });
    
    // 确认删除
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || '确定要删除吗？';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});

// 导出到全局作用域
window.Utils = Utils;
window.ApiClient = ApiClient;
window.api = api; 