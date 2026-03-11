<?php

use App\Controllers\UserController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/{id}", UserController::Select());

$router->AddRoutes('/user', $routes);
