<?php

namespace App\Database;

use App\Database\Exceptions\DatabaseException;
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
     * Constructs an array of differences between DB table and the
     * @return array<string, array{reason: string, dbValue: ?string, modelValue: ?string}> Column name is the key
     *               Item is [
     *                 'reason' => The reason why this column is different,
     *                 'dbValue' => The database value/type (if applicable),
     *                 'modelValue' => The model value/type (if applicable),
     *               ]
     */
    final public static function CompareColumns(): array
    {
        /**
         * @param array $differences
         * @param string $column
         * @param string $reason
         * @param ?string $dbValue
         * @param ?string $modelValue
         * @return void
         */
        function AddDifference(array &$differences, string $column, string $reason, ?string $dbValue = null, ?string $modelValue = null): void
        {
            $differences[$column] = [
                'reason' => $reason,
                'dbValue' => $dbValue,
                'modelValue' => $modelValue
            ];
        }

        static::Init();
        static::CheckNotBase();

        $differences = [];

        $sql = 'SHOW COLUMNS FROM  `' . static::GetTableName() . '`';
        $columnsData = SQL::Execute($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $sqlProperties = static::GetSQLProperties();

        foreach ($columnsData as $columnData) {
            $column = $columnData['Field'];
            $sqlType = trim(strtolower($columnData['Type']));

            $property = null;
            foreach ($sqlProperties as $i => $sqlProperty) {
                if ($sqlProperty['column'] != $column) {
                    continue;
                }
                $property = $sqlProperty;
                unset($sqlProperties[$i]);
                break;
            }

            if ($property == null) {
                AddDifference($differences, $column, 'Extra column');
                continue;
            }

            $propertyType = trim(strtolower($property['type']));
            if ($propertyType != $sqlType) {
                AddDifference($differences, $column, 'Type mismatch', $sqlType, $propertyType);
                continue;
            }
        }

        foreach ($sqlProperties as $sqlProperty) {
            AddDifference($differences, $sqlProperty['column'], 'Missing column');
        }

        return $differences;
    }

    /**
     * Gets the columns defined in the class
     * <br>Shouldn't be called from the base class
     * @throws DatabaseException
     * @return array{name: string, type: string , sql: string}
     */
    final public static function GetSQLProperties(): array
    {
        static::CheckNotBase();

        $reflectionClass = new \ReflectionClass(get_called_class());
        $properties = $reflectionClass->getProperties();

        $sqlProperties = [];
        foreach ($properties as $property) {
            $docComment = $property->getDocComment();
            $propertyName = $property->getName();

            if (!$docComment) {
                continue;
            }

            $isPropertyColumn = preg_match('/@sqlType\s+(.+)/', $docComment, $sqlTypeMatches);
            $hasCustomSql = preg_match('/@sql\s+(.+)/', $docComment, $sqlMatches);

            if (!$isPropertyColumn) {
                if ($hasCustomSql) {
                    throw new DatabaseException("Property '$propertyName' has @sql but not @sqlType");
                }
                continue;
            }

            $sqlProperties[] = [
                'column' => $property->getName(),
                'type' => $sqlTypeMatches[1],
                'sql' => $hasCustomSql ? $sqlMatches[1] : ''
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
                return '`' . $property['column'] . '` ' . $property['type'] . ' ' . $property['sql'];
            },
            $sqlProperties
        );

        $columnDefinitions = implode(',', $columns);
        $tableName = static::GetTableName();
        $sql = "CREATE TABLE `$tableName` (
            $columnDefinitions
        )";
        SQL::Execute($sql);
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
            $name = $property['column'];

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
     * Selects data from DB and creates the model, if found
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
     * Default in every model
     * @sqlType INT
     * @sql NOT NULL AUTO_INCREMENT PRIMARY KEY
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
            $name = $property['column'];

            if (!$reflectionClass->hasProperty($name)) {
                throw new DatabaseException("Missing property in class: $name");
            }

            $reflectionProperty = $reflectionClass->getProperty($name);
            $data[$name] = $reflectionProperty->getValue($this);
        }

        return $data;
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

}
