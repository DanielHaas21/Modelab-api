<?php

require_once __DIR__ . '/autoload.php';

use App\Database\SQL;

SQL::InitPDO();

// $router = new Router();

// $router->AddGET('/', function (Request $req, Response $res): void {
//     // $res->SetText("Modellab API");

//     // $value = SQL::MiscMissingTable("user");
//     // $res->SetText($value);
// });

// $router->DispatchRequest(Request::GetServerRequestURI());
