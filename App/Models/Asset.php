<?php

namespace App\Models;

use App\Database\BaseModels\BaseModelId;

class Asset extends BaseModelId
{
    /**
     * @sql VARCHAR(128) NOT NULL
     * @var string
     */
    public $name;

    /**
     * @sql VARCHAR(320) NOT NULL
     * @var string
     */
    public $description;

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $categoryId;

    /**
     * @sql VARCHAR(2048) NOT NULL
     * @var string
     */
    public $filesDirectory;

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $uploaderId;
}
