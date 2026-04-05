<?php

namespace App\Services\Logging\LogHandlers;

use App\Services\Logging\ILogHandler;
use App\Services\Logging\Log;

class EchoLogHandler implements ILogHandler
{
    public function HandleLog(Log $log)
    {
        $log_text = $log->ToText();
        echo "$log_text\n";
    }
}
