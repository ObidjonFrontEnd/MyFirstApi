<?php

namespace common\models;

use yii\db\ActiveRecord;

class ProductViewLog extends ActiveRecord
{
    public static function tableName()
    {
        return 'product_view_log';
    }

    public function rules()
    {
        return [
            [['product_id', 'created_at'], 'integer'],
            ['ip', 'string', 'max' => 45],
        ];
    }
}
