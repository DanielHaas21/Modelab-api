<?php

namespace App\Models;

use App\Database\BaseModels\BaseModelId;

class File extends BaseModelId
{
    /**
     * @sql VARCHAR(2048) NOT NULL
     * @var string
     */
    public $path;

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $assetId;

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $fileTypeId;
}
