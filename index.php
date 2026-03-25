<?php

require_once __DIR__ . '/autoload.php';

use App\Helpers\Loggers\Logger;
use App\Helpers\Loggers\LogHandlers\DBLogHandler;
use App\Helpers\Loggers\LogHandlers\FileLogHandler;
use App\Router\RequestError;
use App\Router\Response;

// Configure root logging

Logger::RegisterHandler(new FileLogHandler(__DIR__ . '/logs'));
Logger::RegisterHandler(new DBLogHandler());

// Handle compile errors

register_shutdown_function(function () {
    $error = error_get_last();
    $fatalTypes = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR];

    if ($error !== null && in_array($error['type'], $fatalTypes)) {
        Logger::LogError("FATAL: " . $error['message'], $error['file']);

        $res = new Response();
        $res->SetError(new RequestError(500, 'server', 'Internal fatal error'));
        $res->Respond();
    }
});

// App

require_once __DIR__ . '/app.php';
