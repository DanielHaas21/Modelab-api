<?php

namespace App\Helpers\Loggers\LogHandlers;

use App\Helpers\Loggers\ILogHandler;
use App\Helpers\Loggers\Log;
use App\Models\Config\Log as DBLog;
use Exception;

class DBLogHandler implements ILogHandler
{
    public function HandleLog(Log $log)
    {
        try {
            $db_log = new DBLog();
            $db_log->status = $log->GetStatus();
            $db_log->message = $log->GetMessage();
            $db_log->origin = $log->GetOrigin();
            $db_log->date = $log->GetDate()->format('Y-m-d H:i:s');

            DBLog::InsertModel($db_log);
        } catch (Exception $e) {
            throw new Exception('Failed to save log', 0, $e);
        }
    }
}
