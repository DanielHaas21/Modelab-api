<?php

require_once __DIR__ . '/autoload.php';

use App\Database\SQL;
use App\Helpers\Loggers\Logger;
use App\Helpers\Loggers\LogHandlers\FileLogHandler;
use App\Router\Request;
use App\Router\RequestError;
use App\Router\Response;
use App\Router\Router;

// things go bomboclat if this isnt here bcs preflight cors
header("Access-Control-Allow-Origin: *", true);
header("Access-Control-Allow-Methods: GET, POST, OPTIONS", true);
header("Access-Control-Allow-Headers: Content-Type, Authorization", true);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configure logging

Logger::RegisterHandler(new FileLogHandler(__DIR__ . '/logs'));

// Listen for errors

ini_set('display_errors', '0');
error_reporting(0);
register_shutdown_function(function () {
    $error = error_get_last();

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

    if ($error !== null && in_array($error['type'], $fatalTypes)) {
        Logger::LogError($error['message'], $error['file']);

        $res = new Response();
        $res->SetError(new RequestError(500, 'server', 'Internal error'));
        $res->Respond();
    }
});

// Setup routes

$router = new Router();

require_once __DIR__ . '/routes/categoryRoutes.php';
require_once __DIR__ . '/routes/tagRoutes.php';
require_once __DIR__ . '/routes/assetRoutes.php';
require_once __DIR__ . '/routes/fileRoutes.php';
require_once __DIR__ . '/routes/userRoutes.php';

$router->AddGET('/', function (Request $req, Response $res): void {
    $res->SetText('Modelab API');
});

$router->AddGET('/status', function (Request $req, Response $res): void {
    $dbActive = SQL::MiscCheckStatus();

    $res->SetJSON([
        'timestamp' => time(),
        'services' => [
            'database' => $dbActive,
        ],
        'version' => '1.0',
        'php_version' => PHP_VERSION,
    ]);
});

// Start server

$router->DispatchRequest(Request::GetServerRequestURI());
