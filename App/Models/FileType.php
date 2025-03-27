<?php

namespace App\Models;

use App\Database\BaseModels\BaseModelId;

class FileType extends BaseModelId
{
    /**
     * @sql VARCHAR(64) NOT NULL
     * @var string
     */
    public $name;
}
