<?php

namespace App\Services\Logging;

use DateTime;
use Exception;

class Logger
{
    /**
     * @var ILogHandler[]
     */
    private static $handlers = [];

    private static function HandleLog(Log $log)
    {
        $exceptions = [];
        foreach (self::$handlers as $handler) {
            try {
                $handler->HandleLog($log);
            } catch (Exception $e) {
                $exceptions[] = $e;
            }
        }

        if (count($exceptions) > 0) {
            $all_messages = 'Exceptions while handling logs: ' . count($exceptions) . ' => ';
            foreach ($exceptions as $exception) {
                $all_messages .= "\n" . $exception->getMessage();
            }
            throw new Exception($all_messages);
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
