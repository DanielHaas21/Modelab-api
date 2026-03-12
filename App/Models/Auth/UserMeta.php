<?php

namespace App\Models\Auth;

use App\Database\BaseModels\BaseModelId;

class UserMeta extends BaseModelId
{
    /**
     * @param string $email
     * @return User|null
     */
    final public static function SelectOrCreateUserMeta(User $user): UserMeta
    {
        $userMetas = static::SelectWhereModels('userId = :userId', [
            ':userId' => $user->id
        ]);

        $userMeta = count($userMetas) == 0 ? null : $userMetas[0];

        if ($userMeta == null) {
            $userMeta = new UserMeta();
            $userMeta->userId = $user->id;
            $userMeta->clearance = 2;
        }

        return $userMeta;
    }

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $clearance;

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $userId;
}
