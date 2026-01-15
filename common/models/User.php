<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\filters\RateLimitInterface;

// JWT библиотеки Lcobucci
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\Clock\SystemClock;

class User extends ActiveRecord implements IdentityInterface, RateLimitInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    // Секретный ключ (должен совпадать в AuthController и здесь)
    const JWT_SECRET = 'BAW_SECRET_KEY_MINIMUM_32_CHARS_123456789012345';

    public static function tableName() { return '{{%users}}'; }

    public function behaviors() { return [TimestampBehavior::class]; }

    // --- RATE LIMIT (Защита от парсинга) ---
    public function getRateLimit($request, $action) {
        // Гостям (ID=0) 5 запросов в 30 сек, авторизованным 100 запросов в 60 сек
        return ($this->id === 0) ? [5, 30] : [100, 60];
    }

    public function loadAllowance($request, $action) {
        $cacheKey = 'rate_limit_' . ($this->id ?: Yii::$app->request->userIP);
        $data = Yii::$app->cache->get($cacheKey);
        return $data ?: [$this->getRateLimit($request, $action)[0], time()];
    }

    public function saveAllowance($request, $action, $allowance, $timestamp) {
        $cacheKey = 'rate_limit_' . ($this->id ?: Yii::$app->request->userIP);
        Yii::$app->cache->set($cacheKey, [$allowance, $timestamp], 3600);
    }

    // --- JWT IDENTITY (Поиск пользователя по токену) ---
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(self::JWT_SECRET)
        );

        try {
            // 1. Парсим токен
            $parsedToken = $config->parser()->parse((string) $token);

            // 2. Настраиваем проверку подписи и времени (добавляем 60с leeway)
            $constraints = [
                new SignedWith($config->signer(), $config->signingKey()),
                new ValidAt(new SystemClock(new \DateTimeZone(Yii::$app->timeZone)), new \DateInterval('PT60S'))
            ];

            // 3. Валидируем
            if (!$config->validator()->validate($parsedToken, ...$constraints)) {
                return null;
            }

            $claims = $parsedToken->claims();
            $role = $claims->get('role');

            if ($role === 'guest') {
                $guest = new static();
                $guest->id = 0;
                $guest->username = 'Guest';
                return $guest;
            }

            // Для аутентифицированных пользователей токен ДОЛЖЕН содержать ID
            // Пробуем 'sub', если нет - пробуем старый формат в 'role' (user:ID)
            $userId = $claims->get('sub');
            if (!$userId && is_string($role) && strpos($role, 'user:') === 0) {
                $userId = str_replace('user:', '', $role);
            }

            if (!$userId) {
                return null;
            }

            // Мы убираем проверку 'status', так как в миграции этого поля нет
            return static::findOne(['id' => $userId]);

        } catch (\Throwable $e) {
            Yii::error("JWT Validation error: " . $e->getMessage(), 'api');
            return null;
        }
    }

    // --- IDENTITY INTERFACE ---
    public static function findIdentity($id) { return static::findOne(['id' => $id]); }
    public function getId() { return $this->getPrimaryKey(); }
    public function getAuthKey() { return $this->auth_key; }
    public function validateAuthKey($authKey) { return $this->getAuthKey() === $authKey; }

    // --- ВАЛИДАЦИЯ ---
    public function rules() {
        return [
            [['phone', 'username', 'email'], 'required', 'on' => 'create'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => self::class],
            ['password', 'string', 'min' => 8], // Исправлено на password
            ['username', 'string', 'max' => 255],
            ['username', 'unique'],
            ['phone', 'match', 'pattern' => '/^\+998\d{9}$/']
        ];
    }

    // Исправлено: используем колонку 'password'
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    // Исправлено: сохраняем в колонку 'password'
    public function setPassword($password) {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    public static function findByUsername($username) { return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]); }

    public function generateAuthKey() { $this->auth_key = Yii::$app->security->generateRandomString(); }

    // Поля, которые возвращаются в API (пароль исключен для безопасности)
    public function fields() { return ['id', 'username', 'phone', 'email']; }
}