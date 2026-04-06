<?php

namespace App\Services\Files;

use App\Configuration\Env;

class AssetFilesConfig
{
    public const ENV_DATA_PATH = 'DATA_PATH';
    public const ENV_DATA_MAX_SIZE_MB = 'DATA_MAX_SIZE_MB';

    public const FILE_GROUP_MODEL = 'model';
    public const FILE_GROUP_AUDIO = 'audio';
    public const FILE_GROUP_IMAGE = 'image';
    public const FILE_GROUP_OTHER = 'other';

    public static $DATA_PATH = '';
    public static $MAX_SIZE_BYTES = 0;
    public static $SUPPORTED_EXTENSIONS = [
        self::FILE_GROUP_MODEL => [],
        self::FILE_GROUP_AUDIO => [],
        self::FILE_GROUP_IMAGE => [],
        self::FILE_GROUP_OTHER => [],
    ];

    public static function Load()
    {
        self::$DATA_PATH = Env::ENV_PATHS_ROOT . $_ENV[self::ENV_DATA_PATH];
        self::$MAX_SIZE_BYTES = intval($_ENV[self::ENV_DATA_MAX_SIZE_MB]) * 1000000;

        require_once __DIR__ . '/../../../config/files.php';
        $supported_types = FILES_CONFIG['supported_extensions'];
        foreach (self::$SUPPORTED_EXTENSIONS as $group => $types) {
            if (!isset($supported_types[$group])) {
                continue;
            }
            self::$SUPPORTED_EXTENSIONS[$group] = $supported_types[$group];
        }
    }
}
