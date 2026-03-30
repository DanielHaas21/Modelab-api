<?php

require_once __DIR__ . '/autoload.php';

use App\Database\SQL;
use App\Router\Request;
use App\Router\Response;
use App\Router\Router;
use App\Helpers\Loggers\Logger;
use App\Helpers\Loggers\LogHandlers\DBLogHandler;
use App\Helpers\Loggers\LogHandlers\FileLogHandler;
use App\Router\RouterError;

// Configure logging

Logger::RegisterHandler(new FileLogHandler(__DIR__ . '/logs'));
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

require_once __DIR__ . '/routes/categoryRoutes.php';
require_once __DIR__ . '/routes/tagRoutes.php';
require_once __DIR__ . '/routes/assetRoutes.php';
require_once __DIR__ . '/routes/fileRoutes.php';
require_once __DIR__ . '/routes/userRoutes.php';
require_once __DIR__ . '/routes/adminRoutes.php';

$router->AddGET('/', function (Request $req, Response $res): void {
    $res->SetText('Modelab API');
});

$router->AddPOST('/health', function (Request $req, Response $res): void {
    $dbActive = SQL::MiscCheckStatus();
    $res->SetJSON([
        'health' => [
            'timestamp' => time(),
            'services' => ['database' => $dbActive],
            'version' => '1.0'
        ]
    ]);
});

// Dispatch request

$router->DispatchRequest();
