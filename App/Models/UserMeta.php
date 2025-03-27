<?php

namespace App\Models;

use App\Database\BaseModels\BaseModelId;

class UserMeta extends BaseModelId
{
    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $level;

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $userId;
}
