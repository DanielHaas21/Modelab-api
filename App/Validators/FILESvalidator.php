<?php

namespace App\Validators;

require_once './App/Validators/Base/BaseValidator.php';
require_once './App/Validators/Base/ValidatorStructure.php';


use App\Validators\Base\BaseValidator;
use App\Validators\Base\ValidatorStructure;

class FILESvalidator extends BaseValidator implements ValidatorStructure
{
    public function __construct($constant)
    {
        parent::__construct($constant);
    }
    /**
     * @var $isValid Should always be utilized to return bool, Can be found in BaseValidator
     * @return bool
     *
     */
    public function Validate(): bool
    {
        self::$isValid = $this->ValidateStructure([
            'dataPath' => '',
            'maxSizeBytes' => '',
            'supportedTypes' => [
                'model' => [

                ],
                'audio' => [

                ],
                'image' => [

                ],
                'other' => [

                ],
            ],
        ]) && self::$isValid;

        self::$isValid = $this->CheckProperty('dataPath', function ($val) {
            return !empty($val) ?: (!print "dataPath shouldn't be empty \n");
        }) && self::$isValid;

        self::$isValid = $this->CheckProperty('maxSizeBytes', function ($val) {
            return !empty($val) ?: (!print "dataPath shouldn't be empty \n");
        }) && self::$isValid;

        self::$isValid = $this->CheckProperty('supportedTypes.model', function ($val) {
            $expected_vals = [ 'application/x-tgif', 'model/mtl', 'model/gltf-binary', 'application/octet-stream', 'model/gltf+json', 'model/stl'];
            foreach ($expected_vals as $v) {
                if (!in_array($v, $val, true)) {
                    echoLine("value $v is missing from supportedTypes.model");
                    return false;
                }
            }
            return true;
        }) && self::$isValid;

        self::$isValid = $this->CheckProperty('supportedTypes.audio', function ($val) {
            $expected_vals = [ 'audio/ogg', 'audio/mpeg','audio/wav', 'audio/flac'];
            foreach ($expected_vals as $v) {
                if (!in_array($v, $val, true)) {
                    echoLine("value $v is missing from supportedTypes.audio");
                    return false;
                }
            }
            return true;
        }) && self::$isValid;

        self::$isValid = $this->CheckProperty('supportedTypes.image', function ($val) {
            $expected_vals = ['image/png','image/jpeg', 'image/gif', 'image/svg+xml', 'image/webp', 'image/tiff', 'image/bmp'];
            foreach ($expected_vals as $v) {
                if (!in_array($v, $val, true)) {
                    echoLine("value $v is missing from supportedTypes.image");
                    return false;
                }
            }
            return true;
        }) && self::$isValid;

        self::$isValid = $this->CheckProperty('supportedTypes.other', function ($val) {
            $expected_vals = [ 'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/gzip','application/x-tar'];
            foreach ($expected_vals as $v) {
                if (!in_array($v, $val, true)) {
                    echoLine("value $v is missing from supportedTypes.other");
                    return false;
                }
            }
            return true;
        }) && self::$isValid;


        return self::$isValid;
    }
    public function Build(): void
    {
        $this->GenerateFile('./config/files.example.php', './config/files.php');
    }
    public function Run(): void
    {
        echoLine("--------- FILES config ------------");
        if ($this->FileExists('./config/files.php')) {
            echoLine("files.php config file already exists");
            echoLine("        Skipping process          ");
            return;
        }
        if (!$this->Validate()) {
            echoLine("      Validation failed          ");
            return;
        }
        echoLine("         No errors found          ");
        echoLine("  Building config files script... ");
        $this->Build();
    }
}