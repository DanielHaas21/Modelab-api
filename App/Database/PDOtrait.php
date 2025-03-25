<?php

namespace App\Database;

require_once __DIR__ . '/../../config/db.php';

/**
 * PDO trait eliminates repeated including of the db config, since its directly included into the db library itself
 */
trait PDO
{
    protected static $pdo = null;
    public static function InitPDO()
    {
        if (self::$pdo != null) {
            return;
        }

        $DBservername = DB_CONFIG['servername'];
        $DBusername   = DB_CONFIG['username'];
        $DBpassword   = DB_CONFIG['password'];
        $DBdatabase   = DB_CONFIG['database'];

        self::$pdo = new \PDO("mysql:host=$DBservername;dbname=$DBdatabase", $DBusername, $DBpassword);
        self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }
}
