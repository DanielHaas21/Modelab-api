<?php

namespace App\Middleware;

use App\Models\Auth\LoginSession;
use App\Router\Request;
use App\Router\RequestError;
use App\Router\Response;

class MiddlewareController
{
    public const USER_MIDDLEWARE = 'user';

    private const BEARER_TOKEN_PREFIX = 'Bearer ';

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function UserMiddleware(): callable
    {
        return function (Request $req, Response $res): void {
            $bearerToken = $req->GetHeaders('Authorization');

            if ($bearerToken == null || strpos($bearerToken, static::BEARER_TOKEN_PREFIX) !== 0) {
                throw RequestError::CreateFieldError(401, 'token', 'Bearer token missing.');
            }

            $token = substr($bearerToken, strlen(static::BEARER_TOKEN_PREFIX));

            $user = LoginSession::GetUser($token);

            if ($user == null) {
                throw RequestError::CreateFieldError(401, 'token', 'Failed to authenticate.');
            }

            $req->SetMiddlewareData(static::USER_MIDDLEWARE, $user);
        };
    }
}
