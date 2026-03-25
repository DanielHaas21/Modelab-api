<?php

namespace App\Helpers\Loggers;

interface ILogHandler
{
    public function HandleLog(Log $log);
}
