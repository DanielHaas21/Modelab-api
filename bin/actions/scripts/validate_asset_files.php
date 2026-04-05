<?php

use App\Helpers\Env;
use App\Helpers\Files\AssetFileManagerConfig;
use App\Validators\FILESvalidator;

require_once __DIR__ . '/utils.php';

require_once __DIR__ . '/../../../config/files.example.php';

echoLine('Validating files config...');

Env::Load();

$FILESvalidator = new FILESvalidator(FILES_CONFIG);
$FILESvalidator->Run();

AssetFileManagerConfig::Load();

echoLine('Files config OK');

echoLine('Validating Asset files...');

$data_path = AssetFileManagerConfig::$DATA_PATH;

if (!is_dir($data_path)) {
    echoLine('Data folder not found, attempting to create...');

    // Source - https://stackoverflow.com/a/37270421
    // Posted by Oldskool, modified by community. See post 'Timeline' for change history
    // Retrieved 2026-04-05, License - CC BY-SA 3.0
    if (!mkdir($data_path, 0777, true)) {
        echoLine('Data folder failed to create');
        exit(1);
    }

    if (!chmod($data_path, 999)) {
        echoLine('Data folder failed to set permissions');
        rmdir($data_path);
        exit(1);
    }
}

echoLine('Asset files OK');
