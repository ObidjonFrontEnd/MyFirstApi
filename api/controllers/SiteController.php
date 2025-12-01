<?php
namespace api\controllers;

use Yii;



class SiteController extends ApiController
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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'status' => 'success',
            'message' => 'API is working!',
            'version' => '1.0',
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    public function actionError()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return [
                'status' => 'error',
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ];
        }
    }
}