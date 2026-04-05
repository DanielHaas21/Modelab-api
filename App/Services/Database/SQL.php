<?php

namespace App\Services\Database;

use App\Services\Database\Exceptions\SQLExecutionException;
use Exception;
use PDOException;
use PDOStatement;

/**
 * Library for elemental SQL functions
 *
 * @uses PDOTrait::InitPDO() For PDO instance
 *
 * Naming conventions
 * - First word represent the type of CRUD function that is Select Insert Update Delete
 * - Misc as a first word represents methods that dont fit the former categorization
 * - WithCondition means its the variant with condition only and vice versa
 * - Conditiniable means both condition or not is supported
 * - Distinct
 *
 * Conditions
 * - When defining conditions use : placeholders for variables for exmaple test = :test \m
 * - Params array: [":test" => $test]
 * - Data array: ["test" => $test]
 */
class SQL
{
    use PDOTrait;

    /**
     * Generates sql clauses and parameters used in PDO commands
     * @param array<string, string> $data
     * @return array<string, string>
     */
    private static function GenerateParams(array $data): array
    {
        $params = [];
        foreach ($data as $key => $value) {
            $params[":$key"] = $value;
        }
        return $params;
    }

    /**
     * Checks whether a table exists in db
     * @param string $table
     * @return bool|int 0 is returned if the command fails
     */
    public static function MiscTableExists(string $table)
    {
        self::InitPDO();

        $sql = "SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = :dbName AND table_name = :tableName LIMIT 1";
        try {
            $sql_com = self::$pdo->prepare($sql);
            $sql_com->execute(['dbName' => PDOConfig::$DATABASE, 'tableName' => $table]);

            if ($sql_com->rowCount() === 0) {
                return false;
            } else {
                return true;
            }
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while selecting data: " . $e->getMessage());
        }
    }

