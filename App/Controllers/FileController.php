<?php

namespace App\Controllers;

use App\Models\Asset;
use App\Services\Files\AssetFilesConfig;
use App\Models\File;
use App\Services\Files\AssetFilesService;
use App\Services\Router\Request;
use App\Services\Router\RequestError;
use App\Services\Router\DataValidator;
use App\Services\Router\Response;

require_once __DIR__ . '/../../config/preview-data/preview_data.php';

class FileController
{
    private static function HostRawFile(string $path, string $name): void
    {
        if (!file_exists($path)) {
            throw RequestError::CreateFieldError(404, 'server', 'File not found.');
        }

        $mimeType = mime_content_type($path) ?: 'application/octet-stream';

        http_response_code(200);
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($path));

        $safeFileName = str_replace(['"', "'", '\\'], '', $name);
        $encodedFileName = rawurlencode($safeFileName);

        header('Content-Disposition: inline; filename="' . $safeFileName . '"; filename*=UTF-8\'\'' . $encodedFileName);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($path);
        exit;
    }

    private static function TryHostWatermarkedImagePreview(File $image_file): void
    {
        $watermarkPath = PREVIEW_IMAGES['watermark'];

        if (!file_exists($image_file->path) || !file_exists($watermarkPath)) {
            throw RequestError::CreateFieldError(404, 'server', 'File not found.');
        }

        $service = new AssetFilesService();

        $extension = $service->ExtractFileExtension($image_file->name);

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($image_file->path);
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($image_file->path);
                break;
            case 'webp':
                $sourceImage = imagecreatefromwebp($image_file->path);
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($image_file->path);
                break;
            default:
                return; // exit if unsupported watermarking
        }

        $watermarkImage = imagecreatefrompng($watermarkPath);

        $srcW = imagesx($sourceImage);
        $srcH = imagesy($sourceImage);
        $wmW = imagesx($watermarkImage);
        $wmH = imagesy($watermarkImage);

        imagealphablending($sourceImage, true);
        imagesavealpha($sourceImage, true);

        for ($y = 0; $y < $srcH; $y += $wmH) {
            for ($x = 0; $x < $srcW; $x += $wmW) {
                imagecopy($sourceImage, $watermarkImage, $x, $y, 0, 0, $wmW, $wmH);
            }
        }

        ob_start();
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($sourceImage);
                break;
            case 'png':
                imagepng($sourceImage);
                break;
            case 'webp':
                imagewebp($sourceImage);
                break;
            case 'gif':
                imagegif($sourceImage);
                break;
        }
        $imageData = ob_get_clean();

        $safeFileName = str_replace(['"', "'", '\\'], '', $image_file->name);
        $encodedFileName = rawurlencode($safeFileName);

        $mime_type = \mime_content_type($image_file->path);

        http_response_code(200);
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . strlen($imageData));
        header('Content-Disposition: inline; filename="watermarked-' . $safeFileName . '"; filename*=UTF-8\'\'' . $encodedFileName);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $imageData;

        imagedestroy($sourceImage);
        imagedestroy($watermarkImage);
        exit;
    }

    private static function HostPreview(string $group, File $previewedFile): void
    {
        $path = PREVIEW_IMAGES['placeholders'][$group];
        $name = pathinfo($previewedFile->name, \PATHINFO_FILENAME) . '-preview.' . pathinfo($path, \PATHINFO_EXTENSION);

        if ($group == AssetFilesConfig::FILE_GROUP_IMAGE) {
            // Prefer hosting watermaked preview
            static::TryHostWatermarkedImagePreview($previewedFile);
        }

        static::HostRawFile($path, $name);
    }

    private static function HostFile(File $file): void
    {
        static::HostRawFile($file->path, $file->name);
    }

    /**
     * @param File $file
     * @param Asset $asset
     * @return array{id: int, name: string, group: string, fileType: string, isHidden: string, order: int, isPreview: string}
     */
    private static function CreateFileData(File $file, Asset $asset): array
    {
        $mimeType = mime_content_type($file->path) ?: 'application/octet-stream';

        return [
            'id' => $file->id,
            'name' => $file->name,
            'group' => $file->group,
            'fileType' => $mimeType,
            'isHidden' => $file->isHidden,
            'order' => $file->order,
            'isPreview' => $file->id == $asset->previewFileId
        ];
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
                return self::CreateFileData($file, $asset);
            }, File::SelectWhereModels('assetId = :assetId', [
                ':assetId' => $asset->id
            ]));

            $res->SetJSON([
                'files' => $files
            ]);
        };
    }

    /**
     * Returns the file's modified preview.
     * - Image is watermarked
     * - 3D models, Audio and others have their placeholder image
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function SelectPreview(): \Closure
    {
        return function (Request $req, Response $res): void {
            $variables = $req->GetVariables();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $variables, ['id']);
            $id = intval($variables['id']);

            /**
             * @var File
             */
            $file = File::SelectModel($id);

            if ($file == null) {
                throw RequestError::CreateFieldError(404, 'id', 'File with %key%: \'' . $id . '\' doesn\'t exist');
            }

            static::HostPreview($file->group, $file);
        };
    }

    /**
     * Returns the file as is.
     * @return (\Closure(Request $req, Response $res): void)
     */
    public static function SelectAsset(): \Closure
    {
        return function (Request $req, Response $res): void {
            $variables = $req->GetVariables();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $variables, ['id']);
            $id = intval($variables['id']);

            /**
             * @var File
             */
            $file = File::SelectModel($id);

            if ($file == null) {
                throw RequestError::CreateFieldError(404, 'id', 'File with %key%: \'' . $id . '\' doesn\'t exist');
            }

            static::HostFile($file);
        };
    }

    /**
    * Returns the files meta.
    * @return (\Closure(Request $req, Response $res): void)
    */
    public static function SelectAssetMeta(): \Closure
    {
        return function (Request $req, Response $res): void {
            $variables = $req->GetVariables();

            DataValidator::ValidateFieldsAre([DataValidator::REQUIRED, DataValidator::NUMERIC], $variables, ['id']);
            $id = intval($variables['id']);

            /**
             * @var File
             */
            $file = File::SelectModel($id);

            if ($file == null) {
                throw RequestError::CreateFieldError(404, 'id', 'File with %key%: \'' . $id . '\' doesn\'t exist');
            }

            /**
             * @var Asset
             */
            $asset = Asset::SelectModel($file->assetId);

            $res->SetJSON([
                'meta' => self::CreateFileData($file, $asset),
            ]);
        };
    }

    /**
    * @return (\Closure(Request $req, Response $res): void)
    */
    public static function SelectSupportedFileTypes(): \Closure
    {
        return function (Request $req, Response $res): void {
            $res->SetJSON([
                'supportedFileExtensions' => AssetFilesConfig::$SUPPORTED_EXTENSIONS,
            ]);
        };
    }

}
