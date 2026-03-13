<?php

use App\Controllers\FileController;
use App\Middleware\Clearance;
use App\Middleware\MiddlewareController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddGET("/{id}", FileController::Select());

$router->AddRoutes('/file', $routes);
