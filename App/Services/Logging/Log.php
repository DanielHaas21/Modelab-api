<?php

namespace App\Services\Logging;

use App\Services\Database\DateUtils;
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
        $status = LogStatus::GetName($this->status);

        return sprintf(
            '[%s] %s | %s: %s',
            DateUtils::ToDatabase($this->date),
            $status,
            $this->origin,
            $this->message
        );
    }
}
