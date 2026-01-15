<?php

namespace api\controllers;

use Yii;
use yii\rest\Controller;
use yii\rest\Serializer;
use yii\filters\Cors;
use yii\filters\ContentNegotiator;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\RateLimiter;
use yii\web\Response;

class ApiController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * Настройка сериализатора для REST
     * collectionEnvelope позволяет обернуть список элементов в ключ 'items'
     */
    public $serializer = [
        'class' => Serializer::class,
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Очищаем стандартные, чтобы избежать конфликтов порядка
        unset($behaviors['authenticator'], $behaviors['corsFilter'], $behaviors['rateLimiter']);

        // 1. CORS — должен быть первым
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Current-Page',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Total-Count',
                    'X-Rate-Limit-Limit',
                    'X-Rate-Limit-Remaining'
                ],
            ],
        ];

        // 2. Формат ответа JSON
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => ['application/json' => Response::FORMAT_JSON],
        ];

        // 3. Аутентификация
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
            ],
            'except' => ['options', 'guest', 'login', 'register', 'image'],
        ];

        // 4. Лимиты запросов (Rate Limit)
        $behaviors['rateLimiter'] = [
            'class' => RateLimiter::class,
            'enableRateLimitHeaders' => true,
        ];

        return $behaviors;
    }

    /**
     * Переопределяем метод сериализации данных
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }

    /**
     * Стандартный успешный ответ
     */
    protected function success($data = null, $message = "Success") {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Стандартный ответ с ошибкой
     */
    protected function error($message = "Xatolik yuz berdi", $code = 400, $errors = null)
    {
        Yii::$app->response->statusCode = $code;
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];
    }

    /**
     * Заглушка для OPTIONS запросов (CORS)
     */
    public function actionOptions()
    {
        return null;
    }
}