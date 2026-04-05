<?php

namespace App\Services\Logging;

interface ILogHandler
{
    public function HandleLog(Log $log);
}
