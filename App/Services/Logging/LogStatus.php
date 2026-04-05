<?php

namespace App\Services\Logging;

use Exception;

final class LogStatus
{
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const ERROR = 'error';
    public const DEBUG = 'debug';

    public const ALL_STATUSES = [
        self::INFO,
        self::WARNING,
        self::ERROR,
        self::DEBUG
    ];

    public static function IsStatus(string $status)
    {
        return \in_array($status, self::ALL_STATUSES, true);
    }

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
