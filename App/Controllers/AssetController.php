<?php

namespace App\Controllers;

use App\Database\SQL;
use App\Models\Asset;
use App\Models\AssetTag;
use App\Models\Category;
use App\Models\File;
use App\Models\Tag;
use App\Router\DataValidator;
use App\Router\Request;
use App\Router\RequestError;
use App\Router\Response;
use Error;

require_once __DIR__ . '/../../config/files.php';

class AssetController
{
    public const MAX_COUNT_PER_PAGE = 50;

    /**
     * @return (callable(Request, Response):void)
     */
    public static function SelectAll(): callable
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
                $tags = array_map(function ($assetTag) {
                    return Tag::SelectModel($assetTag->tagId)->GetData();
                }, AssetTag::SelectWhereModels('assetId = :assetId', [
                    ':assetId' => $asset->id
                ]));

                $category = Category::SelectModel($asset->categoryId);
                return [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'description' => $asset->description,
                    'tags' => $tags,
                    'category' => $category->GetData()
                ];
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
     * @return (callable(Request, Response):void)
     */
    public static function Search(): callable
    {
        return function (Request $req, Response $res): void {
            $data = $req->GetJSON();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $data, ['page', 'count']);

            $page = intval($data['page']);
            $countPerPage = intval($data['count']);

            $nameQuery = $data['nameQuery'] ?? '';
            $descriptionQuery = $data['descriptionQuery'] ?? '';
            $categoryQuery = $data['categoryQuery'] ?? '';
            $tagQuery = $data['tagQuery'] ?? '';

            if ($countPerPage <= 0 || $countPerPage > self::MAX_COUNT_PER_PAGE) {
                throw RequestError::CreateFieldError(416, 'count', '%key% must be in range (1-' . self::MAX_COUNT_PER_PAGE . ')');
            }

            $searchConditions = [];
            $searchParams = [];

            $beforeWhereSql = [];
            $afterWhereSql = [];

            $tableName = Asset::GetTableName();

            // Query name
            if (strlen($nameQuery) != 0) {
                $searchConditions[] = "name LIKE CONCAT('%', :nameQuery, '%')";
                $searchParams[':nameQuery'] = $nameQuery;
            }

            // Query description
            if (strlen($descriptionQuery) != 0) {
                $searchConditions[] = "description LIKE CONCAT('%', :descriptionQuery, '%')";
                $searchParams[':descriptionQuery'] = $descriptionQuery;
            }

            // Query categoryId
            if (strlen($categoryQuery) != 0) {
                $categoryQueries = array_map(function ($query) {
                    $query = trim($query);
                    if (!is_numeric($query)) {
                        throw RequestError::CreateFieldError(400, 'categoryQuery', '%key% has a non numeric id');
                    }
                    return intval($query);
                }, explode(',', $categoryQuery));

                $searchConditions[] = "categoryId IN (" . join(',', $categoryQueries) . ")";
            }

            // Query tagIds, must match all
            if (strlen($tagQuery) != 0) {
                $tagQueries = array_map(function ($query) {
                    $query = trim($query);
                    if (!is_numeric($query)) {
                        throw RequestError::CreateFieldError(400, 'tagQuery', '%key% has a non numeric id');
                    }
                    return intval($query);
                }, explode(',', $tagQuery));

                $assetTagTableName = AssetTag::GetTableName();
                $tagCount = count($tagQueries);

                $beforeWhereSql[] = "INNER JOIN $assetTagTableName ON $assetTagTableName.assetId = $tableName.id";

                $searchConditions[] = "tagId IN (" . join(',', $tagQueries) . ")";

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
                $tags = array_map(function ($assetTag) {
                    return Tag::SelectModel($assetTag->tagId)->GetData();
                }, AssetTag::SelectWhereModels('assetId = :assetId', [
                    ':assetId' => $asset->id
                ]));

                $category = Category::SelectModel($asset->categoryId);
                return [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'description' => $asset->description,
                    'tags' => $tags,
                    'category' => $category->GetData()
                ];
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
     * @return (callable(Request, Response):void)
     */
    public static function Select(): callable
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

            $tags = array_map(function ($assetTag) {
                return Tag::SelectModel($assetTag->tagId)->GetData();
            }, AssetTag::SelectWhereModels('assetId = :assetId', [
                ':assetId' => $asset->id
            ]));

            $category = Category::SelectModel($asset->categoryId);

            $assetData = [
                'id' => $asset->id,
                'name' => $asset-> name,
                'description' => $asset-> description,
                'category' => $category->GetData(),
                'tags' => $tags
            ];

            $res->SetJSON([
                'asset' => $assetData
            ]);
        };
    }

    /**
     * @return (callable(Request, Response):void)
     */
    public static function SelectFiles(): callable
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

