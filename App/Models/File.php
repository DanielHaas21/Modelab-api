<?php

namespace App\Models;

use App\Services\Database\BaseModels\BaseModelId;

class File extends BaseModelId
{
    /**
     * @sql VARCHAR(2048) NOT NULL
     * @var string
     */
    public $path;

    /**
     * @sql VARCHAR(128) NOT NULL
     * @var string
     */
    public $name;

    /**
     * @sql VARCHAR(128) NOT NULL
     * @var string
     */
    public $group;

    /**
     * @sql BOOLEAN NOT NULL
     * @var bool
     */
    public $isHidden;

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $order;

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $assetId;
}
