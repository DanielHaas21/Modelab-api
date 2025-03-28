<?php

use App\Controllers\CategoryController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/all", CategoryController::GetAllCategories());
$routes->AddPOST("/create", CategoryController::CreateCategory());
$routes->AddPOST("/{id}", CategoryController::GetCategory());
$routes->AddPOST("/{id}/delete", CategoryController::DeleteCategory());

$router->AddRoutes('/category', $routes);
