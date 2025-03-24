<?php 
namespace App\Database;

use PDOException;

require_once 'PDOtrait.php';

/**
 * Library for elemental SQL functions 
 * 
 * @uses PDO::initPdo() For PDO instance
 * 
 * Naming conventions
 * - First word represent the type of CRUD function that is Select Insert Update Delete
 * - Misc as a first word represents methods that dont fit the former categorization
 * - WithCondition means its the variant with condition only and vice versa
 * - Conditiniable means both condition or not is supported
 * - Distinct
 * 
 * Conditions 
 * - When defining conditions use : placeholders for variables  for exmaple  test = :test \m
 * - Condition array pairs then look like this  [":test" => $test] 
 * @method InserData omits this rule
 */
class DatabaseFunctions{
    use PDO;
    /**
     * MiscMissingTable checks wheter a table exists in db
     * @param string $table
     * @return bool|int 0 is returned if the command fails
     */
    public static function MiscMissingTable(string $table): bool|int{
        self::initPDO();
        $stmt = self::$pdo->prepare("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = :dbName AND table_name = :tableName LIMIT 1");

        if ($stmt->execute(['dbName' => 'modelab-api', 'tableName' => $table])) {
            if ($stmt->rowCount() === 0) {
                return false;
            }else{
                return true;
            }
        }else{
            return 0;
        }
    }
    /*
    Not sure if this will be used..
     */
    public static function createTable($table, $specifications): void{
        self::initPDO();
        $new_table = "CREATE TABLE IF NOT EXISTS {$table}(
            {$specifications}
        );";
        $sql_com = self::$pdo->prepare($new_table);
        $sql_com->execute();
    }
    /**
     * SelectTableCount returns count of logs in a table
     * @param string $countColumn
     * @param string $table
     * @return int
     */
    public static function SelectTableCount(string $countColumn = "*",string $table): int {
        self::initPDO();
        $countColumn = ($countColumn === "*") ? "*" : $countColumn;

        $count = "SELECT COUNT($countColumn) AS total_count FROM $table";
        
        try {
            $sql_com = self::$pdo->prepare($count);
            $sql_com->execute();
            
            $countResult = $sql_com->fetch(\PDO::FETCH_ASSOC)['total_count'];
            
            return (int)$countResult; 
        } catch (PDOException $e) {
            echo "Error in counting rows: " . $e->getMessage();
            return 0;
        }
    }

    /**
     * MiscIsDataInTable checks if a column contains any data in the $data array
     * @param string $table
     * @param string $column
     * @param array $data
     * @return bool
     */
    public static function MiscIsDataInTable(string $table,string $column,array $data): bool{
        self::initPDO();
        $result = self::SelectDistinctDataWithInCondition($table,'*',$column,$data);
        if(count($result) > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * SelectTableCountWithCondition returns a conditioned count of logs in a table
     * @param string $table
     * @param string $countColumn
     * @param string $condition
     * @param array $params
     * @return bool|int
     */
    public static function SelectTableCountWithCondition(string $table,string $countColumn = "*",string $condition = "",array $params = []): bool|int {
        self::initPDO();
        if ($countColumn === "*") {
            $countColumn = "COUNT(*)";
        } else {
            $countColumn = "COUNT($countColumn)";
        }

        $sql = "SELECT $countColumn FROM $table";
        if (!empty($condition)) {
            $sql .= " WHERE $condition";
        }
        $sql_com = self::$pdo->prepare($sql);

        if ($sql_com === false) {
            return false;
        }

        $sql_com->execute($params);
        $count = (int)$sql_com->fetchColumn();

        return $count;
    }

    /**
     * SelectData
     * @param string $table
     * @param string|array $Something
     * @return mixed
     */
    public static function SelectData(string $table,string|array $Something): mixed {
        self::initPDO();
        $Something = is_array($Something) == true ? implode(", ", $Something) : $Something; 
        $sql = "SELECT $Something FROM $table";
        $sql_com = self::$pdo->prepare($sql);
        $sql_com->execute();
        $return = $sql_com->fetchAll(\PDO::FETCH_ASSOC);
        return $return;
    }

    /**
     * SelectDataDistinctData
     * @param mixed $table
     * @param mixed $Something
     * @return mixed
     */
    public static function SelectDataDistinctData($table, $Something): mixed {
        self::initPDO();
        $sql = "SELECT DISTINCT $Something FROM $table";
        $sql_com = self::$pdo->prepare($sql);
        $sql_com->execute();
        $return = $sql_com->fetchAll(\PDO::FETCH_ASSOC);
        return $return;
    }

    /**
     * SelectDistinctDataWithCondition
     * @param string $table
     * @param string|array $columns - multiple column selection is supported
     * @param string $condition
     * @param array $params
     * @return mixed
     */
    public static function SelectDistinctDataWithCondition(string $table,string|array $columns,string $condition,array $params = []): mixed{
        self::initPDO();
        $columns = is_array(  $columns) == true ? implode(", ",   $columns) :   $columns;
        $sql = "SELECT DISTINCT $columns FROM $table WHERE $condition";
        $sql_com = self::$pdo->prepare($sql);

        if ($sql_com === false) {
            return false;
        }

        foreach ($params as $paramName => $paramValue) {
            $paramType = is_int($paramValue) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $sql_com->bindValue($paramName, $paramValue, $paramType);
        }

        $sql_com->execute();
        $results = $sql_com->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }
    /**
     * SelectDataWithCondition
     * @param string $table
     * @param string $columns
     * @param string $condition
     * @param array $params
     * @return mixed
     */
    public static function SelectDataWithCondition(string $table,string $columns,string $condition,array $params = []): mixed {
        self::initPDO();
        $sql = "SELECT $columns FROM $table WHERE $condition";
        $sql_com = self::$pdo->prepare($sql);

        if ($sql_com === false) {
            return false;
        }

        foreach ($params as $paramName => $paramValue) {
            $paramType = is_int($paramValue) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $sql_com->bindValue($paramName, $paramValue, $paramType);
        }

        $sql_com->execute();
        $results = $sql_com->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }

    /**
     * SelectDataInConditiniable  Selects data where in an array
     * @param string $table
     * @param string|array $columns
     * @param string $conditionColumn - refers to the column to which the data in the array is comapred to
     * @param array $paramArray
     * @param string|null $condition
     * @return mixed
     */
    public static function SelectDataInConditiniable(string $table,string|array $columns,string $conditionColumn,array $paramArray,string|null $condition = null): mixed {
        self::initPDO();
        $columns = is_array(  $columns) == true ? implode(", ",   $columns) :   $columns;
        if (!is_array($paramArray) || empty($paramArray)) {
            return false;
        }

        $inPlaceholder = implode(',', array_fill(0, count($paramArray), '?'));
        if($condition){
            $sql = "SELECT $columns FROM $table WHERE $conditionColumn IN ($inPlaceholder) $condition";
        }else{
            $sql = "SELECT $columns FROM $table WHERE $conditionColumn IN ($inPlaceholder)";
        }
     
        $sql_com = self::$pdo->prepare($sql);

        if ($sql_com === false) {
            return false;
        }

        $sql_com->execute($paramArray);
        $results = $sql_com->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }
    /**
     * SelectDistinctDataWithInCondition
     * @param string $table
     * @param string $columns
     * @param string $conditionColumn
     * @param array $paramArray
     * @return mixed
     */
    protected static function SelectDistinctDataWithInCondition(string $table,string $columns,string $conditionColumn,array $paramArray): mixed {
        self::initPDO();
        if (!is_array($paramArray) || empty($paramArray)) {
            return false;
        }

        $inPlaceholder = implode(',', array_fill(0, count($paramArray), '?'));
       
            $sql = "SELECT DISTINCT $columns FROM $table WHERE $conditionColumn IN ($inPlaceholder)";
        
     
        $sql_com = self::$pdo->prepare($sql);

        if ($sql_com === false) {
            return false;
        }

        $sql_com->execute($paramArray);
        $results = $sql_com->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }
    /**
     * InsertData
     * @param string $table
     * @param array $data 
     * @return mixed
     */
    public static function InsertData(string $table,array $data = []): mixed {
        self::initPDO();
        $columns = implode(', ', array_map(function($column) {
            return "`$column`";
        }, array_keys($data)));

        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT IGNORE INTO $table ($columns) VALUES ($placeholders)";
        $sql_com = self::$pdo->prepare($sql);
        if ($sql_com === false) {
            return false;
        }
        
        $sql_com->execute($data);
        return self::$pdo->lastInsertId();
    }
    /**
     * UpdateDataWithCondition
     * @param string $table
     * @param array $data
     * @param array $definition - array of definitions for the condition
     * @param string $condition
     * @return mixed
     */
    public static function UpdateDataWithCondition(string $table,array $data = [],array $definition,string $condition ): mixed {
        self::initPDO();
        $setClauses = [];
        
        foreach ($data as $key => $value) {
            $setClauses[] = "$key = :$key";
        }

        $data1 = array_merge($data, $definition);
        
        $setClause = implode(', ', $setClauses);
        
        $sql = "UPDATE $table SET $setClause WHERE $condition";
        
        $sql_com = self::$pdo->prepare($sql);
        
        if ($sql_com === false) {
            return false;
        }
        
        $sql_com->execute($data1);
        return $sql_com->rowCount(); 
    }
    /**
     * DeleteDataWithCondition
     * @param string $table
     * @param string $condition
     * @param array $params
     * @return mixed
     */
    public static function DeleteDataWithCondition(string $table,string $condition,array $params = []): mixed {
        self::initPDO();
        $sql = "DELETE FROM $table WHERE $condition";
        $sql_com = self::$pdo->prepare($sql);
    
        if ($sql_com === false) {
            return false;
        }
    
        $sql_com->execute($params);

        $affectedRows = $sql_com->rowCount();
    
        return $affectedRows;
    }

    /**
     * DeleteData
     * @param string $table
     * @return mixed
     */
    public static function DeleteData(string $table): mixed {
        self::initPDO();
        $sql = "DELETE FROM $table";
        $sql_com = self::$pdo->prepare($sql);
    
        if ($sql_com === false) {
            return false;
        }
        $sql_com->execute();
    
        $affectedRows = $sql_com->rowCount();
    
        return $affectedRows;
    }
}
