<?php

use App\Configuration\Env;
use App\Services\Files\AssetFilesConfig;

require_once __DIR__ . '/utils.php';

echoLine('Validating files config...');

Env::Load();
AssetFilesConfig::Load();

echoLine('Files config OK');

echoLine('Validating Asset files...');

$data_path = AssetFilesConfig::$DATA_PATH;

if (!is_dir($data_path)) {
    echoLine('Data folder not found, attempting to create...');

    umask(0);
    // Source - https://stackoverflow.com/a/37270421
    // Posted by Oldskool, modified by community. See post 'Timeline' for change history
    // Retrieved 2026-04-05, License - CC BY-SA 3.0
    if (!mkdir($data_path, 0777, true)) {
        echoLine('Data folder failed to create');
        exit(1);
    }

    if (!chmod($data_path, 0777)) {
        echoLine('Data folder failed to set permissions');
        rmdir($data_path);
        exit(1);
    }
}

echoLine('Asset files OK');
