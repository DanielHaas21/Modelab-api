<?php

use App\Controllers\CategoryController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/all", CategoryController::SelectAll());
$routes->AddPOST("/create", CategoryController::Create());
$routes->AddPOST("/{id}", CategoryController::Select());
$routes->AddPOST("/{id}/delete", CategoryController::Delete());

$router->AddRoutes('/category', $routes);
