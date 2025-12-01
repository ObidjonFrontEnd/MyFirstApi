<?php

namespace api\controllers;

use Yii;

class UploadController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index' => ['POST'],
                'view' => ['GET'],
                'delete' => ['DELETE'],
            ],
        ];

        return $behaviors;
    }


    public function actionView($id)
    {
        $folder = Yii::$app->request->get('folder', '');
        $filePath = Yii::getAlias(Yii::$app->params['uploadsPath'])
            . ($folder ? '/' . $folder : '')
            . '/' . $id;

        if (!file_exists($filePath)) {
            return $this->error('File not found', 404);
        }

        return Yii::$app->response->sendFile($filePath, null, ['inline' => true]);
    }

    public function actionCreate()
    {
        $file = \yii\web\UploadedFile::getInstanceByName('file');
        if (!$file) {
            return $this->error('No file uploaded');
        }

        $folder = Yii::$app->request->post('folder', '');
        $fileName = \common\components\FileUploader::upload($file, $folder);

        if ($fileName) {
            $hostInfo = Yii::$app->request->hostInfo;
            $url = $hostInfo . '/api/upload/' . $fileName;

            return $this->success(['url' => $url], 'File uploaded successfully');
        }

        return $this->error('File upload failed');
    }

    public function actionDelete()
    {
        $url = Yii::$app->request->post('url');
        if (!$url) {
            return $this->error('URL is required');
        }

        $filename = basename($url); // получаем имя файла
        $folder = Yii::$app->request->post('folder', '');

        $deleted = \common\components\FileUploader::delete($filename, $folder);

        if ($deleted) {
            return $this->success(null, 'File deleted successfully');
        }

        return $this->error('File not found', 404);
    }
}
