<?php

require_once __DIR__ . '/autoload.php';

use App\Router\Request;
use App\Router\Response;
use App\Router\Router;

$router = new Router();

require_once __DIR__ . '/routes/categoryRoutes.php';
require_once __DIR__ . '/routes/tagRoutes.php';
require_once __DIR__ . '/routes/assetRoutes.php';
require_once __DIR__ . '/routes/fileRoutes.php';

$router->AddGET('/', function (Request $req, Response $res): void {
    $res->SetText('Modelab API');
});

$router->AddGET('/info', function (Request $req, Response $res): void {
    $phpVersion = phpversion();

    $res->SetText("php: $phpVersion");
});


$router->DispatchRequest(Request::GetServerRequestURI());
