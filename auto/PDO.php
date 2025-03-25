<?php

/*
    This is a bash-bat executable script
    !DO NOT INCLUDE IT ANYWHERE!
*/

require_once __DIR__ . '/../config/db.php';

function echoLine(string $msg): void
{
    echo "$msg\n";
}

function echoError(Exception $e): void
{
    echoLine(get_class($e) . ": " . $e->getMessage());
}

$serverName = DB_CONFIG['servername'];
$username   = DB_CONFIG['username'];
$password   = DB_CONFIG['password'];
$database   = DB_CONFIG['database'];

// Check PDO

echoLine("Initializing PDO...");
$db = null;
try {
    $db = new PDO("mysql:host=$serverName", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echoError($e);
    echoLine("PDO Failed");
    exit(1);
}
echoLine("PDO OK");

// Check database

echoLine("Checking DB '$database'...");
try {
    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbname";
    $sql_com = $db->prepare($sql);
    $sql_com->execute([
        ':dbname' => $database
    ]);
    $databaseExists = $sql_com->fetchColumn() == 0;

    if ($databaseExists) {
        $sql = "CREATE DATABASE `$database`";
        $db->exec($sql);
        echoLine("DB was created.");
    } else {
        echoLine("DB already exists.");
    }
} catch (PDOException $e) {
    echoError($e);
    echoLine("Checking DB Failed");
    exit(1);
}
echoLine("DB '$database' OK");
