<?php

namespace App\Database\Exceptions;

/**
 * Error to describe fail in sql execution
 */
class SQLExecutionException extends DatabaseException
{
    /**
     * Constructs the exception
     * @param mixed $message
     */
    public function __construct(string $sql, string $message = "")
    {
        parent::__construct("Error while executing '$sql': $message");
    }
}
