<?php

use App\Controllers\TagController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/all", TagController::SelectAll());
$routes->AddPOST("/create", TagController::Create());
$routes->AddPOST("/{id}", TagController::Select());
$routes->AddPOST("/{id}/delete", TagController::Delete());

$router->AddRoutes('/tag', $routes);
