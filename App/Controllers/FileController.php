<?php

namespace App\Controllers;

use App\Models\File;
use App\Router\Request;
use App\Router\RequestError;
use App\Router\DataValidator;
use App\Router\Response;

class FileController
{
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
             * @var File
             */
            $file = File::SelectModel($id);

            if ($file == null) {
                throw RequestError::CreateFieldError(404, 'id', 'File with %key%: \'' . $id . '\' doesn\'t exist');
            }

            $path = $file->path;

            if (!file_exists($path)) {
                throw RequestError::CreateFieldError(404, 'server', 'File not found');
            }

            http_response_code(200);
            header('Content-Type: ' . $file->type);
            header('Content-Length: ' . filesize($path));

            $safeFileName = str_replace(['"', "'", '\\'], '', $file->name);

            header('Content-Disposition: inline; filename="' . $safeFileName . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            readfile($path);
            exit;
        };
    }
}
