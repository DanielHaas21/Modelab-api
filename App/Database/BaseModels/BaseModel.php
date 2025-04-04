<?php

namespace App\Database\BaseModels;

use App\Database\Exceptions\DatabaseException;
use App\Database\SQL;
use App\Database\SQLUtils;

/**
 * Abstract base class of a model that defines a database table.
 *
 * The model static class implements basic CRUD operations.
 * Each model instance CRUD operations uses the static methods.
 *
 * Use:
 * - @sql in PHPDoc above a field to define it as a column:
 *
 * /* @sql INT NOT NULL PRIMARY KEY *\/
 * public $id;
 *
 * - @isPrimaryKey to mark the field as a primary key
 * - @isUnique to mark the field as unique
 */
abstract class BaseModel
{
    /**
     * Ensures that called class is not the base class
     * @throws DatabaseException
     * @return void
     */
    final protected static function CheckNotBase(): void
    {
        if (get_called_class() == BaseModel::class) {
            throw new DatabaseException('Don\'t call methods from the base class');
        }
    }

    /**
     * Gets the columns defined in the class
     *
     * Shouldn't be called from the base class
     * @throws DatabaseException
     * @return array{type: string, field: string, sql: string, isPrimaryKey: bool, isUnique: bool}
     */
    final public static function GetSQLProperties(): array
    {
        static::CheckNotBase();

        $reflectionClass = new \ReflectionClass(get_called_class());
        $properties = $reflectionClass->getProperties();

        $sqlProperties = [];
        foreach ($properties as $property) {
            $docComment = $property->getDocComment();

            if (!$docComment) {
                continue;
            }

            $isDBColumn = preg_match('/@sql\s+(.+)/', $docComment, $sqlMatches);

            if (!$isDBColumn) {
                continue;
            }

            $isPrimaryKey = preg_match('/@isPrimaryKey/', $docComment);
            $isUnique = preg_match('/@isUnique/', $docComment);

            $sql = $sqlMatches[1];
            $sqlProperties[] = [
                'field' => $property->getName(),
                'sql' => $sql,
                'type' => SQLUtils::GetTypeFromCreateSQL($sql),
                'isPrimaryKey' => $isPrimaryKey,
                'isUnique' => $isUnique,
            ];
        }

        return $sqlProperties;
    }

    /**
     * Gets the name of the table (same as class name)
     *
     * Shouldn't be called from the base class
     * @return string
     */
    final public static function GetTableName(): string
    {
        static::CheckNotBase();

        $className = explode('\\', get_called_class());
        return end($className);
    }


    /**
     * Creates an instance of the model and sets data
     *
     * Shouldn't be called from the base class
     * @param array $data ["column" => "value"]
     * @throws DatabaseException
     * @return object
     */
    final public static function CreateFrom(array $data): object
    {
        static::CheckNotBase();

        $sqlProperties = static::GetSQLProperties();

        $reflectionClass = new \ReflectionClass(get_called_class());
        $model = $reflectionClass->newInstance();

        foreach ($sqlProperties as $property) {
            $name = $property['field'];
            $type = $property['type'];

            if (!isset($data[$name])) {
                throw new DatabaseException("Missing property in data: $name");
            }

            if (!$reflectionClass->hasProperty($name)) {
                throw new DatabaseException("Missing property in class: $name");
            }

            $reflectionProperty = $reflectionClass->getProperty($name);
            $value = SQLUtils::CastFromSQLType($data[$name], $type);
            $reflectionProperty->setValue($model, $value);
        }

        return $model;
    }

