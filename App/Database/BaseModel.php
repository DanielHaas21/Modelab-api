<?php

namespace App\Database;

use App\Database\Exceptions\DatabaseException;
use App\Database\SQL;

/**
 * Abstract base class of a model that defines a database table.
 *
 * The model static class implements basic CRUD operations.
 * Each model instance CRUD operations uses the static methods.
 *
 * Model uses INT ID as a primary key.
 *
 * Use:
 * - @sql in PHPDoc above a field to define it as a column:
 *
 * /* @sql INT NOT NULL PRIMARY KEY *\/
 * public $id;
 */
abstract class BaseModel
{
    /**
     * Ensures that called class is not the base class
     * @throws DatabaseException
     * @return void
     */
    private static function CheckNotBase(): void
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
     * @return array{type: string, field: string, sql: string}
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

            $sql = $sqlMatches[1];
            $sqlProperties[] = [
                'field' => $property->getName(),
                'sql' => $sql,
                'type' => SQLUtils::GetTypeFromCreateSQL($sql)
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

            if (!isset($data[$name])) {
                throw new DatabaseException("Missing property in data: $name");
            }

            if (!$reflectionClass->hasProperty($name)) {
                throw new DatabaseException("Missing property in class: $name");
            }

            $reflectionProperty = $reflectionClass->getProperty($name);
            $reflectionProperty->setValue($model, $data[$name]);
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

        $sqlProperties = static::GetSQLProperties();

        $columns = array_map(
            function ($property): string {
                return '`' . $property['field'] . '` ' . $property['sql'];
            },
            $sqlProperties
        );

        $columnDefinitions = implode(',', $columns);
        $tableName = static::GetTableName();
        $sql = "CREATE TABLE `$tableName` (
            $columnDefinitions
        )";
        SQL::MiscExecute($sql);
    }

    /**
     * Drops the model table, if it exists
     *
     * Shouldn't be called from the base class
     * @return void
     */
    final public static function Drop(): void
    {
        static::CheckNotBase();

        if (!SQL::MiscTableExists(static::GetTableName())) {
            return;
        }

        $tableName = static::GetTableName();
        $sql = "DROP TABLE `$tableName`";
        SQL::MiscExecute($sql);
    }

    /**
     * Truncates the model table
     *
     * Shouldn't be called from the base class
     * @return void
     */
    final public static function Truncate(): void
    {
        static::CheckNotBase();
        static::Init();

        $tableName = static::GetTableName();
        $sql = "TRUNCATE TABLE `$tableName`";
        SQL::MiscExecute($sql);
    }

    /**
     * Selects data from DB and creates the model, if found
     *
     * Shouldn't be called from the base class
     * @param int $id
     * @return object|null
     */
    final public static function SelectModel(int $id): ?object
    {
        static::CheckNotBase();
        static::Init();

        $datas = SQL::SelectDataWithCondition(static::GetTableName(), '*', 'id = :id', [
            ':id' => $id
        ]);

        if (count($datas) == 0) {
            return null;
        }

        return static::CreateFrom($datas[0]);
    }

    /**
     * Select datas from DB with where, creates models
     *
     * Shouldn't be called from the base class
     * @param string $condition
     * @param array $params
     * @return object[]
     */
    final public static function SelectWhereModels(string $condition, array $params): array
    {
        static::CheckNotBase();
        static::Init();

        $datas = SQL::SelectDataWithCondition(static::GetTableName(), '*', $condition, $params);
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
    final public static function SelectAllModels(): array
    {
        static::CheckNotBase();
        static::Init();

        $datas = SQL::SelectData(static::GetTableName(), '*');
        $models = array_map(function ($data) {
            return static::CreateFrom($data);
        }, $datas);

        return $models;
    }

    /**
     * Inserts data into DB
     *
     * Shouldn't be called from the base class
     * @param BaseModel $model
     * @return int Inserted row ID
     */
    final public static function InsertModel(BaseModel $model): int
    {
        static::CheckNotBase();
        static::Init();

        $data = $model->GetDataRaw();
        unset($data['id']);
        return SQL::InsertData(static::GetTableName(), $data);
    }

    /**
     * Updates data in DB
     *
     * Shouldn't be called from the base class
     * @param BaseModel $model
     * @return void
     */
    final public static function UpdateModel(BaseModel $model): void
    {
        static::CheckNotBase();
        static::Init();

        $data = $model->GetDataRaw();
        SQL::UpdateDataWithCondition(static::GetTableName(), $data, [
            ':id' => $model->id
        ], "id = :id");
    }

    /**
     * Deletes data from DB
     *
     * Shouldn't be called from the base class
     * @param BaseModel $model
     * @return bool Whether the model was deleted
     */
    final public static function DeleteModel(BaseModel $model): int
    {
        static::CheckNotBase();
        static::Init();

        $affectedRows = SQL::DeleteDataWithCondition(static::GetTableName(), "id = :id", [
            ':id' => $model->id
        ]);

        return $affectedRows != 0;
    }

    /**
     * Default in every model
     * @sql INT NOT NULL AUTO_INCREMENT PRIMARY KEY
     * @var int
     */
    public $id;

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
    final public function Insert(): void
    {
        static::InsertModel($this);
    }

    /**
     * Updates the DB row based on id
     * @return void
     */
    final public function Update(): void
    {
        static::UpdateModel($this);
    }

    /**
     * Deletes from the DB based on id
     * @return void
     */
    final public function Delete(): void
    {
        static::DeleteModel($this);
    }

    /**
     * Constructs an array of data from the model as is
     * @throws DatabaseException
     * @return array ["column" => "value"]
     */
    final public function GetDataRaw(): array
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
            $value = strval($reflectionProperty->getValue($this));

            $data[$name] = $value;
        }

        return $data;
    }

    /**
     * Constructs JSON ready data from the model
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
            $value = strval($reflectionProperty->getValue($this));

            $type = $property['type'];
            $data[$name] = SQLUtils::CastFromSQLType($value, $type);
        }

        return $data;
    }

}
