<?php

use App\Controllers\UserController;
use App\Middleware\MiddlewareController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/login", UserController::Login());
$routes->AddPOST("/info", UserController::Select(), MiddlewareController::UserMiddleware());

$router->AddRoutes('/user', $routes);
