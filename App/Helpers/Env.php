<?php

namespace App\Helpers;

use Exception;

/**
 * ENV
 *
 * With this class you can use .env variables
 * @var Env[] is the superglobal you can call the ENV variables with
 *
 *
 * Usage:
 * To use .env, on top of your file call this method:
 * @method void Load()
 */
final class Env
{
    public const ENV_PATHS_ROOT = __DIR__ . '/../..';
    public const ENV_PATH = self::ENV_PATHS_ROOT . '/.env';

    /**
     * Loads an .env file
     *
     * @param string $filePath must be relative to where the script is being executed
     * @throws Exception
     * @return void
     */
    public static function Load(string $filePath = ''): void
    {
        if (strlen($filePath) == 0) {
            $filePath = self::ENV_PATH;
        }

        if (!file_exists($filePath)) {
            throw new Exception(".env file not found at: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);

            $key   = trim($key);
            $value = trim($value, "\"'");

            $_ENV[$key] = $value;
        }
    }
}
