<?php

use App\Controllers\CategoryController;
use App\Services\Database\PDOConfig;
use App\Configuration\Env;
use App\Models\Category;
use App\Models\Tag;

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/dev_assets/data.php';

echoLine();
echoLine('Populating DB...');

Env::Load();
PDOConfig::Load();

$create_category = CategoryController::Create();

echoLine('Populating categories...');
foreach (BASE_CATEGORIES as $category_name) {
    $category = new Category();
    $category->name = $category_name;

    $insertedId = $category->Insert();

    if ($insertedId == 0) {
        echoLine('Category \'' . $category_name . '\' already exists');
    } else {
        echoLine('Category \'' . $category_name . '\' created');
    }
}
echoLine('Categories OK...');

echoLine('Populating tags...');
foreach (BASE_TAGS as $tag_name) {
    $tag = new Tag();
    $tag->name = $tag_name;

    $insertedId = $tag->Insert();

    if ($insertedId == 0) {
        echoLine('Tag \'' . $tag_name . '\' already exists');
    } else {
        echoLine('Tag \'' . $tag_name . '\' created');
    }
}
echoLine('Tags OK...');

echoLine('Populating DB OK');
