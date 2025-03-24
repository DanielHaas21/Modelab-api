<?php
/*
    This is a bash-bat executable script
    !DO NOT INCLUDE IT ANYWHERE!
*/

$DBservername = "localhost";
$DBusername = "root";
$DBpassword = "";
$DBdatabase = "modelab-api";

try {
    // Create a new PDO connection to the MySQL server (without selecting a database)
    $db = new PDO("mysql:host=$DBservername", $DBusername, $DBpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the database exists
    $query = $db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbname");
    $query->bindParam(':dbname', $DBdatabase);
    $query->execute();
    
    // If the database does not exist, create it
    if ($query->fetchColumn() == 0) {

        $createDbQuery = "CREATE DATABASE `$DBdatabase`";
        $db->exec($createDbQuery);
        echo "Database '$DBdatabase' created successfully. \n";
    } else {
        echo "Database '$DBdatabase' already exists.  \n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>