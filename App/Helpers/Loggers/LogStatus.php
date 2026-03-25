<?php

namespace App\Helpers\Loggers;

use Exception;

final class LogStatus
{
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const ERROR = 'error';
    public const DEBUG = 'debug';

    public static function GetName(string $status)
    {
        switch ($status) {
            case self::INFO:
                return 'INFO';
            case self::WARNING:
                return 'WARNING';
            case self::ERROR:
                return 'ERROR';
            case self::DEBUG:
                return 'DEBUG';
            default:
                throw new Exception('Unknown status: \'' . $status . '\'');
        }
    }
}
