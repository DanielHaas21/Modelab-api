<?php

namespace App\Validators;

require_once './App/Validators/Base/BaseValidator.php';
require_once './App/Validators/Base/ValidatorStructure.php';


use App\Validators\Base\BaseValidator;
use App\Validators\Base\ValidatorStructure;

class DBvalidator extends BaseValidator implements ValidatorStructure
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
            'servername' => '',
            'username'   => '',
            'password'   => '',
            'database'   => '',
        ]) && self::$isValid;

        self::$isValid = $this->CheckProperty('database', function ($val) {
            if ($val !== 'modelab_api') {
                echoLine("Database name should be modelab_api");
                return false;
            }
            return true;
        }) && self::$isValid;

        self::$isValid = $this->CheckProperty('username', function ($val) {
            return !empty($val) ?: (!print "Username shouldn't be empty \n");
        }) && self::$isValid;

        self::$isValid = $this->CheckProperty('servername', function ($val) {
            return !empty($val) ?: (!print "Servername is missing \n");
        }) && self::$isValid;

        return self::$isValid;
    }
    public function Build(): void
    {
        $this->GenerateFile('./config/db.example.php', './config/db.php');
    }
    public function Run(): void
    {
        echoLine("----------- DB config ------------");
        if ($this->FileExists('./config/db.php')) {
            echoLine("db.php config file already exists");
            echoLine("        Skipping process          ");
            return;
        }
        if (!$this->Validate()) {
            echoLine("      Validation failed          ");
            return;
        }
        echoLine("      No errors found            ");
        echoLine("  Building config db script...   ");
        $this->Build();
    }
}