<?php

namespace api\controllers;

use api\models\Product;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\rest\Serializer;


class ProductController extends ApiController
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
                'view' => ['GET', 'OPTIONS'],
                'create' => ['POST', 'OPTIONS'],
                'update' => ['PUT', 'PATCH', 'OPTIONS'],
                'delete' => ['DELETE', 'OPTIONS'],
            ],
        ];

        return $behaviors;
    }

    public $serializer = [
        'class' => Serializer::class,
        'collectionEnvelope' => 'items',
    ];
    protected function serializeData($data)
    {
        $serializer = \Yii::createObject($this->serializer);
        return $serializer->serialize($data);
    }

    public function actionIndex(){
        $data = new ActiveDataProvider([
            'query'=> Product::find(),
            'pagination' => [
                'pageSize' => 10,
                'pageSizeParam' => false,
            ]
        ]);


        return $this->success($this->serializeData($data));

    }

    public function actionView($id)
    {
        $model = Product::findOne($id);

        if (!$model) {
            return $this->error("Продукт не найден", 404);
        }

        $model->registerView();

        return $this->success($model);
    }



    public function actionCreate()
    {
        $model = new Product();
        $model->load(Yii::$app->request->post(), '');

        if ($model->save()) {
            return $this->success($model, "Продукт успешно создан");
        }

        return $this->error("Ошибка при создании продукта", 400, $model->errors);
    }



    public function actionUpdate($id)
    {
        $model = Product::findOne($id);
        if (!$model) {
            return $this->error("Продукт не найден", 404);
        }

        $data = Yii::$app->request->bodyParams;

        if (empty($data)) {
            return $this->error("Нет данных для обновления", 400);
        }

        $model->load($data, '');
        if ($model->save()) {
            return $this->success($model, "Продукт обновлён");
        }

        return $this->error("Ошибка при обновлении продукта", 400, $model->errors);
    }


    public function actionDelete($id)
    {
        $model = Product::findOne($id);

        if ($model && $model->delete()) {
            return ['status' => 'deleted'];
        }

        return ['status' => 'not found'];
    }
}