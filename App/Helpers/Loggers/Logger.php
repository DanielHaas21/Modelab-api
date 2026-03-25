<?php

namespace App\Helpers\Loggers;

use DateTime;

class Logger
{
    /**
     * @var ILogHandler[]
     */
    private static $handlers = [];

    private static function HandleLog(Log $log)
    {
        foreach (self::$handlers as $handler) {
            $handler->HandleLog($log);
        }
    }

    public static function RegisterHandler(ILogHandler $handler)
    {
        self::$handlers[] = $handler;
    }

    public static function Log(Log $log)
    {
        self::HandleLog($log);
    }

    public static function LogInfo(string $message, string $origin)
    {
        $log = new Log($message, LogStatus::INFO, $origin, new DateTime());
        self::Log($log);
    }

    public static function LogWarning(string $message, string $origin)
    {
        $log = new Log($message, LogStatus::WARNING, $origin, new DateTime());
        self::Log($log);
    }

    public static function LogError(string $message, string $origin)
    {
        $log = new Log($message, LogStatus::ERROR, $origin, new DateTime());
        self::Log($log);
    }

    public static function LogDebug(string $message, string $origin)
    {
        $log = new Log($message, LogStatus::DEBUG, $origin, new DateTime());
        self::Log($log);
    }
}
