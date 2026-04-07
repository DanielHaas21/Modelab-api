<?php

namespace App\Models\Auth;

use App\Services\Database\BaseModels\BaseModelId;
use App\Services\Database\DateUtils;

/**
 * Model of a user login session
 */
class LoginSession extends BaseModelId
{
    public const MAX_TOKEN_REFRESH_INTERVAL_SECONDS = 60 * 60 * 24 * 2; // 2 days

    private static function GenerateToken(): string
    {
        $bytes = random_bytes(32);
        return bin2hex($bytes);
    }

    private static function TryRefreshSession(?LoginSession $session): ?LoginSession
    {
        if ($session == null) {
            return null;
        }

        $currentTime = new \DateTime();
        $dateRefreshed = DateUtils::FromDatabase($session->refreshed);

        $isRefreshable = $currentTime->getTimestamp() - $dateRefreshed->getTimestamp() <= static::MAX_TOKEN_REFRESH_INTERVAL_SECONDS;

        if ($isRefreshable) {
            $session->refreshed = DateUtils::ToDatabase($currentTime);
            static::UpdateModel($session);
            return $session;
        } else {
            static::DeleteModel($session);
            return null;
        }
    }

    final public static function GetUser(string $token): ?User
    {
        $sessions = static::SelectWhereModels('token = :token', [
            ':token' => $token
        ]);

        /**
         * @var LoginSession|null
        */
        $session = count($sessions) == 0 ? null : $sessions[0];

        $session = static::TryRefreshSession($session);

        if ($session == null) {
            return null;
        }

        $user = User::SelectModel($session->userId);

        if ($user == null) {
            static::DeleteModel($session);
            $session = null;
        }

        return $user;
    }

    /**
     * @param User $user
     * @return LoginSession|null
     */
    final public static function SelectOrInsertLoginSession(User $user): LoginSession
    {
        $sessions = static::SelectWhereModels('userId = :userId', [
            ':userId' => $user->id
        ]);

        /**
         * @var LoginSession|null
         */
        $session = count($sessions) == 0 ? null : $sessions[0];

        $session = static::TryRefreshSession($session);

        if ($session == null) {
            $currentTime = new \DateTime();

            $session = new LoginSession();
            $session->userId = $user->id;
            $session->token = static::GenerateToken();
            $session->refreshed = DateUtils::ToDatabase($currentTime);

            $sessionId = static::InsertModel($session);
            return static::SelectModel($sessionId);
        }
        return $session;
    }

    /**
     * @sql INTEGER NOT NULL
     * @var int
     */
    public $userId;

    /**
     * @sql VARCHAR(256) NOT NULL
     * @var string
     */
    public $token;

    /**
     * @sql DATETIME NOT NULL
     * @var string
     */
    public $refreshed;
}
