<?php
namespace common\components;

use Yii;
use yii\web\UploadedFile;
use yii\imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;

class ImageUploader
{
    // Предустановленные размеры
    const SIZE_ORIGINAL = 'original';
    const SIZE_THUMBNAIL = 'thumbnail'; // 100x100
    const SIZE_MEDIUM = 'medium'; // 250x250
    const SIZE_LARGE = 'large'; // 800x800

    /**
     * Конфигурация размеров
     */
    public static $sizes = [
        self::SIZE_THUMBNAIL => ['width' => 100, 'height' => 100],
        self::SIZE_MEDIUM => ['width' => 250, 'height' => 250],
        self::SIZE_LARGE => ['width' => 800, 'height' => 800],
    ];

    /**
     * Загрузка файла с созданием разных размеров
     * @param UploadedFile $file
     * @param string $folder - 'products', 'users' и т.д.
     * @param array $sizes - массив размеров ['thumbnail', 'medium', 'large']
     * @param bool $groupByDate - группировать ли по дате (как в галерее телефона)
     * @return array|false - ['original' => 'filename.jpg', 'thumbnail' => 'filename_thumb.jpg', 'path' => '2024/12']
     */
    public static function upload($file, $folder = '', $sizes = [], $groupByDate = true)
    {
        if (!$file) {
            return false;
        }

        // Проверка типа файла
        if (!self::isImage($file)) {
            return false;
        }

        // Генерация имени файла (короткое и уникальное)
        $fileName = uniqid() . '.' . $file->extension;

        // Базовый путь загрузки
        $uploadPath = Yii::getAlias(Yii::$app->params['uploadsPath']);

        if ($folder) {
            $uploadPath .= '/' . $folder;
        }

        // Группировка по дате (год/месяц)
        $datePath = '';
        if ($groupByDate) {
            $datePath = date('Y/m');
            $uploadPath .= '/' . $datePath;
        }

        // Создание директории
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Сохранение оригинала
        $originalPath = $uploadPath . '/' . $fileName;
        if (!$file->saveAs($originalPath)) {
            return false;
        }

        $result = [
            self::SIZE_ORIGINAL => $fileName,
            'path' => $datePath,
            'sizes' => []
        ];

        // Создание уменьшенных версий
        if (!empty($sizes)) {
            foreach ($sizes as $sizeName) {
                if (isset(self::$sizes[$sizeName])) {
                    $resizedFileName = self::createResizedVersion(
                        $originalPath,
                        $uploadPath,
                        $fileName,
                        $sizeName,
                        self::$sizes[$sizeName]
                    );

                    if ($resizedFileName) {
                        $result['sizes'][$sizeName] = $resizedFileName;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Создание уменьшенной версии изображения
     * @param string $originalPath - путь к оригиналу
     * @param string $uploadPath - базовый путь для сохранения
     * @param string $fileName - имя файла
     * @param string $sizeName - название размера
     * @param array $dimensions - ['width' => 100, 'height' => 100]
     * @return string|false
     */
    protected static function createResizedVersion($originalPath, $uploadPath, $fileName, $sizeName, $dimensions)
    {
        try {
            // Создание директории для размера
            $sizeDir = $uploadPath . '/' . $sizeName;
            if (!is_dir($sizeDir)) {
                mkdir($sizeDir, 0777, true);
            }

            // Путь для сохранения с тем же именем файла
            $resizedPath = $sizeDir . '/' . $fileName;

            Image::thumbnail($originalPath, $dimensions['width'], $dimensions['height'])
                ->save($resizedPath, ['quality' => 90]);

            return $fileName;
        } catch (\Exception $e) {
            Yii::error("Resize error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Получение URL файла
     * @param string $fileName
     * @param string $folder
     * @param string $size - размер изображения
     * @param string $datePath - путь с датой (например: '2024/12')
     * @return string
     */
    public static function getUrl($fileName, $folder = '', $size = self::SIZE_ORIGINAL, $datePath = '')
    {
        if (!$fileName) {
            return '/images/default.png';
        }

        $url = Yii::$app->params['uploadsUrl'];

        if ($folder) {
            $url .= '/' . $folder;
        }

        if ($datePath) {
            $url .= '/' . $datePath;
        }

        if ($size !== self::SIZE_ORIGINAL) {
            $url .= '/' . $size;
        }

        return $url . '/' . $fileName;
    }

    /**
     * Удаление файла со всеми его версиями
     * @param string $fileName
     * @param string $folder
     * @param string $datePath
     * @param array $sizes - размеры для удаления
     * @return bool
     */
    public static function delete($fileName, $folder = '', $datePath = '', $sizes = [])
    {
        if (!$fileName) {
            return false;
        }

        $uploadPath = Yii::getAlias(Yii::$app->params['uploadsPath']);

        if ($folder) {
            $uploadPath .= '/' . $folder;
        }

        if ($datePath) {
            $uploadPath .= '/' . $datePath;
        }

        $success = true;

        // Удаление оригинала
        $originalPath = $uploadPath . '/' . $fileName;
        if (file_exists($originalPath)) {
            $success = unlink($originalPath) && $success;
        }

        // Удаление всех размеров
        foreach ($sizes as $sizeName) {
            $sizePath = $uploadPath . '/' . $sizeName . '/' . $fileName;
            if (file_exists($sizePath)) {
                $success = unlink($sizePath) && $success;
            }
        }

        return $success;
    }

    /**
     * Получение галереи изображений сгруппированных по дате
     * @param string $folder
     * @return array - ['2024-12' => [...files], '2024-11' => [...files]]
     */
    public static function getGalleryByDate($folder = '')
    {
        $uploadPath = Yii::getAlias(Yii::$app->params['uploadsPath']);

        if ($folder) {
            $uploadPath .= '/' . $folder;
        }

        $gallery = [];

        if (!is_dir($uploadPath)) {
            return $gallery;
        }

        // Сканирование директорий с годами
        $years = glob($uploadPath . '/*', GLOB_ONLYDIR);

        foreach ($years as $yearPath) {
            $year = basename($yearPath);

            // Сканирование месяцев
            $months = glob($yearPath . '/*', GLOB_ONLYDIR);

            foreach ($months as $monthPath) {
                $month = basename($monthPath);
                $dateKey = "$year-$month";

                // Получение всех изображений в этой папке
                $images = [];
                $files = glob($monthPath . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

                foreach ($files as $file) {
                    $fileName = basename($file);
                    $images[] = [
                        'filename' => $fileName,
                        'url' => self::getUrl($fileName, $folder, self::SIZE_ORIGINAL, "$year/$month"),
                        'thumbnail' => self::getUrl($fileName, $folder, self::SIZE_THUMBNAIL, "$year/$month"),
                        'date' => date('Y-m-d H:i:s', filemtime($file)),
                    ];
                }

                if (!empty($images)) {
                    // Сортировка по дате (новые сначала)
                    usort($images, function($a, $b) {
                        return strtotime($b['date']) - strtotime($a['date']);
                    });

                    $gallery[$dateKey] = $images;
                }
            }
        }

        // Сортировка по датам (новые периоды сначала)
        krsort($gallery);

        return $gallery;
    }

    /**
     * Проверка, является ли файл изображением
     * @param UploadedFile $file
     * @return bool
     */
    protected static function isImage($file)
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        return in_array(strtolower($file->extension), $allowedExtensions);
    }

    /**
     * Получение информации о файле
     * @param string $fileName
     * @param string $folder
     * @param string $datePath
     * @return array|false
     */
    public static function getFileInfo($fileName, $folder = '', $datePath = '')
    {
        if (!$fileName) {
            return false;
        }

        $uploadPath = Yii::getAlias(Yii::$app->params['uploadsPath']);

        if ($folder) {
            $uploadPath .= '/' . $folder;
        }

        if ($datePath) {
            $uploadPath .= '/' . $datePath;
        }

        $filePath = $uploadPath . '/' . $fileName;

        if (!file_exists($filePath)) {
            return false;
        }

        return [
            'filename' => $fileName,
            'size' => filesize($filePath),
            'date' => date('Y-m-d H:i:s', filemtime($filePath)),
            'url' => self::getUrl($fileName, $folder, self::SIZE_ORIGINAL, $datePath),
        ];
    }
}