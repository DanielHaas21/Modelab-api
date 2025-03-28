<?php

namespace App\Database\BaseModels;

use App\Database\SQL;

abstract class BaseModelId extends BaseModel
{
    /**
     * Selects data from DB and creates the model, if found
     *
     * Shouldn't be called from the base class
     * @param int $id
     * @return BaseModelId|null
     */
    final public static function SelectModel(int $id): ?object
    {
        static::CheckNotBase();
        static::Init();

        $datas = SQL::SelectDataWithCondition(static::GetTableName(), '*', 'id = :id', [
            ':id' => $id
        ]);

        if (count($datas) == 0) {
            return null;
        }

        return static::CreateFrom($datas[0]);
    }

    /**
     * Inserts data into DB
     *
     * Shouldn't be called from the base class
     * @param BaseModelId $model
     * @return int Inserted row ID
     */
    final public static function InsertModel(BaseModel $model): int
    {
        static::CheckNotBase();
        static::Init();

        $data = $model->GetData();
        unset($data['id']);
        return SQL::InsertData(static::GetTableName(), $data);
    }

    /**
     * Updates data in DB
     *
     * Shouldn't be called from the base class
     * @param BaseModelId $model
     * @return void
     */
    final public static function UpdateModel(BaseModel $model): void
    {
        static::CheckNotBase();
        static::Init();

        $data = $model->GetData();
        SQL::UpdateDataWithCondition(static::GetTableName(), $data, [
            ':id' => $model->id
        ], "id = :id");
    }

    /**
     * Deletes data from DB
     *
     * Shouldn't be called from the base class
     * @param BaseModelId $model
     * @return bool Whether the model was deleted
     */
    final public static function DeleteModel(BaseModel $model): bool
    {
        static::CheckNotBase();
        static::Init();

        $affectedRows = SQL::DeleteDataWithCondition(static::GetTableName(), "id = :id", [
            ':id' => $model->id
        ]);

        return $affectedRows != 0;
    }

    /**
     * Unique identificator
     * @sql INT NOT NULL AUTO_INCREMENT
     * @isPrimaryKey
     * @var int
     */
    public $id;
}
