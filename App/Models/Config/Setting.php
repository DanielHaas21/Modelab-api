<?php

namespace App\Models\Config;

use App\Services\Database\BaseModels\BaseModelId;

class Setting extends BaseModelId
{
    /**
     * @sql VARCHAR(512) NOT NULL
     * @isUnique
     * @var string
     */
    public $key;

    /**
     * @sql LONGTEXT NULL
     * @var string|null
     */
    public $value;

    /**
     * @sql INTEGER NULL
     * @var int
     */
    public $read_clearance;

    /**
     * @sql INTEGER NULL
     * @var int
     */
    public $write_clearance;

    /**
     * @sql DATETIME NOT NULL
     * @var string
     */
    public $updated;
}
