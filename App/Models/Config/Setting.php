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
    public $name;

    /**
     * @sql TEXT NULL
     * @var string|null
     */
    public $value;
}
