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
            /**
             * @var Category[]
             */
            $categories = Category::SelectAllModels();

            $categoryData = array_map(function ($category) {
                return $category->GetData();
            }, $categories);

            $res->SetJSON([
                'categories' => $categoryData
            ]);
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

            $category = new Category();
            $category->name = $name;

            $insertedId = $category->Insert();

            $res->SetJSON([
                'message' => $insertedId == 0 ? 'Category already exists' : 'Category created',
                'id' => $insertedId
            ]);
        };
    }

    /**
     * Creates category
     * @return (callable(Request, Response):void)
     */
    public static function DeleteCategory(): callable
    {
        return function (Request $req, Response $res): void {
            $variables = $req->GetVariables();

            if (! isset($variables['id'])) {
                throw RequestError::CreateFieldError(400, 'id', '%key% is required');
            }
            $id = $variables['id'];

            if (! is_numeric($id)) {
                throw RequestError::CreateFieldError(400, 'id', '%key% is not numeric');
            }
            $id = intval($id);

            $category = Category::SelectModel($id);

            if ($category == null) {
                throw RequestError::CreateFieldError(404, 'id', 'category with %key%: \'' . $id . '\' doesn\'t exist');
            }

            $category->Delete();

            $res->SetJSON([
                'message' => 'Category deleted',
                'id' => $id
            ]);
        };
    }
}