            $files = array_map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'type' => $file->type,
                    'isHidden' => $file->isHidden
                ];
            }, File::SelectWhereModels('assetId = :assetId', [
                ':assetId' => $asset->id
            ]));

            $res->SetJSON([
                'files' => $files
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

            DataValidator::ValidateFieldsAre(DataValidator::REQUIRED, $data, ['name', 'description', 'categoryId']);
            DataValidator::ValidateFieldsAre(DataValidator::NUMERIC, $data, ['categoryId']);

            $name = $data['name'];
            $description = $data['description'];
            $categoryId = intval($data['categoryId']);
            $tagIds = $data['tagIds'] ?? [];
            $filesMeta = $data['filesMeta'] ?? [];

            if (strlen($name) == 0 || strlen($name) > 128) {
                throw RequestError::CreateFieldError(400, 'name', '%key% must have a length between (1-128)');
            }
            if (strlen($description) == 0 || strlen($description) > 320) {
                throw RequestError::CreateFieldError(400, 'description', '%key% must have a length between (1-320)');
            }

            $category = Category::SelectModel($categoryId);
            if ($category == null) {
                throw RequestError::CreateFieldError(404, 'categoryId', 'Category with id:\'' . $categoryId . '\' doesn\'t exist');
            }

            if (!is_array($tagIds)) {
                throw RequestError::CreateFieldError(400, 'tagIds', '%key% must be an array');
            }

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

            $filesData = [];
            $uploadedFiles = $_FILES['files'];
            for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
                $type = $uploadedFiles['type'][$i];
                $fileName = $uploadedFiles['name'][$i];
                $tmpName = $uploadedFiles['tmp_name'][$i];
                $size = $uploadedFiles['size'][$i];

                $meta = $filesMeta[$i] ?? [];

                if ($size > FILES_CONFIG['maxSizeBytes']) {
                    throw RequestError::CreateFieldError(400, 'files', 'File \'' . $fileName . '\' is too large. Max size: \'' . FILES_CONFIG['maxSizeBytes'] . ' B\'');
                }

                $foundTypeGroup = null;
                foreach (FILES_CONFIG['supportedTypes'] as $groupName => $typeGroup) {
                    if (in_array($type, $typeGroup)) {
                        $foundTypeGroup = $groupName;
                        break;
                    }
                }

                if ($foundTypeGroup == null) {
                    throw RequestError::CreateFieldError(400, 'files', 'File \'' . $fileName . '\' has unsupported file type: \'' . $type . '\'');
                }

                $isHidden = ($meta['isHidden'] ?? false) == true;

                $filesData[] = [
                    'name' => $fileName,
                    'type' => $type,
                    'tmpName' => $tmpName,
                    'isHidden' => $isHidden
                ];
            }

            umask(0);

            $dataDir = FILES_CONFIG['dataPath'];
            if (!is_dir($dataDir) && !mkdir($dataDir, 0777, true)) {
                throw new Error('Failed to create data directory');
            }

            $asset = new Asset();
            $asset->name = $name;
            $asset->description = $description;

            $asset->categoryId = $categoryId;
            $asset->uploaderId = 0;

            $asset->Insert();

            $assetDir = FILES_CONFIG['dataPath'] . '/' . uniqid($asset->id . '_');

            $asset->filesDirectory = $assetDir;
            $asset->Update();

            if (!mkdir($assetDir, 0777, true)) {
                $asset->Delete();
                throw new Error('Failed to create asset directory');
            }

            foreach ($filesData as $fileData) {
                $fileName = $fileData['name'];
                $type = $fileData['type'];
                $tmpName = $fileData['tmpName'];
                $isHidden = $fileData['isHidden'];

                $path = $assetDir . '/' . uniqid() . '_' . $fileName;
                move_uploaded_file($tmpName, $path);

                $file = new File();
                $file->path = $path;
                $file->name = $fileName;
                $file->type = $type;
                $file->isHidden = $isHidden ? 1 : 0;
                $file->assetId = $asset->id;

                $file->Insert();
            }

            foreach ($tags as $tag) {
                $assetTag = new AssetTag();
                $assetTag->assetId = $asset->id;
                $assetTag->tagId = $tag->id;

                $assetTag->Insert();
            }

            $res->SetJSON([
                'message' => 'Asset created',
                'id' => $asset->id
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

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $variables, ['id']);
            $id = intval($variables['id']);

            /**
             * @var ?Asset
             */
            $asset = Asset::SelectModel($id);

            if ($asset == null) {
                throw RequestError::CreateFieldError(404, 'id', 'Asset with %key%: \'' . $id . '\' doesn\'t exist');
            }

            /**
             * @var File[]
             */
            $files = File::SelectWhereModels('assetId = :assetId', [
                ':assetId' => $asset->id
            ]);
            foreach ($files as $file) {
                if (!unlink($file->path)) {
                    throw new Error('Failed to delete asset file');
                }

                $file->Delete();
            }

            if (!rmdir($asset->filesDirectory)) {
                throw new Error('Failed to delete asset directory');
            }

            $asset->Delete();

            $res->SetJSON([
                'message' => 'Asset deleted',
                'id' => $id
            ]);
        };
    }
}
