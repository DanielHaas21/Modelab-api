<?php

namespace App\Services\Files;

use App\Configuration\Env;

class AssetFilesConfig
{
    public const ENV_DATA_PATH = 'DATA_PATH';
    public const ENV_DATA_MAX_SIZE_MB = 'DATA_MAX_SIZE_MB';

    public static $DATA_PATH = '';
    public static $MAX_SIZE_BYTES = 0;
    public static $SUPPORTED_TYPES = [
        'model' => [],
        'audio' => [],
        'image' => [],
        'other' => [],
    ];

    public static function Load()
    {
        self::$DATA_PATH = Env::ENV_PATHS_ROOT . $_ENV[self::ENV_DATA_PATH];
        self::$MAX_SIZE_BYTES = intval($_ENV[self::ENV_DATA_MAX_SIZE_MB]) * 1000000;

        require_once __DIR__ . '/../../../config/files.php';
        self::$SUPPORTED_TYPES = FILES_CONFIG['supportedTypes'];
    }
}
