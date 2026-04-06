<?php

namespace App\Services\Logging\LogHandlers;

use App\Services\Logging\ILogHandler;
use App\Services\Logging\Log;
use DateTime;
use Exception;

class FileLogHandler implements ILogHandler
{
    private $logs_folder;

    public function __construct()
    {
        FileLogHandlerConfig::Load();

        $this->logs_folder = FileLogHandlerConfig::$LOG_PATH;
    }

    private function GetLogFilePath()
    {
        $date = new DateTime();
        $timestamp = $date->format('Y-m-d');
        return $this->logs_folder . '/' . $timestamp . '.log';
    }

    private function WriteLog(string $line)
    {
        $file_path = $this->GetLogFilePath();

        $file = fopen($file_path, 'a');
        if (!$file) {
            throw new Exception('Failed to open log file: ' . $file_path . ', most likely because of permissions');
        }

        try {
            fwrite($file, trim($line) . PHP_EOL);
        } catch (Exception $e) {
            throw new Exception('Failed to write log', 0, $e);
        }
    }

    public function HandleLog(Log $log)
    {
        if (!is_dir($this->logs_folder)) {
            throw new Exception('Log folder not found at \'' . $this->logs_folder . '\'');
        }

        $this->WriteLog($log->ToText());
    }
}
