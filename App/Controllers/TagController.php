<?php

namespace App\Controllers;

use App\Models\AssetTag;
use App\Models\Tag;
use App\Router\Request;
use App\Router\RequestError;
use App\Router\Response;

class TagController
{
    /**
     * @return (callable(Request, Response):void)
     */
    public static function SelectAll(): callable
    {
        return function (Request $req, Response $res): void {
            /**
             * @var Tag[]
             */
            $tags = Tag::SelectAllModels();

            $tagData = array_map(function ($category) {
                return $category->GetData();
            }, $tags);

            $res->SetJSON([
                'tags' => $tagData
            ]);
        };
    }

    /**
     * @return (callable(Request, Response):void)
     */
    public static function Select(): callable
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

            /**
             * @var Tag
             */
            $tag = Tag::SelectModel($id);

            if ($tag == null) {
                throw RequestError::CreateFieldError(404, 'id', 'Tag with %key%: \'' . $id . '\' doesn\'t exist');
            }

            $res->SetJSON([
                'tag' => $tag->GetData()
            ]);
        };
    }

    /**
     * @return (callable(Request, Response):void)
     */
    public static function Create(): callable
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

            $tag = new Tag();
            $tag->name = $name;

            $insertedId = $tag->Insert();

            if ($insertedId == 0) {
                $id = null;
                $message = 'Tag already exists';
            } else {
                $id = $insertedId;
                $message = 'Tag created';
            }

            $res->SetJSON([
                'message' => $message,
                'id' => $id
            ]);
        };
    }

    /**
     * @return (callable(Request, Response):void)
     */
    public static function Delete(): callable
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

            /**
             * @var Tag
             */
            $tag = Tag::SelectModel($id);

            if ($tag == null) {
                throw RequestError::CreateFieldError(404, 'id', 'Tag with %key%: \'' . $id . '\' doesn\'t exist');
            }

            $assetIds = array_map(function ($assetTag) {
                return $assetTag->assetId;
            }, AssetTag::SelectWhereModels("tagId = :tagId", [
                ':tagId' => $tag->id
            ]));

            if (count($assetIds) > 0) {
                throw RequestError::CreateFieldError(409, 'link', 'Tag \'' . $tag->name . '\' is linked to some assets', [
                    'linkedAssets' => $assetIds,
                ]);
            }

            $tag->Delete();

            $res->SetJSON([
                'message' => 'Tag deleted',
                'id' => $id
            ]);
        };
    }
}
