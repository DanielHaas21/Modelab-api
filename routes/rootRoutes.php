<?php

use App\Services\Database\SQL;
use App\Services\Router\Request;
use App\Services\Router\Response;
use App\Services\Router\Routes;

$routes = new Routes();

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

$router->AddRoutes('/', $routes);
