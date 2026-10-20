<?php
/**
 * CRP API 配置（默认值 / 可提交版本）
 *
 * JWT token 属于敏感信息，不要写到本文件里：
 *   - 环境变量 CRP_JWT_TOKEN
 *   - 或同目录下的 crp_config.local.php（已 gitignore，配置页保存时也会写到这里）
 *
 * 本文件只放不敏感的默认值，读取时与本地私有配置合并。
 */

require_once __DIR__ . '/../lib/endpoints.php';

$config = [
    'jwt_token' => getenv('CRP_JWT_TOKEN') ?: '',
    'base_url' => CRP_API,
    'timeout' => 30,
    'verify_ssl' => false,
    'default_topic' => [
        'TpcType' => 'public',
        'Type' => 'test',
        // 默认主题负责人，按需在 crp_config.local.php 里覆盖
        'Owner' => getenv('CRP_DEFAULT_OWNER') ?: 'owner1;owner2',
        'RepoType' => 'deb',
    ],
];

$localConfigFile = __DIR__ . '/crp_config.local.php';
if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
    if (is_array($localConfig)) {
        $config = array_merge($config, $localConfig);
    }
}

return $config;
