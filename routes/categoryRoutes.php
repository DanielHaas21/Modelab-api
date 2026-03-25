<?php

use App\Controllers\CategoryController;
use App\Middleware\Clearance;
use App\Middleware\MiddlewareController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/all", CategoryController::SelectAll());
$routes->AddPOST("/{id}", CategoryController::Select());
$routes->AddPOST("/create", CategoryController::Create(), MiddlewareController::UserClearanceMiddleware(Clearance::ADMIN));
$routes->AddPOST("/{id}/delete", CategoryController::Delete(), MiddlewareController::UserClearanceMiddleware(Clearance::ADMIN));

$router->AddRoutes('/category', $routes);
