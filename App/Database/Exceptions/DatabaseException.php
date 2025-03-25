<?php

namespace App\Database\Exceptions;

/**
 * Error to describe a generic database error
 */
class DatabaseException extends \Exception implements \Throwable
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
