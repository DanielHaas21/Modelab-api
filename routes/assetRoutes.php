<?php

use App\Controllers\AssetController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/all", AssetController::SelectAll());
$routes->AddPOST("/create", AssetController::Create());
$routes->AddPOST("/{id}", AssetController::Select());
$routes->AddPOST("/{id}/delete", AssetController::Delete());
$routes->AddPOST("/{id}/files", AssetController::SelectFiles());

$router->AddRoutes('/asset', $routes);
