<?php

namespace api\models;
use common\models\Products;
use common\models\ProductViewLog;
use yii\behaviors\TimestampBehavior;



class Product extends Products
{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
        ];
    }



    public function fields()
    {
        return [
            'id',
            'category_id',
            'name',
            'description',
            'price',
            'discount_price',
            'images',
            'views',
            'category',
            'rating_avg',
            'rating_count',
        ];
    }

    public function extraFields(){
        return [
            'productReviews',
            'productDetails',
            'productStocks',
        ];
    }

    public function registerView()
    {
        $ip = \Yii::$app->request->userIP;
        $timeLimit = time() - 3600; // 1 час

        $exists = ProductViewLog::find()
            ->where([
                'product_id' => $this->id,
                'ip' => $ip,
            ])
            ->andWhere(['>', 'created_at', $timeLimit])
            ->exists();

        if (!$exists) {
            $log = new ProductViewLog();
            $log->product_id = $this->id;
            $log->ip = $ip;
            $log->created_at = time();
            $log->save(false);

            // атомарно
            $this->updateCounters(['views' => 1]);
            $this->refresh();
        }
    }

}
