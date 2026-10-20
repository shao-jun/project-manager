-- 研发自动化管理平台数据库初始化脚本

-- 创建数据库
CREATE DATABASE IF NOT EXISTS dev_automation DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE dev_automation;

-- 技术分类表
CREATE TABLE IF NOT EXISTS tech_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT '分类名称',
    description TEXT COMMENT '分类描述',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='技术分类表';

-- 模块表
CREATE TABLE IF NOT EXISTS modules (
    pk_id INT AUTO_INCREMENT PRIMARY KEY,
    id INT NOT NULL COMMENT '模块ID',
    category_id INT NOT NULL COMMENT '所属分类ID',
    name VARCHAR(200) NOT NULL COMMENT '模块名称',
    repo_url TEXT NOT NULL COMMENT '代码仓库地址',
    main_branch VARCHAR(100) NOT NULL DEFAULT 'main' COMMENT '主线分支',
    current_version VARCHAR(50) NOT NULL DEFAULT 'v1.0.0' COMMENT '当前版本',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT '状态',
    build_type VARCHAR(50),
    dependencies VARCHAR(500) NULL COMMENT '依赖模块ID，逗号分隔，如1,2,3',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES tech_categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_name (name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='模块表';

-- 项目表
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT '项目名称',
    category_id INT NOT NULL COMMENT '技术分类ID',
    description TEXT COMMENT '项目描述',
    status ENUM('running', 'paused', 'stopped') DEFAULT 'running' COMMENT '项目状态',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES tech_categories(id) ON DELETE RESTRICT,
    INDEX idx_name (name),
    INDEX idx_category (category_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目表';

-- 项目模块配置表
CREATE TABLE IF NOT EXISTS project_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL COMMENT '项目ID',
    module_id INT NOT NULL COMMENT '模块ID',
    project_branch VARCHAR(100) COMMENT '项目分支（如果为空则使用主线分支）',
    project_version VARCHAR(50) COMMENT '项目版本（如果为空则使用主线版本）',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT '在项目中的状态',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    UNIQUE KEY uk_project_module (project_id, module_id),
    INDEX idx_project (project_id),
    INDEX idx_module (module_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目模块配置表';

-- 插入初始技术分类数据
INSERT INTO tech_categories (name, description) VALUES
('DDE V20', '深度桌面环境V20相关模块'),
('窗管', '窗口管理系统模块'),
('DTK', '开发工具包模块');

-- 获取分类ID
SET @dde_v20_id = (SELECT id FROM tech_categories WHERE name = 'DDE V20');
SET @window_mgr_id = (SELECT id FROM tech_categories WHERE name = '窗管');
SET @dtk_id = (SELECT id FROM tech_categories WHERE name = 'DTK');

-- 插入DDE V20模块数据
INSERT INTO modules (id, category_id, name, repo_url, main_branch, current_version, status) VALUES
(1, @dde_v20_id, 'deepin-repo-config-tool', 'https://gerrit.example.com/plugins/gitiles//deepin-repo-config-tool', 'develop/eagle', 'v1.0.0', 'active'),
(2, @dde_v20_id, 'deepin-passkey', 'https://gerrit.example.com/plugins/gitiles//deepin-passkey', 'develop/eagle', 'v1.0.0', 'active'),
(3, @dde_v20_id, 'deepin-network-proxy', 'https://gerrit.example.com/plugins/gitiles//deepin-network-proxy', 'develop/eagle', 'v1.0.0', 'active'),
(4, @dde_v20_id, 'dde-wldpms', 'https://gerrit.example.com/plugins/gitiles//dde-wldpms', 'develop/eagle', 'v1.0.0', 'active'),
(5, @dde_v20_id, 'startdde', 'https://gerrit.example.com/plugins/gitiles//startdde', 'develop/eagle', 'v1.0.0', 'active'),
(6, @dde_v20_id, 'qt5platform-plugins', 'https://gerrit.example.com/plugins/gitiles//qt5platform-plugins', 'develop/eagle', 'v1.0.0', 'active'),
(7, @dde_v20_id, 'qt5integration', 'https://gerrit.example.com/plugins/gitiles//qt5integration', 'develop/eagle', 'v1.0.0', 'active'),
(8, @dde_v20_id, 'lastore-daemon', 'https://gerrit.example.com/plugins/gitiles//lastore-daemon', 'develop/eagle', 'v1.0.0', 'active'),
(9, @dde_v20_id, 'kelvinu-dm', 'https://gerrit.example.com/plugins/gitiles//kelvinu-dm', 'develop/eagle', 'v1.0.0', 'active'),
(10, @dde_v20_id, 'kelvinu/dde-wloutput-daemon', 'https://gerrit.example.com/plugins/gitiles//kelvinu/dde-wloutput-daemon', 'develop/eagle', 'v1.0.0', 'active'),
(11, @dde_v20_id, 'go-lib', 'https://gerrit.example.com/plugins/gitiles//go-lib', 'develop/eagle', 'v1.0.0', 'active'),
(12, @dde_v20_id, 'go-dbus-factory', 'https://gerrit.example.com/plugins/gitiles//go-dbus-factory', 'develop/eagle', 'v1.0.0', 'active'),
(13, @dde_v20_id, 'dtkwidget', 'https://gerrit.example.com/plugins/gitiles//dtkwidget', 'develop/eagle', 'v1.0.0', 'active'),
(14, @dde_v20_id, 'dtkcore', 'https://gerrit.example.com/plugins/gitiles//dtkcore', 'develop/eagle', 'v1.0.0', 'active'),
(15, @dde_v20_id, 'default-settings', 'https://gerrit.example.com/plugins/gitiles//default-settings', 'develop/eagle', 'v1.0.0', 'active'),
(16, @dde_v20_id, 'deepin-update-ui', 'https://gerrit.example.com/plugins/gitiles//deepin-update-ui', 'develop/eagle', 'v1.0.0', 'active'),
(17, @dde_v20_id, 'deepin-tooltips', 'https://gerrit.example.com/plugins/gitiles//deepin-tooltips', 'develop/eagle', 'v1.0.0', 'active'),
(18, @dde_v20_id, 'deepin-service-manager', 'https://gerrit.example.com/plugins/gitiles//deepin-service-manager', 'develop/eagle', 'v1.0.0', 'active'),
(19, @dde_v20_id, 'deepin-pw-check', 'https://gerrit.example.com/plugins/gitiles//deepin-pw-check', 'develop/eagle', 'v1.0.0', 'active'),
(20, @dde_v20_id, 'deepin-network-displays', 'https://gerrit.example.com/plugins/gitiles//deepin-network-displays', 'develop/eagle', 'v1.0.0', 'active'),
(21, @dde_v20_id, 'deepin-keyring-whitebox', 'https://gerrit.example.com/plugins/gitiles//deepin-keyring-whitebox', 'develop/eagle', 'v1.0.0', 'active'),
(22, @dde_v20_id, 'deepin-icon-theme', 'https://gerrit.example.com/plugins/gitiles//deepin-icon-theme', 'master', 'v1.0.0', 'active'),
(23, @dde_v20_id, 'deepin-face', 'https://gerrit.example.com/plugins/gitiles//deepin-face', 'develop/eagle', 'v1.0.0', 'active'),
(24, @dde_v20_id, 'deepin-desktop-schemas', 'https://gerrit.example.com/plugins/gitiles//deepin-desktop-schemas', 'develop/eagle', 'v1.0.0', 'active'),
(25, @dde_v20_id, 'deepin-authentication', 'https://gerrit.example.com/plugins/gitiles//deepin-authentication', 'develop/eagle', 'v1.0.0', 'active'),
(26, @dde_v20_id, 'deepin-ab-recovery', 'https://gerrit.example.com/plugins/gitiles//deepin-ab-recovery', 'develop/eagle', 'v1.0.0', 'active'),
(27, @dde_v20_id, 'dde-wloutput', 'https://gerrit.example.com/plugins/gitiles//dde-wloutput', 'develop/eagle', 'v1.0.0', 'active'),
(28, @dde_v20_id, 'dde-session-ui', 'https://gerrit.example.com/plugins/gitiles//dde-session-ui', 'develop/eagle', 'v1.0.0', 'active'),
(29, @dde_v20_id, 'dde-session-shell', 'https://gerrit.example.com/plugins/gitiles//dde-session-shell', 'develop/eagle', 'v1.0.0', 'active'),
(30, @dde_v20_id, 'dde-online-authentication', 'https://gerrit.example.com/plugins/gitiles//dde-online-authentication', 'develop/eagle', 'v1.0.0', 'active'),
(31, @dde_v20_id, 'dde-network-display-ui', 'https://gerrit.example.com/plugins/gitiles//dde-network-display-ui', 'develop/eagle', 'v1.0.0', 'active'),
(32, @dde_v20_id, 'dde-network-core', 'https://gerrit.example.com/plugins/gitiles//dde-network-core', 'develop/eagle', 'v1.0.0', 'active'),
(33, @dde_v20_id, 'dde-manual-content', 'https://gerrit.example.com/plugins/gitiles//dde-manual-content', 'develop/eagle', 'v1.0.0', 'active'),
(34, @dde_v20_id, 'dde-launcher', 'https://gerrit.example.com/plugins/gitiles//dde-launcher', 'develop/eagle', 'v1.0.0', 'active'),
(35, @dde_v20_id, 'dde-dock', 'https://gerrit.example.com/plugins/gitiles//dde-dock', 'develop/eagle', 'v1.0.0', 'active'),
(36, @dde_v20_id, 'dde-daemon', 'https://gerrit.example.com/plugins/gitiles//dde-daemon', 'develop/eagle', 'v1.0.0', 'active'),
(37, @dde_v20_id, 'dde-control-center', 'https://gerrit.example.com/plugins/gitiles//dde-control-center', 'develop/eagle', 'v1.0.0', 'active'),
(38, @dde_v20_id, 'dde-clipboard', 'https://gerrit.example.com/plugins/gitiles//dde-clipboard', 'develop/eagle', 'v1.0.0', 'active'),
(39, @dde_v20_id, 'dde-app-services', 'https://gerrit.example.com/plugins/gitiles//dde-app-services', 'develop/eagle', 'v1.0.0', 'active'),
(40, @dde_v20_id, 'dde-api', 'https://gerrit.example.com/plugins/gitiles//dde-api', 'develop/eagle', 'v1.0.0', 'active'),
(41, @dde_v20_id, 'xdg-desktop-portal-dde', 'https://gerrit.example.com/plugins/gitiles//xdg-desktop-portal-dde', 'develop/eagle', 'v1.0.0', 'active'),
(42, @dde_v20_id, 'dde-wallpaper-cache', 'https://gerrit.example.com/plugins/gitiles//dde-wallpaper-cache', 'develop/eagle', 'v1.0.0', 'active'),
(43, @dde_v20_id, 'deepin-security-loader', 'https://gerrit.example.com/plugins/gitiles//deepin-security-loader', 'develop/eagle', 'v1.0.0', 'active'),
(44, @dde_v20_id, 'dde-network-utils', 'https://gerrit.example.com/plugins/gitiles//dde-network-utils', 'develop/eagle', 'v1.0.0', 'active'),
(45, @dde_v20_id, 'os-config', 'https://gerrit.example.com/plugins/gitiles//base/os-config', 'master', 'v1.0.0', 'active'),
(46, @dde_v20_id, 'uos-license-content', 'https://gerrit.example.com/plugins/gitiles//uos-license-content', 'master', 'v1.0.0', 'active'),
(47, @dde_v20_id, 'sssd', 'https://gerrit.example.com/plugins/gitiles//sssd', 'uos', 'v1.0.0', 'active'),
(48, @dde_v20_id, 'go-dlib', 'https://gerrit.example.com/plugins/gitiles//go-dlib', 'develop/eagle', 'v1.0.0', 'active'),
(49, @dde_v20_id, 'dtkgui', 'https://gerrit.example.com/plugins/gitiles//dtkgui', 'develop/eagle', 'v1.0.0', 'active'),
(50, @dde_v20_id, 'deepin-turbo', 'https://gerrit.example.com/plugins/gitiles//deepin-turbo', 'develop/eagle', 'v1.0.0', 'active'),
(51, @dde_v20_id, 'deepin-sync-client', 'https://gerrit.example.com/plugins/gitiles//deepin-sync-client', 'develop/eagle', 'v1.0.0', 'active'),
(52, @dde_v20_id, 'deepin-deepinid-plugin', 'https://gerrit.example.com/plugins/gitiles//deepin-deepinid-plugin', 'develop/eagle', 'v1.0.0', 'active'),
(53, @dde_v20_id, 'deepin-deepinid-client', 'https://gerrit.example.com/plugins/gitiles//deepin-deepinid-client', 'develop/eagle', 'v1.0.0', 'active'),
(54, @dde_v20_id, 'dde-qt-dbus-factory', 'https://gerrit.example.com/plugins/gitiles//dde-qt-dbus-factory', 'develop/eagle', 'v1.0.0', 'active'),
(55, @dde_v20_id, 'dde-polkit-agent', 'https://gerrit.example.com/plugins/gitiles//dde-polkit-agent', 'develop/eagle', 'v1.0.0', 'active'),
(56, @dde_v20_id, 'spdx-licenses', 'https://gerrit.example.com/plugins/gitiles//base/spdx-licenses', 'develop/eagle', 'v1.0.0', 'active'),
(57, @dde_v20_id, 'base/polkit-qt5-1', 'https://gerrit.example.com/plugins/gitiles//base/polkit-qt5-1', 'develop/eagle', 'v1.0.0', 'active'),
(58, @dde_v20_id, 'base/networkmanager-qt', 'https://gerrit.example.com/plugins/gitiles//base/networkmanager-qt', 'develop/eagle', 'v1.0.0', 'active'),
(59, @dde_v20_id, 'base/network-manager', 'https://gerrit.example.com/plugins/gitiles//base/network-manager', 'develop/eagle', 'v1.0.0', 'active'),
(60, @dde_v20_id, 'base/lightdm', 'https://gerrit.example.com/plugins/gitiles//base/lightdm', 'develop/eagle', 'v1.0.0', 'active'),
(61, @dde_v20_id, 'base/libfido2', 'https://gerrit.example.com/plugins/gitiles//base/libfido2', 'develop/eagle', 'v1.0.0', 'active'),
(62, @dde_v20_id, 'base/gnome-keyring', 'https://gerrit.example.com/plugins/gitiles//base/gnome-keyring', 'develop/eagle', 'v1.0.0', 'active');

-- 插入窗管模块数据
INSERT INTO modules (id, category_id, name, repo_url, main_branch, current_version, status) VALUES
(70, @window_mgr_id, 'window-manager', 'https://git.example.com/wm/manager.git', 'main', 'v2.1.0', 'active'),
(71, @window_mgr_id, 'window-service', 'https://git.example.com/wm/service.git', 'main', 'v1.5.2', 'active'),
(72, @window_mgr_id, 'window-client', 'https://git.example.com/wm/client.git', 'main', 'v1.3.1', 'active');

-- 插入DTK模块数据
INSERT INTO modules (id, category_id, name, repo_url, main_branch, current_version, status) VALUES
(80, @dtk_id, 'dtk-core', 'https://git.example.com/dtk/core.git', 'main', 'v3.0.0', 'active'),
(81, @dtk_id, 'dtk-widgets', 'https://git.example.com/dtk/widgets.git', 'main', 'v2.5.1', 'active'),
(82, @dtk_id, 'dtk-common', 'https://git.example.com/dtk/common.git', 'main', 'v1.8.0', 'active'),
(83, @dtk_id, 'dtk-plugins', 'https://git.example.com/dtk/plugins.git', 'develop', 'v1.2.0-rc1', 'inactive');

-- 插入示例项目数据
INSERT INTO projects (name, category_id, description, status) VALUES
('1074', @dde_v20_id, '1074项目的DDE V20相关模块', 'running'),
('交行', @dde_v20_id, '交行项目的DDE V20相关模块', 'running'),
('窗管测试', @window_mgr_id, '窗管系统测试项目', 'paused');

-- 为项目添加模块配置（这里只添加部分示例，实际使用时会通过API动态添加）
SET @project_1074_id = (SELECT id FROM projects WHERE name = '1074');
SET @project_icbc_id = (SELECT id FROM projects WHERE name = '交行');

-- 1074项目使用DDE V20的所有模块
INSERT INTO project_modules (project_id, module_id, status)
SELECT @project_1074_id, id, 'active'
FROM modules 
WHERE category_id = @dde_v20_id;

-- 交行项目使用DDE V20的所有模块
INSERT INTO project_modules (project_id, module_id, status)
SELECT @project_icbc_id, id, 'active'
FROM modules 
WHERE category_id = @dde_v20_id;
