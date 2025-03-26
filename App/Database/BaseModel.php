<?php

namespace App\Database;

use App\Database\Exceptions\DatabaseException;
use App\Database\SQL;

/**
 * Abstract class of a model that defines a database table
 *
 * use @sql in PHPDoc above a field to define it as a column:
 * <br> \* @sql INT NOT NULL PRIMARY KEY
 * <br>*\/
 * <br>public $id;
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
     * <br>Shouldn't be called from the base class
     * @throws DatabaseException
     * @return array{name: string, sql: string}
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

            $sqlProperties[] = [
                'field' => $property->getName(),
                'sql' => $sqlMatches[1],
            ];
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
        static::CheckNotBase();

        $className = explode('\\', get_called_class());
        return end($className);
    }


    /**
     * Creates an instance of the model and sets data
     * <br>Shouldn't be called from the base class
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
     * <br>Shouldn't be called from the base class
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
                return '`' . $property['field'] . '` ' . $property['type'] . ' ' . $property['sql'];
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
     * <br>Shouldn't be called from the base class
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
     * <br>Shouldn't be called from the base class
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
     * <br>Shouldn't be called from the base class
     * @param int $id
     * @return object|null
     */
    final public static function Select(int $id): ?object
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
     * Inserts data into DB
     * @param array $data
     * @return int Inserted row ID
     */
    final public static function Insert(array $data): int
    {
        unset($data['id']);
        return SQL::InsertData(static::GetTableName(), $data);
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
     * Updates the DB row based on the id
     * @return void
     */
    final public function Update(): void
    {
        $data = $this->GetData();
        SQL::UpdateDataWithCondition(static::GetTableName(), $data, [], "id = :id");
    }

    /**
     * Deletes the DB row based on the id
     * @return void
     */
    final public function Delete(): void
    {
        $data = $this->GetData();
        SQL::DeleteDataWithCondition(static::GetTableName(), "id = :id", $data);
    }

    /**
     * Constructs the data array from the model
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
            $data[$name] = $reflectionProperty->getValue($this);
        }

        return $data;
    }

}
