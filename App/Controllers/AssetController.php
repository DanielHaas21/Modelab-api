<?php

namespace App\Controllers;

use App\Services\Database\SQL;
use App\Services\Files\AssetFilesConfig;
use App\Services\Files\AssetFilesService;
use App\Middleware\MiddlewareController;
use App\Models\Asset;
use App\Models\AssetTag;
use App\Models\Auth\User;
use App\Models\Category;
use App\Models\File;
use App\Models\Tag;
use App\Services\Router\DataValidator;
use App\Services\Router\Request;
use App\Services\Router\RequestError;
use App\Services\Router\Response;
use Error;
use Exception;

class AssetController
{
    public const MAX_COUNT_PER_PAGE = 50;

    /**
     * @param Asset $asset
     * @param array $tags
     * @param Category $category
     * @return array{category: array, description: string, id: int, name: string, tags: array}
     */
    private static function CreateAssetData(Asset $asset, array $tags, Category $category): array
    {
        return [
            'id' => $asset->id,
            'name' => $asset->name,
            'description' => $asset->description,
            'author' => $asset->author,
            'category' => $category->GetData(),
            'tags' => array_map(function ($assetTag) {
                $tag = Tag::SelectModel($assetTag->tagId);
                if ($tag == null) {
                    throw new Exception('Tag with id:\'' . $assetTag->tagId . '\' not found');
                }
                return $tag->GetData();
            }, $tags),
            'created' => $asset->created,
            'updated' => $asset->updated,
        ];
    }

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function SelectAll(): \Closure
    {
        return function (Request $req, Response $res): void {
            $data = $req->GetJSON();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $data, ['page', 'count']);

            $page = intval($data['page']);
            $countPerPage = intval($data['count']);

            if ($countPerPage <= 0 || $countPerPage > self::MAX_COUNT_PER_PAGE) {
                throw RequestError::CreateFieldError(416, 'count', '%key% must be in range (1-' . self::MAX_COUNT_PER_PAGE . ')');
            }

            $assetCount = SQL::SelectTableCount('*', Asset::GetTableName());
            $pageCount = max(ceil($assetCount / $countPerPage), 1);

            if ($page < 0 || $page >= $pageCount) {
                throw RequestError::CreateFieldError(416, 'page', '%key% must be in range (0-' . ($pageCount - 1) . ')', ['totalPages' => $pageCount]);
            }

            $assetModels = Asset::SelectAllModelsLimited($countPerPage, $countPerPage * $page);

            $assets = array_map(function ($asset) {
                $tags = AssetTag::SelectWhereModels('assetId = :assetId', [
                    ':assetId' => $asset->id
                ]);
                $category = Category::SelectModel($asset->categoryId);

                return self::CreateAssetData($asset, $tags, $category);
            }, $assetModels);

            $res->SetJSON([
                'assets' => $assets,
                'info' => [
                    'page' => $page,
                    'count' => $countPerPage,
                    'pageCount' => $pageCount
                ]
            ]);
        };
    }

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function Search(): \Closure
    {
        return function (Request $req, Response $res): void {
            $data = $req->GetJSON();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $data, ['page', 'count']);

            $page = intval($data['page']);
            $countPerPage = intval($data['count']);

            $nameQuery = $data['nameQuery'] ?? '';
            $descriptionQuery = $data['descriptionQuery'] ?? '';
            $categoryQuery = $data['categoryQuery'] ?? [];
            $tagQuery = $data['tagQuery'] ?? [];
            $authorQuery = $data['authorQuery'] ?? '';

            if ($countPerPage <= 0 || $countPerPage > self::MAX_COUNT_PER_PAGE) {
                throw RequestError::CreateFieldError(416, 'count', '%key% must be in range (1-' . self::MAX_COUNT_PER_PAGE . ')');
            }

            $searchConditions = [];
            $searchParams = [];

            $beforeWhereSql = [];
            $afterWhereSql = [];

            $tableName = Asset::GetTableName();

            if (strlen($nameQuery) != 0) {
                $searchConditions[] = "name LIKE CONCAT('%', :nameQuery, '%')";
                $searchParams[':nameQuery'] = $nameQuery;
            }

            if (strlen($descriptionQuery) != 0) {
                $searchConditions[] = "description LIKE CONCAT('%', :descriptionQuery, '%')";
                $searchParams[':descriptionQuery'] = $descriptionQuery;
            }

            if (strlen($authorQuery) != 0) {
                $searchConditions[] = "author LIKE CONCAT('%', :authorQuery, '%')";
                $searchParams[':authorQuery'] = $authorQuery;
            }

            if (is_array($categoryQuery) && count($categoryQuery) > 0) {
                $categoryIds = array_map(function ($id) {
                    if (!is_numeric($id)) {
                        throw RequestError::CreateFieldError(400, 'categoryQuery', 'All items of %key% must be numeric');
                    }
                    return intval($id);
                }, $categoryQuery);

                $searchConditions[] = "categoryId IN (" . join(',', $categoryIds) . ")";
            }

            if (is_array($tagQuery) && count($tagQuery) > 0) {
                $tagIds = array_map(function ($id) {
                    if (!is_numeric($id)) {
                        throw RequestError::CreateFieldError(400, 'tagQuery', 'All items of %key% must be numeric');
                    }
                    return intval($id);
                }, $tagQuery);

                $assetTagTableName = AssetTag::GetTableName();
                $tagCount = count($tagIds);

                $beforeWhereSql[] = "INNER JOIN $assetTagTableName ON $assetTagTableName.assetId = $tableName.id";

                $searchConditions[] = "tagId IN (" . join(',', $tagIds) . ")";

                $afterWhereSql[] = "GROUP BY $tableName.id HAVING COUNT(DISTINCT $assetTagTableName.tagId) = $tagCount";
            }

            if (count($searchConditions) == 0) {
                throw RequestError::CreateFieldError(400, 'query', 'Not a single %key% specified');
            }

            $searchSql = join(' AND ', $searchConditions);
            $beforeWhereSql = join(' ', $beforeWhereSql);
            $afterWhereSql = join(' ', $afterWhereSql);

            $countSql = "SELECT COUNT(*) FROM (SELECT {$tableName}.* FROM $tableName $beforeWhereSql WHERE $searchSql $afterWhereSql) AS subquery";
            $sqlCom = SQL::MiscExecute($countSql, $searchParams);
            $count = $sqlCom->fetchColumn();

            $pageCount = max(1, ceil($count / $countPerPage));

            if ($page < 0 || $page >= $pageCount) {
                throw RequestError::CreateFieldError(416, 'page', '%key% must be in range (0-' . ($pageCount - 1) . ')', [
                    'totalPages' => $pageCount
                ]);
            }

            $limit = intval($countPerPage);
            $offset = intval($countPerPage * $page);

            $sql = "SELECT {$tableName}.* FROM $tableName $beforeWhereSql WHERE $searchSql $afterWhereSql LIMIT $limit OFFSET $offset";
            $sqlCom = SQL::MiscExecute($sql, $searchParams);
            $assetModels = array_map(function ($data) {
                return Asset::CreateFrom($data);
            }, $sqlCom->fetchAll(\PDO::FETCH_ASSOC));

            $assets = array_map(function ($asset) {
                $tags = AssetTag::SelectWhereModels('assetId = :assetId', [
                    ':assetId' => $asset->id
                ]);
                $category = Category::SelectModel($asset->categoryId);

                return self::CreateAssetData($asset, $tags, $category);
            }, $assetModels);

            $res->SetJSON([
                'assets' => $assets,
                'info' => [
                    'page' => $page,
                    'count' => $countPerPage,
                    'pageCount' => $pageCount
                ]
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
             * @var ?Asset
             */
            $asset = Asset::SelectModel($id);

            if ($asset == null) {
                throw RequestError::CreateFieldError(404, 'id', 'Asset with %key%: \'' . $id . '\' doesn\'t exist');
            }

            $tags =  AssetTag::SelectWhereModels('assetId = :assetId', [
                ':assetId' => $asset->id
            ]);
            $category = Category::SelectModel($asset->categoryId);

            $assetData = self::CreateAssetData($asset, $tags, $category);

            $res->SetJSON([
                'asset' => $assetData
            ]);
        };
    }

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function Create(): \Closure
    {
        return function (Request $req, Response $res): void {
            /**
             * @var User
             */
            $user = $req->GetMiddlewareData(MiddlewareController::USER_MIDDLEWARE);

            $data = $req->GetJSON();

            DataValidator::ValidateFieldsAre(DataValidator::REQUIRED, $data, ['name', 'description', 'categoryId']);
            DataValidator::ValidateFieldsAre(DataValidator::NUMERIC, $data, ['categoryId']);

            $name = $data['name'];
            $description = $data['description'];
            $author = $data['author'] ?? null;
            $categoryId = intval($data['categoryId']);
            $tagIds = $data['tagIds'] ?? [];
            $filesMeta = $data['filesMeta'] ?? [];

            if (strlen($name) == 0 || strlen($name) > 128) {
                throw RequestError::CreateFieldError(400, 'name', '%key% must have a length between (1-128)');
            }
            if (mb_strlen($description) == 0 || mb_strlen($description) > 320) {
                throw RequestError::CreateFieldError(400, 'description', '%key% must have a length between (1-320)');
            }

            $category = Category::SelectModel($categoryId);
            if ($category == null) {
                throw RequestError::CreateFieldError(404, 'categoryId', 'Category with id:\'' . $categoryId . '\' doesn\'t exist');
            }

            if (!is_array($tagIds)) {
                throw RequestError::CreateFieldError(400, 'tagIds', '%key% must be an array');
            }

            /**
             * @var Tag[]
             */
            $tags = [];
            foreach ($tagIds as $tagId) {
                if (!is_numeric($tagId)) {
                    throw RequestError::CreateFieldError(400, 'tagIds', 'All items of %key% must be numeric');
                }
                $tagId = intval($tagId);
                $tag = Tag::SelectModel($tagId);
                if ($tag == null) {
                    throw RequestError::CreateFieldError(404, 'tagIds', 'Tag with id:\'' . $tagId . '\' doesn\'t exist');
                }
                $tags[] = $tag;
            }

            if (!isset($_FILES) || !isset($_FILES['files']) || count($_FILES['files']) == 0) {
                throw RequestError::CreateFieldError(400, 'files', 'No files specified');
            }
            if (!is_array($filesMeta)) {
                throw RequestError::CreateFieldError(400, 'filesMeta', '%key% must be an array');
            }

            $service = new AssetFilesService();
            $filesData = $service->ExtractFilesData($filesMeta, $_FILES['files'] ?? null);

            $asset = $service->CreateAsset($user, $name, $description, $category, $tags, $filesData, $author);

            $res->SetJSON([
                'message' => 'Asset created',
                'id' => $asset->id
            ]);
        };
    }

    /**
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function Update(): \Closure
    {
        return function (Request $req, Response $res): void {
            $variables = $req->GetVariables();
            $data = $req->GetJSON();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $variables, ['id']);
            $id = intval($variables['id']);

            DataValidator::ValidateFieldsAre(DataValidator::REQUIRED, $data, ['name', 'description', 'categoryId']);
            DataValidator::ValidateFieldsAre(DataValidator::NUMERIC, $data, ['categoryId']);

            $name = trim($data['name']);
            $description = trim($data['description']);
            $description = str_replace("\r\n", "\n", $description);

            $author = $data['author'] ?? null;
            $categoryId = intval($data['categoryId']);
            $tagIds = $data['tagIds'] ?? [];
            $filesMeta = $data['filesMeta'] ?? [];

            if (strlen($name) == 0 || strlen($name) > 128) {
                throw RequestError::CreateFieldError(400, 'name', '%key% must have a length between (1-128)');
            }
            if (mb_strlen($description) == 0 || mb_strlen($description) > 320) {
                throw RequestError::CreateFieldError(400, 'description', '%key% must have a length between (1-320)' . mb_strlen($description));
            }

            $category = Category::SelectModel($categoryId);
            if ($category == null) {
                throw RequestError::CreateFieldError(404, 'categoryId', 'Category with id:\'' . $categoryId . '\' doesn\'t exist');
            }

            if (!is_array($tagIds)) {
                throw RequestError::CreateFieldError(400, 'tagIds', '%key% must be an array');
            }

            /**
             * @var Tag[]
             */
            $tags = [];
            foreach ($tagIds as $tagId) {
                if (!is_numeric($tagId)) {
                    throw RequestError::CreateFieldError(400, 'tagIds', 'All items of %key% must be numeric');
                }
                $tagId = intval($tagId);
                $tag = Tag::SelectModel($tagId);
                if ($tag == null) {
                    throw RequestError::CreateFieldError(404, 'tagIds', 'Tag with id:\'' . $tagId . '\' doesn\'t exist');
                }
                $tags[] = $tag;
            }

            /**
             * @var ?Asset
             */
            $asset = Asset::SelectModel($id);

            if ($asset == null) {
                throw RequestError::CreateFieldError(404, 'id', 'Asset with %key%: \'' . $id . '\' doesn\'t exist');
            }

            $service = new AssetFilesService();
            $filesData = $service->ExtractFilesData($filesMeta, $_FILES['files'] ?? null);

            $service->UpdateAsset($asset, $name, $description, $category, $tags, $filesData, $author);

            $res->SetJSON([
                'message' => 'Asset updated',
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
             * @var ?Asset
             */
            $asset = Asset::SelectModel($id);

            if ($asset == null) {
                throw RequestError::CreateFieldError(404, 'id', 'Asset with %key%: \'' . $id . '\' doesn\'t exist');
            }

            $service = new AssetFilesService();
            $service->DeleteAsset($asset);

            $res->SetJSON([
                'message' => 'Asset deleted',
                'id' => $id
            ]);
        };
    }
}
