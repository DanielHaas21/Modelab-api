<?php

namespace App\Helpers\Loggers\LogHandlers;

use App\Helpers\Loggers\ILogHandler;
use App\Helpers\Loggers\Log;

class EchoLogHandler implements ILogHandler
{
    public function HandleLog(Log $log)
    {
        $log_text = $log->ToText();
        echo "$log_text\n";
    }
}
