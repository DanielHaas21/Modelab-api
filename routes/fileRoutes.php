<?php

use App\Controllers\FileController;
use App\Middleware\Clearance;
use App\Middleware\MiddlewareController;
use App\Router\Routes;

$routes = new Routes();

$routes->AddGET("/supported", FileController::SelectSupportedFileTypes());

$routes->AddGET("/{id}/preview", FileController::SelectPreview());
$routes->AddGET("/{id}", FileController::SelectAsset(), MiddlewareController::UserClearanceMiddleware(Clearance::USER));

$router->AddRoutes('/file', $routes);
