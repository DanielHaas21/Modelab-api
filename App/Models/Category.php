<?php

namespace App\Models;

use App\Services\Database\BaseModels\BaseModelId;

class Category extends BaseModelId
{
    /**
     * @sql VARCHAR(64) NOT NULL
     * @isUnique
     * @var string
     */
    public $name;
}
