<?php

require_once __DIR__ . '/autoload.php';

use App\Services\Database\PDOConfig;
use App\Configuration\AppConfig;
use App\Configuration\Env;
use App\Services\Files\AssetFilesConfig;
use App\Services\Router\Router;
use App\Services\Logging\Logger;
use App\Services\Logging\LogHandlers\DBLogHandler;
use App\Services\Logging\LogHandlers\FileLogHandler;
use App\Services\Logging\LogHandlers\FileLogHandlerConfig;
use App\Services\Router\RouterError;

// Load configs

Env::Load();

AppConfig::Load();
AssetFilesConfig::Load();
PDOConfig::Load();
FileLogHandlerConfig::Load();

// Configure logging

Logger::RegisterHandler(new FileLogHandler());
Logger::RegisterHandler(new DBLogHandler());

// Configure router

$router = new Router();

$router->OnError(function (RouterError $error): void {
    if ($error->GetType() != RouterError::TYPE_FATAL) {
        return;
    }

    $shutdown_error = $error->GetShutdownError();
    Logger::LogError($shutdown_error['message'], $shutdown_error['file'] . ':' . $shutdown_error['line']);
});

// Configure routes

require_once __DIR__ . '/routes/rootRoutes.php';
require_once __DIR__ . '/routes/categoryRoutes.php';
require_once __DIR__ . '/routes/tagRoutes.php';
require_once __DIR__ . '/routes/assetRoutes.php';
require_once __DIR__ . '/routes/fileRoutes.php';
require_once __DIR__ . '/routes/userRoutes.php';
require_once __DIR__ . '/routes/adminRoutes.php';

// Dispatch request

$router->DispatchRequest();
