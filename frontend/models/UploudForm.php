<?php

namespace frontend\models;



use common\components\ImageUploader;
use yii\base\Model;

class UploudForm extends Model
{
    public $imageFile;
    public function rules()
    {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],
        ];
    }


    public function uploadImage()
    {
        $this->imageFile = ImageUploader::getInstance($this, 'imageFile');

        if (!$this->imageFile) {
            return true; // нет файла - не ошибка
        }

        // Удаляем старое изображение если есть
        if ($this->image) {
            ImageUploader::delete(
                $this->image,
                '',
                $this->image_path,
                [ImageUploader::SIZE_THUMBNAIL, ImageUploader::SIZE_MEDIUM, ImageUploader::SIZE_LARGE]
            );
        }

        // Загружаем новое
        $result = ImageUploader::upload(
            $this->imageFile,
            '',
            [ImageUploader::SIZE_THUMBNAIL, ImageUploader::SIZE_MEDIUM, ImageUploader::SIZE_LARGE],
            true
        );

        if ($result) {
            $this->image = $result['original'];
            $this->image_path = $result['path'];
            return true;
        }

        return false;
    }

}