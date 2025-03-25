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
     * @return RequestError
     */
    public static function CreateFieldError(int $code, string $key, string $message): RequestError
    {
        $message = str_replace('%key%', $key, $message);
        return new RequestError($code, $key, $message);
    }

    /**
     * Cause of the error
     * @var string
     */
    private $cause;

    /**
     * Constructor of the RequestError
     * @param int $code HTTP Status code
     * @param string $cause Cause of the error
     * @param string $message Message of the error
     */
    public function __construct(int $code, string $cause, string $message)
    {
        parent::__construct($message, $code);
        $this->cause = $cause;
    }

    /**
     * Convert this error to a JSON array
     * @return array{cause: string, code: int, message: string}
     */
    public function GetJSON(): array
    {
        return [
            'cause'   => $this->cause,
            'message' => $this->message,
            'code'    => $this->code,
        ];
    }

}
