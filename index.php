<?php

require_once __DIR__ . '/autoload.php';

use App\Database\PDOConfig;
use App\Database\SQL;
use App\Helpers\AppConfig;
use App\Helpers\Env;
use App\Helpers\Files\AssetFileManagerConfig;
use App\Router\Request;
use App\Router\Response;
use App\Router\Router;
use App\Helpers\Loggers\Logger;
use App\Helpers\Loggers\LogHandlers\DBLogHandler;
use App\Helpers\Loggers\LogHandlers\FileLogHandler;
use App\Helpers\Loggers\LogHandlers\FileLogHandlerConfig;
use App\Router\RouterError;

// Load configs

Env::Load();

AppConfig::Load();
AssetFileManagerConfig::Load();
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
