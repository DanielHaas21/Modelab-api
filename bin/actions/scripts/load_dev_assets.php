<?php

use App\Database\PDOConfig;
use App\Helpers\Env;
use App\Models\Category;
use App\Models\Tag;

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/dev_assets/data.php';

echoLine();
echoLine('Loading DEV assets...');

Env::Load();
PDOConfig::Load();

echoLine('Loading category IDs...');
$category_ids = [];
foreach (BASE_CATEGORIES as $category_name) {
    $categories = Category::SelectWhereModels('name = :name', [
        ':name' => $category_name
    ]);

    if (count($categories) == 0) {
        echoLine('Category \'' . $category_name . '\' not found');
        exit(1);
    }
    /**
     * @var Category
     */
    $category = $categories[0];
    $category_ids[] = $category->id;
}

echoLine('Loading tag IDs...');
$tag_ids = [];
foreach (BASE_TAGS as $tag_name) {
    $tags = Tag::SelectWhereModels('name = :name', [
        ':name' => $tag_name
    ]);

    if (count($tags) == 0) {
        echoLine('Tag \'' . $tag_name . '\' not found');
        exit(1);
    }
    /**
     * @var Tag
     */
    $tag = $tags[0];
    $tag_ids[] = $tag->id;
}

echoLine('Checking assets...');
foreach (DEV_ASSETS as $dev_asset) {
    // TODO: Load assets
}

echoLine('DEV assets loading OK');
