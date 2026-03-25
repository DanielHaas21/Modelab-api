<?php

use App\Controllers\AdminController;
use App\Middleware\Clearance;
use App\Middleware\MiddlewareController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/log/all", AdminController::SelectAllLogs(), MiddlewareController::UserClearanceMiddleware(Clearance::ADMIN));
$routes->AddPOST("/log/search", AdminController::SearchLogs(), MiddlewareController::UserClearanceMiddleware(Clearance::ADMIN));

$router->AddRoutes('/admin', $routes);
