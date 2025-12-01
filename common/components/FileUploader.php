<?php
namespace common\components;

use Yii;
use yii\web\UploadedFile;

class FileUploader
{
    /**
     * Faylni yuklash
     * @param UploadedFile $file
     * @param string $folder - masalan: 'products', 'users'
     * @return string|false - fayl nomi yoki false
     */
    public static function upload($file, $folder = '')
    {
        if (!$file) {
            return false;
        }

        // Unique fayl nomi yaratish
        $fileName = uniqid() . '_' . time() . '.' . $file->extension;

        // To'liq yo'l
        $uploadPath = Yii::getAlias(Yii::$app->params['uploadsPath']);

        if ($folder) {
            $uploadPath .= '/' ;
        }

        // Papka yo'qligini tekshirish va yaratish
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Faylni saqlash
        if ($file->saveAs($uploadPath . '/' . $fileName)) {
            return $fileName;
        }

        return false;
    }

    /**
     * Fayl URL ini olish
     * @param string $fileName
     * @param string $folder
     * @return string
     */
    public static function getUrl($fileName, $folder = '')
    {
        if (!$fileName) {
            return '/images/default.png';
        }

        $url = Yii::$app->params['uploadsUrl'];

        if ($folder) {
            $url .= '/' . $folder;
        }

        return $url . '/' . $fileName;
    }

    /**
     * Faylni o'chirish
     * @param string $fileName
     * @param string $folder
     * @return bool
     */
    public static function delete($fileName, $folder = '')
    {
        if (!$fileName) {
            return false;
        }

        $uploadPath = Yii::getAlias(Yii::$app->params['uploadsPath']);

        if ($folder) {
            $uploadPath .= '/' . $folder;
        }

        $filePath = $uploadPath . '/' . $fileName;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}