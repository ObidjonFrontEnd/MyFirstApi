<?php

namespace api\controllers;

class ExamController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {

        return $this->success(['user' => 'demo']);
    }

    public function actionTestError()
    {
        return $this->error("Bu yerda xatolik bor", 400);
    }
}
