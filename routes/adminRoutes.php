<?php

use App\Controllers\AdminController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/log/all", AdminController::SelectAllLogs());
$routes->AddPOST("/log/search", AdminController::SearchLogs());

$router->AddRoutes('/admin', $routes);
