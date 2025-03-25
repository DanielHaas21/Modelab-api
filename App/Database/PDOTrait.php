<?php

namespace App\Database;

use App\Database\Exceptions\DatabaseException;

require_once __DIR__ . '/../../config/db.php';

/**
 * PDO trait eliminates repeated including of the db config, since its directly included into the db library itself
 */
trait PDOTrait
{
    /**
     * PDO Instance
     * @var \PDO
     */
    protected static $pdo = null;

    /**
     * Initializes PDO connection
     * @throws DatabaseException
     * @return void
     */
    public static function InitPDO(): void
    {
        if (self::$pdo != null) {
            return;
        }

        $DBservername = DB_CONFIG['servername'];
        $DBusername   = DB_CONFIG['username'];
        $DBpassword   = DB_CONFIG['password'];
        $DBdatabase   = DB_CONFIG['database'];

        try {
            self::$pdo = new \PDO("mysql:host=$DBservername;dbname=$DBdatabase", $DBusername, $DBpassword);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to init PDO: " . $e->getMessage());
        }

    }
}
