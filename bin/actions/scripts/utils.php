<?php

use App\Models\Asset;
use App\Models\AssetTag;
use App\Models\Category;
use App\Models\File;
use App\Models\Tag;
use App\Models\Auth\User;
use App\Models\Auth\UserMeta;
use App\Models\Auth\LoginSession;
use App\Models\Config\Log;
use App\Models\Config\Setting;

// remove warnings
error_reporting(E_ALL ^ E_WARNING);

// useful funcitons
function echoLine(string $msg = ''): void
{
    echo "$msg\n";
}

function echoError(Exception $e): void
{
    echoLine(get_class($e) . ': ' . $e->getMessage());
}

// database utils

// TODO: Replace with a recursive function that searches the entire Models/ folder for all classes that extend BaseModel
const DB_ALL_MODELS = [
    Asset::class,
    AssetTag::class,
    Category::class,
    File::class,
    Tag::class,

    User::class,
    UserMeta::class,
    LoginSession::class,

    Setting::class,
    Log::class
];
