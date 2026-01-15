<?php

namespace api\controllers;

use common\models\Swipper;

class SwipperController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // УБИРАЕМ 'index' и 'view' из except.
        // Теперь они требуют Bearer Token (хотя бы гостевой).
        $behaviors['authenticator']['except'] = array_merge(
            $behaviors['authenticator']['except'] ?? [],
            ['options'] // Оставляем только options для CORS
        );

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET', 'OPTIONS'],
            ],
        ];

        return $behaviors;
    }


    public function actionIndex(){
        $data = Swipper::find()->all();
        return $this->success($data);
    }

}