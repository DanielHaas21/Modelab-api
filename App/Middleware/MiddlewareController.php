<?php

namespace App\Middleware;

use App\Models\Auth\LoginSession;
use App\Models\Auth\User;
use App\Models\Auth\UserMeta;
use App\Router\Request;
use App\Router\RequestError;
use App\Router\Response;

class MiddlewareController
{
    public const USER_MIDDLEWARE = 'user';
    public const USER_META_MIDDLEWARE = 'userMeta';

    private const BEARER_TOKEN_PREFIX = 'Bearer ';

    private static function LoadMiddleware(Request $req)
    {
        $bearerToken = $req->GetHeaders('Authorization');

        if ($bearerToken == null || strpos($bearerToken, static::BEARER_TOKEN_PREFIX) !== 0) {
            throw RequestError::CreateFieldError(401, 'token', 'Bearer token missing.');
        }

        $token = substr($bearerToken, strlen(static::BEARER_TOKEN_PREFIX));

        $user = LoginSession::GetUser($token);

        if ($user == null) {
            throw RequestError::CreateFieldError(401, 'token', 'Failed to authenticate.');
        }

        $userMeta = UserMeta::SelectOrCreateUserMeta($user);

        $req->SetMiddlewareData(static::USER_MIDDLEWARE, $user);
        $req->SetMiddlewareData(static::USER_META_MIDDLEWARE, $userMeta);
    }

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function UserMiddleware(): callable
    {
        return function (Request $req, Response $res): void {
            static::LoadMiddleware($req);
        };
    }

    /**
     * @param int $minClearance
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function UserClearanceMiddleware(int $minClearance): callable
    {
        return function (Request $req, Response $res) use ($minClearance): void {
            static::LoadMiddleware($req);

            /**
            * @var UserMeta
            */
            $userMeta = $req->GetMiddlewareData(MiddlewareController::USER_META_MIDDLEWARE);

            if (!$userMeta->HasClearance($minClearance)) {
                throw RequestError::CreateFieldError(401, 'clearance', 'You do not have the minimum clearance.');
            }
        };
    }
}
