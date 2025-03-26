<?php

namespace App\Controllers;

use App\Models\Category;
use App\Router\Request;
use App\Router\RequestError;
use App\Router\Response;

class CategoryController
{
    /**
     * Gets all categories
     * @return (callable(Request, Response):void)
     */
    public static function GetAllCategories(): callable
    {
        return function (Request $req, Response $res): void {
            $categories = Category::SelectAll();

            $res->SetText(var_export($categories, true));
        };
    }

    /**
     * Creates category
     * @return (callable(Request, Response):void)
     */
    public static function CreateCategory(): callable
    {
        return function (Request $req, Response $res): void {
            $data = $req->GetJSON();

            if (! isset($data['name'])) {
                throw RequestError::CreateFieldError(400, 'name', '%key% is required');
            }
            $name = strval($data['name']);
            if (strlen($name) == 0) {
                throw RequestError::CreateFieldError(400, 'name', '%key% can\'t be empty');
            }
            if (strlen($name) > 64) {
                throw RequestError::CreateFieldError(400, 'name', '%key% can\'t be longer than 64 chars');
            }

            $insertedId = Category::Insert(['name' => $name]);

            $res->SetJSON([
                'message' => 'Category created',
                'id' => $insertedId
            ]);
        };
    }
}
