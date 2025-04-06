<?php

/*
    This is a bash-bat executable script
    !DO NOT INCLUDE IT ANYWHERE!
*/

require_once './config/db.example.php';
require_once './config/keys.example.php';
require_once './config/files.example.php';
require_once './App/Validators/DBvalidator.php';
require_once './App/Validators/KEYSvalidator.php';
require_once './App/Validators/FILESvalidator.php';
require_once './auto/utils.php';

use App\Validators\DBvalidator;
use App\Validators\KEYSvalidator;
use App\Validators\FILESvalidator;

$DBvalidator = new DBvalidator(DB_CONFIG);
$DBvalidator->Run();

$KEYSvalidator = new KEYSvalidator(KEYS_CONFIG);
$KEYSvalidator->Run();

$FILESvalidator = new FILESvalidator(FILES_CONFIG);
$FILESvalidator->Run();