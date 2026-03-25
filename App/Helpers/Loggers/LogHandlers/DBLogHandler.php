<?php

namespace App\Helpers\Loggers\LogHandlers;

use App\Helpers\Loggers\ILogHandler;
use App\Helpers\Loggers\Log;
use App\Models\Config\Log as DBLog;

class DBLogHandler implements ILogHandler
{
    public function HandleLog(Log $log)
    {
        $dbLog = new DBLog();
        $dbLog->status = $log->GetStatus();
        $dbLog->message = $log->GetMessage();
        $dbLog->origin = $log->GetOrigin();
        $dbLog->date = $log->GetDate()->format('Y-m-d H:i:s');

        DBLog::InsertModel($dbLog);
    }
}
