<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-api',
            'cookieValidationKey' => 'pL9mK6jH3gF0eD1cB4aZ7yX2wV5uT8sR',
            'baseUrl' => '/api',
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'format' => \yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@api/runtime/logs/app.log',
                    'categories' => ['api', 'application'],
                ],
            ],
        ],
        'errorHandler' => [
            // API uchun errorAction ni o'chiramiz
            'errorAction' => null,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'baseUrl' => '/api',
            'scriptUrl' => '/api/index.php',
            'rules' => [
                'GET upload/<filename:.+\.(jpg|jpeg|png|gif|webp)>' => 'upload/image',

                // Auth routes
                'POST auth/guest' => 'auth/guest',    // <--- ДОБАВЛЕНО: для получения гостевого токена
                'POST auth/login' => 'auth/login',
                'POST auth/register' => 'auth/register',
                'POST auth/logout' => 'auth/logout',
                'POST auth/refresh' => 'auth/refresh',
                'GET auth/me' => 'auth/me',
                'OPTIONS auth/<action>' => 'auth/options',

                [
                    'class' => 'yii\rest\UrlRule',
                    // Убедитесь, что 'auth' здесь нужен, если вы уже описали его маршруты выше
                    'controller' => ['user', 'product', 'category', 'upload', 'auth', 'swipper', 'wishlist'],
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:[^/]+>',
                    ],
                    'extraPatterns' => [
                        'GET uploads/<id>' => 'view',
                        'POST upload' => 'upload',
                        'DELETE delete/<id>' => 'delete',
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];