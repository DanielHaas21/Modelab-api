<?php

use App\Controllers\FileController;
use App\Middleware\Clearance;
use App\Middleware\MiddlewareController;
use App\Services\Router\Routes;

$routes = new Routes();

$routes->AddPOST("/supported", FileController::SelectSupportedFileTypes());

$routes->AddGET("/{id}/preview", FileController::SelectPreview());
$routes->AddGET("/{id}", FileController::SelectAsset(), MiddlewareController::UserClearanceMiddleware(Clearance::USER));

$routes->AddPOST("/{id}/meta", FileController::SelectAssetMeta());

$router->AddRoutes('/file', $routes);
