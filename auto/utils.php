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

require_once __DIR__ . '/../autoload.php';

function echoLine(string $msg = ""): void
{
    echo "$msg\n";
}

function echoError(Exception $e): void
{
    echoLine(get_class($e) . ": " . $e->getMessage());
}

const ALL_MODELS = [
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
