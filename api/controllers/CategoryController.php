<?php

namespace api\controllers;

use api\models\Categories;
use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\Controller;

class CategoryController extends ApiController
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


    public function actionIndex(){
        return $this->success(Categories::find()->all());
    }

    public function actionView($id){
        return Categories::findOne($id);
    }


    public function actionCreate()
    {
        $model = new Categories();
        $model->load(Yii::$app->request->post(), '');

        if ($model->save()) {
            return $this->success($model, "category успешно создан");
        }


        return $this->error("Ошибка при создании category", 400, $model->errors);
    }


    public function actionUpdate($id)
    {
        $model = Categories::findOne($id);
        $model->load(Yii::$app->request->bodyParams, '');

        if ($model->save()) {
            return $this->success($model, "category muvafiqatli o'zgartilidi");
        }

        return $this->error("category o'zgartirishda hatolik yuz berdi", 400, $model->errors);
    }

    public function actionDelete($id)
    {
        $model = Categories::findOne($id);

        if ($model && $model->delete()) {
            return ['status' => 'deleted'];
        }

        return ['status' => 'not found'];
    }
}