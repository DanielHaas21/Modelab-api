<?php

use App\Services\Database\Exceptions\SQLExecutionException;
use App\Configuration\Env;
use App\Services\Files\AssetFilesService;

require_once __DIR__ . '/utils.php';

echoLine();
echoLine('Droping Models...');

Env::Load();

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

echoLine('Clearing files...');

$service = new AssetFilesService();
$service->RemoveStrayAssetFiles();

echoLine('Dropping Models OK');
