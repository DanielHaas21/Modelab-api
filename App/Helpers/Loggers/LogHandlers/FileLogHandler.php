<?php

namespace App\Helpers\Loggers\LogHandlers;

use App\Helpers\Loggers\ILogHandler;
use App\Helpers\Loggers\Log;
use DateTime;

class FileLogHandler implements ILogHandler
{
    private $logsFolder;

    public function __construct(string $logsFolder)
    {
        $this->logsFolder = $logsFolder;
    }

    private function GetLogFilePath()
    {
        $date = new DateTime();
        $timestamp = $date->format('Y-m-d');
        return $this->logsFolder . '/' . $timestamp . '.log';
    }

    private function WriteLog(string $line)
    {
        $filePath = $this->GetLogFilePath();
        $file = fopen($filePath, 'a');
        fwrite($file, trim($line) .  PHP_EOL);
    }

    public function HandleLog(Log $log)
    {
        if (!is_dir($this->logsFolder)) {
            return;
        }

        $this->WriteLog($log->ToText());
    }
}
