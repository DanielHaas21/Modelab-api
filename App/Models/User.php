<?php

namespace App\Models;

use App\Database\BaseModel;

/**
 * Model of a User from Google OAuth
 */
class User extends BaseModel
{
    /**
     * @sqlType VARCHAR(512)
     * @sql NOT NULL
     * @var string
     */
    public $email;

    // public $givenName;
    // public $familyName;
    // public $picture;
    // public $userMetaId;
}
