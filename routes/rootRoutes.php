<?php

use App\Database\SQL;
use App\Router\Request;
use App\Router\Response;
use App\Router\Routes;

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
