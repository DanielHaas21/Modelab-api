<?php

namespace App\Models;

use App\Database\BaseModels\BaseModelId;

/**
 * Model of a User from Google OAuth
 */
class User extends BaseModelId
{
    /**
     * @sql VARCHAR(512) NOT NULL
     * @var string
     */
    public $email;

    /**
     * @sql VARCHAR(64) NOT NULL
     * @var string
     */
    public $givenName;

    /**
     * @sal VARCHAR(64) NOT NULL
     * @var string
     */
    public $familyName;

    /**
     * @sql VARCHAR(2048) NOT NULL
     * @var string
     */
    public $picture;

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $userMetaId;
}
