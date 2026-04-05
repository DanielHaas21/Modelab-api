<?php

namespace App;

class AppConfig
{
    public const ENV_DEV_MODE = 'DEV_MODE';

    public static $DEV_MODE = false;

    public static function Load()
    {
        self::$DEV_MODE = $_ENV[self::ENV_DEV_MODE] == '1';
    }
}
