<?php

namespace App\Services\Users;

use App\Configuration\AppConfig;
use App\Middleware\Clearance;
use App\Models\Auth\LoginSession;
use App\Models\Auth\User;
use App\Models\Auth\UserMeta;
use App\Services\Settings\SettingKey;
use App\Services\Settings\SettingsService;
use Exception;

class UserService
{
    /**
     * @var SettingsService
     */
    private $settings_service;

    public function __construct()
    {
        AppConfig::Load();

        $this->settings_service = new SettingsService();
    }

    public function IsEmailAllowed(string $email): bool
    {
        $domain = substr($email, strrpos($email, '@') + 1);

        /**
         * @var string[]
         */
        $domain_whitelist = $this->settings_service->GetSetting(SettingsService::ALLOWED_EMAIL_DOMAINS)['value'];

        return in_array($domain, $domain_whitelist);
    }

    private function CreateUncheckedUser(string $email, string $givenName, string $familyName, string $picture): User
    {
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

    public function GetOrCreateUser(string $email, string $givenName, string $familyName, string $picture): User
    {
        $user = User::SelectUser($email);

        if ($user != null) {
            return $user;
        }

        if (!$this->IsEmailAllowed($email)) {
            throw new Exception('Provided email is not allowed');
        }

        return $this->CreateUncheckedUser($email, $givenName, $familyName, $picture);
    }

    public function GetOrCreateDevUser(): User
    {
        return $this->CreateUncheckedUser(
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
