<?php

use App\Database\PDOConfig;
use App\AppConfig;
use App\Helpers\Env;
use App\Services\Files\AssetFilesConfig;
use App\Helpers\Loggers\LogHandlers\FileLogHandlerConfig;

require_once __DIR__ . '/utils.php';

echoLine();
echoLine('Validating ENV...');

try {
    Env::Load();
} catch (Exception $e) {
    echoError($e);
    echoLine('ENV validation failed');
    exit(1);
}

/**
 * Defines an ENV field
 * @param string $key exact name
 * @param bool $is_required whether validation fails when this is not defined
 * @param string $default the default value when this is not defined
 * @return array{default: string, is_required: bool, key: string}
 */
function define_env_field(string $key, bool $is_required, string $default = '')
{
    return [
        'key' => $key,
        'is_required' => $is_required,
        'default' => $default
    ];
}

/**
 * Defines an ENV issue
 * @param string $key exact name
 * @param string $reason the reason there is an issue
 * @return array{key: string, reason: string}
 */
function define_env_issue(string $key, string $reason)
{
    return [
        'key' => $key,
        'reason' => $reason,
    ];
}

$env_fields = [
    define_env_field(AppConfig::ENV_DEV_MODE, false, '0'),
    define_env_field(FileLogHandlerConfig::ENV_LOG_PATH, true),
    define_env_field(AssetFilesConfig::ENV_DATA_PATH, true),
    define_env_field(AssetFilesConfig::ENV_DATA_MAX_SIZE_MB, true),
    define_env_field(PDOConfig::ENV_SERVERNAME, true),
    define_env_field(PDOConfig::ENV_USERNAME, true),
    define_env_field(PDOConfig::ENV_PASSWORD, true),
    define_env_field(PDOConfig::ENV_DATABASE, true),
];

$env_issues = [];

foreach ($env_fields as $field) {
    $key = $field['key'];
    $is_required = $field['is_required'];
    $default = $field['default'];

    if (isset($_ENV[$key])) {
        continue;
    }

    if ($is_required) {
        $env_issues[] = define_env_issue($key, 'This field is required');
        continue;
    }

    $_ENV[$key] = $default;
}

if (count($env_issues) > 0) {
    foreach ($env_issues as $issue) {
        $key = $issue['key'];
        $issue = $issue['reason'];

        echoLine('Field \'' . $key . '\': ' . $issue);
    }

    echoLine('ENV validation failed');
    exit(1);
}

echoLine('ENV OK');
