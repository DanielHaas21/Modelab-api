<?php

namespace App\Models;

use App\Database\BaseModel;

/**
 * Model of a User from Google OAuth
 */
class User extends BaseModel
{
    /**
     * @sql VARCHAR(512) NOT NULL
     * @var string
     */
    public $email;

    // public $givenName;
    // public $familyName;
    // public $picture;
    // public $userMetaId;
}
