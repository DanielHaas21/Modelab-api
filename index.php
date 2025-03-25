<?php

require_once __DIR__ . '/autoload.php';

use App\Database\SQL;
use App\Router\Request;
use App\Router\Response;
use App\Router\Router;

$router = new Router();

$router->AddGET('/', function (Request $req, Response $res): void {
    $res->SetText("Modellab API");
});

$router->AddGET('/info', function (Request $req, Response $res): void {
    SQL::InitPDO();

    $phpVersion = phpversion();

    $res->SetText("Database initialized, php: $phpVersion");
});


$router->DispatchRequest(Request::GetServerRequestURI());
