<?php

use App\Controllers\TagController;
use App\Middleware\Clearance;
use App\Middleware\MiddlewareController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/all", TagController::SelectAll());
$routes->AddPOST("/create", TagController::Create(), MiddlewareController::UserClearanceMiddleware(Clearance::ADMIN));
$routes->AddPOST("/{id}", TagController::Select());
$routes->AddPOST("/{id}/delete", TagController::Delete(), MiddlewareController::UserClearanceMiddleware(Clearance::ADMIN));

$router->AddRoutes('/tag', $routes);