    /**
     * Creates the model table, if it doesn't exist
     *
     * Shouldn't be called from the base class
     * @return void
     */
    final public static function Init(): void
    {
        static::CheckNotBase();

        if (SQL::MiscTableExists(static::GetTableName())) {
            return;
        }

        $properties = static::GetSQLProperties();

        $columns = [];
        $primaryKeys = [];
        $unique = [];
        foreach ($properties as $property) {
            $sqlField = '`' . $property['field'] . '`';

            $columns[] =  $sqlField . ' ' . $property['sql'];

            if ($property['isPrimaryKey']) {
                $primaryKeys[] = $sqlField;
            }
            if ($property['isUnique']) {
                $unique[] = $sqlField;
            }
        }

        $columnDefinitions = implode(',', $columns);
        $primaryKeysSql = count($primaryKeys) == 0
            ? ''
            : ',PRIMARY KEY (' . implode(',', $primaryKeys) . ')';
        $uniqueSql = count($unique) == 0
            ? ''
            : ',UNIQUE (' . implode(',', $unique) . ')';

        $tableName = static::GetTableName();
        $sql = "CREATE TABLE `$tableName` (
            $columnDefinitions
            $primaryKeysSql
            $uniqueSql
        )";
        SQL::MiscExecute($sql);
    }

    /**
     * Drops the model table, if it exists
     *
     * Shouldn't be called from the base class
     * @return bool Whether the model didn't already exist
     */
    public static function Drop(): bool
    {
        static::CheckNotBase();

        if (!SQL::MiscTableExists(static::GetTableName())) {
            return false;
        }

        $tableName = static::GetTableName();
        $sql = "DROP TABLE `$tableName`";
        SQL::MiscExecute($sql);

        return true;
    }

    /**
     * Truncates the model table
     *
     * Shouldn't be called from the base class
     * @return void
     */
    public static function Truncate(): void
    {
        static::CheckNotBase();
        static::Init();

        $tableName = static::GetTableName();
        $sql = "TRUNCATE TABLE `$tableName`";
        SQL::MiscExecute($sql);
    }

    /**
     * Select datas from DB with where, creates models
     *
     * Shouldn't be called from the base class
     * @param string $condition
     * @param array $params
     * @param string $columns
     * @return object[]
     */
    public static function SelectWhereModels(string $condition, array $params, string $columns = '*'): array
    {
        static::CheckNotBase();
        static::Init();

        $datas = SQL::SelectDataWithCondition(static::GetTableName(), $columns, $condition, $params);
        $models = array_map(function ($data) {
            return static::CreateFrom($data);
        }, $datas);

        return $models;
    }

    /**
     * Select all datas from DB, creates models
     *
     * Shouldn't be called from the base class
     * @return object[]
     */
    public static function SelectAllModels(): array
    {
        static::CheckNotBase();
        static::Init();

        $datas = SQL::SelectData(
            static::GetTableName(),
            '*'
        );
        $models = array_map(function ($data) {
            return static::CreateFrom($data);
        }, $datas);

        return $models;
    }

    /**
     * Select all datas from DB, creates models
     *
     * Shouldn't be called from the base class
     * @return object[]
     */
    public static function SelectAllModelsLimited(int $count, int $offset): array
    {
        static::CheckNotBase();
        static::Init();

        $datas = SQL::SelectDataWithCondition(
            static::GetTableName(),
            '*',
            'TRUE LIMIT :p_count OFFSET :p_offset',
            [
            ':p_count' => max(0, $count),
            ':p_offset' => max(0, $offset),
        ]
        );
        $models = array_map(function ($data) {
            return static::CreateFrom($data);
        }, $datas);

        return $models;
    }

    /**
     * Inserts data into DB
     *
     * Shouldn't be called from the base class
     * @param BaseModelId $model
     * @return int Inserted row ID
     */
    public static function InsertModel(BaseModel $model): int
    {
        static::CheckNotBase();
        static::Init();

        return SQL::InsertData(static::GetTableName(), $model->GetData());
    }

    abstract public static function UpdateModel(BaseModel $model): void;
    abstract public static function DeleteModel(BaseModel $model): bool;

    /**
     * Constructs the model and initializes the DB table
     */
    public function __construct()
    {
        static::Init();
    }

    /**
     * Inserts into the DB
     * @return void
     */
    public function Insert(): int
    {
        return static::InsertModel($this);
    }

    /**
     * Updates the DB row based on id
     * @return void
     */
    public function Update(): void
    {
        static::UpdateModel($this);
    }

    /**
     * Deletes from the DB based on id
     * @return void
     */
    public function Delete(): bool
    {
        return static::DeleteModel($this);
    }

    /**
     * Constructs an array of data from the model as is
     * @throws DatabaseException
     * @return array ["column" => "value"]
     */
    final public function GetData(): array
    {
        $sqlProperties = static::GetSQLProperties();
        $reflectionClass = new \ReflectionClass(get_called_class());

        $data = [];

        foreach ($sqlProperties as $property) {
            $name = $property['field'];

            if (!$reflectionClass->hasProperty($name)) {
                throw new DatabaseException("Missing property in class: $name");
            }

            $reflectionProperty = $reflectionClass->getProperty($name);
            $value = $reflectionProperty->getValue($this);

            $data[$name] = $value;
        }

        return $data;
    }

}
