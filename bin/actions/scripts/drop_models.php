<?php

use App\Database\Exceptions\SQLExecutionException;
use App\Database\PDOConfig;
use App\Helpers\Env;

require_once __DIR__ . '/utils.php';

echoLine();
echoLine('Droping Models...');

Env::Load();
PDOConfig::Load();

foreach (DB_ALL_MODELS as $modelClass) {
    echoLine('Dropping Model $modelClass...');
    try {
        $wasDropped = $modelClass::Drop();

        if ($wasDropped) {
            echoLine('Done');
        } else {
            echoLine('Nothing to drop');
        }
    } catch (SQLExecutionException $e) {
        echoError($e);
        echoLine('Dropping Model $modelClass Failed');
        echoLine('Dropping Models Failed');
        exit(1);
    }
}

echoLine('Dropping Models OK');
