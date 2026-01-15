<?php

namespace api\controllers;


use common\models\User;
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
            'query'=> User::find(),
            'pagination' => [
                'pageSize' => 10,
            ]
        ]);
    }

    public function actionView($id){
        return User::findOne($id);
    }

    public function actionCreate()
    {
        $model = new User();
        $model->load(Yii::$app->request->post(), '');
        $model->setPassword(Yii::$app->request->bodyParams['password']);
        if ($model->save()) {

            return $this->success($model, "User created successfully");
        }

        return $this->error("An error occurred while creating the user", 400, $model->errors);
    }


    public function actionUpdate($id)
    {
        $model = User::findOne($id);

        if (!$model) {
            return $this->error("User not found", 404);
        }

        $model->load(Yii::$app->request->bodyParams, '');

        // Проверяем, есть ли пароль в запросе
        $password = Yii::$app->request->bodyParams['password'] ?? null;
        if ($password) {
            $model->setPassword($password);
        }

        if ($model->save()) {
            return $this->success($model, "User updated successfully");
        }

        return $this->error("User update failed", 400, $model->errors);
    }



    public function actionDelete($id)
    {
        $model = User::findOne($id);

        if ($model && $model->delete()) {
            return ['status' => 'deleted'];
        }

        return ['status' => 'not found'];
    }



}