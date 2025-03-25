<?php

namespace App\Database;

use App\Database\SQL;

/**
 * Abstract class of a model that defines a database table
 *
 * use @sql in PHPDoc above a field to define it as a column:
 * <br>\/**
 * <br> \* @sql INT NOT NULL PRIMARY KEY
 * <br>*\/
 * <br>public $id;
 */
abstract class BaseModel
{
    /**
     * Ensures that called class is not the base class
     * @throws \App\Database\DatabaseException
     * @return void
     */
    final private static function CheckNotBase(): void
    {
        if (get_called_class() == BaseModel::class) {
            throw new DatabaseException('Don\'t call methods from the base class');
        }
    }

    /**
     * Gets the columns defined in the class
     * <br>Shouldn't be called from the base class
     * @return array{name: string, sql: string}
     */
    final public static function GetSQLProperties(): array
    {
        self::CheckNotBase();

        $reflectionClass = new \ReflectionClass(get_called_class());
        $properties = $reflectionClass->getProperties();

        $sqlProperties = [];
        foreach ($properties as $property) {
            $docComment = $property->getDocComment();

            if (!$docComment) {
                continue;
            }

            if (preg_match('/@sql\s+(.+)/', $docComment, $matches)) {
                $sqlProperties[] = [
                    'name' => $property->getName(),
                    'sql' => $matches[1]
                ];
            }
        }

        return $sqlProperties;
    }

    /**
     * Gets the name of the table (same as class name)
     * <br>Shouldn't be called from the base class
     * @return string
     */
    final public static function GetTableName(): string
    {
        self::CheckNotBase();

        $className = explode('\\', get_called_class());
        return end($className);
    }

    /**
     * Creates the model table if it doesn't exist
     * <br>Shouldn't be called from the base class
     * @return void
     */
    final public static function Init(): void
    {
        self::CheckNotBase();

        $sqlProperties = static::GetSQLProperties();

        $columns = array_map(
            function ($property): string {
                return '`' . $property['name'] . '` ' . $property['sql'];
            },
            $sqlProperties
        );

        $sql = 'CREATE TABLE IF NOT EXISTS `' . static::GetTableName() . '` (
            ' . implode(',', $columns) . '
        )';

        SQL::Execute($sql);
    }

    /**
     * Creates an instance of the model and sets data
     * <br>Shouldn't be called from the base class
     * @param array $data
     * @throws \App\Database\DatabaseException
     * @return BaseModel
     */
    final public static function CreateFrom(array $data): BaseModel
    {
        self::CheckNotBase();

        $sqlProperties = static::GetSQLProperties();

        $reflectionClass = new \ReflectionClass(get_called_class());
        $model = $reflectionClass->newInstance();

        foreach ($sqlProperties as $property) {
            $name = $property['name'];

            if (!isset($data[$name])) {
                throw new DatabaseException("Missing property in data: $name");
            }

            $reflectionProperty = $reflectionClass->getProperty($name);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($model, $data[$name]);
        }

        return $model;
    }

}
