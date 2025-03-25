<?php

/*
    This is a bash-bat executable script
    !DO NOT INCLUDE IT ANYWHERE!
*/

use App\Database\Exceptions\DatabaseException;
use App\Database\Exceptions\SQLExecutionException;
use App\Database\SQL;
use App\Models\User;

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../config/db.php';

function echoLine(string $msg = ""): void
{
    echo "$msg\n";
}

function echoError(Exception $e): void
{
    echoLine(get_class($e) . ": " . $e->getMessage());
}

$modelClasses = [User::class];

// Check PDO

echoLine("Initializing PDO...");
try {
    SQL::InitPDO();
} catch (DatabaseException $e) {
    echoError($e);
    echoLine("PDO Init Failed");
    exit(1);
}
echoLine("PDO OK");

// Check database
$database = DB_CONFIG['database'];

echoLine();
echoLine("Checking DB '$database'...");
try {
    $sql_com = SQL::Execute(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbname",
        [':dbname' => $database]
    );
    $databaseExists = $sql_com->fetchColumn() == 0;

    if ($databaseExists) {
        SQL::Execute("CREATE DATABASE `$database`");
        echoLine("DB was created.");
    } else {
        echoLine("DB already exists.");
    }
} catch (SQLExecutionException $e) {
    echoError($e);
    echoLine("Checking DB Failed");
    exit(1);
}
echoLine("DB '$database' OK");

// Check models

echoLine();
echoLine("Checking Models...");
foreach ($modelClasses as $modelClass) {
    echoLine("Checking Model $modelClass...");

    $tableName = $modelClass::GetTableName();
    $differences = $modelClass::CompareColumns();

    if (count($differences) > 0) {
        echoLine("Different columns:");
        foreach ($differences as $column => $difference) {
            $reason = $difference['reason'];
            $dbValue = $difference['dbValue'];
            $modelValue = $difference['modelValue'];

            echoLine("  $column:");
            echoLine("    reason : $reason");
            echoLine("    db     : $dbValue");
            echoLine("    model  : $modelValue");
        }

        echoLine("Checking Model $modelClass Failed");
        echoLine("Checking Models Failed");
        exit(1);
    }
}
echoLine("Models OK");
