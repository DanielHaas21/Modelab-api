<?php

namespace App\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Services\Router\Request;
use App\Services\Router\RequestError;
use App\Services\Router\DataValidator;
use App\Services\Router\Response;

class CategoryController
{
    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function SelectAll(): \Closure
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
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function Select(): \Closure
    {
        return function (Request $req, Response $res): void {
            $variables = $req->GetVariables();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $variables, ['id']);
            $id = intval($variables['id']);

            /**
             * @var Category
             */
            $category = Category::SelectModel($id);

            if ($category == null) {
                throw RequestError::CreateFieldError(404, 'id', 'Category with %key%: \'' . $id . '\' doesn\'t exist');
            }

            $res->SetJSON([
                'category' => $category->GetData()
            ]);
        };
    }

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function Create(): \Closure
    {
        return function (Request $req, Response $res): void {
            $data = $req->GetJSON();

            DataValidator::ValidateFieldsAre(DataValidator::REQUIRED, $data, ['name']);

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

            if ($insertedId == 0) {
                $id = null;
                $message = 'Category already exists';
            } else {
                $id = $insertedId;
                $message = 'Category created';
            }

            $res->SetJSON([
                'message' => $message,
                'id' => $id
            ]);
        };
    }

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function Delete(): \Closure
    {
        return function (Request $req, Response $res): void {
            $variables = $req->GetVariables();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $variables, ['id']);
            $id = intval($variables['id']);

            /**
             * @var Category
             */
            $category = Category::SelectModel($id);

            if ($category == null) {
                throw RequestError::CreateFieldError(404, 'id', 'Category with %key%: \'' . $id . '\' doesn\'t exist');
            }

            $assetIds = array_map(function ($asset) {
                return $asset->id;
            }, Asset::SelectWhereModels("categoryId = :categoryId", [
                ':categoryId' => $category->id
            ]));

            if (count($assetIds) > 0) {
                throw RequestError::CreateFieldError(409, 'link', 'Category \'' . $category->name . '\' is linked to some assets', [
                    'linkedAssets' => $assetIds
                ]);
            }

            $category->Delete();

            $res->SetJSON([
                'message' => 'Category deleted',
                'id' => $id
            ]);
        };
    }
}
