<?php

namespace App\Services\Database;

class SQLUtils
{
    /**
     * Extracts the type from the CREATE TABLE column row sql
     * If SQL is wrong null is returned
     * @param string $sql
     * @return string|null
     */
    public static function GetTypeFromCreateSQL(string $sql): ?string
    {
        $sql = trim($sql);
        if (!preg_match('/^(\w+)/', $sql, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        return $matches[1][0];
    }

    /**
     * Given by the SQL type, returns a parsed JSON ready value
     * @param ?string $value
     * @param string $type
     * @return mixed
     */
    public static function CastFromSQLType(?string $value, string $type)
    {
        $type = trim(strtolower($type));
        switch ($type) {
            case 'int':
            case 'integer':
            case 'smallint':
            case 'tinyint':
            case 'mediumint':
            case 'bigint':
                return $value !== '' ? intval($value) : null;

            case 'float':
            case 'double':
            case 'real':
            case 'decimal':
            case 'numeric':
                return $value !== '' ? floatval($value) : null;

            case 'bit':
                return $value !== '' ? bindec($value) : null;

            case 'boolean':
                return $value !== '' ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : null;

            case 'char':
            case 'varchar':
            case 'tinytext':
            case 'text':
            case 'mediumtext':
            case 'longtext':
            case 'enum':
            case 'set':
                return $value !== '' ? strval($value) : null;

            case 'blob':
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
                return $value !== '' ? base64_encode($value) : null;

            case 'date':
                return $value !== '' ? date('Y-m-d', strtotime($value)) : null;

            case 'datetime':
            case 'timestamp':
                return $value !== '' ? date('c', strtotime($value)) : null;

            case 'time':
                return $value !== '' ? date('H:i:s', strtotime($value)) : null;

            case 'year':
                return $value !== '' ? intval($value) : null;

            case 'json':
                return $value !== '' ? json_decode($value, true) : null;

            default:
                return $value !== '' ? $value : null;
        }
    }
}
