<?php

namespace App\Models\Auth;

use App\Services\Database\BaseModels\BaseModelId;

class UserMeta extends BaseModelId
{
    /**
     * @param string $email
     * @return UserMeta|null
     */
    final public static function SelectUserMeta(User $user): UserMeta
    {
        $userMetas = static::SelectWhereModels('userId = :userId', [
            ':userId' => $user->id
        ]);

        $userMeta = count($userMetas) == 0 ? null : $userMetas[0];

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

    public function HasClearance(int $minClearance): bool
    {
        return $this->clearance >= $minClearance;
    }
}
