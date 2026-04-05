<?php

namespace App\Services\Files;

use App\Models\Asset;
use App\Models\AssetTag;
use App\Models\Auth\User;
use App\Models\Category;
use App\Models\File;
use App\Models\Tag;
use App\Router\RequestError;
use Exception;

class AssetFilesService
{
    public function ExtractFilesData(array $filesMeta, ?array $uploadedFiles): array
    {
        $filesData = [];
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

                if ($size > AssetFilesConfig::$MAX_SIZE_BYTES) {
                    throw RequestError::CreateFieldError(400, 'files', 'File \'' . $fileName . '\' is too large. Max size: \'' . AssetFilesConfig::$MAX_SIZE_BYTES . ' B\'');
                }

                $foundTypeGroup = null;
                foreach (AssetFilesConfig::$SUPPORTED_TYPES as $groupName => $typeGroup) {
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

    public function CreateAsset(User $uploader, string $name, string $description, Category $category, array $tags, array $filesData, ?string $author): Asset
    {
        $asset = new Asset();
        $asset->name = trim($name);
        $asset->description = trim($description);
        $asset->author = ($author === null || trim($author) === '') ? null : trim($author);
        $asset->categoryId = $category->id;
        $asset->uploaderId = $uploader->id;

        $currentTime = new \DateTime();
        $asset->created = $currentTime->format('Y-m-d H:i:s');
        $asset->updated = $currentTime->format('Y-m-d H:i:s');

        $asset->Insert();

        $filesDirectory = AssetFilesConfig::$DATA_PATH . '/' . uniqid($asset->id . '_');
        $asset->filesDirectory = $filesDirectory;
        $asset->Update();

        if (!mkdir($asset->filesDirectory, 0777, true)) {
            $asset->Delete();
            throw new Exception('Failed to create asset directory');
        }

        foreach ($filesData as $fileData) {
            $tmpName = $fileData['tmpName'];
            $fileName = $fileData['name'];
            $path = $asset->filesDirectory . '/' . uniqid() . '_' . $fileName;

            // Conditional upload check for CLI support
            if (is_uploaded_file($tmpName)) {
                if (!move_uploaded_file($tmpName, $path)) {
                    throw new Exception('Failed to move uploaded file: \'' . $fileName . '\'');
                }
            } else {
                if (!copy($tmpName, $path)) {
                    throw new Exception('Failed to copy local file: \'' . $fileName . '\'');
                }
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

        return $asset;
    }

    /**
     * @param Asset $asset
     * @param string $name
     * @param string $description
     * @param Category $category
     * @param Tag[] $tags
     * @param array $filesData
     * @param string|null $author
     * @return void
     */
    public function UpdateAsset(Asset $asset, string $name, string $description, Category $category, array $tags, array $filesData, ?string $author): void
    {
        $asset->name = trim($name);
        $asset->description = trim($description);
        $asset->author = ($author === null || trim($author) === '') ? null : trim($author);
        $asset->categoryId = $category->id;

        $currentTime = new \DateTime();
        $asset->updated = $currentTime->format('Y-m-d H:i:s');
        $asset->Update();

        foreach ($filesData as $fileData) {
            $file = $fileData['file'];
            $isRemoved = $fileData['isRemoved'];
            $isHidden = $fileData['isHidden'];
            $isMain = $fileData['isMain'];
            $isPreview = $fileData['isPreview'];

            if ($file != null) {
                if ($file->assetId != $asset->id) {
                    throw RequestError::CreateFieldError(404, 'filesMeta', 'File with id: \'' . $file->id . '\' doesn\'t belong to asset with id: \'' . $asset->id . '\'');
                }

                if ($isRemoved) {
                    if (file_exists($file->path) && !unlink($file->path)) {
                        throw new Exception('Failed to delete asset file with id: \'' . $file->id . '\'');
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

                if (is_uploaded_file($tmpName)) {
                    if (!move_uploaded_file($tmpName, $path)) {
                        throw new Exception('Failed to move uploaded file: \'' . $fileName . '\'');
                    }
                } else {
                    if (!copy($tmpName, $path)) {
                        throw new Exception('Failed to copy local file: \'' . $fileName . '\'');
                    }
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

        $assetTags = AssetTag::SelectWhereModels('assetId = :assetId', [':assetId' => $asset->id]);

        // Sync tags (delete removed ones)
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

        // Sync tags (insert new ones)
        foreach ($tags as $tag) {
            $existingTags = AssetTag::SelectWhereModels('assetId = :assetId AND tagId = :tagId', [':assetId' => $asset->id, ':tagId' => $tag->id]);
            if (count($existingTags) > 0) {
                continue;
            }
            $assetTag = new AssetTag();
            $assetTag->assetId = $asset->id;
            $assetTag->tagId = $tag->id;
            $assetTag->Insert();
        }
    }

    public function DeleteAsset(Asset $asset): void
    {
        $files = File::SelectWhereModels('assetId = :assetId', [
            ':assetId' => $asset->id
        ]);

        foreach ($files as $file) {
            if (file_exists($file->path) && !unlink($file->path)) {
                throw new Exception('Failed to delete asset file with id: \'' . $file->id . '\'');
            }
            $file->Delete();
        }

        if (is_dir($asset->filesDirectory) && !rmdir($asset->filesDirectory)) {
            throw new Exception('Failed to delete asset directory');
        }

        $asset->Delete();
    }
}
