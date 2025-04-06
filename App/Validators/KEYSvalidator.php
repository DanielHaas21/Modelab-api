<?php

namespace App\Validators;

require_once './App/Validators/Base/BaseValidator.php';
require_once './App/Validators/Base/ValidatorStructure.php';


use App\Validators\Base\BaseValidator;
use App\Validators\Base\ValidatorStructure;

class KEYSvalidator extends BaseValidator implements ValidatorStructure
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
            'google' => [
                'clientId' => ''
            ],
        ]) && self::$isValid;

        self::$isValid = $this->CheckProperty('google.clientId', function ($val) {
            return !empty($val) ?: (!print "Google clientId shouldn't be empty \n");
        }) && self::$isValid;

        return self::$isValid;
    }
    public function Build(): void
    {
        $this->GenerateFile('./config/keys.example.php', './config/keys.php');
    }
    public function Run(): void
    {
        echoLine("--------- KEYS config ------------");
        if ($this->FileExists('./config/keys.php')) {
            echoLine("keys.php config file already exists");
            echoLine("        Skipping process         ");
            return;
        }
        if (!$this->Validate()) {
            echoLine("      Validation failed          ");
            return;
        }
        echoLine("        No errors found          ");
        echoLine("  Building config keys script... ");
        $this->Build();
    }
}