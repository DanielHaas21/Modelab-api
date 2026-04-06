<?php

use App\Controllers\AssetController;
use App\Controllers\FileController;
use App\Middleware\Clearance;
use App\Middleware\MiddlewareController;
use App\Services\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/all", AssetController::SelectAll());
$routes->AddPOST("/search", AssetController::Search());
$routes->AddPOST("/create", AssetController::Create(), MiddlewareController::UserClearanceMiddleware(Clearance::ADMIN));
$routes->AddPOST("/{id}", AssetController::Select());
$routes->AddPOST("/{id}/delete", AssetController::Delete(), MiddlewareController::UserClearanceMiddleware(Clearance::ADMIN));
$routes->AddPOST("/{id}/update", AssetController::Update(), MiddlewareController::UserClearanceMiddleware(Clearance::ADMIN));
$routes->AddPOST("/{id}/files", FileController::SelectFiles());

$router->AddRoutes('/asset', $routes);
