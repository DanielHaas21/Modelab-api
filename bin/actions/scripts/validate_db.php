<?php

use App\Database\Exceptions\DatabaseException;
use App\Database\Exceptions\SQLExecutionException;
use App\Database\PDOConfig;
use App\Database\SQL;
use App\Helpers\Env;

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/../../../autoload.php';

echoLine();
echoLine('Validating DB...');

Env::Load();
PDOConfig::Load();

// Check PDO
echoLine('Initializing PDO...');
try {
    SQL::InitPDO();
} catch (DatabaseException $e) {
    echoError($e);
    echoLine('PDO Init Failed');
    exit(1);
}
echoLine('PDO OK');

// Not needed, since SQL::InitPDO() already tries to connect to the database

// // Check database
// $database = PDOConfig::$DATABASE;

// echoLine('Checking DB \'' . $database . '\'...');
// try {
//     $sql_com = SQL::MiscExecute(
//         "SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbname",
//         [':dbname' => $database]
//     );
//     $databaseExists = $sql_com->fetchColumn() == 0;

//     if ($databaseExists) {
//         SQL::MiscExecute("CREATE DATABASE `$database`");
//         echoLine('DB was created.');
//     } else {
//         echoLine('DB already exists.');
//     }
// } catch (SQLExecutionException $e) {
//     echoError($e);
//     echoLine('Checking DB Failed');
//     exit(1);
// }
// echoLine('DB \'' . $database . '\' OK');

// Check models
echoLine('Checking Models...');
foreach (DB_ALL_MODELS as $modelClass) {
    echoLine('Checking Model ' . $modelClass . '...');
    try {
        $modelClass::Init();
    } catch (SQLExecutionException $e) {
        echoError($e);
        echoLine('Checking Model ' . $modelClass . ' Failed');
        echoLine('Checking Models Failed');
        exit(1);
    }
}
echoLine('Models OK');

echoLine('DB OK');
