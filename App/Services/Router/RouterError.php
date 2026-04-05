<?php

namespace App\Services\Router;

use Error;

/**
 * FATAL or ACCESS router error
 */
class RouterError extends Error
{
    public const TYPE_FATAL = 0;
    public const TYPE_ACCESS = 1;

    /**
     * The error type
     * @var int
     */
    private $type;

    /**
     * The developer message, not visible to the API users
     * @var string
     */
    private $devMessage;

    /**
     * The shutdown error
     * @var array{type: int, message: string, file: string, line: int}
     */
    private $shutdown_error;

    /**
     * Constructor of the RequestError
     * @param int $type Type of the error
     * @param int $code Code of the error
     * @param string $message Message of the error
     * @param string $devMessage Developer message of the error, not visible to the API users
     * @param array{type: int, message: string, file: string, line: int}|null $shutdown_error The shutdown error
     * @param \Throwable $error The encapsulated error
     */
    public function __construct(int $type, int $code, string $message, string $devMessage, array $shutdown_error = null)
    {
        parent::__construct($message, $code);
        $this->type = $type;
        $this->devMessage = $devMessage;

        if ($shutdown_error == null) {
            $this->shutdown_error = [
                'message' => $message,
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'type' => E_ALL,
            ];
        } else {
            $this->shutdown_error = $shutdown_error;
        }
    }

    /**
     * Returns the type of the error
     * @return int
     */
    public function GetType()
    {
        return $this->type;
    }

    /**
     * Returns the shutdown error
     * @return array{type: int, message: string, file: string, line: int}
     */
    public function GetShutdownError()
    {
        return $this->shutdown_error;
    }

    /**
     * Returns the developer message, not visible to the API users
     * @return string
     */
    public function GetDevMessage()
    {
        return $this->devMessage;
    }

}
