<?php

namespace App\Models;

use App\Database\BaseModels\BaseModelId;

class Tag extends BaseModelId
{
    /**
     * @sql VARCHAR(64) NOT NULL
     * @isUnique
     * @var string
     */
    public $name;
}
