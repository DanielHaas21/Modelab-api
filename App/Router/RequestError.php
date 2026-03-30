<?php

namespace App\Router;

use Error;

/**
 * Error with a cause and a status code
 */
class RequestError extends Error
{
    /**
     * Creates RequestError. All %key% in message are replaced with key
     * @param int $code
     * @param string $key
     * @param string $message
     * @param array $data
     * @return RequestError
     */
    public static function CreateFieldError(int $code, string $key, string $message, array $data = []): RequestError
    {
        $message = str_replace('%key%', $key, $message);
        return new RequestError($code, $key, $message, $data);
    }

    /**
     * Cause of the error
     * @var string
     */
    private $cause;

    /**
     * Other JSON data
     * @var array
     */
    private $data;

    /**
     * The encapsulated error
     * @var \Throwable|null
     */
    private $error;

    /**
     * Constructor of the RequestError
     * @param int $code HTTP Status code
     * @param string $cause Cause of the error
     * @param string $message Message of the error
     * @param array $data Other JSON data
     * @param \Throwable $error The encapsulated error
     */
    public function __construct(int $code, string $cause, string $message, array $data = [], \Throwable $error = null)
    {
        parent::__construct($message, $code);
        $this->cause = $cause;
        $this->data = $data;
        $this->error = $error;
    }

    /**
     * Returns the error cause
     * @return string
     */
    public function GetCause()
    {
        return $this->cause;
    }

    /**
     * Returns the encapsulated error
     * @return \Throwable
     */
    public function GetError()
    {
        return $this->error;
    }

    /**
     * Convert this error to a JSON array
     * @return array{cause: string, code: int, message: string}
     */
    public function GetJSON(): array
    {
        $data = [
            'cause'   => $this->cause,
            'message' => $this->message,
            'code'    => $this->code,
        ];

        return array_merge($this->data, $data);
    }

}
