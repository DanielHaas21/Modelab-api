<?php
namespace App\Models;

class Asset
{
    public $id;
    public $name;
    public $description;

    public $category_id;
    public $owner_id;

    public $preview_asset_id;
    public $main_asset_id;
}
