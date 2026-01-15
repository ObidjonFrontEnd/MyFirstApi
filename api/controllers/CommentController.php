<?php

namespace api\controllers;

use common\models\ProductDetails;
use common\models\ProductReviews;
use yii\data\ActiveDataProvider;

class CommentController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();


        $behaviors['authenticator']['except'] = array_merge(
            $behaviors['authenticator']['except'] ?? [],
            ['index', 'view', 'options']
        );

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET', 'OPTIONS'],
                'view' => ['GET', 'OPTIONS'],
                'create' => ['POST', 'OPTIONS'],
                'update' => ['PUT', 'PATCH', 'OPTIONS'],
                'delete' => ['DELETE', 'OPTIONS'],
            ],
        ];

        return $behaviors;
    }

    public function actionView($id , $size = null){
        $data = new ActiveDataProvider([
            'query' => ProductReviews::find()->where(['product_id' => $id]),
            'pagination' => [
                'pageSize' => $size,
            ]
        ]);

        return $this->success([
            'items' => $data->getModels(),
            'pagination' => [
                'totalCount' => $data->getTotalCount(),
                'pageCount' => $data->pagination->pageCount,
                'currentPage' => $data->pagination->page + 1,
                'size' => $data->pagination->pageSize,
            ]
        ]);
    }

}