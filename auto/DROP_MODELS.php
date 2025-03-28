<?php

/*
    This is a bash-bat executable script
    !DO NOT INCLUDE IT ANYWHERE!
*/

use App\Database\Exceptions\SQLExecutionException;

require_once __DIR__ . '/utils.php';

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../config/db.php';

// Drop models
echoLine("Droping Models...");
foreach (ALL_MODELS as $modelClass) {
    echoLine("Dropping Model $modelClass...");
    try {
        $wasDropped = $modelClass::Drop();

        if ($wasDropped) {
            echoLine("Done");
        } else {
            echoLine("Nothing to drop");
        }
    } catch (SQLExecutionException $e) {
        echoError($e);
        echoLine("Dropping Model $modelClass Failed");
        echoLine("Dropping Models Failed");
        exit(1);
    }
}
echoLine("Dropping Models OK");
