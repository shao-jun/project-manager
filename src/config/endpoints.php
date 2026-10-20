<?php
/**
 * 外部系统地址配置
 *
 * 所有对接系统的地址都在这里集中管理，代码中不再硬编码任何内网域名。
 * 私有部署时请不要修改本文件（它会被提交到仓库），
 * 而是在同目录下新建 endpoints.local.php 覆盖需要的项，该文件已被 .gitignore 忽略：
 *
 *   return [
 *       'crp'         => 'https://crp.your-company.com',
 *       'gerrit'      => 'https://gerrit.your-company.com',
 *       'pms'         => 'https://pms.your-company.com',
 *       'shuttle'     => 'https://shuttle.your-company.com',
 *       'cooperation' => 'https://cooperation.your-company.com',
 *       'pools'       => 'https://pools.your-company.com',
 *   ];
 *
 * 也可以用环境变量注入，优先级：endpoints.local.php > 环境变量 > 下面的默认值。
 */

$defaults = [
    // CRP 系统（发布/主题管理）
    'crp'         => getenv('CRP_BASE_URL') ?: 'https://crp.example.com',
    // Gerrit 代码评审
    'gerrit'      => getenv('GERRIT_BASE_URL') ?: 'https://gerrit.example.com',
    // PMS 缺陷/任务系统
    'pms'         => getenv('PMS_BASE_URL') ?: 'https://pms.example.com',
    // Shuttle 构建平台
    'shuttle'     => getenv('SHUTTLE_BASE_URL') ?: 'https://shuttle.example.com',
    // 明道云等表单/协作平台
    'cooperation' => getenv('COOPERATION_BASE_URL') ?: 'https://cooperation.example.com',
    // 软件源（Packages 索引）
    'pools'       => getenv('POOLS_BASE_URL') ?: 'https://pools.example.com',
];

$localFile = __DIR__ . '/endpoints.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
    if (is_array($local)) {
        foreach ($local as $key => $value) {
            if (is_string($value) && $value !== '') {
                $defaults[$key] = $value;
            }
        }
    }
}

return $defaults;
