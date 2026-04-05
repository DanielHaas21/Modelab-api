<?php

namespace App\Models\Auth;

use App\Services\Database\BaseModels\BaseModelId;

/**
 * Model of a User
 */
class User extends BaseModelId
{
    /**
     * @param string $email
     * @return User|null
     */
    final public static function SelectUser(string $email): ?User
    {
        $users = static::SelectWhereModels('email = :email', [
            ':email' => $email
        ]);

        if (count($users) == 0) {
            return null;
        }

        return $users[0];
    }

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
     * @sql VARCHAR(64) NOT NULL
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
