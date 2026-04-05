<?php

namespace App\Controllers;

use App\Configuration\AppConfig;
use App\Services\GoogleAuth\GoogleAuth;
use App\Middleware\MiddlewareController;
use App\Models\Auth\User;
use App\Models\Auth\UserMeta;
use App\Services\Router\DataValidator;
use App\Services\Router\Request;
use App\Services\Router\RequestError;
use App\Services\Router\Response;
use App\Services\Users\UserService;

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

            $userService = new UserService();

            if (AppConfig::$DEV_MODE) {
                $user = $userService->GetOrCreateDevUser();
            } else {
                $result = GoogleAuth::Login($accessToken);

                if (!$result['success']) {
                    throw RequestError::CreateFieldError(401, 'accessToken', 'Failed to authenticate accessToken.');
                }

                $googleUser = $result['user'];

                $user = $userService->GetOrCreateUser(
                    $googleUser->email,
                    $googleUser->givenName,
                    $googleUser->familyName,
                    $googleUser->picture
                );
            }

            $session = $userService->CreateLoginSession($user);

            $res->SetJSON([
                'token' => $session->token,
            ]);
        };
    }
}
