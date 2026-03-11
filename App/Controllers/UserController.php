<?php

namespace App\Controllers;

use App\Router\Request;
use App\Router\Response;

class UserController
{
    /**
     * @return (callable(Request, Response):void)
     */
    public static function Select(): callable
    {
        return function (Request $req, Response $res): void {
            $res->SetJSON([
              'test' => 'test'
            ]);
        };
    }
}
