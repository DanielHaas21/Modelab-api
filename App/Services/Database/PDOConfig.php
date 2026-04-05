<?php

namespace App\Services\Database;

final class PDOConfig
{
    public const ENV_SERVERNAME = 'DB_SERVERNAME';
    public const ENV_USERNAME = 'DB_USERNAME';
    public const ENV_PASSWORD = 'DB_PASSWORD';
    public const ENV_DATABASE = 'DB_DATABASE';

    public static $SERVERNAME = '';
    public static $USERNAME = '';
    public static $PASSWORD = '';
    public static $DATABASE = '';

    public static function Load()
    {
        self::$SERVERNAME = $_ENV[self::ENV_SERVERNAME];
        self::$USERNAME = $_ENV[self::ENV_USERNAME];
        self::$PASSWORD = $_ENV[self::ENV_PASSWORD];
        self::$DATABASE = $_ENV[self::ENV_DATABASE];
    }
}