    /**
     * Checks if the database connection is active and responding
     * @return bool
     */
    public static function MiscCheckStatus(): bool
    {
        try {
            self::InitPDO();
            return (bool) self::$pdo->query("SELECT 1");
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Runs raw SQL code
     * @param string $sql
     * @param ?array $params Raw parameters, use [":key" => "value"]
     * @return \PDOStatement
     */
    public static function MiscExecute(string $sql, ?array $params = null): PDOStatement
    {
        self::InitPDO();

        try {
            $sql_com = self::$pdo->prepare($sql);
            if ($params == null) {
                $sql_com->execute();
            } else {
                $sql_com->execute($params);
            }

            return $sql_com;
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while executing sql: " . $e->getMessage());
        }
    }

    /**
     * Returns count of logs in a table
     * @param string $countColumn
     * @param string $table
     * @return int
     */
    public static function SelectTableCount(string $countColumn = "*", string $table = ""): int
    {
        self::InitPDO();

        $sql = "SELECT COUNT($countColumn) AS total_count FROM $table";

        try {
            $sql_com = self::$pdo->prepare($sql);
            $sql_com->execute();

            $totalCount = $sql_com->fetch(\PDO::FETCH_ASSOC)['total_count'];
            return (int) $totalCount;
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while counting rows: " . $e->getMessage());
        }
    }

    /**
     * Checks if a column contains any data in the $data array
     * @param string $table
     * @param string $column
     * @param array $params
     * @return bool
     */
    public static function MiscIsDataInTable(string $table, string $column, array $params): bool
    {
        self::InitPDO();

        $result = self::SelectDistinctDataWithInCondition($table, '*', $column, $params);
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns a conditioned count of logs in a table
     * @param string $table
     * @param string $countColumn
     * @param string $condition
     * @param array $params
     * @throws SQLExecutionException
     * @return int
     */
    public static function SelectTableCountWithCondition(string $table, string $countColumn = "*", string $condition = "", array $params = []): int
    {
        self::InitPDO();

        $countColumn = "COUNT($countColumn)";

        $sql = "SELECT $countColumn FROM $table";
        if (! empty($condition)) {
            $sql .= " WHERE $condition";
        }

        try {
            $sql_com = self::$pdo->prepare($sql);

            $sql_com->execute($params);
            $count = (int) $sql_com->fetchColumn();

            return $count;
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while selecting count with condition: " . $e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param string|array $something
     * @throws SQLExecutionException
     * @return array
     */
    public static function SelectData(string $table, $something): array
    {
        self::InitPDO();

        $something = is_array($something) == true ? implode(", ", $something) : $something;
        $sql = "SELECT $something FROM $table";

        try {
            $sql_com = self::$pdo->prepare($sql);
            $sql_com->execute();

            return $sql_com->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while selecting data: " . $e->getMessage());
        }
    }

    /**
     * @param mixed $table
     * @param mixed $something
     * @throws SQLExecutionException
     * @return array
     */
    public static function SelectDataDistinctData($table, $something): array
    {
        self::InitPDO();

        $sql = "SELECT DISTINCT $something FROM $table";

        try {
            $sql_com = self::$pdo->prepare($sql);
            $sql_com->execute();
            $return = $sql_com->fetchAll(\PDO::FETCH_ASSOC);
            return $return;
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while selecting distinct data: " . $e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param string|array $columns - multiple column selection is supported
     * @param string $condition
     * @param array $params
     * @throws SQLExecutionException
     * @return array
     */
    public static function SelectDistinctDataWithCondition(string $table, $columns, string $condition, array $params = []): array
    {
        self::InitPDO();

        $columns = is_array($columns) == true ? implode(", ", $columns) : $columns;
        $sql = "SELECT DISTINCT $columns FROM $table WHERE $condition";

        try {
            $sql_com = self::$pdo->prepare($sql);

            foreach ($params as $paramName => $paramValue) {
                $paramType = is_int($paramValue) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $sql_com->bindValue($paramName, $paramValue, $paramType);
            }

            $sql_com->execute();
            $results = $sql_com->fetchAll(\PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while selecting distinct data with condition: " . $e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param string $columns
     * @param string $condition
     * @param array $params
     * @throws SQLExecutionException
     * @return array
     */
    public static function SelectDataWithCondition(string $table, string $columns, string $condition, array $params = []): array
    {
        self::InitPDO();

        $sql = "SELECT $columns FROM $table WHERE $condition";

        try {
            $sql_com = self::$pdo->prepare($sql);

            foreach ($params as $paramName => $paramValue) {
                $paramType = is_int($paramValue) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $sql_com->bindValue($paramName, $paramValue, $paramType);
            }

            $sql_com->execute();
            $results = $sql_com->fetchAll(\PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while selecting data with condition: " . $e->getMessage());
        }
    }

    /**
     * Selects data where in an array
     * @param string $table
     * @param string|array $columns
     * @param string $conditionColumn - refers to the column to which the data in the array is comapred to
     * @param array $params
     * @param string|null $afterCondition
     * @throws SQLExecutionException
     * @throws Exception
     * @return array
     */
    public static function SelectDataInConditiniable(string $table, $columns, string $conditionColumn, array $params, $afterCondition = null): array
    {
        self::InitPDO();

        $columns = is_array($columns) == true ? implode(", ", $columns) : $columns;
        if (! is_array($params) || empty($params)) {
            throw new Exception("ParamArray is either empty or not an array");
        }

        $inPlaceholder = implode(',', array_fill(0, count($params), '?'));
        $sql = "SELECT $columns FROM $table WHERE $conditionColumn IN ($inPlaceholder) " . ($afterCondition == null ? '' : $afterCondition);

        try {
            $sql_com = self::$pdo->prepare($sql);

            $sql_com->execute($params);
            $results = $sql_com->fetchAll(\PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while selecting data in conditiniable: " . $e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param string $columns
     * @param string $conditionColumn
     * @param array $params
     * @param string|null $afterCondition
     * @throws SQLExecutionException
     * @return array
     */
    protected static function SelectDistinctDataWithInCondition(string $table, string $columns, string $conditionColumn, array $params, $afterCondition = null): array
    {
        self::InitPDO();

        if (! is_array($params) || empty($params)) {
            throw new Exception("ParamArray is either empty or not an array");
        }

        $inPlaceholder = implode(',', array_fill(0, count($params), '?'));
        $sql = "SELECT DISTINCT $columns FROM $table WHERE $conditionColumn IN ($inPlaceholder) " . ($afterCondition == null ? '' : $afterCondition);

        try {
            $sql_com = self::$pdo->prepare($sql);

            $sql_com->execute($params);
            $results = $sql_com->fetchAll(\PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while selecting distinct data with condition: " . $e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param array $data
     * @throws SQLExecutionException
     * @return int Last inserted id
     */
    public static function InsertData(string $table, array $data = []): int
    {
        self::InitPDO();

        $params = self::GenerateParams($data);

        $columns = implode(', ', array_map(function ($column) {
            return "`$column`";
        }, array_keys($data)));

        $placeholders = implode(', ', array_keys($params));

        $sql = "INSERT IGNORE INTO $table ($columns) VALUES ($placeholders)";
        try {
            $sql_com = self::$pdo->prepare($sql);

            $sql_com->execute($params);
            return intval(self::$pdo->lastInsertId());
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while inserting data: " . $e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param array $data
     * @param array $params - array of definitions for the condition
     * @param string $condition
     * @throws SQLExecutionException
     * @return int Affected rows
     */
    public static function UpdateDataWithCondition(string $table = "", array $data = [], array $params = [], string $condition = ""): int
    {
        self::InitPDO();

        $params = array_merge(self::GenerateParams($data), $params);

        $setClauses = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "$key = :$key";
        }

        $setClause = implode(', ', $setClauses);

        $sql = "UPDATE $table SET $setClause WHERE $condition";

        try {
            $sql_com = self::$pdo->prepare($sql);

            $sql_com->execute($params);
            return $sql_com->rowCount();
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while updating data with condition: " . $e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param string $condition
     * @param array $params
     * @throws SQLExecutionException
     * @return int Affected rows
     */
    public static function DeleteDataWithCondition(string $table, string $condition, array $params = []): int
    {
        self::InitPDO();

        $sql = "DELETE FROM $table WHERE $condition";

        try {
            $sql_com = self::$pdo->prepare($sql);

            $sql_com->execute($params);

            return $sql_com->rowCount();
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while deleting data with condition: " . $e->getMessage());
        }
    }

    /**
     * @param string $table
     * @throws SQLExecutionException
     * @return int Affected rows
     */
    public static function DeleteData(string $table): int
    {
        self::InitPDO();

        $sql = "DELETE FROM $table";

        try {
            $sql_com = self::$pdo->prepare($sql);
            $sql_com->execute();

            return $sql_com->rowCount();
        } catch (PDOException $e) {
            throw new SQLExecutionException($sql, "Error while deleting data: " . $e->getMessage());
        }
    }
}
