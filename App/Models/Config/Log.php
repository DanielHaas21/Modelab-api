<?php

namespace App\Models\Config;

use App\Services\Database\BaseModels\BaseModelId;

class Log extends BaseModelId
{
    /**
     * @sql VARCHAR(512) NOT NULL
     * @var string
     */
    public $status;

    /**
     * @sql TEXT NOT NULL
     * @var string
     */
    public $message;

    /**
     * @sql TEXT NOT NULL
     * @var string
     */
    public $origin;

    /**
     * @sql DATETIME NOT NULL
     * @var string
     */
    public $date;
}
