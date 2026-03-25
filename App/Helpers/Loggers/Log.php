<?php

namespace App\Helpers\Loggers;

use DateTime;

class Log
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $origin;

    /**
     * @var DateTime
     */
    private $date;

    public function __construct(string $message, string $status, string $origin, DateTime $date)
    {
        $this->message = $message;
        $this->status = $status;
        $this->origin = $origin;
        $this->date = $date;
    }

    public function GetMessage()
    {
        return $this->message;
    }

    public function GetStatus()
    {
        return $this->status;
    }

    public function GetOrigin()
    {
        return $this->origin;
    }

    public function GetDate()
    {
        return $this->date;
    }

    public function ToText()
    {
        $timestamp = $this->date->format('Y-m-d H:i:s');
        $status = LogStatus::GetName($this->status);

        return sprintf(
            '[%s] %s | %s: %s',
            $timestamp,
            $status,
            $this->origin,
            $this->message
        );
    }
}
