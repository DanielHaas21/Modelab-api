<?php

use App\Services\Database\PDOConfig;
use App\Configuration\Env;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Tag;
use App\Services\Files\AssetFilesService;
use App\Services\Users\UserService;

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/dev_assets/data.php';

echoLine();
echoLine('Loading DEV assets...');

Env::Load();
PDOConfig::Load();

echoLine('Loading category IDs...');
/**
 * @var Category[]
 */
$category_models = [];
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
    $category_models[] = $category;
}

echoLine('Loading tag IDs...');
/**
 * @var Tag[]
 */
$tag_models = [];
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
    $tag_models[] = $tag;
}

$user_service = new UserService();
$asset_file_service = new AssetFilesService();

$dev_user = $user_service->GetOrCreateDevUser();

foreach (DEV_ASSETS as $dev_asset) {
    $assets = Asset::SelectWhereModels('name = :name', [
        ':name' => $dev_asset['name'],
    ]);

    if (count($assets) > 0) {
        echoLine('Asset \'' . $dev_asset['name'] . '\' already exists');
        continue;
    }

    echoLine('Creating asset \'' . $dev_asset['name'] . '\'...');

    $files_data = [];

    foreach ($dev_asset['files'] as $file_info) {
        $sourcePath = DEV_ASSETS_PATH_ROOT . '/' . $file_info['path'];

        if (!file_exists($sourcePath)) {
            echoLine('Dev file not found: ' . $sourcePath);
            exit(1);
        }

        $files_data[] = [
            'name' => basename($sourcePath),
            'type' => mime_content_type($sourcePath) ?: 'application/octet-stream',
            'tmpName' => $sourcePath,
            'isHidden' => $file_info['isHidden'],
            'isMain' => $file_info['isMain'],
            'isRemoved' => false,
            'isPreview' => $file_info['isPreview'],
            'file' => null
        ];
    }

    try {
        $asset_file_service->CreateAsset(
            $dev_user,
            $dev_asset['name'],
            $dev_asset['description'],
            $category_models[$dev_asset['category']],
            array_map(function ($tag_index) use ($tag_models) {
                return $tag_models[$tag_index];
            }, $dev_asset['tags']),
            $files_data,
            'Test Author'
        );
    } catch (Exception $e) {
        echoError($e);
        echoLine('Failed creating asset \'' . $dev_asset['name'] . '\'');
        exit(1);
    }
}

echoLine('DEV assets loading OK');
