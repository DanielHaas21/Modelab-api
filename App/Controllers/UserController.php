<?php

namespace App\Controllers;

use App\Middleware\GoogleAuth\GoogleAuth;
use App\Middleware\GoogleAuth\GoogleUser;
use App\Middleware\MiddlewareController;
use App\Models\Auth\LoginSession;
use App\Models\Auth\User;
use App\Models\Auth\UserMeta;
use App\Router\DataValidator;
use App\Router\Request;
use App\Router\RequestError;
use App\Router\Response;

class UserController
{
    /**
     * @param User $user
     * @param UserMeta $userMeta
     * @return array{email: string, givenName: string, familyName: string, picture: string}
     */
    private static function CreateUserData(User $user, UserMeta $userMeta): array
    {
        return [
            'email' => $user->email,
            'givenName' => $user->givenName,
            'familyName' => $user->familyName,
            'picture' => $user->picture,
            'clearance' => $userMeta->clearance,
        ];
    }

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function Select(): \Closure
    {
        return function (Request $req, Response $res): void {
            /**
             * @var User
             */
            $user = $req->GetMiddlewareData(MiddlewareController::USER_MIDDLEWARE);

            /**
            * @var UserMeta
            */
            $userMeta = $req->GetMiddlewareData(MiddlewareController::USER_META_MIDDLEWARE);

            $res->SetJSON([
                'user' => self::CreateUserData($user, $userMeta),
            ]);
        };
    }

    /**
    * @return (\Closure(Request $req, Response $res): void)
    */
    public static function Login(): \Closure
    {
        return function (Request $req, Response $res): void {
            $data = $req->GetJSON();

            DataValidator::ValidateFieldsAre(DataValidator::REQUIRED, $data, ['accessToken']);

            $accessToken = $data['accessToken'];

            $result = GoogleAuth::Login($accessToken);

            if (!$result['success']) {
                throw RequestError::CreateFieldError(401, 'accessToken', 'Failed to authenticate accessToken.');
            }

            $googleUser = $result['user'];

            $user = self::GetOrCreateUser($googleUser);

            $session = LoginSession::SelectOrInsertLoginSession($user);

            $res->SetJSON([
              'token' => $session->token,
            ]);
        };
    }

    private static function GetOrCreateUser(GoogleUser $googleUser): User
    {
        $user = User::SelectUser($googleUser->email);

        if ($user != null) {
            return $user;
        }

        $user = new User();
        $user->email = $googleUser->email;
        $user->givenName = $googleUser->givenName;
        $user->familyName = $googleUser->familyName;
        $user->picture = $googleUser->picture;

        $userId = User::InsertModel($user);

        return User::SelectModel($userId);
    }
}
