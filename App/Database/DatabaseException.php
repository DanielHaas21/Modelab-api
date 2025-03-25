<?php

namespace App\Database;

/**
 * Errors when managing the database
 */
class DatabaseException extends \Exception
{
    /**
     * Constructs the exception
     * @param mixed $message
     */
    public function __construct(string $message = "")
    {
        parent::__construct($message);
    }
}
