<?php

namespace App\Services\Database;

use App\Services\Database\Exceptions\DatabaseException;

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

        $DBservername = PDOConfig::$SERVERNAME;
        $DBusername   = PDOConfig::$USERNAME;
        $DBpassword   = PDOConfig::$PASSWORD;
        $DBdatabase   = PDOConfig::$DATABASE;

        try {
            self::$pdo = new \PDO("mysql:host=$DBservername;dbname=$DBdatabase", $DBusername, $DBpassword);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new DatabaseException("Failed to init PDO: " . $e->getMessage());
        }

    }
}
