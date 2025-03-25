<?php

namespace App\Models;

use App\Database\BaseModel;

/**
 * Model of a User from Google OAuth
 */
class User extends BaseModel
{
    /**
     * @sql INT NOT NULL PRIMARY KEY
     * @var int
     */
    public $id;

    /**
     * @sql VARCHAR(512) NOT NULL
     * @var string
     */
    // public $email;

    /**
     * @sql VARCHAR(256) NOT NULL
     * @var string
     */
    // public $givenName;

    /**
     * @sql VARCHAR(256) NOT NULL
     * @var string
     */
    // public $familyName;
    /**
     * @sql VARCHAR(2048) NOT NULL
     * @var string
     */
    // public $picture;

    /**
     * @sql INT NOT NULL
     * @var string
     */
    // public $userMetaId;
}
