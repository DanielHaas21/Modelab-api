<?php

require_once __DIR__ . '/autoload.php';

use App\Router\Request;
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

$router = new Router();

require_once __DIR__ . '/routes/categoryRoutes.php';
require_once __DIR__ . '/routes/tagRoutes.php';
require_once __DIR__ . '/routes/assetRoutes.php';
require_once __DIR__ . '/routes/fileRoutes.php';
require_once __DIR__ . '/routes/userRoutes.php';

$router->AddGET('/', function (Request $req, Response $res): void {
    $phpVersion = phpversion();

    $res->SetText('Modelab API, php: ' . $phpVersion);
});

$router->DispatchRequest(Request::GetServerRequestURI());
