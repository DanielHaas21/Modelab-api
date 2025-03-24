<?php
    /*
        DB connection string
        Including this file anywhere isnt recommended and isnt required
    */
    $DBservername = "localhost";
    $DBusername = "root";
    $DBpassword = "";
    $DBdatabase = "modelab-api";

    global $db;
    global $dbname;
    $dbname = 'modelab-api';
    
    $db = new PDO("mysql:host=$DBservername;dbname=$DBdatabase", $DBusername, $DBpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

