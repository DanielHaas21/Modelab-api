<?php

namespace App\Controllers;

use App\Database\SQL;
use App\Middleware\MiddlewareController;
use App\Models\Asset;
use App\Models\AssetTag;
use App\Models\Auth\User;
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
     * @param array $filesMeta
     * @return array{isHidden: bool, isMain: bool, isRemoved: bool, isPreview: bool, name: string|null, tmpName: string|null, type: string|null, file: File|null}
     */
    private static function ExtractFilesData(array $filesMeta): array
    {
        $filesData = [];
        $uploadedFiles = $_FILES['files'] ?? null;
        $uploadIndex = 0;

        foreach ($filesMeta as $meta) {
            $id = $meta['id'] ?? null;
            $isHidden = ($meta['isHidden'] ?? false) == true;
            $isMain = ($meta['isMain'] ?? false) == true;
            $isRemoved = ($meta['isRemoved'] ?? false) == true;
            $isPreview = ($meta['isPreview'] ?? false) == true;

            if ($id != null) {
                $id = intval($id);
                $file = File::SelectModel($id);

                if ($file == null) {
                    throw RequestError::CreateFieldError(404, 'filesMeta', 'File with id: \'' . $id . '\' doesn\'t exist');
                }

                $filesData[] = [
                    'name' => null,
                    'type' => null,
                    'tmpName' => null,
                    'isHidden' => $isHidden,
                    'isMain' => $isMain,
                    'isRemoved' => $isRemoved,
                    'isPreview' => $isPreview,
                    'file' => $file
                ];
            } else {
                if ($isRemoved) {
                    continue;
                }

                if ($uploadedFiles === null || !isset($uploadedFiles['name'][$uploadIndex])) {
                    throw RequestError::CreateFieldError(400, 'files', 'Missing file upload for metadata');
                }

                $type = $uploadedFiles['type'][$uploadIndex];
                $fileName = $uploadedFiles['name'][$uploadIndex];
                $tmpName = $uploadedFiles['tmp_name'][$uploadIndex];
                $size = $uploadedFiles['size'][$uploadIndex];

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

                $filesData[] = [
                    'name' => $fileName,
                    'type' => $type,
                    'tmpName' => $tmpName,
                    'isHidden' => $isHidden,
                    'isMain' => $isMain,
                    'isRemoved' => false,
                    'isPreview' => $isPreview,
                    'file' => null
                ];

                $uploadIndex++;
            }
        }

        return $filesData;
    }

    private static function SetupDataDirectory(): void
    {
        umask(0);

        $dataDir = FILES_CONFIG['dataPath'];
        if (!is_dir($dataDir) && !mkdir($dataDir, 0777, true)) {
            throw new Error('Failed to create data directory');
        }
    }

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
                return Tag::SelectModel($assetTag->tagId)->GetData();
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
    public static function SelectFiles(): \Closure
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

            $files = array_map(function ($file) use ($asset) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'fileType' => $file->type,
                    'isHidden' => $file->isHidden,
                    'isMain' => $file->isMain,
                    'isPreview' => $file->id == $asset->previewFileId
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

            $filesData = self::ExtractFilesData($filesMeta);

            self::SetupDataDirectory();

            $asset = new Asset();
            $asset->name = trim($name);
            $asset->description = trim($description);
            $description = str_replace("\r\n", "\n", $description);
            $asset->author = ($author === null || trim($author) === '') ? null : trim($author);

            $asset->categoryId = $categoryId;
            $asset->uploaderId = $user->id;

            $currentTime = new \DateTime();
            $asset->created = $currentTime->format('Y-m-d H:i:s');
            $asset->updated = $currentTime->format('Y-m-d H:i:s');

            $asset->Insert();

            $filesDirectory = FILES_CONFIG['dataPath'] . '/' . uniqid($asset->id . '_');

            $asset->filesDirectory = $filesDirectory;
            $asset->Update();

            if (!mkdir($asset->filesDirectory, 0777, true)) {
                $asset->Delete();
                throw new Error('Failed to create asset directory');
            }

            foreach ($filesData as $fileData) {
                $tmpName = $fileData['tmpName'];
                $fileName = $fileData['name'];
                $path = $asset->filesDirectory . '/' . uniqid() . '_' . $fileName;

                if (!move_uploaded_file($tmpName, $path)) {
                    throw new Error('Failed to move uploaded file: \'' . $fileName . '\'');
                }

                $file = new File();
                $file->path = $path;
                $file->name = $fileData['name'];
                $file->type = $fileData['type'];
                $file->isMain = $fileData['isMain'] ? 1 : 0;
                $file->isHidden = $fileData['isHidden'] ? 1 : 0;
                $file->assetId = $asset->id;

                $fileId = $file->Insert();

                if ($fileData['isPreview']) {
                    $asset->previewFileId = $fileId;
                    $asset->Update();
                }
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

            $asset->name = $name;
            $asset->description = $description;
            $asset->author = ($author === null || trim($author) === '') ? null : trim($author);
            $asset->categoryId = $categoryId;

            $currentTime = new \DateTime();
            $asset->updated = $currentTime->format('Y-m-d H:i:s');

            $asset->Update();

            $filesData = self::ExtractFilesData($filesMeta);

            foreach ($filesData as $fileData) {
                /**
                 * @var File|null
                 */
                $file = $fileData['file'];
                $isRemoved = $fileData['isRemoved'];
                $isHidden = $fileData['isHidden'];
                $isMain = $fileData['isMain'];
                $isPreview = $fileData['isPreview'];

                if ($file != null) {
                    if ($file->assetId != $asset->id) {
                        throw RequestError::CreateFieldError(
                            404,
                            'filesMeta',
                            'File with id: \'' . $file->id . '\' doesn\'t belong to asset with id: \'' . $asset->id . '\''
                        );
                    }
                    if ($isRemoved) {
                        if (file_exists($file->path) && !unlink($file->path)) {
                            throw new Error('Failed to delete asset file with id: \'' . $file->id . '\'');
                        }
                        $file->Delete();

                        if ($asset->previewFileId == $file->id) {
                            $asset->previewFileId = null;
                            $asset->Update();
                        }
                        continue;
                    }

                    $file->isHidden = $isHidden ? 1 : 0;
                    $file->isMain = $isMain ? 1 : 0;
                    $file->Update();

                    if ($isPreview) {
                        $asset->previewFileId = $file->id;
                        $asset->Update();
                    }
                } else {
                    $tmpName = $fileData['tmpName'];
                    $fileName = $fileData['name'];
                    $path = $asset->filesDirectory . '/' . uniqid() . '_' . $fileName;

                    if (!move_uploaded_file($tmpName, $path)) {
                        throw new Error('Failed to move uploaded file: \'' . $fileName . '\'');
                    }

                    $file = new File();
                    $file->path = $path;
                    $file->name = $fileData['name'];
                    $file->type = $fileData['type'];
                    $file->isMain = $fileData['isMain'] ? 1 : 0;
                    $file->isHidden = $fileData['isHidden'] ? 1 : 0;
                    $file->assetId = $asset->id;

                    $fileId = $file->Insert();

                    if ($isPreview) {
                        $asset->previewFileId = $fileId;
                        $asset->Update();
                    }
                }
            }

            /**
             * @var AssetTag[]
             */
            $assetTags = AssetTag::SelectWhereModels('assetId = :assetId', [':assetId' => $asset->id]);

            foreach ($assetTags as $curentTag) {
                $wasDeleted = true;
                foreach ($tags as $tag) {
                    if ($tag->id == $curentTag->tagId) {
                        $wasDeleted = false;
                        break;
                    }
                }

                if ($wasDeleted) {
                    $curentTag->Delete();
                }
            }

            foreach ($tags as $tag) {
                $assetTags = AssetTag::SelectWhereModels('assetId = :assetId AND tagId = :tagId', [':assetId' => $asset->id, ':tagId' => $tag->id]);
                if (count($assetTags) > 0) {
                    continue;
                }
                $assetTag = new AssetTag();
                $assetTag->assetId = $asset->id;
                $assetTag->tagId = $tag->id;

                $assetTag->Insert();
            }

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

            /**
             * @var File[]
             */
            $files = File::SelectWhereModels('assetId = :assetId', [
                ':assetId' => $asset->id
            ]);
            foreach ($files as $file) {
                if (file_exists($file->path) && !unlink($file->path)) {
                    throw new Error('Failed to delete asset file with id: \'' . $file->id . '\'');
                }

                $file->Delete();
            }

            if (is_dir($asset->filesDirectory) && !rmdir($asset->filesDirectory)) {
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
