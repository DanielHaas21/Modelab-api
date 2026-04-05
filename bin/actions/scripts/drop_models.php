<?php

use App\Database\Exceptions\SQLExecutionException;
use App\Database\PDOConfig;
use App\Helpers\Env;
use App\Models\Asset;
use App\Services\Files\AssetFilesService;

require_once __DIR__ . '/utils.php';

echoLine();
echoLine('Droping Models...');

Env::Load();
PDOConfig::Load();

echoLine('Clearing data...');

$asset_file_service = new AssetFilesService();

/**
 * @var Asset[]
 */
$assets = Asset::SelectAllModels();
foreach ($assets as $asset) {
    $asset_file_service->DeleteAsset($asset);
}

foreach (DB_ALL_MODELS as $modelClass) {
    echoLine('Dropping Model ' . $modelClass . '...');
    try {
        $modelClass::Drop();
    } catch (SQLExecutionException $e) {
        echoError($e);
        echoLine('Dropping Model ' . $modelClass . ' Failed');
        echoLine('Dropping Models Failed');
        exit(1);
    }
}

echoLine('Dropping Models OK');
