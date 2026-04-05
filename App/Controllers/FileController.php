<?php

namespace App\Controllers;

use App\Services\Files\AssetFilesConfig;
use App\Models\File;
use App\Services\Router\Request;
use App\Services\Router\RequestError;
use App\Services\Router\DataValidator;
use App\Services\Router\Response;

require_once __DIR__ . '/../../config/preview-data/preview_data.php';

class FileController
{
    private static function HostRawFile(string $path, string $type, string $name): void
    {
        if (!file_exists($path)) {
            throw RequestError::CreateFieldError(404, 'server', 'File not found.');
        }

        http_response_code(200);
        header('Content-Type: ' . $type);
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

    // Generated with Gemini
    private static function HostWatermarkedImage(File $imageFile): void
    {
        $watermarkPath = PREVIEW_IMAGES['watermark'];

        if (!file_exists($imageFile->path) || !file_exists($watermarkPath)) {
            throw RequestError::CreateFieldError(404, 'server', 'File not found.');
        }

        // Load source image based on type
        switch ($imageFile->type) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($imageFile->path);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($imageFile->path);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($imageFile->path);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($imageFile->path);
                break;
            default:
                throw RequestError::CreateFieldError(400, 'type', 'Unsupported image type.');
        }

        // Load watermark
        $watermarkImage = imagecreatefrompng($watermarkPath);

        $srcW = imagesx($sourceImage);
        $srcH = imagesy($sourceImage);
        $wmW = imagesx($watermarkImage);
        $wmH = imagesy($watermarkImage);

        imagealphablending($sourceImage, true);
        imagesavealpha($sourceImage, true);

        // Tile watermark across the image
        for ($y = 0; $y < $srcH; $y += $wmH) {
            for ($x = 0; $x < $srcW; $x += $wmW) {
                imagecopy($sourceImage, $watermarkImage, $x, $y, 0, 0, $wmW, $wmH);
            }
        }

        // Buffer image output to get content length
        ob_start();
        switch ($imageFile->type) {
            case 'image/jpeg':
                imagejpeg($sourceImage);
                break;
            case 'image/png':
                imagepng($sourceImage);
                break;
            case 'image/webp':
                imagewebp($sourceImage);
                break;
            case 'image/gif':
                imagegif($sourceImage);
                break;
        }
        $imageData = ob_get_clean();

        $safeFileName = str_replace(['"', "'", '\\'], '', $imageFile->name);
        $encodedFileName = rawurlencode($safeFileName);

        http_response_code(200);
        header('Content-Type: ' . $imageFile->type);
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

    private static function HostPreview(string $category, File $previewedFile): void
    {
        $path = PREVIEW_IMAGES['placeholders'][$category];
        $type = PREVIEW_IMAGES['preview_image_type'];

        $name = pathinfo($previewedFile->name, \PATHINFO_FILENAME) . '-preview.' . pathinfo($path, \PATHINFO_EXTENSION);

        static::HostRawFile($path, $type, $name);
    }

    private static function HostFile(File $file): void
    {
        static::HostRawFile($file->path, $file->type, $file->name);
    }

    /**
     * @param File $file
     * @return array{id: int, name: string, fileType: string}
     */
    private static function CreateFileData(File $file): array
    {
        return [
            'id' => $file->id,
            'name' => $file->name,
            'fileType' => $file->type,
        ];
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

            if (in_array($file->type, AssetFilesConfig::$SUPPORTED_TYPES['model'])) {
                static::HostPreview('model', $file);
            }


            if (in_array($file->type, AssetFilesConfig::$SUPPORTED_TYPES['audio'])) {
                static::HostPreview('audio', $file);
            }


            if (in_array($file->type, AssetFilesConfig::$SUPPORTED_TYPES['image'])) {
                static::HostWatermarkedImage($file);
            }

            static::HostPreview('other', $file);
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

            $res->SetJSON([
                'meta' => self::CreateFileData($file),
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
                'supportedFileTypes' => AssetFilesConfig::$SUPPORTED_TYPES,
            ]);
        };
    }

}
