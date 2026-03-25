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

// Handle errors

ini_set('display_errors', '0');
error_reporting(0);
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        Logger::LogError($error['message'], $error['file'] . ':' . $error['line']);

        // make sure to send a response
        $res = new Response();
        $res->SetError(new RequestError(500, 'server', 'Internal error'));
        $res->Respond();
    }
});

// App

require_once __DIR__ . '/app.php';
