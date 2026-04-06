<?php

namespace App\Services\Files;

use App\Models\Asset;
use App\Models\AssetTag;
use App\Models\Auth\User;
use App\Models\Category;
use App\Models\File;
use App\Models\Tag;
use App\Services\Router\RequestError;
use Exception;

class AssetFilesService
{
    public function __construct()
    {
        AssetFilesConfig::Load();
    }

    public function ExtractFileExtension(string $file_name): string
    {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }

    public function FindFileGroupFromName(string $file_name): ?string
    {
        $extension = $this->ExtractFileExtension($file_name);
        return $this->FindFileGroup($extension);
    }

    public function FindFileGroup(string $extension): ?string
    {
        foreach (AssetFilesConfig::$SUPPORTED_EXTENSIONS as $group => $extensions) {
            if (in_array($extension, $extensions)) {
                return $group;
            }
        }
        return null;
    }

    public function ExtractFilesData(array $files_meta, ?array $uploaded_files): array
    {
        $files_data = [];
        $upload_index = 0;

        foreach ($files_meta as $meta) {
            $id = $meta['id'] ?? null;
            $isHidden = ($meta['isHidden'] ?? false) == true;
            $order = ($meta['order'] ?? false) == true;
            $isRemoved = ($meta['isRemoved'] ?? false) == true;
            $isPreview = ($meta['isPreview'] ?? false) == true;

            if ($id != null) {
                $id = intval($id);
                $file = File::SelectModel($id);

                if ($file == null) {
                    throw RequestError::CreateFieldError(404, 'filesMeta', 'File with id: \'' . $id . '\' doesn\'t exist');
                }

                $files_data[] = [
                    'name' => null,
                    'type' => null,
                    'tmp_name' => null,
                    'isHidden' => $isHidden,
                    'order' => $order,
                    'isRemoved' => $isRemoved,
                    'isPreview' => $isPreview,
                    'file' => $file
                ];
            } else {
                if ($isRemoved) {
                    continue;
                }

                if ($uploaded_files === null || !isset($uploaded_files['name'][$upload_index])) {
                    throw RequestError::CreateFieldError(400, 'files', 'Missing file upload for metadata');
                }

                $type = $uploaded_files['type'][$upload_index];
                $file_name = $uploaded_files['name'][$upload_index];
                $tmp_name = $uploaded_files['tmp_name'][$upload_index];
                $size = $uploaded_files['size'][$upload_index];

                if ($size > AssetFilesConfig::$MAX_SIZE_BYTES) {
                    throw RequestError::CreateFieldError(400, 'files', 'File \'' . $file_name . '\' is too large. Max size: \'' . AssetFilesConfig::$MAX_SIZE_BYTES . ' B\'');
                }

                $extension = $this->ExtractFileExtension($file_name);
                $file_group = $this->FindFileGroup($extension);
                if ($file_group == null) {
                    throw RequestError::CreateFieldError(400, 'files', 'File \'' . $file_name . '\' is of unsupported file extension: \'' . $extension . '\'');
                }

                $files_data[] = [
                    'name' => $file_name,
                    'type' => $type,
                    'tmp_name' => $tmp_name,
                    'isHidden' => $isHidden,
                    'order' => $order,
                    'isRemoved' => false,
                    'isPreview' => $isPreview,
                    'file' => null
                ];

                $upload_index++;
            }
        }

        return $files_data;
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

        try {
            if (!mkdir($asset->filesDirectory, 0777, true)) {
                throw new Exception('Failed to create asset directory');
            }

            foreach ($filesData as $fileData) {
                $tmp_name = $fileData['tmp_name'];
                $file_name = $fileData['name'];
                $path = $asset->filesDirectory . '/' . uniqid() . '_' . $file_name;

                $group = $this->FindFileGroupFromName($file_name);
                if ($group == null) {
                    throw new Exception('File is not supported: \'' . $file_name . '\'');
                }

                if (is_uploaded_file($tmp_name)) {
                    if (!move_uploaded_file($tmp_name, $path)) {
                        throw new Exception('Failed to move uploaded file: \'' . $file_name . '\'');
                    }
                } else {
                    if (!copy($tmp_name, $path)) {
                        throw new Exception('Failed to copy local file: \'' . $file_name . '\'');
                    }
                }

                $file = new File();
                $file->path = realpath($path) ?: $path;
                $file->name = $file_name;
                $file->group = $group;
                $file->order = $fileData['order'];
                $file->isHidden = $fileData['isHidden'] ? 1 : 0;
                $file->assetId = $asset->id;

                $fileId = $file->Insert();

                if ($fileData['isPreview']) {
                    $asset->previewFileId = $fileId;
                    $asset->Update();
                }
            }
        } catch (Exception $e) {
            $asset->Delete();
            throw $e;
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
            $order = $fileData['order'];
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
                $file->order = $order;
                $file->Update();

                if ($isPreview) {
                    $asset->previewFileId = $file->id;
                    $asset->Update();
                }
            } else {
                $tmp_name = $fileData['tmp_name'];
                $file_name = $fileData['name'];
                $path = $asset->filesDirectory . '/' . uniqid() . '_' . $file_name;

                $group = $this->FindFileGroupFromName($file_name);
                if ($group == null) {
                    throw new Exception('File is not supported: \'' . $file_name . '\'');
                }

                if (is_uploaded_file($tmp_name)) {
                    if (!move_uploaded_file($tmp_name, $path)) {
                        throw new Exception('Failed to move uploaded file: \'' . $file_name . '\'');
                    }
                } else {
                    if (!copy($tmp_name, $path)) {
                        throw new Exception('Failed to copy local file: \'' . $file_name . '\'');
                    }
                }

                $file = new File();
                $file->path = $path;
                $file->name = $file_name;
                $file->group = $this->FindFileGroupFromName($file_name);
                $file->order = $fileData['order'];
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

        // sync tags (delete removed ones)
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

        // sync tags (insert new ones)
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

    public function RemoveStrayAssetFiles(): void
    {
        $asset_dirs = glob(AssetFilesConfig::$DATA_PATH . '/*');

        /**
         * @var Asset[]
         */
        $assets = Asset::SelectAllModels();

        /**
         * @var File[]
         */
        $files = File::SelectAllModels();

        foreach ($asset_dirs as $asset_dir) {
            $file_paths = glob($asset_dir . '/*');

            foreach ($file_paths as $file_path) {
                $is_stray = true;

                foreach ($files as $file) {
                    if (realpath($file->path) == realpath($file_path)) {
                        $is_stray = false;
                        break;
                    }
                }

                if (!$is_stray) {
                    continue;
                }

                if (!unlink($file_path)) {
                    throw new Exception('Failed to remove stray file: \'' . $file_path . '\'');
                }
            }

            $is_stray = true;

            foreach ($assets as $asset) {
                if (realpath($asset->filesDirectory) == realpath($asset_dir)) {
                    $is_stray = false;
                    break;
                }
            }

            if (!$is_stray) {
                continue;
            }

            if (!rmdir($asset_dir)) {
                throw new Exception('Failed to remove stray asset folder: \'' . $asset_dir . '\'');
            }
        }
    }
}
