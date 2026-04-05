<?php

namespace App\Models;

use App\Services\Database\BaseModels\BaseModel;
use App\Services\Database\SQL;

class AssetTag extends BaseModel
{
    /**
         * Selects data from DB and creates the model, if found
         *
         * Shouldn't be called from the base class
         * @param int $id
         * @return AssetTag|null
         */
    final public static function SelectModel(int $assetId, int $tagId): ?object
    {
        static::CheckNotBase();
        static::Init();

        $datas = SQL::SelectDataWithCondition(static::GetTableName(), '*', 'tagId = :tagId AND assetId = :assetId', [
            ':assetId' => $assetId,
            ':tagId' => $tagId,
        ]);

        if (count($datas) == 0) {
            return null;
        }

        return static::CreateFrom($datas[0]);
    }

    /**
     * Updates data in DB
     *
     * Shouldn't be called from the base class
     * @param AssetTag $model
     * @return void
     */
    final public static function UpdateModel(BaseModel $model): void
    {
        static::CheckNotBase();
        static::Init();

        $data = $model->GetData();
        SQL::UpdateDataWithCondition(static::GetTableName(), $data, [
            ':assetId' => $model->assetId,
            ':tagId' => $model->tagId,
        ], "id = :id");
    }

    /**
     * Deletes data from DB
     *
     * Shouldn't be called from the base class
     * @param AssetTag $model
     * @return bool Whether the model was deleted
     */
    final public static function DeleteModel(BaseModel $model): bool
    {
        static::CheckNotBase();
        static::Init();

        $affectedRows = SQL::DeleteDataWithCondition(static::GetTableName(), 'tagId = :tagId or assetId = :assetId', [
            ':assetId' => $model->assetId,
            ':tagId' => $model->tagId,
        ]);

        return $affectedRows != 0;
    }

    /**
     * @sql INT NOT NULL
     * @isPrimaryKey
     * @var int
     */
    public $assetId;
    /**
     * @sql INT NOT NULL
     * @isPrimaryKey
     * @var int
     */
    public $tagId;
}
