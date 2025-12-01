<?php

namespace api\controllers;

use api\models\Categories;
use api\models\Users;
use Yii;
use yii\data\ActiveDataProvider;


class UserController extends ApiController
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
        return new ActiveDataProvider([
            'query'=> Users::find(),
            'pagination' => [
                'pageSize' => 10,
            ]
        ]);
    }

    public function actionView($id){
        return Users::findOne($id);
    }

    public function actionCreate()
    {
        $model = new Users();
        $model->load(Yii::$app->request->post(), '');

        if ($model->save()) {
            return $this->success($model, "User created successfully");
        }

        return $this->error("An error occurred while creating the user", 400, $model->errors);
    }


    public function actionUpdate($id)
    {
        $model = Users::findOne($id);

        if (!$model) {
            return $this->error("User not found", 404);
        }

        $model->load(Yii::$app->request->bodyParams, '');

        if ($model->save()) {
            return $this->success($model, "User updated successfully");
        }

        return $this->error("User update failed", 400, $model->errors);
    }


    public function actionDelete($id)
    {
        $model = Users::findOne($id);

        if ($model && $model->delete()) {
            return ['status' => 'deleted'];
        }

        return ['status' => 'not found'];
    }



}