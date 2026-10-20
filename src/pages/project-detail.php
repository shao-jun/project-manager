<?php
// 项目详情页面 - 独立显示单个项目的详细信息
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../lib/endpoints.php';

// 获取项目ID
$projectId = $_GET['id'] ?? null;
if (!$projectId) {
    header('Location: /projects');
    exit;
}

ob_start();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="pageTitle">项目详情 - 研发自动化管理平台</title>
    <link rel="stylesheet" href="/assets/css/notification.css">
    <script>window.APP_ENDPOINTS = <?php echo app_endpoints_json(); ?>;</script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .back-btn {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a67d8;
        }
        
        .btn.active {
            background: #4c51bf;
        }
        
        .btn-primary {
            background: #007bff;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .btn-success:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.65;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .main-content {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tab-nav {
            display: flex;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            padding: 12px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-btn:hover {
            background: #f8f9fa;
            color: #333;
        }
        
        .tab-btn.active {
            color: #007bff;
            border-bottom-color: #007bff;
            background: #f8f9fa;
        }
        
        .tab-content {
            position: relative;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        .tab-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .tab-panel-title {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
        }
        
        .button-group .btn {
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .modal-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }
        
        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        
        .close:hover {
            color: #000;
        }
        
        .modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        
        .modal-footer-left {
            display: flex;
            gap: 10px;
        }
        
        .modal-footer-right {
            display: flex;
            gap: 10px;
        }
        
        /* 模块列表样式 */
        .modules-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .modules-table th,
        .modules-table td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 0.8em;
        }
        
        .modules-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .modules-table tr:hover {
            background: #f8f9fa;
        }
        
        /* 勾选框样式 */
        .modules-table input[type="checkbox"] {
            cursor: pointer;
            transform: scale(1.1);
        }
        
        .modules-table th:first-child,
        .modules-table td:first-child {
            text-align: center;
        }
        
        .commit-hash {
            font-family: 'Courier New', monospace;
            font-size: 0.8em;
            color: #666;
            background: #f5f5f5;
            padding: 2px 4px;
            border-radius: 3px;
            cursor: text;
            user-select: all;
            word-break: break-all;
            max-width: 200px;
            display: inline-block;
        }
        
        .commit-link-btn {
            display: inline-block;
            margin-left: 5px;
            text-decoration: none;
            font-size: 0.8em;
            color: #007cba;
            transition: all 0.2s ease;
            opacity: 0.7;
            white-space: nowrap;
            vertical-align: middle;
        }
        
        .commit-link-btn:hover {
            opacity: 1;
            color: #005a8b;
            transform: scale(1.1);
        }
        
        .commit-link-btn:active {
            transform: scale(0.95);
        }

        .refresh-icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 2px 4px;
            opacity: 0.7;
            transition: all 0.2s ease;
        }

        .refresh-icon-btn:hover {
            opacity: 1;
            transform: scale(1.2);
        }

        .refresh-icon-btn:active {
            transform: scale(0.95);
        }

        .refresh-icon-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
        }
        
        .sortable:hover {
            background: #e9ecef;
        }
        
        .sort-icon {
            font-size: 0.75em;
            color: #666;
            margin-left: 4px;
        }
        
        .sort-asc .sort-icon::before {
            content: '↑';
        }
        
        .sort-desc .sort-icon::before {
            content: '↓';
        }
        
        .branch-input, .version-input, .commit-input {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            padding: 3px 5px;
            border-radius: 3px;
            font-size: 0.8em;
            transition: all 0.2s;
        }
        
        .branch-input:hover, .version-input:hover, .commit-input:hover {
            border-color: #ddd;
            background: #f8f9fa;
        }
        
        .branch-input:focus, .version-input:focus, .commit-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        .package-info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .package-info-table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .package-info-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .package-info-table tr:hover {
            background: #f8f9fa;
        }
        
        .build-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .build-status.success {
            background: #d4edda;
            color: #155724;
        }
        
        .build-status.failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .build-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .build-status.building {
            background: #cce5ff;
            color: #004085;
        }
        
        .build-status.cancelled {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .build-status.UPLOAD_OK {
            background: #d4edda;
            color: #155724;
        }
        
        .build-status.UPLOAD_CRP {
            background: #cce5ff;
            color: #004085;
        }
        
        .build-status.abandoned {
            background: #e2e3e5;
            color: #6c757d;
        }
        
        /* 内联打包信息样式 */
        .delivery-package-info {
            margin-top: 15px;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .package-info-placeholder {
            text-align: center;
            padding: 20px;
        }
        
        .package-info-content {
        }
        
        .inline-table-container {
            max-height: 50vh;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-left: 3px solid #2196f3;
            border-radius: 5px;
        }
        
        .inline-package-table {
            font-size: 12px;
        }
        
        .inline-package-table th,
        .inline-package-table td {
            padding: 6px 8px;
            font-size: 12px;
            text-align: center;
            vertical-align: middle;
        }
        
        /* 左对齐的列 */
        .inline-package-table th:nth-child(1),
        .inline-package-table th:nth-child(2),
        .inline-package-table th:nth-child(3),
        .inline-package-table th:nth-child(4),
        .inline-package-table th:nth-child(5),
        .inline-package-table th:nth-child(6),
        .inline-package-table td:nth-child(1),
        .inline-package-table td:nth-child(2),
        .inline-package-table td:nth-child(3),
        .inline-package-table td:nth-child(4),
        .inline-package-table td:nth-child(5),
        .inline-package-table td:nth-child(6) {
            text-align: left;
        }
                
        .loading-spinner {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        
        .error-message {
            text-align: center;
            color: #dc3545;
            font-size: 14px;
            padding: 10px;
        }
        
        /* 打包信息模态框样式 */
        .package-info-modal {
            width: 95% !important;
            max-width: 1400px !important;
            height: 90% !important;
            max-height: 800px !important;
            margin: 2% auto !important;
            position: relative;
            top: 0;
            transform: none;
        }
        
        .package-info-modal .modal-body {
            max-height: calc(90vh - 120px);
            overflow-y: auto;
            overflow-x: hidden;
            padding: 20px;
            position: relative;
        }
        
        .package-info-modal .package-info-table th {
            background-color: #f8f9fa !important;
            color: #495057 !important;
            font-weight: 600 !important;
            border-bottom: 2px solid #dee2e6 !important;
        }
        
        /* 提测信息模态框样式 */
        .test-info-modal {
            width: 95% !important;
            max-width: 1400px !important;
            height: 90% !important;
            max-height: 800px !important;
            margin: 2% auto !important;
            position: relative;
            top: 0;
            transform: none;
            overflow: hidden !important;
            display: flex !important;
            flex-direction: column !important;
        }
        
        .test-info-modal .modal-header {
            flex-shrink: 0;
            margin-bottom: 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .test-info-modal .modal-footer {
            flex-shrink: 0;
            margin-top: 0;
            border-top: 1px solid #e9ecef;
            padding-top: 15px;
        }
        
        .test-info-modal .modal-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 20px;
            position: relative;
            min-height: 0;
        }
        
        .test-info-modal .pms-info-container {
            max-height: 100%;
            overflow-y: visible;
        }
        
        .test-info-modal .pms-modules-section {
            max-height: none;
        }
        
        .test-info-modal .pms-table-container {
            max-height: none;
            overflow-x: auto;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .test-info-modal .pms-table {
            width: 100%;
            table-layout: auto;
            min-width: 800px; /* 确保表格有最小宽度 */
        }
        
        .add-pms-links-modal {
            width: 600px !important;
            max-width: 90% !important;
        }
        
        .add-pms-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .add-pms-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.4;
            resize: vertical;
            box-sizing: border-box;
        }
        
        .add-pms-form textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        .form-tips {
            margin-top: 8px;
        }
        
        .form-tips small {
            color: #6c757d;
            font-style: italic;
        }
        
        .package-info-table {
            font-size: 13px;
        }
        
        .package-info-table th,
        .package-info-table td {
            padding: 8px 6px;
            white-space: nowrap;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .package-info-table th {
            background-color: #f8f9fa !important;
            color: #495057 !important;
            font-weight: 600 !important;
        }
        
        .package-info-table th:nth-child(1),
        .package-info-table td:nth-child(1) {
            min-width: 120px;
            max-width: 200px;
            white-space: normal;
        }
        
        .package-info-table th:nth-child(6),
        .package-info-table td:nth-child(6) {
            min-width: 80px;
        }
        
        .package-info-table th:nth-child(7),
        .package-info-table td:nth-child(7) {
            min-width: 80px;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .package-info-table {
            margin: 0;
            min-width: 900px;
        }
        
        .module-status {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .module-status.有更新 {
            background: #fff3cd;
            color: #856404;
        }
        
        .module-status.已更新 {
            background: #d4edda;
            color: #155724;
        }
        
        .module-status.未知 {
            background: #f8d7da;
            color: #721c24;
        }
        
        .build-status {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .build-status.UPLOAD_OK {
            background: #d4edda;
            color: #155724;
        }
        
        .build-status.UPLOAD_CRP {
            background: #fff3cd;
            color: #856404;
        }
        
        .build-status.BUILDING {
            background: #cce5ff;
            color: #004085;
        }
        
        .build-status.FAILED {
            background: #f8d7da;
            color: #721c24;
        }
        
        .build-status.unknown {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .code-link {
            color: #007bff;
            text-decoration: none;
            font-size: 0.8em;
        }
        
        .code-link:hover {
            text-decoration: underline;
        }
        
        .package-link {
            color: #28a745;
            text-decoration: none;
            font-size: 0.8em;
        }
        
        .package-link:hover {
            text-decoration: underline;
        }
        
        .module-branch-input,
        .module-version-input {
            width: 100px;
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 0.9em;
        }
        
        .module-branch-input:focus,
        .module-version-input:focus {
            outline: none;
            border-color: #007bff;
        }
        
        /* 项目目标样式 */
        .goals-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .goal-item {
            padding: 10px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 3px solid #007bff;
        }
        
        .goal-item.high { border-left-color: #dc3545; }
        .goal-item.medium { border-left-color: #ffc107; }
        .goal-item.low { border-left-color: #28a745; }
        
        .goal-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .goal-description {
            font-size: 0.9em;
            color: #666;
        }
        
        .goal-priority {
            font-size: 0.8em;
            color: #999;
            margin-top: 5px;
        }
        
        /* 交付记录样式 */
        .deliveries-container {
            /* 移除高度限制，直接全部展示 */
        }
        
        .delivery-item {
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #eee;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .delivery-item:hover {
            background: #e9ecef;
        }
        
        .delivery-item.expanded {
            background: white;
            border-color: #007bff;
        }
        
        .delivery-header {
            display: grid;
            grid-template-columns: 1fr auto;
            row-gap: 6px;
        }
        
        .delivery-title-section {
            display: contents; /* 让内部元素直接参与父级grid布局 */
        }

        .delivery-title {
            grid-column: 1;
        }

        .delivery-actions {
            grid-column: 2;
        }
        
        .delivery-details {
            display: block;
            grid-column: 1 / -1; /* 细节行占满整行，从标题列开始 */
        }
        
        .delivery-title {
            font-weight: bold;
            color: #333;
            font-size: 1em;
            flex: 1;
            margin-right: 10px;
        }
        
        .delivery-date {
            font-size: 0.9em;
            color: #666;
        }
        
        .delivery-info {
            margin-top: 2px;
            font-size: 0.75em;
            color: #999;
        }
        
        .delivery-info span {
            margin-right: 10px;
        }
        
        .delivery-addresses {
            margin-top: 2px;
            font-size: 0.75em;
            color: #666;
        }
        
        .delivery-addresses span {
            margin-right: 6px;
        }
        
        .delivery-addresses a {
            color: #007bff;
            text-decoration: none;
        }
        
        .delivery-addresses a:hover {
            text-decoration: underline;
        }

        .delivery-meta-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px 12px;
            font-size: 0.75em;
            color: #666;
        }

        .repo-addresses {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 2px 6px;
        }

        .repo-addr-item {
            display: inline-flex;
        }

        .repo-addr-item a {
            display: inline-flex;
            width: 18px;
            height: 18px;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        
        .delivery-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .delivery-actions .btn {
            padding: 4px 8px;
            font-size: 0.8em;
        }
        
        .delivery-action-btn {
            width: auto !important;
            min-width: 80px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* 同步按钮：进行中态 */
        .delivery-action-btn.sync-btn.is-syncing,
        .delivery-action-btn.is-syncing {
            opacity: 0.75;
            cursor: not-allowed;
            filter: grayscale(0.2);
        }

        .delivery-action-btn:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .sync-spinner {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 4px;
            vertical-align: -1px;
            border: 2px solid rgba(255, 255, 255, 0.45);
            border-top-color: #fff;
            border-radius: 50%;
            animation: sync-spin 0.7s linear infinite;
        }

        @keyframes sync-spin {
            to { transform: rotate(360deg); }
        }
        
        .delivery-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .delivery-status.pending { background: #fff3cd; color: #856404; }
        .delivery-status.in_progress { background: #cce5ff; color: #004085; }
        .delivery-status.completed { background: #d4edda; color: #155724; }
        .delivery-status.failed { background: #f8d7da; color: #721c24; }
        .delivery-status.cancelled { background: #e2e3e5; color: #383d41; }
        
        .delivery-modules {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .delivery-modules.expanded {
            display: block;
        }
        
        .delivery-module-item {
            padding: 10px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .delivery-module-name {
            font-weight: bold;
        }
        
        .delivery-module-branch {
            font-size: 0.9em;
            color: #666;
        }
        
        .delivery-module-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .delivery-module-status.pending { background: #fff3cd; color: #856404; }
        .delivery-module-status.building { background: #cce5ff; color: #004085; }
        .delivery-module-status.success { background: #d4edda; color: #155724; }
        .delivery-module-status.failed { background: #f8d7da; color: #721c24; }
        .delivery-module-status.cancelled { background: #e2e3e5; color: #383d41; }
        
        /* 新增交付记录表单样式 */
        .new-delivery-form {
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border: 2px dashed #007bff;
            border-radius: 8px;
            display: none;
        }
        
        .new-delivery-form.active {
            display: block;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-col {
            flex: 1;
        }
        
        .form-col.small {
            flex: 0 0 150px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .new-delivery-form .form-input,
        .new-delivery-form .form-textarea {
            margin-bottom: 0;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        
        /* 批量打包确认框样式 */
        .batch-package-modal {
            max-width: 70vw !important;
            width: 70vw !important;
            height: 75vh !important;
            max-height: 75vh !important;
            margin: 0 !important;
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            display: flex;
            flex-direction: column;
        }
        
        .batch-modules-list {
            flex: 1;
            min-height: 200px;
            max-height: none;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 12px;
            background: #f8f9fa;
            /* 防止滚动穿透 */
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        
        .batch-module-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            margin-bottom: 8px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            transition: all 0.2s;
        }
        
        .batch-module-item:hover {
            border-color: #007bff;
            box-shadow: 0 2px 4px rgba(0,123,255,0.1);
        }
        
        .batch-module-item.selected {
            border-color: #007bff;
            background-color: #f0f8ff;
        }
        
        .batch-module-checkbox {
            margin-right: 16px;
            transform: scale(1.1);
        }
        
        .batch-module-info {
            flex: 1;
            display: grid;
            grid-template-columns: 2fr 1.5fr 1.5fr;
            gap: 16px;
            align-items: center;
        }
        
        .batch-module-name {
            font-weight: 600;
            color: #333;
            font-size: 0.95em;
        }
        
        .batch-module-current-version {
            color: #6c757d;
            font-size: 0.85em;
        }
        
        .batch-module-new-version {
            color: #28a745;
            font-weight: 600;
            font-size: 0.85em;
        }
        
        .batch-module-status {
            font-size: 0.8em;
            padding: 2px 8px;
            border-radius: 3px;
            background: #e9ecef;
            color: #495057;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        /* 确保对话框footer正确显示 */
        .batch-package-modal .modal-footer {
            flex-shrink: 0;
            margin-top: auto;
            border-top: 1px solid #dee2e6;
            padding: 12px 20px;
            background: white;
        }
        
        /* 批量打包进度样式 */
        .batch-package-progress {
            width: 100%;
        }
        
        .progress-list {
            max-height: 50vh;
            overflow-y: auto;
        }
        
        .progress-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            margin-bottom: 8px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            transition: all 0.2s;
        }
        
        .progress-index {
            background: #007bff;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: 600;
            margin-right: 16px;
            flex-shrink: 0;
        }
        
        .progress-item .module-name {
            flex: 1;
            font-weight: 600;
            color: #333;
            font-size: 0.95em;
        }
        
        .progress-item .progress-status {
            font-size: 0.85em;
            font-weight: 600;
            margin-left: 16px;
        }
        
        .progress-status.processing {
            color: #ffc107;
        }
        
        .progress-status.success {
            color: #28a745;
        }
        
        .progress-status.failed {
            color: #dc3545;
        }
        
        /* 批量打包结果样式 */
        .batch-package-result {
            width: 100%;
        }
        
        .batch-package-result h4 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 1.2em;
        }
        
        .result-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .result-summary p {
            margin: 5px 0;
            font-size: 0.95em;
            color: #333;
        }
        
        .result-details {
            max-height: 40vh;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .result-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 6px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .result-item.success {
            border-left: 4px solid #28a745;
            background: #f8fff9;
        }
        
        .result-item.failed {
            border-left: 4px solid #dc3545;
            background: #fff8f8;
        }
        
        .result-item .module-name {
            flex: 1;
            font-weight: 600;
            color: #333;
        }
        
        .result-item .result-status {
            margin: 0 15px;
            font-size: 1.1em;
        }
        
        .result-item .result-message {
            color: #6c757d;
            font-size: 0.85em;
        }
        
        .result-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
                
        /* 项目模块标题和操作按钮样式 */
        .tab-panel-title-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-delete-selected, .btn-add-selected {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            width: 28px;
            height: 28px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            padding: 0;
        }
        
        .btn-delete-selected {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            width: 28px;
            height: 28px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            padding: 0;
        }
        
        .btn-delete-selected:hover {
            background: #c82333;
            transform: scale(1.05);
        }
        
        .btn-delete-selected:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        /* 确保模块列表容器正确处理滚动 */
        .batch-modules-list::-webkit-scrollbar {
            width: 8px;
        }
        
        .batch-modules-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .batch-modules-list::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .batch-modules-list::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* 批量打包对话框内容布局优化 */
        .batch-package-modal .modal-body {
            padding: 20px;
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .batch-package-modal .form-group {
            margin-bottom: 20px;
        }
        
        .batch-package-modal .form-group:last-child {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        
        .batch-package-modal .form-label {
            font-size: 0.95em;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .batch-package-modal .form-input {
            font-size: 0.9em;
            padding: 10px 12px;
        }
        
        /* 打包对话框样式 */
        .package-modal {
            max-width: 450px;
        }
        
        .package-modal .form-group {
            margin-bottom: 20px;
        }
        
        .package-modal .form-input {
            width: 100%;
        }
        
        /* 模块过滤器样式 */
        .filter-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-right: 15px;
        }
        
        .filter-label {
            font-size: 0.9em;
            font-weight: 500;
            color: #495057;
            white-space: nowrap;
        }
        
        .filter-select {
            min-width: 120px;
            padding: 6px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            background-color: white;
            font-size: 0.9em;
            color: #495057;
            cursor: pointer;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* 架构选择样式 */
        .arch-checkboxes,
        .project-arch-checkboxes {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 8px;
        }
        
        .arch-checkbox {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 8px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .arch-checkbox:hover {
            background-color: #f5f5f5;
        }
        
        .arch-checkbox input[type="checkbox"] {
            margin-right: 8px;
            cursor: pointer;
        }
        
        .arch-checkbox input[type="checkbox"]:checked + * {
            font-weight: 500;
            color: #007bff;
        }
        
        /* 分支选择器样式 */
        .branch-select-container {
            position: relative;
        }
        
        /* 项目类型选择器样式 */
        .project-type-select-container {
            position: relative;
        }
        
        .branch-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .branch-dropdown.show {
            display: block;
        }
        
        .branch-option {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }
        
        .branch-option:hover {
            background-color: #f8f9fa;
        }
        
        .branch-option.active {
            background-color: #e3f2fd;
            color: #1976d2;
            font-weight: 500;
        }
        
        .branch-option:last-child {
            border-bottom: none;
        }
        
        .branch-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .branch-name {
            font-weight: 500;
            color: #333;
        }
        
        .branch-id {
            font-size: 0.875rem;
            color: #666;
            background: #f5f5f5;
            padding: 2px 8px;
            border-radius: 12px;
        }
        
        /* 表单行布局样式 */
        .form-row {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            margin-bottom: 20px;
        }
        
        .form-group-inline {
            flex: 1;
            margin-bottom: 0;
        }
        
        .form-group-inline .form-label {
            margin-bottom: 8px;
            display: block;
        }
        
        /* 紧凑的架构选择样式 */
        .project-arch-checkboxes-compact {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        
        .arch-checkbox-compact {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .arch-checkbox-compact:hover {
            background-color: #e9ecef;
            border-color: #007bff;
        }
        
        .arch-checkbox-compact input[type="checkbox"] {
            margin-right: 4px;
            cursor: pointer;
        }
        
        .arch-checkbox-compact input[type="checkbox"]:checked + * {
            font-weight: 500;
            color: #007bff;
        }
        
        .arch-checkbox-compact input[type="checkbox"]:checked {
            accent-color: #007bff;
        }
        
        /* 响应式布局 */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-group-inline {
                margin-bottom: 20px;
            }
            
            .project-arch-checkboxes-compact {
                gap: 6px;
            }
            
            .arch-checkbox-compact {
                font-size: 0.8rem;
                padding: 3px 6px;
            }
        }
        
        /* tag信息样式 */
        .tag-info-display {
            color: #666;
            font-size: 0.8em;
        }
        
        .create-tag-btn {
            background: #007cba;
            color: white;
            border: none;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 0.8em;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .create-tag-btn:hover {
            background: #005a87;
        }
        
        /* 归档开关样式 */
        .archive-switch {
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            padding: 4px 8px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            transition: all 0.2s;
            margin-right: 8px;
        }
        
        .archive-switch:hover {
            background: #e9ecef;
            border-color: #007bff;
        }
        
        .archive-checkbox {
            margin-right: 6px;
            cursor: pointer;
            transform: scale(1.1);
        }
        
        .archive-checkbox:checked {
            accent-color: #007bff;
        }
        
        .archive-label {
            font-size: 0.8em;
            font-weight: 500;
            color: #495057;
            user-select: none;
        }
        
        .archive-checkbox:checked + .archive-label {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 id="projectTitle">📱 加载中...</h1>
            </div>
            <a href="/projects" class="back-btn">← 返回项目列表</a>
        </div>
        
        <div class="main-content">
            <!-- Tab导航 -->
            <div class="tab-nav">
                <button class="tab-btn active" onclick="switchTab('project-info', event)">📋 项目信息</button>
                <button class="tab-btn" onclick="switchTab('project-modules', event)">🔧 项目模块</button>
                <button class="tab-btn" onclick="switchTab('project-deliveries', event)">📦 交付记录</button>
            </div>
            
            <!-- Tab内容 -->
            <div class="tab-content">
                <!-- 项目信息Tab -->
                <div id="project-info" class="tab-pane active">
                    <!-- 第一行：项目名称、打包分支和项目分类 -->
                    <div class="form-row">
                        <div class="form-group form-group-inline">
                            <label class="form-label">项目名称</label>
                            <input type="text" class="form-input" id="projectName" placeholder="请输入项目名称">
                        </div>
                        <div class="form-group form-group-inline">
                            <label class="form-label">打包分支</label>
                            <div class="branch-select-container">
                                <input type="text" class="form-input" id="projectBranch" placeholder="请输入或选择打包分支" autocomplete="off">
                                <div class="branch-dropdown" id="branchDropdown">
                                    <!-- 分支选项将在这里动态加载 -->
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-group-inline">
                            <label class="form-label">项目分类</label>
                            <div class="project-type-select-container">
                                <input type="text" class="form-input" id="projectType" placeholder="请选择或输入项目分类" list="projectTypeOptions" autocomplete="off">
                                <datalist id="projectTypeOptions">
                                    <!-- 项目分类选项将在这里动态加载 -->
                                </datalist>
                            </div>
                        </div>
                        <div class="form-group form-group-inline">
                            <label class="form-label" id="baselineLabel">系统基线</label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="branch-select-container" style="flex: 1;">
                                    <input type="text" class="form-input" id="systemBaseline" placeholder="请输入或选择系统基线" autocomplete="off">
                                    <div class="branch-dropdown" id="baselineDropdown">
                                        <!-- 系统基线选项将在这里动态加载 -->
                                    </div>
                                </div>
                                <label class="arch-checkbox-compact" style="white-space: nowrap; margin-bottom: 0;">
                                    <input type="checkbox" id="appendBaseline"> append
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 第二行：打包架构和项目经理 -->
                    <div class="form-row">
                        <div class="form-group form-group-inline">
                            <label class="form-label">打包架构</label>
                            <div class="project-arch-checkboxes-compact">
                                <label class="arch-checkbox-compact">
                                    <input type="checkbox" value="amd64" id="projectArch_amd64"> amd64
                                </label>
                                <label class="arch-checkbox-compact">
                                    <input type="checkbox" value="i386" id="projectArch_i386"> i386
                                </label>
                                <label class="arch-checkbox-compact">
                                    <input type="checkbox" value="mips64el" id="projectArch_mips64el"> mips64el
                                </label>
                                <label class="arch-checkbox-compact">
                                    <input type="checkbox" value="arm64" id="projectArch_arm64"> arm64
                                </label>
                                <label class="arch-checkbox-compact">
                                    <input type="checkbox" value="sw_64" id="projectArch_sw_64"> sw_64
                                </label>
                                <label class="arch-checkbox-compact">
                                    <input type="checkbox" value="loongarch64" id="projectArch_loongarch64"> loongarch64
                                </label>
                                <label class="arch-checkbox-compact">
                                    <input type="checkbox" value="loong64" id="projectArch_loong64"> loong64
                                </label>
                            </div>
                        </div>
                        <div class="form-group form-group-inline">
                            <label class="form-label">项目经理</label>
                            <div class="project-manager-select-container">
                                <input type="text" class="form-input" id="projectManager" placeholder="请选择或输入项目经理" list="projectManagerOptions" autocomplete="off">
                                <datalist id="projectManagerOptions">
                                    <!-- 项目经理选项将在这里动态加载 -->
                                </datalist>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">项目描述</label>
                        <textarea class="form-textarea" id="projectDescription" placeholder="请输入项目描述"></textarea>
                    </div>

                    <!-- 项目目标 -->
                    <div class="form-group">
                        <label class="form-label">项目目标</label>
                        <div id="projectGoals" class="goals-container">
                            <!-- 目标列表将在这里动态加载 -->
                        </div>
                        <button class="btn btn-secondary" onclick="showAddGoalModal()">+ 添加目标</button>
                    </div>

                    <div class="form-group" style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <button class="btn btn-primary" onclick="saveProject()">💾 保存项目</button>
                            <button class="btn btn-danger" onclick="deleteProject()">🗑️ 删除项目</button>
                        </div>
                        <button class="btn btn-success" id="fetchBaselineVersionBtn" onclick="fetchBaselineVersions()" disabled>📦 获取基线版本</button>
                    </div>
                </div>
                
                <!-- 项目模块Tab -->
                <div id="project-modules" class="tab-pane">
                    <div class="tab-panel-header">
                        <div class="tab-panel-title-container">
                            <div class="tab-panel-title">项目模块</div>
                                                        <button class="btn-delete-selected" onclick="deleteSelectedModules()" id="deleteModulesBtn" disabled title="可不要乱点">
                                🗑️
                            </button>
                            <button class="btn-add-selected" onclick="showAddModulesModal()" id="addModulesBtn" title="添加模块">
                                ➕
                            </button>
                        </div>
                        <div class="button-group">
                            <div class="filter-container">
                                <label for="moduleFilterSelect" class="filter-label">模块过滤:</label>
                                <select class="form-select filter-select" id="moduleFilterSelect" onchange="onModuleFilterChange()">
                                    <option value="all">所有</option>
                                    <option value="packaged">已打包</option>
                                    <option value="updated">有更新</option>
                                    <option value="refreshed">已更新</option>
                                    <option value="unknown">未知</option>
                                </select>
                            </div>

                            <button class="btn btn-secondary" onclick="batchPackage()" id="batchPackageBtn">
                                📦 批量打包
                            </button>
                            <button class="btn btn-info" onclick="updateCodeCommits()" id="updateCodeCommitsBtn">
                                🔄 更新代码
                            </button>
                            <button class="btn btn-success" onclick="updateBuildTags()" id="updateBuildTagsBtn">
                                🏷️ 更新所有
                            </button>
                        </div>
                    </div>
                    <div id="modulesList" class="modules-container">
                        <!-- 模块列表将在这里动态加载 -->
                    </div>
                </div>
                
                <!-- 交付记录Tab -->
                <div id="project-deliveries" class="tab-pane">
                    <div class="tab-panel-header">
                        <div class="tab-panel-title">交付记录</div>
                        <button class="btn btn-secondary" onclick="addNewDeliveryRow()">
                            + 新增记录
                        </button>
                    </div>
                    <div id="deliveriesList" class="deliveries-container">
                        <!-- 交付记录列表将在这里动态加载 -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 编辑交付记录模态框 -->
    <div id="editDeliveryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">编辑交付记录</h3>
                <span class="close" onclick="closeEditDeliveryModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">主题ID</label>
                    <input type="text" class="form-input" id="editDeliveryCrpId" placeholder="请输入CRP主题ID">
                </div>
                <div class="form-group">
                    <label class="form-label">主题名称</label>
                    <input type="text" class="form-input" id="editDeliveryCrpName" placeholder="请输入主题名称">
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-footer-left">
                    <button class="btn btn-danger" onclick="deleteDeliveryRecord()">🗑️ 删除</button>
                </div>
                <div class="modal-footer-right">
                    <button class="btn btn-sm" onclick="closeEditDeliveryModal()">取消</button>
                    <button class="btn btn-primary" onclick="saveEditDeliveryRecord()">保存</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 打包信息模态框 -->
    <div id="packageInfoModal" class="modal">
        <div class="modal-content package-info-modal">
            <div class="modal-header">
                <h3 class="modal-title">打包信息</h3>
                <span class="close" onclick="closePackageInfoModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="packageInfoContent">
                    <!-- 打包信息内容将在这里动态加载 -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" onclick="closePackageInfoModal()">关闭</button>
            </div>
        </div>
    </div>

        <!-- 添加模块模态框 -->
        <div id="addModulesModal" class="modal">
            <div class="modal-content" style="max-width: 800px;">
                <div class="modal-header">
                    <h3 class="modal-title">添加模块到项目</h3>
                    <span class="close" onclick="closeAddModulesModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">搜索模块</label>
                        <input type="text" class="form-input" id="moduleSearchInput" placeholder="输入模块名称搜索" oninput="filterModules()">
                    </div>
                    <div id="allModulesList" class="batch-modules-list" style="max-height: 400px;">
                        <!-- 模块列表将在这里动态加载 -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeAddModulesModal()">取消</button>
                    <button class="btn btn-primary" onclick="confirmAddModules()">确认添加</button>
                </div>
            </div>
        </div>

        <!-- 批量打包确认框 -->
        <div id="batchPackageModal" class="modal">
        <div class="modal-content batch-package-modal">
            <div class="modal-header">
                <h3 class="modal-title">批量打包确认</h3>
                <span class="close" onclick="closeBatchPackageModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">选择交付记录</label>
                    <select class="form-input" id="batchDeliverySelect">
                        <option value="">请选择交付记录...</option>
                        <!-- 交付记录选项将在这里动态加载 -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">批量打包模块列表</label>
                    <div id="batchPackageModulesList" class="batch-modules-list">
                        <!-- 模块列表将在这里动态加载 -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeBatchPackageModal()">取消</button>
                <div style="display: flex; align-items: center; margin: 0 15px;">
                    <input type="checkbox" id="useCurrentVersionCheckbox" style="margin-right: 5px;">
                    <label for="useCurrentVersionCheckbox">使用当前版本号</label>
                </div>
                <button class="btn btn-primary" onclick="confirmBatchPackage()">确认批量打包</button>
            </div>
        </div>
    </div>

    <!-- 打包对话框 -->
    <div id="packageModal" class="modal">
        <div class="modal-content package-modal">
            <div class="modal-header">
                <h3 class="modal-title">模块打包</h3>
                <span class="close" onclick="closePackageModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" id="packageModuleName">模块名称</label>
                </div>
                <div class="form-group">
                    <label class="form-label">版本号</label>
                    <input type="text" class="form-input" id="packageVersionInput" placeholder="请输入版本号，如：1.0.0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">修改Hash</label>
                    <input type="text" class="form-input" id="packageHashInput" placeholder="请输入修改Hash值（可选）">
                </div>
                
                <div class="form-group">
                    <label class="form-label">选择交付记录</label>
                    <select class="form-input" id="packageDeliverySelect">
                        <option value="">请选择交付记录...</option>
                        <!-- 交付记录选项将在这里动态加载 -->
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closePackageModal()">取消</button>
                <button class="btn btn-primary" onclick="confirmPackage()">开始打包</button>
            </div>
        </div>
    </div>

    <!-- 添加项目目标模态框 -->
    <div id="goalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">添加项目目标</div>
                <span class="close" onclick="closeGoalModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">目标名称</label>
                    <input type="text" class="form-input" id="newGoalName" placeholder="请输入目标名称">
                </div>
                <div class="form-group">
                    <label class="form-label">目标描述</label>
                    <textarea class="form-textarea" id="newGoalDescription" placeholder="请输入目标描述"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">优先级</label>
                    <select class="form-input" id="newGoalPriority">
                        <option value="low">低</option>
                        <option value="medium" selected>中</option>
                        <option value="high">高</option>
                        <option value="critical">紧急</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeGoalModal()">取消</button>
                <button class="btn btn-primary" onclick="addGoal()">添加</button>
            </div>
        </div>
    </div>

    <!-- 提测信息模态框 -->
    <div id="testInfoModal" class="modal">
        <div class="modal-content test-info-modal">
            <div class="modal-header">
                <h3 class="modal-title">提测信息</h3>
                <span class="close" onclick="closeTestInfoModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="testInfoContent">
                    <!-- 提测信息内容将在这里动态加载 -->
                    <p>提测信息功能正在开发中...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm" onclick="closeTestInfoModal()">关闭</button>
                    </div>
    </div>
</div>

<!-- 补充转测单模态框 -->
<div id="addPmsLinksModal" class="modal">
    <div class="modal-content add-pms-links-modal">
        <div class="modal-header">
            <h3 class="modal-title">补充其他转测单</h3>
            <span class="close" onclick="closeAddPmsLinksModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="add-pms-form">
                <label for="pmsLinksInput">请粘贴PMS链接（每行一个）：</label>
                <textarea id="pmsLinksInput" rows="10" placeholder="请在此粘贴PMS链接，每行一个。&#10;例如：&#10;https://pms.example.com/bug-view-123456.html&#10;https://pms.example.com/task-view-789012.html"></textarea>
                <div class="form-tips">
                    <small>提示：支持多种PMS链接格式，系统会自动识别编号</small>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeAddPmsLinksModal()">取消</button>
            <button class="btn btn-primary" onclick="submitPmsLinks()">确认添加</button>
        </div>
    </div>
</div>

<script src="/assets/js/notification.js"></script>
<script>
let currentProjectId = <?php echo $projectId; ?>;
// 编辑交付记录时保存原始值，用于判断是否仅修改了主题名称
let originalDeliveryCrpId = '';
let originalDeliveryCrpName = '';

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    loadProjectDetails(currentProjectId);
    
    // 恢复上次的tab状态
    const savedTab = localStorage.getItem('currentProjectTab');
    if (savedTab && document.getElementById(savedTab)) {
        switchTab(savedTab);
    } else {
        // 如果没有保存的tab状态，使用默认的project-info tab
        switchTab('project-info');
    }
    
    // 添加模态框外部点击关闭功能
    // 记录mousedown事件的目标，用于区分拖拽选择和真正的点击
    let modalMouseDownTarget = null;
    
    window.addEventListener('mousedown', function(event) {
        modalMouseDownTarget = event.target;
    });
    
    window.addEventListener('click', function(event) {
        const editDeliveryModal = document.getElementById('editDeliveryModal');
        const packageInfoModal = document.getElementById('packageInfoModal');
        const addModulesModal = document.getElementById('addModulesModal');
        const batchPackageModal = document.getElementById('batchPackageModal');
        const packageModal = document.getElementById('packageModal');
        const goalModal = document.getElementById('goalModal');
        const testModal = document.getElementById('testInfoModal');
        const addPmsLinksModal = document.getElementById('addPmsLinksModal');
        
        // 只有当mousedown和click都在模态框背景上时才关闭
        // 这样可以防止拖拽选择时误关闭模态框
        if (event.target === editDeliveryModal && modalMouseDownTarget === editDeliveryModal) {
            closeEditDeliveryModal();
        }
        if (event.target === packageInfoModal && modalMouseDownTarget === packageInfoModal) {
            closePackageInfoModal();
        }
        if (event.target === addModulesModal && modalMouseDownTarget === addModulesModal) {
            closeAddModulesModal();
        }
        if (event.target === batchPackageModal && modalMouseDownTarget === batchPackageModal) {
            closeBatchPackageModal();
        }
        if (event.target === packageModal && modalMouseDownTarget === packageModal) {
            closePackageModal();
        }
        if (event.target === goalModal && modalMouseDownTarget === goalModal) {
            closeGoalModal();
        }
        if (event.target === testModal && modalMouseDownTarget === testModal) {
            closeTestInfoModal();
        }
        if (event.target === addPmsLinksModal && modalMouseDownTarget === addPmsLinksModal) {
            closeAddPmsLinksModal();
        }
    });
});

// 加载项目详情
async function loadProjectDetails(projectId) {
    try {
        const response = await fetch(`/api/projects?action=detail&project_id=${projectId}`);
        const result = await response.json();
        
        if (result.success) {
            const project = result.data;
            document.getElementById('projectName').value = project.name;
            document.getElementById('projectDescription').value = project.description || '';
            document.getElementById('projectBranch').value = project.crp_branch_name || '';
            document.getElementById('projectType').value = project.project_type || '';
            document.getElementById('projectManager').value = project.pm || '';
            document.getElementById('projectTitle').textContent = `📱 ${project.name}`;
            document.getElementById('pageTitle').textContent = `${project.name} - 研发自动化管理平台`;
            
            // 如果项目有分支信息，设置selectedBranchData
            if (project.crp_branch_id && project.crp_branch_name) {
                selectedBranchData = {
                    crp_id: project.crp_branch_id,
                    name: project.crp_branch_name
                };
            }
            
            // 如果项目有基线信息，设置selectedBaselineData
            if (project.system_baseline_id && project.system_baseline_name) {
                document.getElementById('systemBaseline').value = project.system_baseline_name;
                selectedBaselineData = {
                    crp_id: project.system_baseline_id,
                    name: project.system_baseline_name
                };
            }
            
            // 更新获取基线版本按钮状态
            updateFetchBaselineBtnState();
            
            // 加载所有相关数据
            loadProjectModulesWithFilter(projectId, 'all');
            loadProjectGoals(projectId);
            loadProjectDeliveries(projectId);
            loadProjectArchitectures();
            loadProjectTypes();
            loadProjectManagers();
            
            // 初始化分支选择器
            initBranchSelector();
            
            // 初始化系统基线选择器
            initBaselineSelector();
            
            // 确保按钮状态在初始化后更新
            setTimeout(() => updateFetchBaselineBtnState(), 100);
        }
    } catch (error) {
        console.error('加载项目详情失败：', error);
    }
}

// 加载项目分类选项
async function loadProjectTypes() {
    try {
        const response = await fetch('/api/projects?action=project_types');
        const result = await response.json();
        
        const datalist = document.getElementById('projectTypeOptions');
        
        if (result.success) {
            const projectTypes = result.data;
            
            // 清空现有选项
            datalist.innerHTML = '';
            
            // 添加分类选项
            projectTypes.forEach(typeObj => {
                const option = document.createElement('option');
                option.value = typeObj.project_type;
                datalist.appendChild(option);
            });
        }
    } catch (error) {
        console.error('加载项目分类失败：', error);
    }
}

// 加载项目经理选项
async function loadProjectManagers() {
    try {
        const response = await fetch('/api/projects?action=project_managers');
        const result = await response.json();
        
        const datalist = document.getElementById('projectManagerOptions');
        
        if (result.success) {
            const projectManagers = result.data;
            
            // 清空现有选项
            datalist.innerHTML = '';
            
            // 添加项目经理选项
            projectManagers.forEach(managerObj => {
                const option = document.createElement('option');
                option.value = managerObj.pm;
                datalist.appendChild(option);
            });
        }
    } catch (error) {
        console.error('加载项目经理失败：', error);
    }
}

// 全局变量：当前显示状态
let showAllModules = false; // 默认显示已打包模块

// 加载项目模块
async function loadProjectModules(projectId, showAll = false) {
    try {
        const response = await fetch(`/api/projects?action=modules&project_id=${projectId}&show_all=${showAll}`);
        const result = await response.json();
        
        if (result.success) {
            renderModulesList(result.data);
        }
    } catch (error) {
        console.error('加载项目模块失败：', error);
    }
}

// 渲染模块列表
function renderModulesList(modules) {
    const modulesList = document.getElementById('modulesList');
    
    // 保存数据到全局变量
    currentModulesData = [...modules];
    
    // 更新表头标题中的模块数量
    const panelTitle = document.querySelector('#project-modules .tab-panel-title');
    if (panelTitle) {
        panelTitle.textContent = `项目模块（${modules.length}）`;
    }
    
    if (modules.length === 0) {
        modulesList.innerHTML = '<div class="empty-state"><h3>暂无模块</h3><p>该分类下没有可用的模块</p></div>';
        return;
    }
    
    const tableHTML = `
        <table class="modules-table">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="selectAllModules" onchange="toggleSelectAllModules(this)">
                    </th>
                    <th class="sortable" style="width: 180px;" onclick="sortModules('name')">模块名称 <span class="sort-icon">↕</span></th>
                    <th class="sortable" style="width: 120px;" onclick="sortModules('project_branch')">分支 <span class="sort-icon">↕</span></th>
                    <th class="sortable" style="width: 100px;" onclick="sortModules('branch_commit')">最近提交 <span class="sort-icon">↕</span></th>
                    <th class="sortable" style="width: 150px;" onclick="sortModules('branch_tag')">最新tag <span class="sort-icon">↕</span></th>
                    <th class="sortable" style="width: 200px;" onclick="sortModules('project_version')">系统版本 <span class="sort-icon">↕</span></th>
                    <th class="sortable" onclick="sortModules('project_module_status')">状态 <span class="sort-icon">↕</span></th>
                    <th class="sortable" onclick="sortModules('build_cur_tag')">打包版本 <span class="sort-icon">↕</span></th>
                    <th class="sortable" onclick="sortModules('build_tag_info')">tag信息 <span class="sort-icon">↕</span></th>
                    <th class="sortable" onclick="sortModules('build_commit')">打包提交 <span class="sort-icon">↕</span></th>
                    <th>代码</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                ${modules.map(module => `
                    <tr data-module-id="${module.id}">
                        <td>
                            <input type="checkbox" class="module-checkbox" value="${module.id}" onchange="updateSelectAllState()">
                        </td>
                        <td>${module.alias ? `${module.name}（${module.alias}）` : module.name}</td>
                        <td>
                            <input type="text" class="branch-input" 
                                   value="${module.project_branch || ''}" 
                                   placeholder="输入分支名称"
                                   onblur="saveBranchChange(${module.id}, this.value)"
                                   onkeypress="if(event.key==='Enter') this.blur()">
                        </td>
                        <td title="${module.branch_commit || '-'}">
                            <div style="display: flex; align-items: center; flex-wrap: nowrap;">
                                ${module.branch_commit ? `<span class="commit-hash" style="flex-shrink: 1; overflow: hidden; text-overflow: ellipsis;">${module.branch_commit.substring(0, 8)}</span>` : '-'}
                                ${module.branch_commit_url ? `<a href="${module.branch_commit_url}" target="_blank" class="commit-link-btn" title="查看最近提交">🔗</a>` : ''}
                            </div>
                        </td>
                        <td title="${module.branch_tag_info || ''}">
                            <span class="tag-info-display" style="display: inline-block; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle;">
                                ${module.branch_tag || '-'}${module.branch_tag_info ? ' ' + module.branch_tag_info : ''}
                            </span>
                        </td>
                        <td>
                            <input type="text" class="version-input" 
                                   value="${module.project_version || ''}" 
                                   placeholder="输入系统版本"
                                   onblur="saveVersionChange(${module.id}, this.value)"
                                   onkeypress="if(event.key==='Enter') this.blur()">
                        </td>
                        <td>
                            <span class="module-status ${module.project_module_status || '未知'}">
                                ${getModuleStatusText(module.project_module_status)}
                            </span>
                        </td>
                        <td>${module.build_cur_tag || '-'}</td>
                        <td>
                            ${module.build_tag_info || !module.project_branch || module.project_branch.substr(0,7) == 'project' ? 
                                `<span class="tag-info-display" title="${module.build_tag_info}">${module.build_tag_info}</span>` :
                                `<button class="create-tag-btn" onclick="createTag(${module.id})" title="${module.build_crp_name}">打tag</button>`
                            }
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; flex-wrap: nowrap;">
                                <input type="text" class="commit-input" 
                                       value="${module.build_commit || ''}" 
                                       placeholder="输入打包提交"
                                       onblur="saveCommitChange(${module.id}, this.value)"
                                       onkeypress="if(event.key==='Enter') this.blur()"
                                       style="flex: 1; margin-right: 5px;">
                                ${module.build_commit_url ? `<a href="${module.build_commit_url}" target="_blank" class="commit-link-btn" title="查看打包提交">🔗</a>` : ''}
                            </div>
                        </td>
                        <td>
                            <a href="${module.code_url || '#'}" target="_blank" class="code-link">查看代码</a>
                        </td>
                        <td>
                            <a href="#" class="package-link" onclick="buildPackage(${module.id}, '${module.alias ? `${module.name}（${module.alias}）` : module.name}', '${module.build_cur_tag ? module.build_cur_tag : module.branch_tag || ''}')">打包</a>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
    
    modulesList.innerHTML = tableHTML;
    
    // 更新排序图标
    updateSortIcons();
    
    // 初始化全选框状态
    updateSelectAllState();
}

// 加载项目目标
async function loadProjectGoals(projectId) {
    try {
        const response = await fetch(`/api/projects?action=goals&project_id=${projectId}`);
        const result = await response.json();
        
        if (result.success) {
            renderProjectGoals(result.data);
        }
    } catch (error) {
        console.error('加载项目目标失败：', error);
    }
}

// 渲染项目目标
function renderProjectGoals(goals) {
    const goalsContainer = document.getElementById('projectGoals');
    
    if (goals.length === 0) {
        goalsContainer.innerHTML = '<div class="empty-state"><p>暂无项目目标</p></div>';
        return;
    }
    
    goalsContainer.innerHTML = goals.map(goal => `
        <div class="goal-item ${goal.priority}">
            <div class="goal-name">${goal.goal_name}</div>
            <div class="goal-description">${goal.goal_description || ''}</div>
            <div class="goal-priority">优先级: ${getPriorityText(goal.priority)} | 状态: ${getStatusText(goal.status)}</div>
        </div>
    `).join('');
}



// 加载项目交付记录——交付记录页面
async function loadProjectDeliveries(projectId) {
    try {
        const response = await fetch(`/api/projects?action=deliveries&project_id=${projectId}`);
        const result = await response.json();
        
        if (result.success) {
            renderProjectDeliveries(result.data);
        }
    } catch (error) {
        console.error('加载项目交付记录失败：', error);
    }
}

// 渲染项目交付记录
function renderProjectDeliveries(deliveries) {
    const deliveriesContainer = document.getElementById('deliveriesList');
    
    // 新增记录表单
    const newDeliveryForm = `
        <div id="newDeliveryForm" class="new-delivery-form">
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label">主题ID</label>
                    <input type="text" class="form-input" id="newDeliveryTopicId" placeholder="主题ID（可选，留空将自动创建）">
                </div>
                <div class="form-col">
                    <label class="form-label">主题名称</label>
                    <input type="text" class="form-input" id="newDeliveryTopicName" placeholder="请输入主题名称">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-sm" onclick="cancelNewDeliveryRow()">取消</button>
                <button class="btn btn-primary btn-sm" onclick="saveNewDeliveryRow()">保存</button>
            </div>
        </div>
    `;
    
    let deliveryItemsHTML = '';
    if (deliveries.length === 0) {
        deliveryItemsHTML = '<div class="empty-state"><h3>暂无交付记录</h3><p>点击新增记录开始</p></div>';
    } else {
        deliveryItemsHTML = deliveries.map(delivery => `
            <div class="delivery-item" data-delivery-id="${delivery.id}">
                <div class="delivery-header">
                    <div class="delivery-title-section">
                        <div class="delivery-title">${delivery.crp_name || delivery.crp_id || '未命名主题'}</div>
                        <div class="delivery-actions">
                            <label class="archive-switch" title="点击切换归档状态">
                                <input type="checkbox" 
                                       class="archive-checkbox" 
                                       data-delivery-id="${delivery.id}" 
                                       ${delivery.archive == 1 ? 'checked' : ''}
                                       onchange="toggleArchiveStatus(${delivery.id}, this)">
                                <span class="archive-label">${delivery.archive == 1 ? '已归档' : '未归档'}</span>
                            </label>
                            <span class="delivery-status ${delivery.crp_state}">${getWorkflowStatusText(delivery.crp_state)}</span>
                            <span class="delivery-status ${delivery.crp_state}">${delivery.crp_locked === "1" ? '已锁定' : '未锁定'}</span>
                            ${delivery.crp_locked === "1" ? `<button class="btn btn-sm btn-danger delivery-action-btn" onclick="rejectCrpTopic(${delivery.id}, '${delivery.crp_id}')" style="margin-right: 8px;">❌ 驳回主题</button>` : ''}
                            <button class="btn btn-sm btn-warning delivery-action-btn" onclick="showTestInfo(${delivery.id},'${delivery.repo_addr}')">🧪 提测信息</button>
                            <button class="btn btn-sm btn-success delivery-action-btn" onclick="exportTestInfo(${delivery.id})">📤 导出提测单</button>
                            <button class="btn btn-sm btn-secondary delivery-action-btn sync-btn" onclick="syncDeliveryCrpTopic(${delivery.id}, this)">🔄 同步</button>
                            <button class="btn btn-sm btn-primary delivery-action-btn" onclick="editDeliveryRecord(${delivery.id})">✏️ 修改</button>
                        </div>
                    </div>
                    <div class="delivery-details">
                        <div class="delivery-meta-row">
                            <span>${delivery.created_at}</span>
                            <span>主题ID: ${delivery.crp_id || '-'}</span>
                            <span>主题地址: <a href="${delivery.crp_addr || '#'}" target="_blank" title="${delivery.crp_addr || '-'}">🔗</a></span>
                            ${delivery.crp_id ? `<span>工作流地址: <a href="${window.APP_ENDPOINTS.crp}/#/workflow/workflow?id=${delivery.crp_id}" target="_blank" title="${window.APP_ENDPOINTS.crp}/#/workflow/workflow?id=${delivery.crp_id}">🔗</a></span>` : ''}
                            <div class="repo-addresses">
                                <span>仓库地址:</span>
                                ${delivery.repo_addr ? delivery.repo_addr.split(';').map(addr => 
                                    `<div class=\"repo-addr-item\">\n                                        <a href=\"${addr.trim() || '#'}\" target=\"_blank\" title=\"${addr.trim() || '-'}\">🔗</a>\n                                    </div>`
                                ).join('') : '<div class="repo-addr-item">-</div>'}
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 打包信息展示区域 - 占满整行 -->
                <div class="delivery-package-info" id="delivery-package-${delivery.id}">
                    <div class="package-info-placeholder">
                        <div class="loading-spinner">🔄 加载中...</div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    deliveriesContainer.innerHTML = newDeliveryForm + deliveryItemsHTML;
    
    // 自动加载每个交付记录的打包信息
    if (deliveries.length > 0) {
        deliveries.forEach(delivery => {
            loadAndShowPackageInfo(delivery.id);
        });
    }
}

// 切换交付记录模块显示
async function toggleDeliveryModules(deliverId) {
    const modulesContainer = document.getElementById(`delivery-modules-${deliverId}`);
    const deliveryItem = modulesContainer.parentElement;
    
    if (modulesContainer.classList.contains('expanded')) {
        modulesContainer.classList.remove('expanded');
        deliveryItem.classList.remove('expanded');
    } else {
        // 加载模块详情
        try {
            const response = await fetch(`/api/projects?action=delivery_modules&delivery_id=${deliverId}`);
            const result = await response.json();
            
            if (result.success) {
                renderDeliveryModules(deliverId, result.data);
                modulesContainer.classList.add('expanded');
                deliveryItem.classList.add('expanded');
            }
        } catch (error) {
            console.error('加载交付模块失败：', error);
        }
    }
}

// 渲染交付模块
function renderDeliveryModules(deliveryId, modules) {
    const modulesContainer = document.getElementById(`delivery-modules-${deliveryId}`);
    
    if (modules.length === 0) {
        modulesContainer.innerHTML = '<p>暂无模块信息</p>';
        return;
    }
    
    modulesContainer.innerHTML = modules.map(module => `
        <div class="delivery-module-item">
            <div>
                <div class="delivery-module-name">${module.alias ? `${module.module_name}（${module.alias}）` : module.module_name}</div>
                <div class="delivery-module-branch">分支: ${module.package_branch || '-'}</div>
            </div>
            <div>
                <span class="delivery-module-status ${module.build_status}">${getBuildStatusText(module.build_status)}</span>
                ${module.package_url ? `<a href="${module.package_url}" target="_blank" class="package-link">打包链接</a>` : ''}
            </div>
        </div>
    `).join('');
}

// Tab切换功能
function switchTab(tabName, event = null) {
    // 隐藏所有tab内容
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    
    // 移除所有tab按钮的active状态
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // 显示选中的tab内容
    document.getElementById(tabName).classList.add('active');
    
    // 激活对应的tab按钮（如果有event对象）
    if (event && event.target) {
        event.target.classList.add('active');
    } else {
        // 手动找到对应的按钮并激活
        document.querySelectorAll('.tab-btn').forEach(btn => {
            if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(tabName)) {
                btn.classList.add('active');
            }
        });
    }
    
    // 保存当前tab状态到localStorage
    localStorage.setItem('currentProjectTab', tabName);
}

// 模块过滤器变化处理
async function onModuleFilterChange() {
    const filterSelect = document.getElementById('moduleFilterSelect');
    const filterValue = filterSelect.value;
    // 根据过滤选项加载模块
    await loadProjectModulesWithFilter(currentProjectId, filterValue);
}

// 根据过滤条件加载项目模块
async function loadProjectModulesWithFilter(projectId, filterType) {
    try {
        const response = await fetch(`/api/projects?action=modules&project_id=${projectId}&filter_type=${filterType}`);
        const result = await response.json();
        
        if (result.success) {
            renderModulesList(result.data);
        } else {
            console.error('加载项目模块失败：', result.message);
        }
    } catch (error) {
        console.error('加载项目模块失败：', error);
    }
}

// 打开添加模块对话框
function openAddModulesModal() {
    addModulesModal.style.display = 'block';
    loadAllAvailableModules(currentProjectId);
}

// 关闭添加模块对话框
function closeAddModulesModal() {
    addModulesModal.style.display = 'none';
    document.getElementById('availableModulesList').innerHTML = '';
}

// 加载所有可用模块
async function loadAllAvailableModules(projectId) {
    try {
        const response = await fetch(`/api/categories?action=modules&project_id=${projectId}`);
        const result = await response.json();

        if (result.success) {
            // 保存所有可用模块到全局变量
            allAvailableModules = result.data;
            // 渲染模块列表
            renderAllModulesList();
        }
    } catch (error) {
        console.error('加载模块失败:', error);
    }
}

// 过滤模块列表
function filterModules() {
    const searchTerm = document.getElementById('moduleSearch').value.toLowerCase();
    const moduleItems = document.querySelectorAll('.module-item');
    
    moduleItems.forEach(item => {
        const label = item.querySelector('label').textContent.toLowerCase();
        if (label.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// 添加选中模块到项目
async function addSelectedModules() {
    const checkboxes = document.querySelectorAll('#availableModulesList input[type="checkbox"]:checked');
    const selectedModules = Array.from(checkboxes).map(cb => cb.value);
    
    if (selectedModules.length === 0) {
        alert('请至少选择一个模块');
        return;
    }

    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_modules',
                project_id: currentProjectId,
                module_ids: selectedModules
            })
        });
        
        const result = await response.json();
        if (result.success) {
            alert('模块添加成功');
            closeAddModulesModal();
            loadProjectModules(currentProjectId);
        } else {
            alert(result.message || '添加模块失败');
        }
    } catch (error) {
        console.error('添加模块失败:', error);
        alert('添加模块失败，请检查网络连接');
    }
}

// 批量打包
function batchPackage() {
    // 获取选中的模块
    const selectedModules = getSelectedModules();
    
    if (selectedModules.length === 0) {
        alert('请先选择要打包的模块');
        return;
    }
    
    // 显示批量打包确认框
    document.getElementById('batchPackageModal').style.display = 'block';
    
    // 加载交付记录
    loadBatchDeliveryRecords();
    
    // 渲染批量打包模块列表
    renderBatchPackageModules(selectedModules);
}

// 关闭批量打包确认框
function closeBatchPackageModal() {
    document.getElementById('batchPackageModal').style.display = 'none';
    
    // 重置确认按钮状态
    const confirmBtn = document.querySelector('#batchPackageModal .btn-primary');
    confirmBtn.textContent = '确认批量打包';
    confirmBtn.disabled = false;
    
    // 清空模块列表
    document.getElementById('batchPackageModulesList').innerHTML = '';
}

// 获取选中的模块
function getSelectedModules() {
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox:checked');
    const selectedModules = [];
    
    moduleCheckboxes.forEach(checkbox => {
        const moduleId = checkbox.value;
        
        // 从全局数据中获取完整的模块信息
        const globalModule = currentModulesData.find(m => m.id == moduleId);
        if (globalModule) {
            selectedModules.push({
                id: moduleId,
                name: globalModule.name,
                alias: globalModule.alias,
                currentVersion: globalModule.build_cur_tag || '',
                build_type: globalModule.build_type,
                branch_tag: globalModule.branch_tag
            });
        } else {
            // 如果全局数据中没有找到，则从DOM中获取基本信息
            const moduleRow = checkbox.closest('tr');
            if (moduleRow) {
                // 模块名称在第2列（索引1）
                const moduleNameCell = moduleRow.cells[1];
                const moduleName = moduleNameCell ? moduleNameCell.textContent.trim() : '未知模块';
                
                // 打包版本在第7列（索引6）
                const versionCell = moduleRow.cells[6];
                const currentVersion = versionCell ? versionCell.textContent.trim() : '';
                
                selectedModules.push({
                    id: moduleId,
                    name: moduleName,
                    currentVersion: currentVersion,
                    build_type: "1",
                    branch_tag: ""
                });
            }
        }
    });
    
    return selectedModules;
}

// 获取批量打包对话框中选中的模块
function getBatchPackageSelectedModules() {
    const batchModuleCheckboxes = document.querySelectorAll('.batch-module-checkbox:checked');
    const selectedModules = [];
    
    batchModuleCheckboxes.forEach(checkbox => {
        const moduleItem = checkbox.closest('.batch-module-item');
        if (moduleItem) {
            const moduleId = checkbox.value;
            const moduleNameElement = moduleItem.querySelector('.batch-module-name');
            const currentVersionElement = moduleItem.querySelector('.batch-module-current-version');
            const newVersionElement = moduleItem.querySelector('.batch-module-new-version');
            
            const moduleName = moduleNameElement ? moduleNameElement.textContent.trim() : '未知模块';
            let currentVersion = currentVersionElement ? 
                currentVersionElement.textContent.replace('当前版本: ', '').trim() : '';
            
            // 如果没有获取到当前版本，从系统版本获取
            if (!currentVersion || currentVersion === '未设置') {
                const globalModule = currentModulesData.find(m => m.id == moduleId);
                if (globalModule && globalModule.project_version) {
                    currentVersion = globalModule.project_version;
                }
            }
            const newVersion = newVersionElement ? 
                newVersionElement.textContent.replace('即将打包: ', '').trim() : '';
            
            // 从全局数据中获取build_type
            const globalModule = currentModulesData.find(m => m.id == moduleId);
            const buildType = globalModule ? globalModule.build_type : null;
            
            selectedModules.push({
                id: moduleId,
                name: moduleName,
                currentVersion: currentVersion,
                newVersion: newVersion,
                build_type: buildType
            });
        }
    });
    
    return selectedModules;
}

// 执行批量打包
async function executeBatchPackage(selectedModules, deliveryRecordId, deliveryName, topicId) {
    const confirmBtn = document.querySelector('#batchPackageModal .btn-primary');
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = '打包中...';
    confirmBtn.disabled = true;
    
    try {
        // 显示进度信息
        const progressContainer = document.createElement('div');
        progressContainer.id = 'batchPackageProgress';
        progressContainer.className = 'batch-package-progress';
        progressContainer.innerHTML = `
            <div class="progress-list">
                ${selectedModules.map((module, index) => `
                    <div class="progress-item" data-module-id="${module.id}">
                        <span class="progress-index">${index + 1}</span>
                        <span class="module-name">${module.alias ? `${module.name}（${module.alias}）` : module.name}</span>
                        <span class="progress-status">等待中</span>
                    </div>
                `).join('')}
            </div>
        `;
        
        // 替换模块列表为进度显示
        const modulesListContainer = document.getElementById('batchPackageModulesList');
        modulesListContainer.innerHTML = '';
        modulesListContainer.appendChild(progressContainer);
        
        // 按顺序执行打包
        const results = [];
        
        for (let i = 0; i < selectedModules.length; i++) {
            const module = selectedModules[i];
            const progressItem = progressContainer.querySelector(`[data-module-id="${module.id}"]`);
            const statusSpan = progressItem.querySelector('.progress-status');
            
            // 更新状态为打包中
            statusSpan.textContent = '打包中...';
            statusSpan.className = 'progress-status processing';
            
            try {
                // 调用打包API
                const useCurrentVersion = document.getElementById('useCurrentVersionCheckbox').checked;
                const response = await fetch('/api/package', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'build_package',
                        module_id: module.id,
                        topic_id: topicId,
                        delivery_record_id: deliveryRecordId, // 新增交付记录ID
                        version: module.build_type === 1 ? '' : (useCurrentVersion ? module.currentVersion : module.newVersion), // 先判断build_type，再判断是否使用当前版本号
                        hash: '' // 批量打包暂时不设置hash
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    statusSpan.textContent = '✅ 成功';
                    statusSpan.className = 'progress-status success';
                    results.push({ module: module, success: true, message: '打包成功' });
                } else {
                    statusSpan.textContent = '❌ 失败';
                    statusSpan.className = 'progress-status failed';
                    results.push({ module: module, success: false, message: result.message || '打包失败' });
                }
            } catch (error) {
                statusSpan.textContent = '❌ 错误';
                statusSpan.className = 'progress-status failed';
                results.push({ module: module, success: false, message: '网络错误' });
            }
            
            // 短暂延迟，让用户看到进度
            await new Promise(resolve => setTimeout(resolve, 500));
        }
        

        
        // 更新按钮状态
        confirmBtn.textContent = '完成';
        confirmBtn.disabled = true;
        
        // 重新加载模块列表以显示最新状态
        const currentFilter = document.getElementById('moduleFilterSelect').value;
        loadProjectModulesWithFilter(currentProjectId, currentFilter);
        
        // 刷新对应交付记录的界面
        if (deliveryRecordId) {
            refreshDeliveryRecordDisplay(deliveryRecordId);
        }
        
        // 将确认按钮改为完成按钮
        confirmBtn.textContent = '完成';
        confirmBtn.onclick = closeBatchPackageModal;
        confirmBtn.disabled = false;
        
    } catch (error) {
        console.error('批量打包执行失败：', error);
        alert('批量打包执行失败：' + error.message);
        
        // 恢复按钮状态
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    }
}

// 刷新模块列表
function refreshModuleList() {
    const currentFilter = document.getElementById('moduleFilterSelect').value;
    loadProjectModulesWithFilter(currentProjectId, currentFilter);
    closeBatchPackageModal();
}

// 渲染批量打包模块列表
function renderBatchPackageModules(modules) {
    const container = document.getElementById('batchPackageModulesList');
    
    if (modules.length === 0) {
        container.innerHTML = '<div class="empty-state">没有选中的模块</div>';
        return;
    }
    
    const modulesHTML = modules.map(module => {
        // 获取当前版本，如果没有则从系统版本获取
        let currentVersion = module.currentVersion;
        if (!currentVersion || currentVersion === '未设置') {
            // 从全局数据中查找系统版本
            const globalModule = currentModulesData.find(m => m.id == module.id);

            if (globalModule) {
                if(globalModule.project_version)
                    currentVersion = globalModule.project_version;
                else if (globalModule.branch_tag)
                    currentVersion = globalModule.branch_tag;
            }
        }

        const newVersion = calculateNewVersion(currentVersion, module.build_type);
        
        return `
            <div class="batch-module-item" data-module-id="${module.id}">
                <input type="checkbox" class="batch-module-checkbox" checked value="${module.id}">
                <div class="batch-module-info">
                    <div class="batch-module-name">${module.alias ? `${module.name}（${module.alias}）` : module.name}</div>
                    <div class="batch-module-current-version">
                        当前版本: ${currentVersion || '未设置'}
                    </div>
                    <div class="batch-module-new-version">
                        即将打包: ${newVersion || '根据changelog生成'}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = modulesHTML;
}

// 计算新版本号（版本+1）
function calculateNewVersion(currentVersion, buildType) {
    // 如果打包方式是build_type=1，返回空版本号
    if (buildType === "1") {
        return '';
    }
    
    if (!currentVersion || currentVersion === '未设置') {
        return '-';
    }
    
    // 简单的版本号+1逻辑
    const versionParts = currentVersion.split('.');
    if (versionParts.length > 0) {
        const lastPart = parseInt(versionParts[versionParts.length - 1]);
        if (!isNaN(lastPart)) {
            versionParts[versionParts.length - 1] = (lastPart + 1).toString();
            return versionParts.join('.');
        }
    }
    
    return currentVersion + '.1';
}

// 确认批量打包
function confirmBatchPackage() {
    const deliveryRecordId = document.getElementById('batchDeliverySelect').value;
    if (!deliveryRecordId) {
        alert('请选择交付记录');
        return;
    }
    
    // 获取批量打包对话框中选中的模块数量
    const selectedModules = getBatchPackageSelectedModules();
    if (selectedModules.length === 0) {
        alert('没有选中的模块');
        return;
    }
    
    // 获取交付记录的名称和crp_id
    const deliverySelect = document.getElementById('batchDeliverySelect');
    const selectedOption = deliverySelect.options[deliverySelect.selectedIndex];
    const deliveryName = selectedOption ? selectedOption.text : deliveryRecordId;
    const topicId = selectedOption.getAttribute('data-crp-id');
    
    // 显示确认信息
    const useCurrentVersion = document.getElementById('useCurrentVersionCheckbox').checked;
    const confirmMessage = `确认批量打包以下 ${selectedModules.length} 个模块？\n\n` +
        selectedModules.map(m => useCurrentVersion ? 
            `${m.name} (${m.currentVersion})` : 
            `${m.name} (${m.currentVersion} → ${m.newVersion})`
        ).join('\n') +
        `\n\n交付记录：${deliveryName}`;
    
    if (confirm(confirmMessage)) {
        // 执行批量打包
        executeBatchPackage(selectedModules, deliveryRecordId, deliveryName, topicId);
    }
}

// 全选/全取消模块
function toggleSelectAllModules(checkbox) {
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    moduleCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

// 更新全选框状态
function updateSelectAllState() {
    const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllModules');
    const deleteBtn = document.getElementById('deleteModulesBtn');
    
    if (moduleCheckboxes.length === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        if (deleteBtn) deleteBtn.disabled = true;
        return;
    }
    
    const checkedCount = Array.from(moduleCheckboxes).filter(cb => cb.checked).length;
    
    if (checkedCount === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        if (deleteBtn) deleteBtn.disabled = true;
    } else if (checkedCount === moduleCheckboxes.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
        if (deleteBtn) deleteBtn.disabled = false;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
        if (deleteBtn) deleteBtn.disabled = false;
    }
}

// 删除选中的模块
async function deleteSelectedModules() {
    const selectedModules = getSelectedModules();
    
    if (selectedModules.length === 0) {
        alert('请先选择要删除的模块');
        return;
    }
    
    // 显示确认对话框
    const confirmMessage = `确认删除以下 ${selectedModules.length} 个模块吗？\n\n` +
        selectedModules.map(m => `• ${m.name}`).join('\n') +
        '\n\n⚠️ 此操作不可恢复！';
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    const deleteBtn = document.getElementById('deleteModulesBtn');
    const originalText = deleteBtn.textContent;
    deleteBtn.textContent = '删除中...';
    deleteBtn.disabled = true;
    
    try {
        // 调用删除API
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_modules',
                project_id: currentProjectId,
                module_ids: selectedModules.map(m => m.id)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(`✅ 删除成功！\n\n已删除 ${result.data.deleted_count} 个模块`);
            
            // 重新加载模块列表
            const currentFilter = document.getElementById('moduleFilterSelect').value;
            loadProjectModulesWithFilter(currentProjectId, currentFilter);
        } else {
            alert('删除失败：' + result.message);
        }
    } catch (error) {
        console.error('删除模块失败：', error);
        alert('删除模块失败，请检查网络连接');
    } finally {
        deleteBtn.textContent = originalText;
        deleteBtn.disabled = false;
    }
}

// 更新构建标签
async function updateBuildTags() {
    const button = document.getElementById('updateBuildTagsBtn');
    const originalText = button.textContent;
    button.textContent = '🏷️ 更新中...';
    button.disabled = true;
    
    try {
        const response = await fetch(`/api/projects?action=refresh_build_tags&project_id=${currentProjectId}`);
        const result = await response.json();
        
        if (result.success) {
            console.log('构建标签更新成功:', result.data);
            
            // 显示更新结果
            const data = result.data;
            if (data.error_count)
                alert(`✅ ${result.message}\n\n更新详情：\n- 更新模块：${data.updated_count}个\n- 错误模块：${data.error_count}个`);
            else
                notify('更新成功！');

            // 重新加载模块列表以显示最新状态
            const currentFilter = document.getElementById('moduleFilterSelect').value;
            loadProjectModulesWithFilter(currentProjectId, currentFilter);
        } else {
            alert('构建标签更新失败：' + result.message);
        }
    } catch (error) {
        console.error('构建标签更新失败：', error);
        alert('构建标签更新失败，请检查网络连接');
    } finally {
        button.textContent = originalText;
        button.disabled = false;
    }
}

// 更新代码提交
async function updateCodeCommits() {
    const button = document.getElementById('updateCodeCommitsBtn');
    const originalText = button.textContent;
    button.textContent = '🔄 更新中...';
    button.disabled = true;
    
    try {
        // 检查是否有勾选的模块
        const selectedCheckboxes = document.querySelectorAll('.module-checkbox:checked');
        let url = `/api/projects?action=update_code_commits&project_id=${currentProjectId}`;
        
        // 如果有勾选的模块，只更新勾选的模块
        if (selectedCheckboxes.length > 0) {
            const selectedModuleIds = Array.from(selectedCheckboxes).map(cb => cb.value);
            url += `&module_ids=${selectedModuleIds.join(',')}`;
            console.log('只更新勾选的模块:', selectedModuleIds);
        } else {
            console.log('更新所有模块');
        }
        
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            console.log('代码提交更新成功:', result.data);
            
            // 显示更新结果
            const data = result.data;
            if (data.error_count)
                alert(`✅ ${result.message}\n\n更新详情：\n- 更新模块：${data.updated_count}个\n- 错误模块：${data.error_count}个`);
            else
                notify('更新成功！');
            // 重新加载模块列表以显示最新状态
            const currentFilter = document.getElementById('moduleFilterSelect').value;
            loadProjectModulesWithFilter(currentProjectId, currentFilter);
        } else {
            alert('代码提交更新失败：' + result.message);
        }
    } catch (error) {
        console.error('代码提交更新失败：', error);
        alert('代码提交更新失败，请检查网络连接');
    } finally {
        button.textContent = originalText;
        button.disabled = false;
    }
}

// 全局变量：当前要打包的模块ID
let currentPackageModuleId = null;

// 打包模块
function buildPackage(moduleId, moduleName, currentVersion = '') {
    currentPackageModuleId = moduleId;
    // 显示打包对话框
    document.getElementById('packageModal').style.display = 'block';
    // 填充当前版本号
    document.getElementById('packageVersionInput').value = currentVersion;
    // 清空hash输入框
    document.getElementById('packageHashInput').value = '';
    document.getElementById('packageModuleName').innerHTML = moduleName+'('+currentVersion+')';
    document.getElementById('packageDeliverySelect').innerHTML = '<option value="">请选择交付记录...</option>';
    // 加载交付记录
    loadDeliveryRecords();
}

// 关闭打包对话框
function closePackageModal() {
    document.getElementById('packageModal').style.display = 'none';
    currentPackageModuleId = null;
    // 清空hash输入框
    document.getElementById('packageHashInput').value = '';
}

// 加载交付记录——打包对话框
async function loadDeliveryRecords() {
    try {
        const response = await fetch(`/api/projects?action=deliveries&project_id=${currentProjectId}&archive=0`);
        const result = await response.json();
        
        if (result.success) {
            const select = document.getElementById('packageDeliverySelect');
            select.innerHTML = '<option value="">请选择交付记录...</option>';
            
            result.data.forEach(delivery => {
                const option = document.createElement('option');
                option.value = delivery.id; // 使用交付记录ID而不是crp_id
                option.setAttribute('data-crp-id', delivery.crp_id); // 保存crp_id用于显示
                option.textContent = `${delivery.crp_name || delivery.crp_id} (${delivery.created_at})`;
                select.appendChild(option);
            });
        } else {
            console.error('加载交付记录失败：', result.message);
        }
    } catch (error) {
        console.error('加载交付记录异常：', error);
    }
}

// 加载批量打包交付记录——批量打包对话框
async function loadBatchDeliveryRecords() {
    try {
        const response = await fetch(`/api/projects?action=deliveries&project_id=${currentProjectId}&archive=0`);
        const result = await response.json();
        
        if (result.success) {
            const select = document.getElementById('batchDeliverySelect');
            select.innerHTML = '<option value="">请选择交付记录...</option>';
            
            result.data.forEach(delivery => {
                const option = document.createElement('option');
                option.value = delivery.id; // 使用交付记录ID而不是crp_id
                option.setAttribute('data-crp-id', delivery.crp_id); // 保存crp_id用于显示
                option.textContent = `${delivery.crp_name || delivery.crp_id} (${delivery.created_at})`;
                select.appendChild(option);
            });
        } else {
            console.error('加载批量打包交付记录失败：', result.message);
        }
    } catch (error) {
        console.error('加载批量打包交付记录异常：', error);
    }
}

// 确认打包
async function confirmPackage() {
    const version = document.getElementById('packageVersionInput').value.trim();
    const hash = document.getElementById('packageHashInput').value.trim();
    const deliveryRecordId = document.getElementById('packageDeliverySelect').value;
    
    if (!version) {
        alert('请输入版本号');
        return;
    }
    
    if (!deliveryRecordId) {
        alert('请选择交付记录');
        return;
    }
    
    // 获取选中的交付记录的crp_id
    const deliverySelect = document.getElementById('packageDeliverySelect');
    const selectedOption = deliverySelect.options[deliverySelect.selectedIndex];
    const topicId = selectedOption.getAttribute('data-crp-id');
    
    const confirmBtn = document.querySelector('#packageModal .btn-primary');
    const originalText = confirmBtn.textContent;
    confirmBtn.textContent = '打包中...';
    confirmBtn.disabled = true;
    
    try {
        const response = await fetch('/api/package', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'build_package',
                module_id: currentPackageModuleId,
                topic_id: topicId,
                delivery_record_id: deliveryRecordId, // 新增交付记录ID
                version: version,
                hash: hash
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            notify('打包请求已提交成功！');
            closePackageModal();
            
            // 重新加载模块列表以显示最新状态
            const currentFilter = document.getElementById('moduleFilterSelect').value;
            loadProjectModulesWithFilter(currentProjectId, currentFilter);
            
            // 刷新对应交付记录的界面
            const deliveryRecordId = document.getElementById('packageDeliverySelect').value;
            if (deliveryRecordId) {
                refreshDeliveryRecordDisplay(deliveryRecordId);
            }
        } else {
            alert('打包失败：' + result.message);
        }
    } catch (error) {
        console.error('打包请求失败：', error);
        alert('打包请求失败，请检查网络连接');
    } finally {
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    }
}

// 项目架构管理

// 加载项目架构配置
async function loadProjectArchitectures() {
    try {
        const response = await fetch(`/api/projects?action=detail&project_id=${currentProjectId}`);
        const result = await response.json();
        
        if (result.success && result.data.build_arches) {
            const arches = result.data.build_arches.split(';').filter(arch => arch.trim());
            
            // 重置所有复选框
            document.querySelectorAll('.project-arch-checkboxes-compact input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = arches.includes(checkbox.value);
            });
        }
    } catch (error) {
        console.error('加载架构配置失败：', error);
    }
}

// 获取项目选中的架构
function getProjectSelectedArchitectures() {
    const checkboxes = document.querySelectorAll('.project-arch-checkboxes-compact input[type="checkbox"]:checked');
    return Array.from(checkboxes).map(checkbox => checkbox.value);
}

// 全局变量：选中的分支数据
let selectedBranchData = null;

// 保存项目
async function saveProject() {
    const name = document.getElementById('projectName').value.trim();
    const description = document.getElementById('projectDescription').value.trim();
    const projectManager = document.getElementById('projectManager').value.trim();
    
    if (!name) {
        alert('请输入项目名称');
        return;
    }
    
    // 准备请求数据
    const projectType = document.getElementById('projectType').value.trim();
    const requestData = {
        name: name,
        description: description,
        project_type: projectType
    };
    
    // 添加项目经理字段
    if (projectManager) {
        requestData.pm = projectManager;
    }
    
    // 如果选择了CRP分支，添加相关字段
    if (selectedBranchData) {
        requestData.crp_branch_id = selectedBranchData.crp_id;
        requestData.crp_branch_name = selectedBranchData.name;
        console.log('保存CRP分支信息:', { 
            id: selectedBranchData.crp_id, 
            name: selectedBranchData.name 
        });
    }
    
    // 如果选择了系统基线，添加相关字段
    if (selectedBaselineData) {
        requestData.system_baseline_id = selectedBaselineData.crp_id;
        requestData.system_baseline_name = selectedBaselineData.name;
        console.log('保存系统基线信息:', { 
            id: selectedBaselineData.crp_id, 
            name: selectedBaselineData.name 
        });
    }
    
    // 添加架构配置
    const selectedArches = getProjectSelectedArchitectures();
    requestData.build_arches = selectedArches.join(';');
    
    try {
        const response = await fetch(`/api/projects/${currentProjectId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('项目保存成功');
            // 重新加载项目详情
            loadProjectDetails(currentProjectId);
        } else {
            alert('保存项目失败：' + result.message);
        }
    } catch (error) {
        console.error('保存项目失败：', error);
        alert('保存项目失败，请检查网络连接');
    }
}

// 删除项目
async function deleteProject() {
    if (!confirm('确定要删除这个项目吗？')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/projects/${currentProjectId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('项目删除成功');
            // 跳转回项目列表
            window.location.href = '/projects';
        } else {
            alert('删除项目失败：' + result.message);
        }
    } catch (error) {
        console.error('删除项目失败：', error);
        alert('删除项目失败，请检查网络连接');
    }
}

// 显示添加目标模态框
function showAddGoalModal() {
    document.getElementById('goalModal').style.display = 'block';
}

// 关闭添加目标模态框
function closeGoalModal() {
    document.getElementById('goalModal').style.display = 'none';
    document.getElementById('newGoalName').value = '';
    document.getElementById('newGoalDescription').value = '';
    document.getElementById('newGoalPriority').value = 'medium';
}

// 添加项目目标
async function addGoal() {
    const goalName = document.getElementById('newGoalName').value.trim();
    const goalDescription = document.getElementById('newGoalDescription').value.trim();
    const priority = document.getElementById('newGoalPriority').value;
    
    if (!goalName) {
        alert('请输入目标名称');
        return;
    }
    
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_goal',
                project_id: currentProjectId,
                goal_name: goalName,
                goal_description: goalDescription,
                priority: priority
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeGoalModal();
            alert('项目目标添加成功');
            loadProjectGoals(currentProjectId);
        } else {
            alert('添加项目目标失败：' + result.message);
        }
    } catch (error) {
        console.error('添加项目目标失败：', error);
        alert('添加项目目标失败，请检查网络连接');
    }
}

// 分支选择器相关变量
let branchData = [];
let selectedBranchIndex = -1;

// 初始化分支选择器
function initBranchSelector() {
    const branchInput = document.getElementById('projectBranch');
    const branchDropdown = document.getElementById('branchDropdown');
    
    if (!branchInput || !branchDropdown) return;
    
    // 加载分支数据
    loadBranchData();
    
    // 输入框事件
    branchInput.addEventListener('input', function() {
        const query = this.value.trim();
        filterAndShowBranches(query);
    });
    
    branchInput.addEventListener('focus', function() {
        const query = this.value.trim();
        filterAndShowBranches(query);
    });
    
    branchInput.addEventListener('keydown', function(e) {
        const options = branchDropdown.querySelectorAll('.branch-option');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedBranchIndex = Math.min(selectedBranchIndex + 1, options.length - 1);
            updateBranchSelection();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedBranchIndex = Math.max(selectedBranchIndex - 1, -1);
            updateBranchSelection();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedBranchIndex >= 0 && selectedBranchIndex < options.length) {
                selectBranch(options[selectedBranchIndex]);
            }
        } else if (e.key === 'Escape') {
            hideBranchDropdown();
        }
    });
    
    // 点击外部关闭下拉框
    document.addEventListener('click', function(e) {
        if (!branchInput.contains(e.target) && !branchDropdown.contains(e.target)) {
            hideBranchDropdown();
        }
    });
}

// 加载分支数据
async function loadBranchData() {
    try {
        const response = await fetch('/api/crp-sync?action=list');
        const result = await response.json();
        
        if (result.success) {
            branchData = result.data;
        } else {
            console.error('加载分支数据失败:', result.message);
        }
    } catch (error) {
        console.error('加载分支数据异常:', error);
    }
}

// 过滤并显示分支
function filterAndShowBranches(query) {
    const branchDropdown = document.getElementById('branchDropdown');
    selectedBranchIndex = -1;
    
    if (!query) {
        // 显示所有分支（限制前20个）
        const limitedBranches = branchData.slice(0, 20);
        renderBranchOptions(limitedBranches);
    } else {
        // 按名称过滤分支
        const filtered = branchData.filter(branch => 
            branch.name.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 20);
        renderBranchOptions(filtered);
    }
    
    branchDropdown.classList.add('show');
}

// 渲染分支选项
function renderBranchOptions(branches) {
    const branchDropdown = document.getElementById('branchDropdown');
    
    if (branches.length === 0) {
        branchDropdown.innerHTML = '<div class="branch-no-results">未找到匹配的分支</div>';
        return;
    }
    
    branchDropdown.innerHTML = branches.map((branch, index) => `
        <div class="branch-option" data-index="${index}" onclick="selectBranchByIndex(${index})">
            <div class="branch-name">${branch.name}</div>
            ${branch.description ? `<div class="branch-description">${branch.description}</div>` : ''}
        </div>
    `).join('');
}

// 更新分支选择状态
function updateBranchSelection() {
    const options = document.querySelectorAll('.branch-option');
    options.forEach((option, index) => {
        option.classList.toggle('active', index === selectedBranchIndex);
    });
    
    // 滚动到选中项
    if (selectedBranchIndex >= 0 && selectedBranchIndex < options.length) {
        options[selectedBranchIndex].scrollIntoView({
            block: 'nearest'
        });
    }
}

// 按索引选择分支
function selectBranchByIndex(index) {
    const branchDropdown = document.getElementById('branchDropdown');
    const options = branchDropdown.querySelectorAll('.branch-option');
    
    if (index >= 0 && index < options.length) {
        selectBranch(options[index], index);
    }
}

// 选择分支
function selectBranch(optionElement, dataIndex = null) {
    const branchInput = document.getElementById('projectBranch');
    const branchName = optionElement.querySelector('.branch-name').textContent;
    
    // 如果没有提供数据索引，从元素属性中获取
    if (dataIndex === null) {
        dataIndex = parseInt(optionElement.getAttribute('data-index'));
    }
    
    // 从当前过滤的分支中找到对应的完整数据
    let filteredBranches = branchData;
    const query = branchInput.value.trim();
    if (query) {
        filteredBranches = branchData.filter(branch => 
            branch.name.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 20);
    } else {
        filteredBranches = branchData.slice(0, 20);
    }
    
    // 保存选中分支的完整数据
    if (dataIndex >= 0 && dataIndex < filteredBranches.length) {
        selectedBranchData = filteredBranches[dataIndex];
        console.log('选中分支:', selectedBranchData);
    }
    
    branchInput.value = branchName;
    hideBranchDropdown();
}

// 隐藏分支下拉框
function hideBranchDropdown() {
    const branchDropdown = document.getElementById('branchDropdown');
    branchDropdown.classList.remove('show');
    selectedBranchIndex = -1;
}

// 系统基线选择器相关变量
let baselineData = [];
let selectedBaselineIndex = -1;
let selectedBaselineData = null;

// 初始化系统基线选择器
function initBaselineSelector() {
    const baselineInput = document.getElementById('systemBaseline');
    const baselineDropdown = document.getElementById('baselineDropdown');
    
    if (!baselineInput || !baselineDropdown) return;
    
    // 加载基线数据
    loadBaselineData();

    // 输入框事件
    baselineInput.addEventListener('input', function() {
        const query = this.value.trim();
        filterAndShowBaselines(query);
        updateFetchBaselineBtnState();
    });
    
    baselineInput.addEventListener('change', function() {
        updateFetchBaselineBtnState();
    });
    
    baselineInput.addEventListener('focus', function() {
        const query = this.value.trim();
        filterAndShowBaselines(query);
    });
    
    baselineInput.addEventListener('keydown', function(e) {
        const options = baselineDropdown.querySelectorAll('.branch-option');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedBaselineIndex = Math.min(selectedBaselineIndex + 1, options.length - 1);
            updateBaselineSelection();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedBaselineIndex = Math.max(selectedBaselineIndex - 1, -1);
            updateBaselineSelection();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedBaselineIndex >= 0 && selectedBaselineIndex < options.length) {
                selectBaseline(options[selectedBaselineIndex]);
            }
        } else if (e.key === 'Escape') {
            hideBaselineDropdown();
        }
    });
    
    // 点击外部关闭下拉框
    document.addEventListener('click', function(e) {
        if (!baselineInput.contains(e.target) && !baselineDropdown.contains(e.target)) {
            hideBaselineDropdown();
        }
    });
}

// 加载基线数据
async function loadBaselineData() {
    try {
        const response = await fetch('/api/crp-sync?action=list');
        const result = await response.json();
        
        if (result.success) {
            baselineData = result.data;
        } else {
            console.error('加载基线数据失败:', result.message);
        }
    } catch (error) {
        console.error('加载基线数据异常:', error);
    }
}

// 过滤并显示基线
function filterAndShowBaselines(query) {
    const baselineDropdown = document.getElementById('baselineDropdown');
    selectedBaselineIndex = -1;
    
    if (!query) {
        // 显示所有基线（限制前20个）
        const limitedBaselines = baselineData.slice(0, 20);
        renderBaselineOptions(limitedBaselines);
    } else {
        // 按名称过滤基线
        const filtered = baselineData.filter(baseline => 
            baseline.name.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 20);
        renderBaselineOptions(filtered);
    }
    
    baselineDropdown.classList.add('show');
}

// 渲染基线选项
function renderBaselineOptions(baselines) {
    const baselineDropdown = document.getElementById('baselineDropdown');
    
    if (baselines.length === 0) {
        baselineDropdown.innerHTML = '<div class="branch-no-results">未找到匹配的基线</div>';
        return;
    }
    
    baselineDropdown.innerHTML = baselines.map((baseline, index) => `
        <div class="branch-option" data-index="${index}" onclick="selectBaselineByIndex(${index})">
            <div class="branch-name">${baseline.name}</div>
            ${baseline.description ? `<div class="branch-description">${baseline.description}</div>` : ''}
        </div>
    `).join('');
}

// 更新基线选择状态
function updateBaselineSelection() {
    const options = document.querySelectorAll('#baselineDropdown .branch-option');
    options.forEach((option, index) => {
        option.classList.toggle('active', index === selectedBaselineIndex);
    });
    
    // 滚动到选中项
    if (selectedBaselineIndex >= 0 && selectedBaselineIndex < options.length) {
        options[selectedBaselineIndex].scrollIntoView({
            block: 'nearest'
        });
    }
}

// 按索引选择基线
function selectBaselineByIndex(index) {
    const baselineDropdown = document.getElementById('baselineDropdown');
    const options = baselineDropdown.querySelectorAll('.branch-option');
    
    if (index >= 0 && index < options.length) {
        selectBaseline(options[index], index);
    }
}

// 选择基线
function selectBaseline(optionElement, dataIndex = null) {
    const baselineInput = document.getElementById('systemBaseline');
    const baselineName = optionElement.querySelector('.branch-name').textContent;
    
    // 如果没有提供数据索引，从元素属性中获取
    if (dataIndex === null) {
        dataIndex = parseInt(optionElement.getAttribute('data-index'));
    }
    
    // 从当前过滤的基线中找到对应的完整数据
    let filteredBaselines = baselineData;
    const query = baselineInput.value.trim();
    if (query) {
        filteredBaselines = baselineData.filter(baseline => 
            baseline.name.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 20);
    } else {
        filteredBaselines = baselineData.slice(0, 20);
    }
    
    // 保存选中基线的完整数据
    if (dataIndex >= 0 && dataIndex < filteredBaselines.length) {
        selectedBaselineData = filteredBaselines[dataIndex];
        console.log('选中基线:', selectedBaselineData);
    }
    
    baselineInput.value = baselineName;
    hideBaselineDropdown();
    
    // 更新获取基线版本按钮状态
    updateFetchBaselineBtnState();
}

// 隐藏基线下拉框
function hideBaselineDropdown() {
    const baselineDropdown = document.getElementById('baselineDropdown');
    baselineDropdown.classList.remove('show');
    selectedBaselineIndex = -1;
}

// 更新获取基线版本按钮状态
function updateFetchBaselineBtnState() {
    const btn = document.getElementById('fetchBaselineVersionBtn');
    const baselineInput = document.getElementById('systemBaseline');
    if (btn && baselineInput) {
        btn.disabled = !baselineInput.value.trim();
    }
}

// 获取基线版本
async function fetchBaselineVersions() {
    const baselineInput = document.getElementById('systemBaseline');
    const baselineName = baselineInput.value.trim();
    
    if (!baselineName) {
        alert('请先选择系统基线');
        return;
    }
    
    const btn = document.getElementById('fetchBaselineVersionBtn');
    btn.disabled = true;
    btn.textContent = '⏳ 获取中...';
    
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'fetch_baseline_versions',
                project_id: currentProjectId,
                baseline_name: baselineName,
                append: document.getElementById('appendBaseline').checked
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            // 重新加载模块列表
            loadProjectModulesWithFilter(currentProjectId, 'all');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('获取基线版本失败:', error);
        alert('获取基线版本失败: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.textContent = '📦 获取基线版本';
        updateFetchBaselineBtnState();
    }
}

// 工具函数
function getPriorityText(priority) {
    const priorityMap = {
        'low': '低',
        'medium': '中',
        'high': '高',
        'critical': '紧急'
    };
    return priorityMap[priority] || priority;
}

function getStatusText(status) {
    const statusMap = {
        'pending': '待处理',
        'in_progress': '进行中',
        'completed': '已完成',
        'cancelled': '已取消'
    };
    return statusMap[status] || status;
}

function getWorkflowStatusText(status) {
    const statusMap = {
        'test': '测试主题',
        'release': '发布主题',
        'delete' : '废弃'
    };
    return statusMap[status] || status;
}

function getBuildStatusText(status) {
    const statusMap = {
        'pending': '待构建',
        'building': '构建中',
        'BUILDING': '构建中',
        'success': '成功',
        'UPLOAD_OK': '上传成功',
        'UPLOAD_CRP': '上传CRP',
        'failed': '失败',
        'FAILED': '失败',
        'cancelled': '已取消',
        'CANCELLED': '已取消',
        'abandoned': '已放弃'
    };
    return statusMap[status] || status || '未知';
}

function getModuleStatusText(status) {
    const statusMap = {
        '有更新': '有更新',
        '已更新': '已更新',
        '未知': '未知'
    };
    return statusMap[status] || status || '未知';
}

// 全局变量存储当前模块数据和排序状态
let currentModulesData = [];
let allAvailableModules = []; // 存储所有可用模块
let selectedModulesToAdd = []; // 存储要添加的模块
let currentSortField = 'build_id';
let currentSortDirection = 'desc';

// 显示添加模块模态框
async function showAddModulesModal() {
    try {
        // 加载所有可用模块
        await loadAllAvailableModules(currentProjectId);
        
        // 渲染模块列表
        renderAllModulesList();
        
        // 显示模态框
        document.getElementById('addModulesModal').style.display = 'block';
    } catch (error) {
        console.error('显示添加模块模态框失败：', error);
        alert('加载模块列表失败，请检查网络连接');
    }
}

// 关闭添加模块模态框
function closeAddModulesModal() {
    document.getElementById('addModulesModal').style.display = 'none';
    document.getElementById('moduleSearchInput').value = '';
    selectedModulesToAdd = [];
}

// 过滤模块列表
function filterModules() {
    const searchText = document.getElementById('moduleSearchInput').value.toLowerCase();
    const moduleItems = document.querySelectorAll('#allModulesList .batch-module-item');
    
    moduleItems.forEach(item => {
        const moduleName = item.querySelector('.batch-module-name').textContent.toLowerCase();
        const categoryName = item.querySelector('.category-name').textContent.toLowerCase();
        
        if (moduleName.includes(searchText)) {
            item.style.display = 'flex';
        } else if(categoryName.includes(searchText)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// 渲染所有模块列表
function renderAllModulesList() {
    const container = document.getElementById('allModulesList');
    
    if (allAvailableModules.length === 0) {
        container.innerHTML = '<div class="empty-state">没有可用的模块</div>';
        return;
    }
    
    // 获取当前项目已有的模块ID
    const currentModuleIds = currentModulesData.map(m => m.id);
    
    const modulesHTML = allAvailableModules.map(module => {
        // 如果模块已经在项目中，则禁用选择
        const isDisabled = currentModuleIds.includes(module.id);
        
        return `
            <div class="batch-module-item" data-module-id="${module.id}" style="${isDisabled ? 'opacity: 0.6;' : ''}">
                <input type="checkbox" class="batch-module-checkbox" 
                       value="${module.id}" 
                       onchange="updateSelectedModules(this, ${module.id})"
                       ${isDisabled ? 'disabled' : ''}>
                <div class="batch-module-info">
                    <div class = "batch-module-name" style="min-width: 200px">${module.alias ? `${module.name}（${module.alias}）` : module.name}</div>
                    <div class = "category-name" >${module.category_name || '未设置'}</div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = modulesHTML;
}

// 更新选中的模块
function updateSelectedModules(checkbox, moduleId) {
    if (checkbox.checked) {
        if (!selectedModulesToAdd.includes(moduleId)) {
            selectedModulesToAdd.push(moduleId);
        }
    } else {
        selectedModulesToAdd = selectedModulesToAdd.filter(id => id !== moduleId);
    }
}

// 确认添加模块
async function confirmAddModules() {
    if (selectedModulesToAdd.length === 0) {
        alert('请先选择要添加的模块');
        return;
    }
    
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_modules',
                project_id: currentProjectId,
                module_ids: selectedModulesToAdd
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            notify(`成功添加 ${selectedModulesToAdd.length} 个模块到项目`, 'success');
            closeAddModulesModal();
            
            // 重新加载模块列表
            const currentFilter = document.getElementById('moduleFilterSelect').value;
            loadProjectModulesWithFilter(currentProjectId, currentFilter);
        } else {
            alert('添加模块失败：' + result.message);
        }
    } catch (error) {
        console.error('添加模块失败：', error);
        alert('添加模块失败，请检查网络连接');
    }
}

// 排序模块
function sortModules(field) {
    // 如果点击的是当前排序字段，则切换排序方向
    if (currentSortField === field) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortField = field;
        currentSortDirection = 'asc';
    }
    
    // 排序数据
    currentModulesData.sort((a, b) => {
        let valueA = a[field] || '';
        let valueB = b[field] || '';
        
        // 对于数字类型的字段
        if (['build_id'].includes(field)) {
            valueA = parseInt(valueA) || 0;
            valueB = parseInt(valueB) || 0;
        } else {
            // 字符串类型转换为小写进行比较
            valueA = String(valueA).toLowerCase();
            valueB = String(valueB).toLowerCase();
        }
        
        if (valueA < valueB) {
            return currentSortDirection === 'asc' ? -1 : 1;
        } else if (valueA > valueB) {
            return currentSortDirection === 'asc' ? 1 : -1;
        }
        return 0;
    });
    
    // 重新渲染表格
    renderModulesList(currentModulesData);
    
    // 更新排序图标
    updateSortIcons();
}

// 更新排序图标
function updateSortIcons() {
    // 清除所有排序类
    document.querySelectorAll('.sortable').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });
    
    // 添加当前排序字段的类
    const currentTh = document.querySelector(`[onclick="sortModules('${currentSortField}')"]`);
    if (currentTh) {
        currentTh.classList.add(currentSortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
    }
}

// 保存分支修改
async function saveBranchChange(moduleId, newBranch) {
    const originalBranch = currentModulesData.find(m => m.id == moduleId)?.project_branch || '';
    
    // 如果没有变化，直接返回
    if (newBranch === originalBranch) {
        return;
    }
    
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_module_branch',
                module_id: moduleId,
                branch: newBranch
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 更新本地数据
            const moduleIndex = currentModulesData.findIndex(m => m.id == moduleId);
            if (moduleIndex !== -1) {
                currentModulesData[moduleIndex].project_branch = newBranch;
            }
            
            // 显示成功提示
            notify('分支修改成功', 'success');
        } else {
            // 恢复原值
            const input = document.querySelector(`tr[data-module-id="${moduleId}"] .branch-input`);
            if (input) {
                input.value = originalBranch;
            }
            notify('分支修改失败: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('保存分支修改失败：', error);
        // 恢复原值
        const input = document.querySelector(`tr[data-module-id="${moduleId}"] .branch-input`);
        if (input) {
            input.value = originalBranch;
        }
        notify('分支修改失败，请检查网络连接', 'error');
    }
}

// 保存系统版本修改
async function saveVersionChange(moduleId, newVersion) {
    const originalVersion = currentModulesData.find(m => m.id == moduleId)?.project_version || '';
    
    // 如果没有变化，直接返回
    if (newVersion === originalVersion) {
        return;
    }
    
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_module_version',
                module_id: moduleId,
                version: newVersion
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 更新本地数据
            const moduleIndex = currentModulesData.findIndex(m => m.id == moduleId);
            if (moduleIndex !== -1) {
                currentModulesData[moduleIndex].project_version = newVersion;
            }
            
            // 显示成功提示
            notify('系统版本修改成功', 'success');
        } else {
            // 恢复原值
            const input = document.querySelector(`tr[data-module-id="${moduleId}"] .version-input`);
            if (input) {
                input.value = originalVersion;
            }
            notify('系统版本修改失败: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('保存系统版本修改失败：', error);
        // 恢复原值
        const input = document.querySelector(`tr[data-module-id="${moduleId}"] .version-input`);
        if (input) {
            input.value = originalVersion;
        }
        notify('系统版本修改失败，请检查网络连接', 'error');
    }
}

// 保存打包提交修改
async function saveCommitChange(moduleId, newCommit) {
    const originalCommit = currentModulesData.find(m => m.id == moduleId)?.build_commit || '';
    
    // 如果没有变化，直接返回
    if (newCommit === originalCommit) {
        return;
    }
    
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_module_commit',
                module_id: moduleId,
                commit: newCommit
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 更新本地数据
            const moduleIndex = currentModulesData.findIndex(m => m.id == moduleId);
            if (moduleIndex !== -1) {
                currentModulesData[moduleIndex].build_commit = newCommit;
                
                // 如果返回了新状态，同时更新状态
                if (result.data && result.data.new_status) {
                    currentModulesData[moduleIndex].project_module_status = result.data.new_status;
                    
                    // 更新页面上的状态显示
                    const statusElement = document.querySelector(`tr[data-module-id="${moduleId}"] .module-status`);
                    if (statusElement) {
                        statusElement.className = `module-status ${result.data.new_status}`;
                        statusElement.textContent = getModuleStatusText(result.data.new_status);
                    }
                }
            }
            
            // 显示成功提示
            notify('打包提交修改成功', 'success');
        } else {
            // 恢复原值
            const input = document.querySelector(`tr[data-module-id="${moduleId}"] .commit-input`);
            if (input) {
                input.value = originalCommit;
            }
            notify('打包提交修改失败: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('保存打包提交修改失败：', error);
        // 恢复原值
        const input = document.querySelector(`tr[data-module-id="${moduleId}"] .commit-input`);
        if (input) {
            input.value = originalCommit;
        }
        notify('打包提交修改失败，请检查网络连接', 'error');
    }
}

// tag信息列已设置为只读，不再需要编辑功能

// 创建tag函数
function createTag(moduleId) {
    if (!moduleId) {
        notify('模块ID不能为空', 'error');
        return;
    }
    
    // 显示加载状态
    notify('正在创建tag...', 'info');
    
    // 调用后端API创建tag
    fetch('/api/projects', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'create_tag',
            module_id: moduleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notify('Tag创建成功: ' + data.data.tag_name, 'success');
            // 可以在这里添加刷新页面或更新UI的逻辑
        } else {
            notify('Tag创建失败: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('创建tag时发生错误:', error);
        notify('创建tag时发生网络错误', 'error');
    });
}

// 通知函数已从 notification.js 引入

// 显示新增交付记录表单
function addNewDeliveryRow() {
    const newForm = document.getElementById('newDeliveryForm');
    if (newForm) {
        newForm.classList.add('active');
        // 聚焦到第一个输入框
        document.getElementById('newDeliveryTopicName').focus();
    }
}

// 取消新增交付记录
function cancelNewDeliveryRow() {
    const newForm = document.getElementById('newDeliveryForm');
    if (newForm) {
        newForm.classList.remove('active');
        // 清空表单
        document.getElementById('newDeliveryTopicId').value = '';
        document.getElementById('newDeliveryTopicName').value = '';
    }
}

// 驳回CRP主题
async function rejectCrpTopic(deliveryId, topicId) {
    // 弹出确认对话框并获取驳回原因
    const rejectReason = prompt('请输入驳回原因：', '');
    
    if (rejectReason === null) {
        // 用户点击了取消
        return;
    }
    
    if (!rejectReason.trim()) {
        notify('驳回原因不能为空', 'warning');
        return;
    }
    
    try {
        // 调用后端API驳回主题
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'crp_reject',
                delivery_id: deliveryId,
                topic_id: topicId,
                msg: rejectReason.trim()
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            notify('主题驳回成功！', 'success');
            
            // 重新加载交付记录列表以显示更新后的锁定状态
            loadProjectDeliveries(currentProjectId);
            
            // 重新加载模块列表
            const currentFilter = document.getElementById('moduleFilterSelect').value;
            loadProjectModulesWithFilter(currentProjectId, currentFilter);
        } else {
            notify('驳回主题失败：' + result.message, 'error');
        }
    } catch (error) {
        console.error('驳回主题失败：', error);
        notify('驳回主题失败，请检查网络连接', 'error');
    }
}

// 同步交付记录CRP主题信息
// 正在同步中的交付记录ID，防止连续点击重复触发
const syncingDeliveryIds = new Set();

async function syncDeliveryCrpTopic(deliveryId, btn) {
    // 已在下发中：提示并忽略本次点击
    if (syncingDeliveryIds.has(deliveryId)) {
        notify('该交付记录正在同步中，请勿重复点击', 'warning');
        return;
    }
    syncingDeliveryIds.add(deliveryId);

    const originalHTML = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.classList.add('is-syncing');
        btn.innerHTML = '<span class="sync-spinner"></span> 同步中...';
    }
    notify('正在同步交付记录，请稍候...', 'info');

    try {
        // 调用crp_releases接口同步数据，使用项目的crp_topicid
        const response = await fetch(`/api/projects?action=crp_releases&project_id=${currentProjectId}&delivery_id=${deliveryId}`);
        const result = await response.json();
        
        if (result.success) {
            const summary = result.data.sync_summary;
            let message = `✅ 交付记录同步成功！\n\n同步详情：\n- 处理模块：${summary.processed}个\n- 更新模块：${summary.updated}个\n- 插入模块：${summary.inserted}个`;
            if (summary.cleared > 0) {
                message += `\n- 清理过期模块：${summary.cleared}个`;
            }
            alert(message);
            // 重新加载交付记录列表以显示更新后的信息（包括主题名称）
            loadProjectDeliveries(currentProjectId);
            
            // 重新加载模块列表以显示最新状态
            const currentFilter = document.getElementById('moduleFilterSelect').value;
            loadProjectModulesWithFilter(currentProjectId, currentFilter);
        } else {
            alert('同步交付记录失败：' + result.message);
        }
    } catch (error) {
        console.error('同步交付记录失败：', error);
        alert('同步交付记录失败，请检查网络连接');
    } finally {
        // 无论成功失败都恢复按钮状态，避免一直卡在“同步中”
        syncingDeliveryIds.delete(deliveryId);
        if (btn && btn.isConnected) {
            btn.disabled = false;
            btn.classList.remove('is-syncing');
            btn.innerHTML = originalHTML;
        }
    }
}

// 创建CRP主题
async function createCrpTopic(topicName, projectId) {
    try {
        // 调用后端代理API创建主题
        const response = await fetch('/api/crp-proxy', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'create_topic',
                project_id: projectId,
                topic_name: topicName
            })
        });
        
        if (!response.ok) {
            throw new Error(`API调用失败: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success && result.data && result.data.topic_id) {
            return result.data.topic_id; // 返回topicid
        } else {
            throw new Error(result.message || 'API返回数据格式错误');
        }
        
    } catch (error) {
        console.error('创建CRP主题失败：', error);
        throw error;
    }
}

// 保存新增交付记录
async function saveNewDeliveryRow() {
    const topicId = document.getElementById('newDeliveryTopicId').value.trim();
    const topicName = document.getElementById('newDeliveryTopicName').value.trim();
    
    console.log('saveNewDeliveryRow - topicId:', topicId, 'topicName:', topicName);
    
    if (!topicName) {
        alert('请输入主题名称');
        document.getElementById('newDeliveryTopicName').focus();
        return;
    }
    
    let finalTopicId = topicId;
    
    // 如果主题ID为空但主题名称不为空，询问是否创建新主题
    if (!topicId && topicName) {
        const createTopic = confirm(`主题ID为空，是否创建新的主题"${topicName}"？`);
        if (createTopic) {
            try {
                // 显示创建中状态
                const submitBtn = document.querySelector('#newDeliveryForm .btn-primary');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = '正在创建主题...';
                
                finalTopicId = await createCrpTopic(topicName, currentProjectId);
                console.log('主题创建成功，ID:', finalTopicId);
                
                // 恢复按钮状态
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                
                // 将创建的主题ID填入输入框
                document.getElementById('newDeliveryTopicId').value = finalTopicId;
                
                alert(`主题创建成功！主题ID: ${finalTopicId}`);
                
            } catch (error) {
                // 恢复按钮状态
                const submitBtn = document.querySelector('#newDeliveryForm .btn-primary');
                submitBtn.disabled = false;
                submitBtn.textContent = '保存';
                
                const continueWithoutTopic = confirm(`创建主题失败: ${error.message}\n\n是否继续添加交付记录（不设置主题ID）？`);
                if (!continueWithoutTopic) {
                    return;
                }
                // 如果用户选择继续，finalTopicId保持为空
                finalTopicId = '';
            }
        } else {
            // 用户取消创建主题
            return;
        }
    }
    
    // 最终检查：确保主题名称不为空
    if (!topicName) {
        alert('错误：主题名称不能为空');
        return;
    }
    
    console.log('发送API请求 - finalTopicId:', finalTopicId, 'topicName:', topicName);
    
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_delivery',
                project_id: currentProjectId,
                topic_id: finalTopicId,
                topic_name: topicName,
                delivery_date: new Date().toISOString().split('T')[0],
                repository_url: '',
                description: ''
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 成功后隐藏表单并重新加载数据
            cancelNewDeliveryRow();
            loadProjectDeliveries(currentProjectId);
        } else {
            alert('添加交付记录失败：' + result.message);
        }
    } catch (error) {
        console.error('添加交付记录失败：', error);
        alert('添加交付记录失败，请检查网络连接');
    }
}

// 编辑交付记录
async function editDeliveryRecord(deliveryId) {
    try {
        // 获取交付记录详情
        const response = await fetch(`/api/projects?action=delivery_detail&delivery_id=${deliveryId}`);
        const result = await response.json();
        
        if (result.success) {
            const delivery = result.data;
            
            // 填充编辑表单
            document.getElementById('editDeliveryCrpId').value = delivery.crp_id || '';
            document.getElementById('editDeliveryCrpName').value = delivery.crp_name || '';
            
            // 保存原始值，用于后续判断是否仅修改了主题名称
            originalDeliveryCrpId = delivery.crp_id || '';
            originalDeliveryCrpName = delivery.crp_name || '';
            
            // 保存当前编辑的记录ID
            window.currentEditDeliveryId = deliveryId;
            
            // 显示编辑模态框
            document.getElementById('editDeliveryModal').style.display = 'block';
        } else {
            alert('获取交付记录详情失败：' + result.message);
        }
    } catch (error) {
        console.error('获取交付记录详情失败：', error);
        alert('获取交付记录详情失败，请检查网络连接');
    }
}

// 关闭编辑模态框
function closeEditDeliveryModal() {
    document.getElementById('editDeliveryModal').style.display = 'none';
    window.currentEditDeliveryId = null;
}

// 删除交付记录
async function deleteDeliveryRecord() {
    if (!window.currentEditDeliveryId) {
        alert('未找到要删除的交付记录');
        return;
    }
    
    const confirmDelete = confirm('确定要删除这个交付记录吗？\n\n删除后将同时删除：\n- 交付记录\n- 关联的打包信息\n- 关联的交付PMS信息\n\n此操作不可恢复！');
    
    if (!confirmDelete) {
        return;
    }
    
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_delivery',
                delivery_id: window.currentEditDeliveryId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('交付记录删除成功！');
            // 关闭模态框并重新加载数据
            closeEditDeliveryModal();
            loadProjectDeliveries(currentProjectId);
        } else {
            alert('删除交付记录失败：' + result.message);
        }
    } catch (error) {
        console.error('删除交付记录失败：', error);
        alert('删除交付记录失败，请检查网络连接');
    }
}

// 保存编辑的交付记录
async function saveEditDeliveryRecord() {
    const crpId = document.getElementById('editDeliveryCrpId').value.trim();
    const crpName = document.getElementById('editDeliveryCrpName').value.trim();
    
    if (!crpId) {
        alert('请输入主题ID');
        document.getElementById('editDeliveryCrpId').focus();
        return;
    }
    
    // 判断是否仅修改了主题名称，且主题ID不为空
    const crpIdChanged = crpId !== originalDeliveryCrpId;
    const crpNameChanged = crpName !== originalDeliveryCrpName;
    const onlyNameChanged = !crpIdChanged && crpNameChanged && crpId !== '';
    
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_delivery',
                delivery_id: window.currentEditDeliveryId,
                crp_id: crpId,
                crp_name: crpName,
                only_name_changed: onlyNameChanged
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 成功后关闭模态框并重新加载数据
            closeEditDeliveryModal();
            loadProjectDeliveries(currentProjectId);
            
            // 如果同步了CRP主题名称，显示提示信息
            if (result.crp_sync_result) {
                if (result.crp_sync_result.success) {
                    notify('交付记录更新成功，CRP主题名称已同步更新');
                } else {
                    alert('交付记录更新成功，但CRP主题名称同步失败：' + result.crp_sync_result.message);
                }
            }
        } else {
            alert('更新交付记录失败：' + result.message);
        }
    } catch (error) {
        console.error('更新交付记录失败：', error);
        alert('更新交付记录失败，请检查网络连接');
    }
}

// 显示打包信息（模态框方式）
async function showPackageInfo(deliveryId) {
    try {
        const response = await fetch(`/api/projects?action=delivery_package_info&delivery_id=${deliveryId}`);
        const result = await response.json();
        
        if (result.success) {
            const packageInfo = result.data;
            renderPackageInfo(packageInfo);
            document.getElementById('packageInfoModal').style.display = 'block';
            // 禁用背景页面滚动
            document.body.style.overflow = 'hidden';
        } else {
            alert('获取打包信息失败：' + result.message);
        }
    } catch (error) {
        console.error('获取打包信息失败：', error);
        alert('获取打包信息失败，请检查网络连接');
    }
}

// 加载并显示打包信息（内联方式）
async function loadAndShowPackageInfo(deliveryId) {
    try {
        const packageContainer = document.getElementById(`delivery-package-${deliveryId}`);
        const placeholder = packageContainer.querySelector('.package-info-placeholder');
        
        const response = await fetch(`/api/projects?action=delivery_package_info&delivery_id=${deliveryId}`);
        const result = await response.json();
        
        if (result.success) {
            const packageInfo = result.data;
            renderInlinePackageInfo(deliveryId, packageInfo);
        } else {
            placeholder.innerHTML = `<div class="error-message">❌ 获取打包信息失败：${result.message}</div>`;
        }
    } catch (error) {
        console.error('获取打包信息失败：', error);
        const packageContainer = document.getElementById(`delivery-package-${deliveryId}`);
        const placeholder = packageContainer.querySelector('.package-info-placeholder');
        placeholder.innerHTML = '<div class="error-message">❌ 获取打包信息失败，请检查网络连接</div>';
    }
}

// 渲染内联打包信息
function renderInlinePackageInfo(deliveryId, packageInfo) {
    const packageContainer = document.getElementById(`delivery-package-${deliveryId}`);
    
    if (packageInfo.length === 0) {
        packageContainer.innerHTML = `
            <div class="package-info-content">
                <p><strong>暂无打包信息</strong></p>
            </div>
        `;
        return;
    }
    
    const tableHTML = `
        <div class="package-info-content">
            <div class="table-container inline-table-container">
                <table class="package-info-table inline-package-table">
                    <thead>
                        <tr>
                            <th>模块名称（共${packageInfo.length}个模块）</th>
                            <th>打包分支</th>
                            <th>基础版本</th>
                            <th>基础提交</th>
                            <th>打包版本</th>
                            <th>打包提交</th>
                            <th>打包状态</th>
                            <th>查看</th>
                            <th>放弃</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${packageInfo.map(pkg => `
                            <tr data-package-id="${pkg.id}">
                                <td title="${pkg.module_name || pkg.build_module_name || '-'}">${pkg.alias ? `${pkg.module_name || pkg.build_module_name || '-'}（${pkg.alias}）` : pkg.module_name || pkg.build_module_name || '-'}</td>
                                <td title="${pkg.build_branch || '-'}">${pkg.build_branch || '-'}</td>
                                <td class="editable-field">
                                    <div style="display: flex; align-items: center; flex-wrap: nowrap;">
                                        <span class="field-display" onclick="editPackageField(${pkg.id}, 'base_tag', this)" style="flex-shrink: 1; overflow: hidden; text-overflow: ellipsis;">${pkg.base_tag || '-'}</span>
                                        <input class="field-input" style="display:none" value="${pkg.base_tag || ''}" onblur="savePackageField(${pkg.id}, 'base_tag', this)" onkeypress="handlePackageFieldKeyPress(event, ${pkg.id}, 'base_tag', this)">
                                    </div>
                                </td>
                                <td class="editable-field">
                                    <div style="display: flex; align-items: center; flex-wrap: nowrap;">
                                        <span class="field-display" onclick="editPackageField(${pkg.id}, 'base_commit', this)" style="flex-shrink: 1; overflow: hidden; text-overflow: ellipsis;">${pkg.base_commit || '-'}</span>
                                        <input class="field-input" style="display:none" value="${pkg.base_commit || ''}" onblur="savePackageField(${pkg.id}, 'base_commit', this)" onkeypress="handlePackageFieldKeyPress(event, ${pkg.id}, 'base_commit', this)">
                                        ${pkg.base_commit_url ? `<a href="${pkg.base_commit_url}" target="_blank" class="commit-link-btn" title="查看基础提交">🔗</a>` : ''}
                                    </div>
                                </td>
                                <td title="${pkg.cur_tag || '-'}">${pkg.cur_tag || '-'}</td>
                                <td>
                                    <div style="display: flex; align-items: center; flex-wrap: nowrap;">
                                        <span style="flex-shrink: 1; overflow: hidden; text-overflow: ellipsis;">${pkg.build_commit || '-'}</span>
                                        ${pkg.build_commit_url ? `<a href="${pkg.build_commit_url}" target="_blank" class="commit-link-btn" title="查看构建提交">🔗</a>` : ''}
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <span class="build-status ${pkg.build_state}">${getBuildStatusText(pkg.build_state)}</span>
                                        ${(pkg.build_itemid && pkg.build_state !== '排队中' && pkg.build_state !== '已放弃') ? `<button class="refresh-icon-btn" onclick="refreshBuildStatus('${pkg.build_id}', ${pkg.id}, ${pkg.build_itemid})" title="刷新构建信息">🔄</button>` : ''}
                                    </div>
                                </td>
                                <td>
                                    ${pkg.build_id ? `<a href="${window.APP_ENDPOINTS.shuttle}/#/tasks/task?taskid=${pkg.build_id}" target="_blank" class="btn btn-sm btn-primary">查看</a>` : '-'}
                                </td>
                                <td>
                                    ${pkg.build_itemid ? `<button class="btn btn-sm btn-danger" onclick="abandonBuild('${pkg.build_itemid}', ${pkg.id})" title="放弃构建">放弃</button>` : '-'}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    packageContainer.innerHTML = tableHTML;
}

// 重置打包信息显示
function resetPackageInfo(deliveryId) {
    const packageContainer = document.getElementById(`delivery-package-${deliveryId}`);
    packageContainer.innerHTML = `
        <div class="package-info-placeholder">
            <button class="btn btn-sm btn-info" onclick="loadAndShowPackageInfo(${deliveryId})">
                📦 点击查看打包信息
            </button>
        </div>
    `;
}

// 渲染打包信息
function renderPackageInfo(packageInfo) {
    const content = document.getElementById('packageInfoContent');
    
    if (packageInfo.length === 0) {
        content.innerHTML = '<p>暂无打包信息</p>';
        return;
    }
    
    const tableHTML = `
        <div class="table-container">
            <table class="package-info-table">
                <thead>
                                            <tr>
                            <th>模块名称</th>
                            <th>打包分支</th>
                            <th>基础版本</th>
                            <th>基础提交</th>
                            <th>构建版本</th>
                            <th>构建提交</th>
                            <th>构建状态</th>
                            <th>查看</th>
                            <th>放弃</th>
                        </tr>
                </thead>
                <tbody>
                    ${packageInfo.map(pkg => `
                        <tr data-package-id="${pkg.id}">
                            <td title="${pkg.module_name || pkg.build_module_name || '-'}">${pkg.module_name || pkg.build_module_name || '-'}</td>
                            <td title="${pkg.build_branch || '-'}">${pkg.build_branch || '-'}</td>
                            <td class="editable-field">
                                <div style="display: flex; align-items: center; flex-wrap: nowrap;">
                                    <span class="field-display" onclick="editPackageField(${pkg.id}, 'base_tag', this)" style="flex-shrink: 1; overflow: hidden; text-overflow: ellipsis;">${pkg.base_tag || '-'}</span>
                                    <input class="field-input" style="display:none" value="${pkg.base_tag || ''}" onblur="savePackageField(${pkg.id}, 'base_tag', this)" onkeypress="handlePackageFieldKeyPress(event, ${pkg.id}, 'base_tag', this)">
                                </div>
                            </td>
                            <td class="editable-field">
                                <div style="display: flex; align-items: center; flex-wrap: nowrap;">
                                    <span class="field-display" onclick="editPackageField(${pkg.id}, 'base_commit', this)" style="flex-shrink: 1; overflow: hidden; text-overflow: ellipsis;">${pkg.base_commit || '-'}</span>
                                    <input class="field-input" style="display:none" value="${pkg.base_commit || ''}" onblur="savePackageField(${pkg.id}, 'base_commit', this)" onkeypress="handlePackageFieldKeyPress(event, ${pkg.id}, 'base_commit', this)">
                                    ${pkg.base_commit_url ? `<a href="${pkg.base_commit_url}" target="_blank" class="commit-link-btn" title="查看基础提交">🔗</a>` : ''}
                                </div>
                            </td>
                            <td title="${pkg.cur_tag || '-'}">${pkg.cur_tag || '-'}</td>
                            <td>
                                <!-- 构建提交字段 - 设为只读，以防以后需要编辑可取消下面注释 -->
                                <div style="display: flex; align-items: center; flex-wrap: nowrap;">
                                    <span style="flex-shrink: 1; overflow: hidden; text-overflow: ellipsis;">${pkg.build_commit || '-'}</span>
                                    ${pkg.build_commit_url ? `<a href="${pkg.build_commit_url}" target="_blank" class="commit-link-btn" title="查看构建提交">🔗</a>` : ''}
                                </div>
                                <!-- <span class="field-display" onclick="editPackageField(${pkg.id}, 'build_commit', this)">${pkg.build_commit || '-'}</span>
                                <input class="field-input" style="display:none" value="${pkg.build_commit || ''}" onblur="savePackageField(${pkg.id}, 'build_commit', this)" onkeypress="handlePackageFieldKeyPress(event, ${pkg.id}, 'build_commit', this)"> -->
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="build-status ${pkg.build_state}">${getBuildStatusText(pkg.build_state)}</span>
                                    ${(pkg.build_itemid && pkg.build_state !== '排队中' && pkg.build_state !== '已放弃') ? `<button class="refresh-icon-btn" onclick="refreshBuildStatus('${pkg.build_id}', ${pkg.id}, ${pkg.build_itemid})" title="刷新构建信息">🔄</button>` : ''}
                                </div>
                            </td>
                            <td>
                                ${pkg.build_id ? `<a href="${window.APP_ENDPOINTS.shuttle}/#/tasks/task?taskid=${pkg.build_id}" target="_blank" class="btn btn-sm btn-primary">查看</a>` : '-'}
                            </td>
                            <td>
                                ${pkg.build_itemid ? `<button class="btn btn-sm btn-danger" onclick="abandonBuild('${pkg.build_itemid}', ${pkg.id})" title="放弃构建">放弃</button>` : '-'}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        
        <style>
        .package-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed; /* 固定表格布局 */
        }
        
        .package-info-table th,
        .package-info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word; /* 允许长单词换行 */
            overflow: hidden; /* 隐藏溢出内容 */
        }
        
        /* 设置各列的宽度 */
        .package-info-table th:nth-child(1), .package-info-table td:nth-child(1) { width: 12%; } /* 模块名称 */
        .package-info-table th:nth-child(2), .package-info-table td:nth-child(2) { width: 8%; }  /* 打包分支 */
        .package-info-table th:nth-child(3), .package-info-table td:nth-child(3) { width: 8%; }  /* 基础版本 */
        .package-info-table th:nth-child(4), .package-info-table td:nth-child(4) { width: 15%; } /* 基础提交 */
        .package-info-table th:nth-child(5), .package-info-table td:nth-child(5) { width: 8%; }  /* 构建版本 */
        .package-info-table th:nth-child(6), .package-info-table td:nth-child(6) { width: 15%; } /* 构建提交 */
        .package-info-table th:nth-child(7), .package-info-table td:nth-child(7) { width: 10%; text-align: center; } /* 构建状态 */
        .package-info-table th:nth-child(8), .package-info-table td:nth-child(8) { width: 8%; text-align: center; } /* 查看 */
        .package-info-table th:nth-child(9), .package-info-table td:nth-child(9) { width: 8%; text-align: center; } /* 放弃 */
        
        .package-info-table th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
        }
        
        .package-info-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .editable-field {
            cursor: pointer;
            position: relative;
        }
        
        .field-display:hover {
            background-color: #e8f4f8;
            border-radius: 3px;
            padding: 2px 4px;
        }
        
        .field-input {
            width: 100%;
            padding: 4px;
            border: 2px solid #007cba;
            border-radius: 3px;
            font-size: 13px;
        }
        
        .table-container {
            max-height: 60vh;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .commit-link-btn {
            display: inline-block;
            margin-left: 5px;
            text-decoration: none;
            font-size: 12px;
            color: #007cba;
            transition: all 0.2s ease;
            opacity: 0.7;
            white-space: nowrap; /* 不换行 */
            vertical-align: middle;
        }
        
        .commit-link-btn:hover {
            opacity: 1;
            color: #005a8b;
            transform: scale(1.1);
        }
        
        .commit-link-btn:active {
            transform: scale(0.95);
        }
        </style>
    `;
    
    content.innerHTML = tableHTML;
}

// 编辑打包字段
function editPackageField(packageId, fieldName, element) {
    const displaySpan = element;
    const inputField = displaySpan.nextElementSibling;
    
    // 隐藏显示元素，显示输入框
    displaySpan.style.display = 'none';
    inputField.style.display = 'block';
    inputField.focus();
    inputField.select();
}

// 保存打包字段
async function savePackageField(packageId, fieldName, inputElement) {
    const newValue = inputElement.value.trim();
    const displaySpan = inputElement.previousElementSibling;
    
    try {
        // 发送更新请求
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_package_field&package_id=${packageId}&field_name=${fieldName}&field_value=${encodeURIComponent(newValue)}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 更新显示文本
            displaySpan.textContent = newValue || '-';
            
            // 切换回显示模式
            inputElement.style.display = 'none';
            displaySpan.style.display = 'inline';
            
            // 显示成功提示
            notify('字段更新成功', 'success');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('保存字段失败：', error);
        notify('保存失败：' + error.message, 'error');
        
        // 恢复原值
        inputElement.value = displaySpan.textContent === '-' ? '' : displaySpan.textContent;
        
        // 切换回显示模式
        inputElement.style.display = 'none';
        displaySpan.style.display = 'inline';
    }
}

// 处理打包字段的键盘事件
function handlePackageFieldKeyPress(event, packageId, fieldName, inputElement) {
    if (event.key === 'Enter') {
        inputElement.blur(); // 触发onblur事件保存
    } else if (event.key === 'Escape') {
        // 取消编辑，恢复原值
        const displaySpan = inputElement.previousElementSibling;
        inputElement.value = displaySpan.textContent === '-' ? '' : displaySpan.textContent;
        inputElement.style.display = 'none';
        displaySpan.style.display = 'inline';
    }
}

// 刷新单个包信息行
async function refreshPackageRow(packageId) {
    try {
        const response = await fetch(`/api/projects?action=package_detail&package_id=${packageId}`);
        const result = await response.json();
        
        if (result.success) {
            const pkg = result.data;
            const row = document.querySelector(`tr[data-package-id="${packageId}"]`);
            
            if (row) {
                // 更新可编辑字段
                const baseTagSpan = row.querySelector('.editable-field:nth-of-type(1) .field-display');
                const baseCommitSpan = row.querySelector('.editable-field:nth-of-type(2) .field-display');
                const baseTagInput = row.querySelector('.editable-field:nth-of-type(1) .field-input');
                const baseCommitInput = row.querySelector('.editable-field:nth-of-type(2) .field-input');
                
                if (baseTagSpan && baseTagInput) {
                    baseTagSpan.textContent = pkg.base_tag || '-';
                    baseTagInput.value = pkg.base_tag || '';
                }
                
                if (baseCommitSpan && baseCommitInput) {
                    baseCommitSpan.textContent = pkg.base_commit || '-';
                    baseCommitInput.value = pkg.base_commit || '';
                }
            }
            
            notify('信息已刷新', 'success');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('刷新失败：', error);
        notify('刷新失败：' + error.message, 'error');
    }
}

// 刷新构建状态和相关信息
async function refreshBuildStatus(taskId, packageId, buildItemid) {
    if (!buildItemid) {
        notify('刷新失败：缺少必要参数', 'error');
        return;
    }
    
    try {
        // 显示加载状态 - 使用更精确的按钮查找方式
        const button = document.querySelector(`button[onclick*="refreshBuildStatus('${taskId}', ${packageId}, ${buildItemid})"][title="刷新构建信息"]`);
        if (button) {
            button.disabled = true;
            // 不改变按钮文本，保持原有的"刷新"
        }
        
        const response = await fetch(`/api/projects?action=item_detail&build_itemid=${buildItemid}&packageId=${packageId}`);
        const result = await response.json();
        
        if (result.success) {
            // 更新表格中的所有相关信息
            const row = document.querySelector(`tr[data-package-id="${packageId}"]`);
            if (row) {
                // 更新构建状态
                const statusCell = row.querySelector('.build-status');
                if (statusCell && result.data.build_state) {
                    // 移除旧的状态类
                    statusCell.className = statusCell.className.replace(/build-status-\w+/g, '');
                    // 添加新的状态类和文本
                    statusCell.className += ` build-status-${result.data.build_state}`;
                    statusCell.textContent = getBuildStatusText(result.data.build_state);
                }
                
                // 更新构建版本
                if (result.data.cur_tag) {
                    const versionCell = row.querySelector('td:nth-child(5)'); // 构建版本列
                    if (versionCell) {
                        versionCell.textContent = result.data.cur_tag;
                        versionCell.title = result.data.cur_tag;
                    }
                }
                
                // 更新构建提交
                if (result.data.build_commit) {
                    const commitCell = row.querySelector('td:nth-child(6)'); // 构建提交列
                    if (commitCell) {
                        const commitSpan = commitCell.querySelector('span');
                        if (commitSpan) {
                            commitSpan.textContent = result.data.build_commit;
                        }
                    }
                }
                
                // 更新查看链接（如果有build_id）
                if (result.data.build_id) {
                    // 查找包含"查看"链接的单元格 - 第8列
                    const viewCell = row.querySelector('td:nth-child(8)');
                    if (viewCell) {
                        const escapedBuildId = String(result.data.build_id).replace(/['"<>&]/g, c => ({'\'': '&#39;', '"': '&quot;', '<': '&lt;', '>': '&gt;', '&': '&amp;'}[c]));
                        const viewLink = document.createElement('a');
                        viewLink.href = `${window.APP_ENDPOINTS.shuttle}/#/tasks/task?taskid=${escapedBuildId}`;
                        viewLink.target = '_blank';
                        viewLink.className = 'btn btn-sm btn-primary';
                        viewLink.textContent = '查看';
                        viewCell.textContent = '';
                        viewCell.appendChild(viewLink);
                    }
                }
                
                // 调试信息：输出API返回的数据结构
                console.log('API返回数据:', result.data);
            }
            
            notify(`构建信息已更新: ${result.data.build_state || '状态更新'}`, 'success');
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('刷新构建信息失败：', error);
        notify('刷新构建信息失败：' + error.message, 'error');
    } finally {
        // 恢复按钮状态 - 使用更精确的按钮查找方式
        const button = document.querySelector(`button[onclick*="refreshBuildStatus('${taskId}', ${packageId}, ${buildItemid})"][title="刷新构建信息"]`);
        if (button) {
            button.disabled = false;
            // 按钮文本保持原有状态，无需恢复
        }
    }
}

// 放弃构建
async function abandonBuild(buildItemId, packageId) {
    if (!buildItemId) {
        notify('放弃构建失败：缺少必要参数', 'error');
        return;
    }
    
    // 确认操作
    if (!confirm('确认要放弃这个构建吗？此操作不可恢复！')) {
        return;
    }
    
    try {
        // 显示加载状态
        const button = document.querySelector(`button[onclick*="abandonBuild('${buildItemId}', ${packageId})"]`);
        if (button) {
            button.disabled = true;
            // 不改变按钮文本，保持原有的"放弃"
        }
        
        // 调用我们的API放弃构建
        const response = await fetch(`/api/projects?action=abandon_build&build_itemid=${buildItemId}&packageId=${packageId}`);
        const result = await response.json();
        
        if (result.success) {
            notify('构建已成功放弃', 'success');
            
            // 更新表格中的构建状态显示
            const row = document.querySelector(`tr[data-package-id="${packageId}"]`);
            if (row) {
                const statusCell = row.querySelector('.build-status');
                if (statusCell) {
                    // 更新状态为已放弃
                    statusCell.className = statusCell.className.replace(/build-status-\w+/g, '');
                    statusCell.className += ' build-status-abandoned';
                    statusCell.textContent = '已放弃';
                }
                
                // 禁用放弃按钮
                if (button) {
                    button.disabled = true;
                    // 不改变按钮文本，保持原有的"放弃"
                    button.className = button.className.replace('btn-danger', 'btn-secondary');
                }
            }
        } else {
            throw new Error(result.message || '放弃构建失败');
        }
    } catch (error) {
        console.error('放弃构建失败：', error);
        notify('放弃构建失败：' + error.message, 'error');
        
        // 恢复按钮状态
        if (button) {
            button.disabled = false;
            // 按钮文本保持原有状态，无需恢复
        }
    }
}

// 通知函数已从 notification.js 引入（第二处）

// 显示提测信息
async function showTestInfo(deliveryId, repoAddr) {
    try {
        document.getElementById('testInfoModal').style.display = 'block';
        // 禁用背景页面滚动
        document.body.style.overflow = 'hidden';
        
        // 显示加载状态
        const testInfoContent = document.getElementById('testInfoContent');
        testInfoContent.innerHTML = '<div class="loading">🔄 正在加载提测信息...</div>';
        
        // 首先检查是否已经有PMS数据
        const checkPmsResponse = await fetch(`/api/projects?action=delivery_pms_data&delivery_id=${deliveryId}`);
        const checkPmsResult = await checkPmsResponse.json();
        
        if (checkPmsResult.success && checkPmsResult.data.length > 0) {
            // 已有数据，直接展示
            renderPmsInfo(deliveryId, repoAddr, checkPmsResult.data, '已有提测信息');
            return;
        }
        
        // 没有数据，需要生成
        testInfoContent.innerHTML = '<div class="loading">🔄 正在生成提测信息...</div>';
        
        const generateResponse = await fetch(`/api/gerrit-api-endpoint?action=generate_delivery_pms&delivery_id=${deliveryId}`);
        const generateResult = await generateResponse.json();
        
        if (!generateResult.success) {
            testInfoContent.innerHTML = `<div class="error">❌ 生成PMS信息失败: ${generateResult.message}</div>`;
            return;
        }
        
        // 生成成功后获取数据
        testInfoContent.innerHTML = '<div class="loading">🔄 正在加载PMS信息...</div>';
        
        const pmsDataResponse = await fetch(`/api/projects?action=delivery_pms_data&delivery_id=${deliveryId}`);
        const pmsDataResult = await pmsDataResponse.json();
        
        if (pmsDataResult.success) {
            renderPmsInfo(deliveryId, repoAddr, pmsDataResult.data, generateResult.message);
        } else {
            testInfoContent.innerHTML = `<div class="error">❌ 获取PMS数据失败: ${pmsDataResult.message}</div>`;
        }
    } catch (error) {
        console.error('获取提测信息失败：', error);
        document.getElementById('testInfoContent').innerHTML = 
            '<div class="error">❌ 获取提测信息失败，请检查网络连接</div>';
    }
}

// 关闭打包信息模态框
function closePackageInfoModal() {
    document.getElementById('packageInfoModal').style.display = 'none';
    // 恢复背景页面滚动
    document.body.style.overflow = 'auto';
}

// 渲染PMS信息
function renderPmsInfo(deliveryId, repoAddr, pmsData, generationMessage = '') {
    const testInfoContent = document.getElementById('testInfoContent');
    
    let html = `
        <div class="pms-info-container">
            <div class="pms-action-header">
                <button onclick="showAddPmsLinksModal(${deliveryId})" class="regenerate-btn add-pms-btn">
                    ➕ 补充其他转测单
                </button>
                <button onclick="addPmsRemarks(${deliveryId},'${repoAddr}')" class="regenerate-btn remark-btn">
                    📝 添加备注
                </button>
                <button onclick="regenerateAllPmsInfo(${deliveryId})" class="regenerate-btn regenerate-all-btn">
                    🔄 全部重新生成
                </button>
            </div>
    `;
    
    // 按模块分组PMS数据
    const groupedByModule = {};
    pmsData.forEach(item => {
        // 手动添加的转测单（module_id 和 module_name 都为空）
        let moduleKey = item.module_name;
        if (!moduleKey) {
            moduleKey = item.module_id ? `模块${item.module_id}` : '手动转测单';
        }
        if (!groupedByModule[moduleKey]) {
            groupedByModule[moduleKey] = [];
        }
        groupedByModule[moduleKey].push(item);
    });
    
    html += '<div class="pms-modules-section">';
    
    // 先显示已有数据的模块（同步执行）
    if (pmsData.length > 0) {
        // 按模块分组
        Object.keys(groupedByModule).forEach(moduleName => {
            const moduleCommits = groupedByModule[moduleName];
            const moduleId = moduleCommits[0].module_id; // 获取模块ID
            const isManualPms = moduleName === '手动转测单';
            html += `
                <div class="pms-module-group">
                    <div class="module-group-header">
                        <div class="module-info">
                            <h5>${moduleName}</h5>
                            <span class="commit-count">${moduleCommits.length} ${moduleName === '手动转测单' ? '个转测单' : '个提交'}</span>
                        </div>
                        ${isManualPms ? 
                            `<button onclick="clearManualPms(${deliveryId}, '${moduleName}')" class="regenerate-btn regenerate-module-btn" style="background: #dc3545;">🗑️ 清空</button>` :
                            `<button onclick="regenerateModulePmsInfo(${deliveryId}, ${moduleId}, '${moduleName}')" class="regenerate-btn regenerate-module-btn">🔄 重新生成</button>`
                        }
                    </div>
                    
                    <div class="pms-table-container">
                        <table class="pms-table">
                            <thead>
                                <tr>
                                    <th>备注状态</th>
                                    <th>提交哈希</th>
                                    <th>作者</th>
                                    <th>PMS链接</th>
                                    <th>影响范围</th>
                                    <th>提交信息</th>
                                    <th>时间</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            moduleCommits.forEach(commit => {
                const isRemarked = commit.remark_status == 1;
                html += `
                    <tr>
                        <td>
                            <input type="checkbox" class="remark-status-checkbox" 
                                data-id="${commit.id}" 
                                ${isRemarked ? 'checked' : ''}
                                onchange="toggleRemarkStatus(${commit.id}, this.checked)">
                        </td>
                        <td>
                            <span class="commit-hash" title="${commit.commit_hash || ''}">${commit.commit_hash ? commit.commit_hash.substring(0, 8) : '-'}</span>
                        </td>
                        <td class="author-cell">${commit.author || '-'}</td>
                        <td class="pms-link-cell">
                            ${commit.pms_url ? 
                                commit.pms_url.split(' ').map(url => {
                                    const trimmedUrl = url.trim();
                                    if (!trimmedUrl) return ''; // 过滤空字符串
                                    return `<a href="${trimmedUrl}" target="_blank" class="pms-link">${extractPmsNumber(trimmedUrl)}</a>`;
                                }).join('<br>') // 多个链接换行显示，也可以换成 ', ' 逗号显示
                                : '-'
                            }
                        </td>
                        <td class="influence-cell">
                            ${commit.influence ? `<span class="influence-text" title="${commit.influence}">${commit.influence.length > 30 ? commit.influence.substring(0, 30) + '...' : commit.influence}</span>` : '-'}
                        </td>
                        <td class="message-cell">
                            ${commit.message ? `<span class="commit-message" title="${commit.message}">${commit.message.length > 50 ? commit.message.substring(0, 50) + '...' : commit.message}</span>` : '-'}
                        </td>
                        <td class="time-cell">${commit.created_at ? new Date(commit.created_at).toLocaleString() : '-'}</td>
                    </tr>
                `;
            });
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        });
    }
    
    // 同步显示已有数据的模块（即使异步请求失败也能显示）
    testInfoContent.innerHTML = html;
    
    // 显示没有PMS数据的模块（异步）
    fetch(`/api/projects?action=delivery_modules&delivery_id=${deliveryId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const pmsModuleNames = new Set();
                pmsData.forEach(item => {
                    // 手动添加的转测单（module_id 和 module_name 都为空）
                    let modName = item.module_name;
                    if (!modName) {
                        modName = item.module_id ? `模块${item.module_id}` : '手动转测单';
                    }
                    pmsModuleNames.add(modName);
                });
                
                result.data.forEach(module => {
                    const moduleName = module.module_name || `模块${module.module_id}`;
                    if (!pmsModuleNames.has(moduleName)) {
                        html += `
                            <div class="pms-module-group">
                                <div class="module-group-header">
                                    <div class="module-info">
                                        <h5>${moduleName}</h5>
                                        <span class="commit-count">暂无PMS数据</span>
                                    </div>
                                    <button onclick="regenerateModulePmsInfo(${deliveryId}, ${module.module_id}, '${moduleName}')" class="generate-btn generate-module-btn">
                                        ➕ 生成PMS信息
                                    </button>
                                </div>
                                <div class="no-pms-data-module">
                                    <p>该模块暂无PMS信息</p>
                                    <p class="hint">可能原因：缺少"基础提交"或"编译提交"信息</p>
                                </div>
                            </div>
                        `;
                    }
                });
                
                // 更新页面内容
                testInfoContent.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('获取模块信息失败:', error);
        });
    
    html += `
            </div>
        </div>
        
        <style>
        .pms-info-container {
            font-family: Arial, sans-serif;
            max-width: 100%;
        }
        

        

        
        .pms-action-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #28a745;
        }
        
        .regenerate-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s;
        }
        
        .regenerate-btn:hover {
            background: #218838;
        }
        
        .regenerate-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .add-pms-btn {
            background: #17a2b8;
        }
        
        .add-pms-btn:hover {
            background: #138496;
        }
        
        .remark-btn {
            background: #ffc107;
            color: #212529;
        }
        
        .remark-btn:hover {
            background: #e0a800;
        }
        
        .regenerate-all-btn {
            background: #dc3545;
        }
        
        .regenerate-all-btn:hover {
            background: #c82333;
        }
        
        .regenerate-module-btn {
            font-size: 0.8em;
            padding: 6px 12px;
        }

        .pms-module-group {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .module-group-header {
            background: #f8f9fa;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e9ecef;
        }
        
        .module-actions {
            display: flex;
            gap: 10px;
        }
        
        .module-status-warning {
            color: #dc3545;
            font-size: 0.85em;
            margin-left: 10px;
        }
        
        .no-pms-data-module {
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0 0 5px 5px;
            text-align: center;
        }
        
        .hint-list {
            text-align: left;
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .hint-list li {
            margin: 5px 0;
            color: #6c757d;
        }
        
        .generate-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.85em;
        }
        
        .generate-btn:hover {
            background: #218838;
        }
        
        .module-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .module-group-header h5 {
            margin: 0;
            color: #495057;
            font-size: 1.1em;
        }
        
        .commit-count {
            background: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        
        .pms-table-container {
            overflow-x: auto;
        }
        
        .pms-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
            min-width: 700px;
        }
        
        .pms-table th {
            background: #f8f9fa;
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            white-space: nowrap;
        }
        
        .pms-table th:nth-child(3) {
            text-align: center !important;
        }
        
        .pms-table td {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        
        .pms-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .commit-hash {
            font-family: monospace;
            background: #e9ecef;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 0.85em;
            cursor: help;
        }
        
        .author-cell {
            min-width: 80px;
            font-weight: 500;
        }
        
        .pms-link-cell {
            text-align: center;
            min-width: 60px;
        }
        
        .pms-link {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9em;
        }
        
        .pms-link:hover {
            text-decoration: underline;
        }
        
        .influence-cell {
            max-width: 200px;
        }
        
        .influence-text {
            color: #495057;
            line-height: 1.4;
            cursor: help;
        }
        
        .message-cell {
            max-width: 250px;
        }
        
        .commit-message {
            color: #6c757d;
            line-height: 1.4;
            cursor: help;
        }
        
        .time-cell {
            min-width: 130px;
            color: #6c757d;
            font-size: 0.85em;
        }
        
        .no-pms-data {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .no-pms-data p {
            margin: 10px 0;
        }
        
        .hint {
            font-size: 0.9em;
            font-style: italic;
        }
        
        .loading {
            text-align: center;
            padding: 40px 20px;
            color: #007bff;
            font-size: 1.1em;
        }
        
        .error {
            text-align: center;
            padding: 20px;
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin: 10px 0;
        }
        </style>
    `;
    
    testInfoContent.innerHTML = html;
}

// 关闭提测信息模态框
function closeTestInfoModal() {
    document.getElementById('testInfoModal').style.display = 'none';
    // 恢复背景页面滚动
    document.body.style.overflow = 'auto';
}

// 重新生成所有模块的PMS信息
async function regenerateAllPmsInfo(deliveryId) {
    if (!confirm('确定要重新生成所有模块的PMS信息吗？这将清空现有数据（手动转测单将保留）！')) {
        return;
    }
    
    try {
        // 禁用所有重新生成按钮
        disableRegenerateButtons(true);
        
        // 显示加载状态
        const testInfoContent = document.getElementById('testInfoContent');
        testInfoContent.innerHTML = '<div class="loading">🔄 正在重新生成所有PMS信息...</div>';
        
        // 调用重新生成API
        const response = await fetch(`/api/gerrit-api-endpoint?action=regenerate_delivery_pms&delivery_id=${deliveryId}`);
        const result = await response.json();
        
        if (!result.success) {
            alert('重新生成失败: ' + result.message);
            return;
        }
        
        // 重新加载PMS数据
        await reloadPmsData(deliveryId, result.message);
        
    } catch (error) {
        console.error('重新生成PMS信息异常:', error);
        alert('操作失败，请稍后重试');
    } finally {
        disableRegenerateButtons(false);
    }
}



// 重新生成指定模块的PMS信息
async function regenerateModulePmsInfo(deliveryId, moduleId, moduleName) {
    if (!confirm(`确定要重新生成模块"${moduleName}"的PMS信息吗？这将清空该模块的现有数据！`)) {
        return;
    }
    
    try {
        // 禁用所有重新生成按钮
        disableRegenerateButtons(true);
        
        // 显示加载状态
        const testInfoContent = document.getElementById('testInfoContent');
        testInfoContent.innerHTML = '<div class="loading">🔄 正在重新生成模块PMS信息...</div>';
        
        // 调用重新生成API
        const response = await fetch(`/api/gerrit-api-endpoint?action=regenerate_delivery_pms&delivery_id=${deliveryId}&module_id=${moduleId}`);
        const result = await response.json();
        
        if (!result.success) {
            alert('重新生成失败: ' + result.message);
            return;
        }
        
        // 重新加载PMS数据
        await reloadPmsData(deliveryId, result.message);
        
    } catch (error) {
        console.error('重新生成模块PMS信息异常:', error);
        alert('操作失败，请稍后重试');
    } finally {
        disableRegenerateButtons(false);
    }
}

// 清空手动添加的转测单
async function clearManualPms(deliveryId, moduleName) {
    if (!confirm('确定要清空手动添加的转测单吗？')) {
        return;
    }
    
    try {
        // 显示加载状态
        const testInfoContent = document.getElementById('testInfoContent');
        testInfoContent.innerHTML = '<div class="loading">🗑️ 正在清空手动转测单...</div>';
        
        // 调用API清空手动添加的PMS记录（module_id为空的记录）
        const response = await fetch(`/api/gerrit-api-endpoint?action=clear_delivery_pms&delivery_id=${deliveryId}&manual_only=true`);
        const result = await response.json();
        
        if (result.success) {
            // 重新加载PMS数据
            await reloadPmsData(deliveryId, '手动转测单已清空');
        } else {
            alert('清空失败: ' + result.message);
            await reloadPmsData(deliveryId, '清空失败，已恢复显示');
        }
    } catch (error) {
        console.error('清空手动转测单异常:', error);
        alert('操作失败，请稍后重试');
        await reloadPmsData(deliveryId, '清空失败，已恢复显示');
    }
}

// 重新加载PMS数据
async function reloadPmsData(deliveryId, message) {
    try {
        const response = await fetch(`/api/projects?action=delivery_pms_data&delivery_id=${deliveryId}`);
        const result = await response.json();
        
        if (result.success) {
            renderPmsInfo(deliveryId, "", result.data, message);
        } else {
            document.getElementById('testInfoContent').innerHTML = '<div class="error">❌ 加载数据失败</div>';
        }
    } catch (error) {
        console.error('重新加载PMS数据异常:', error);
        document.getElementById('testInfoContent').innerHTML = '<div class="error">❌ 加载数据异常</div>';
    }
}

// 添加PMS备注
async function addPmsRemarks(deliveryId, repoAddr) {
    if (!deliveryId) {
        alert('交付记录ID不能为空');
        return;
    }

    if (!repoAddr) {
        alert('仓库地址为空，请先同步');
        return;
    }
    
    // 确认操作
    if (!confirm('确定要给所有PMS工单添加提测信息备注吗？')) {
        return;
    }
    
    try {
        // 禁用按钮
        const button = document.querySelector('.remark-btn');
        if (button) {
            button.disabled = true;
            button.textContent = '📝 添加中...';
        }
        
        // 调用API添加备注
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_pms_remarks',
                delivery_id: deliveryId,
                repo_addr: repoAddr
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(`备注添加完成：\n成功 ${result.data.success_count} 个\n失败 ${result.data.fail_count} 个`);
            
            // 刷新提测信息界面
            showTestInfo(deliveryId, repoAddr);
            
            // 显示详细结果
            if (result.data.results && result.data.results.length > 0) {
                let details = '详细结果：\n';
                result.data.results.forEach(item => {
                    const status = item.success ? '✓' : '✗';
                    details += `${status} ${item.pms_url}\n`;
                });
                console.log(details);
            }
        } else {
            alert('添加备注失败: ' + result.message);
        }
        
    } catch (error) {
        console.error('添加PMS备注异常:', error);
        alert('操作失败，请稍后重试');
    } finally {
        // 恢复按钮状态
        const button = document.querySelector('.remark-btn');
        if (button) {
            button.disabled = false;
            button.textContent = '📝 添加备注';
        }
    }
}

// 禁用/启用重新生成按钮
function disableRegenerateButtons(disabled) {
    const buttons = document.querySelectorAll('.regenerate-btn');
    buttons.forEach(button => {
        button.disabled = disabled;
    });
}

// 从PMS URL中提取编号
function extractPmsNumber(url) {
    if (!url) return '-';
    
    // 匹配常见的PMS URL格式
    // 例如: https://pms.example.com/bug-view-123456.html
    // 或: https://pms.example.com/task-view-789012.html
    const match = url.match(/(bug|task)-view-(\d+)/i);
    if (match) {
        const type = match[1].toLowerCase();
        const number = match[2];
        return `${type}-${number}`;
    }
    
    // 如果没有匹配到标准格式，尝试提取URL中的数字
    const numberMatch = url.match(/(\d{4,})/);
    if (numberMatch) {
        return numberMatch[1];
    }
    
    // 如果都没匹配到，返回链接图标
    return '🔗';
}

// 切换备注状态
async function toggleRemarkStatus(pmsId, checked) {
    const status = checked ? 1 : 0;
    try {
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_remark_status',
                pms_id: pmsId,
                status: status
            })
        });
        const result = await response.json();
        if (!result.success) {
            alert('更新备注状态失败: ' + result.message);
            // 恢复checkbox状态
            document.querySelector(`input[data-id="${pmsId}"]`).checked = !checked;
        }
    } catch (error) {
        alert('更新备注状态失败: ' + error.message);
        document.querySelector(`input[data-id="${pmsId}"]`).checked = !checked;
    }
}

// 全局变量存储当前交付ID
let currentDeliveryId = null;

// 显示补充转测单模态框
function showAddPmsLinksModal(deliveryId) {
    currentDeliveryId = deliveryId;
    document.getElementById('addPmsLinksModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // 清空输入框
    document.getElementById('pmsLinksInput').value = '';
    document.getElementById('pmsLinksInput').focus();
}

// 关闭补充转测单模态框
function closeAddPmsLinksModal() {
    document.getElementById('addPmsLinksModal').style.display = 'none';
    
    // 检查是否还有其他模态框在显示，如果没有则恢复页面滚动
    const testInfoModal = document.getElementById('testInfoModal');
    const addModulesModal = document.getElementById('addModulesModal');
    const batchPackageModal = document.getElementById('batchPackageModal');
    
    if (testInfoModal && testInfoModal.style.display !== 'block' &&
        addModulesModal && addModulesModal.style.display !== 'block' &&
        batchPackageModal && batchPackageModal.style.display !== 'block') {
        document.body.style.overflow = 'auto';
    }
    
    currentDeliveryId = null;
}

// 导出提测单
async function exportTestInfo(deliveryId) {
    try {
        // 显示加载状态
        const button = event.target;
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = '📋 导出中...';
        
        // 调用Excel导出接口
        const link = document.createElement('a');
        link.href = `/api/gerrit-api-endpoint?action=export_test_excel&delivery_id=${deliveryId}`;
        link.download = `提测单_交付记录${deliveryId}_${new Date().toISOString().split('T')[0]}.xlsx`;
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // 延迟恢复按钮状态
        setTimeout(() => {
            button.disabled = false;
            button.textContent = '📋 导出提测单';
        }, 2000);
        
    } catch (error) {
        console.error('导出提测单异常:', error);
        alert('导出失败，请稍后重试');
        
        // 恢复按钮状态
        const button = event.target;
        button.disabled = false;
        button.textContent = '📋 导出提测单';
    }
}

// 提交PMS链接
async function submitPmsLinks() {
    const linksInput = document.getElementById('pmsLinksInput');
    const linksText = linksInput.value.trim();
    
    if (!linksText) {
        alert('请输入至少一个PMS链接');
        return;
    }
    
    if (!currentDeliveryId) {
        alert('交付记录ID不能为空');
        return;
    }
    
    // 按行分割链接
    const links = linksText.split('\n')
        .map(link => link.trim())
        .filter(link => link.length > 0);
    
    if (links.length === 0) {
        alert('请输入有效的PMS链接');
        return;
    }
    
    try {
        // 禁用提交按钮
        const submitBtn = document.querySelector('.modal-footer .btn-primary');
        submitBtn.disabled = true;
        submitBtn.textContent = '正在添加...';
        
        // 调用API添加PMS链接
        const response = await fetch('/api/gerrit-api-endpoint?action=add_pms_links', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                delivery_id: currentDeliveryId,
                pms_links: links
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(`成功添加 ${result.added_count} 个转测单`);
            
            // 先重新加载PMS数据（因为closeAddPmsLinksModal会清空currentDeliveryId）
            const deliveryIdToReload = currentDeliveryId;
            closeAddPmsLinksModal();
            await reloadPmsData(deliveryIdToReload, '补充转测单完成');
        } else {
            alert('添加失败: ' + result.message);
        }
        
    } catch (error) {
        console.error('添加PMS链接异常:', error);
        alert('操作失败，请稍后重试');
    } finally {
        // 恢复提交按钮
        const submitBtn = document.querySelector('.modal-footer .btn-primary');
        submitBtn.disabled = false;
        submitBtn.textContent = '确认添加';
    }
}

// 刷新交付记录显示（获取最新数据库数据）
async function refreshDeliveryRecordDisplay(deliveryRecordId) {
    try {
        // 重新加载交付记录列表（从数据库获取最新数据）
        // await loadProjectDeliveries(currentProjectId);
        
        // 如果当前在交付记录tab，刷新打包信息显示
        const currentTab = localStorage.getItem('currentProjectTab');
        if (currentTab === 'project-deliveries') {
            // 找到对应的交付记录项
            const deliveryItem = document.querySelector(`[data-delivery-id="${deliveryRecordId}"]`);
            if (deliveryItem) {
                // 重新加载该交付记录的打包信息（从数据库获取最新数据）
                const packageContainer = deliveryItem.querySelector('.delivery-package-info');
                if (packageContainer) {
                    // 显示加载状态
                    packageContainer.innerHTML = '<div class="package-info-placeholder"><div class="loading-spinner">🔄 刷新中...</div></div>';
                    
                    // 重新加载打包信息（从数据库获取最新数据，不是同步CRP）
                    await loadAndShowPackageInfo(deliveryRecordId);
                }
            }
        }
        
        // notify('界面已刷新，显示最新数据', 'success');
    } catch (error) {
        console.error('刷新界面失败：', error);
        notify('刷新失败：' + error.message, 'error');
    }
}

// 切换归档状态
async function toggleArchiveStatus(deliveryId, checkbox) {
    const newArchiveStatus = checkbox.checked ? 1 : 0;
    const label = checkbox.nextElementSibling;
    
    try {
        // 调用API更新归档状态
        const response = await fetch('/api/projects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_delivery_archive',
                delivery_id: deliveryId,
                archive: newArchiveStatus
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // 更新标签文本
            label.textContent = newArchiveStatus === 1 ? '已归档' : '未归档';
            
            // 显示成功提示
            notify(newArchiveStatus === 1 ? '已归档' : '已取消归档', 'success');
        } else {
            // 恢复复选框状态
            checkbox.checked = !checkbox.checked;
            notify('更新归档状态失败：' + result.message, 'error');
        }
    } catch (error) {
        console.error('更新归档状态失败：', error);
        // 恢复复选框状态
        checkbox.checked = !checkbox.checked;
        notify('更新归档状态失败，请检查网络连接', 'error');
    }
}

// 添加ESC键监听功能
document.addEventListener('keydown', function(event) {
    // 检测ESC键（键码27）
    if (event.key === 'Escape' || event.keyCode === 27) {
        // 检查打包信息对话框是否打开
        const packageModal = document.getElementById('packageInfoModal');
        if (packageModal && packageModal.style.display === 'block') {
            closePackageInfoModal();
            return;
        }
        
        // 检查提测信息对话框是否打开
        const testModal = document.getElementById('testInfoModal');
        if (testModal && testModal.style.display === 'block') {
            closeTestInfoModal();
            return;
        }
        
        // 检查补充转测单对话框是否打开
        const addPmsModal = document.getElementById('addPmsLinksModal');
        if (addPmsModal && addPmsModal.style.display === 'block') {
            closeAddPmsLinksModal();
            return;
        }
    }
});
</script>
</body>
</html>
<?php
ob_end_flush();
?>
