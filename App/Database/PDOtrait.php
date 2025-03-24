<?php 
namespace App\Database;

require_once '../../config/db.php';

/**
 * PDO trait eliminates repeated including of the db config, since its directly included into the db library itself
 */
trait PDO{
    protected static $pdo; 
    public static function initPDO(){
        global $db;
        self::$pdo = $db;
    }    
}
