<?php

use App\Controllers\CategoryController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/all", CategoryController::GetAllCategories());
$routes->AddPOST("/create", CategoryController::CreateCategory());

$router->AddRoutes('/category', $routes);
