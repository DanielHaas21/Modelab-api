<?php

namespace App\Helpers\Loggers\LogHandlers;

use App\Helpers\Env;

final class FileLogHandlerConfig
{
    public const ENV_LOG_PATH = 'LOG_PATH';

    public static $LOG_PATH = '';

    public static function Load()
    {
        self::$LOG_PATH = Env::ENV_PATHS_ROOT . $_ENV[self::ENV_LOG_PATH];
    }
}
