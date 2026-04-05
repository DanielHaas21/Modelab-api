<?php

namespace App\Services;

use App\AppConfig;
use App\Middleware\Clearance;
use App\Models\Auth\LoginSession;
use App\Models\Auth\User;
use App\Models\Auth\UserMeta;

class UserService
{
    public function GetOrCreateUser(string $email, string $givenName, string $familyName, string $picture): User
    {
        $user = User::SelectUser($email);

        if ($user != null) {
            return $user;
        }

        $user = new User();
        $user->email = $email;
        $user->givenName = $givenName;
        $user->familyName = $familyName;
        $user->picture = $picture;

        $userId = User::InsertModel($user);
        $user->id = $userId;

        $userMeta = new UserMeta();
        $userMeta->userId = $user->id;
        $userMeta->clearance = AppConfig::$DEV_MODE ? Clearance::OVERLORD : Clearance::USER;
        $userMetaId = UserMeta::InsertModel($userMeta);

        $user->userMetaId = $userMetaId;
        User::UpdateModel($user);

        return User::SelectModel($userId);
    }

    public function GetOrCreateDevUser(): User
    {
        return $this->GetOrCreateUser(
            'john.doe@test.com',
            'John',
            'Doe',
            'https://picsum.photos/128/128'
        );
    }

    public function CreateLoginSession(User $user): LoginSession
    {
        return LoginSession::SelectOrInsertLoginSession($user);
    }
}
