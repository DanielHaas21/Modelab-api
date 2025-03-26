<?php

namespace App\Models;

use App\Database\BaseModel;

class Category extends BaseModel
{
    /**
     * @sql VARCHAR(64) NOT NULL
     * @var string
     */
    public $name;
}
