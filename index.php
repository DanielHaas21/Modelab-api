<?php

require_once __DIR__ . '/autoload.php';

use App\Router\Request;
use App\Router\Response;
use App\Router\Router;

$router = new Router();

$router->AddGET('/', function (Request $req, Response $res): void 
{
    $res->SetText("Modellab API");
});

$router->DispatchRequest(Request::GetServerRequestURI());
