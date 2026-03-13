<?php

use App\Controllers\FileController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddGET("/{id}", FileController::Select());

$router->AddRoutes('/file', $routes);
