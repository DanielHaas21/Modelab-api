<?php

use App\Database\PDOConfig;
use App\Helpers\Env;
use App\Helpers\Loggers\Logger;
use App\Helpers\Loggers\LogHandlers\DBLogHandler;
use App\Helpers\Loggers\LogHandlers\EchoLogHandler;
use App\Helpers\Loggers\LogHandlers\FileLogHandler;
use App\Helpers\Loggers\LogHandlers\FileLogHandlerConfig;

require_once __DIR__ . '/utils.php';

echoLine();
echoLine('Validating Logging...');

Env::Load();
FileLogHandlerConfig::Load();
PDOConfig::Load();

$log_path = FileLogHandlerConfig::$LOG_PATH;

if (!is_dir($log_path)) {
    echoLine('Log folder not found, attempting to create...');

    // Source - https://stackoverflow.com/a/37270421
    // Posted by Oldskool, modified by community. See post 'Timeline' for change history
    // Retrieved 2026-04-05, License - CC BY-SA 3.0
    if (!mkdir($log_path, 0777, true)) {
        echoLine('Log folder failed to create');
        exit(1);
    }

    if (!chmod($log_path, 999)) {
        echoLine('Log folder failed to set permissions');
        rmdir($log_path);
        exit(1);
    }
}

echoLine('Simulating test log');
try {
    Logger::RegisterHandler(new FileLogHandler());
    Logger::RegisterHandler(new DBLogHandler());
    Logger::RegisterHandler(new EchoLogHandler());

    Logger::LogDebug('Test log for validation', basename(__FILE__));
} catch (Exception $e) {
    echoError($e);
    echoLine('Logging validation failed');
    exit(1);
}

echoLine('Logging OK');
