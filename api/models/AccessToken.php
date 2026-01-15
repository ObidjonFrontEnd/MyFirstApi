<?php

namespace api\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * AccessToken model
 *
 * @property int $id
 * @property int $user_id
 * @property string $access_token
 * @property string $refresh_token
 * @property int $expires_at
 * @property int $created_at
 * @property int $updated_at
 *
 *
 */
class AccessToken extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%access_tokens}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'access_token', 'refresh_token', 'expires_at'], 'required'],
            [['user_id', 'expires_at', 'created_at', 'updated_at'], 'integer'],
            [['access_token', 'refresh_token'], 'string', 'max' => 255],
            [['access_token'], 'unique'],
            [['refresh_token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'access_token' => 'Access Token',
            'refresh_token' => 'Refresh Token',
            'expires_at' => 'Expires At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Связь с пользователем
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }

    /**
     * Генерация случайного токена
     */
    private static function generateRandomToken()
    {
        return Yii::$app->security->generateRandomString(64);
    }

    /**
     * Создать новый токен для пользователя
     *
     * @param int $userId ID пользователя
     * @param int $expiresInSeconds Время жизни токена в секундах (по умолчанию 30 дней)
     * @return AccessToken|null
     */
    public static function generateToken($userId, $expiresInSeconds = 2592000)
    {
        // Удаляем старые токены пользователя (опционально)
        // self::deleteAll(['user_id' => $userId]);

        $token = new self();
        $token->user_id = $userId;
        $token->access_token = self::generateRandomToken();
        $token->refresh_token = self::generateRandomToken();
        $token->expires_at = time() + $expiresInSeconds;

        if ($token->save()) {
            return $token;
        }

        return null;
    }

    /**
     * Найти токен по access_token и проверить его валидность
     *
     * @param string $accessToken
     * @return AccessToken|null
     */
    public static function findByAccessToken($accessToken)
    {
        return self::find()
            ->where(['access_token' => $accessToken])
            ->andWhere(['>', 'expires_at', time()])
            ->one();
    }

    /**
     * Обновить токен используя refresh_token
     *
     * @param string $refreshToken
     * @return AccessToken|null
     */
    public static function refreshToken($refreshToken)
    {
        // Найти токен по refresh_token
        $oldToken = self::findOne(['refresh_token' => $refreshToken]);

        if (!$oldToken) {
            return null;
        }

        // Удалить старый токен
        $userId = $oldToken->user_id;
        $oldToken->delete();

        // Создать новый токен
        return self::generateToken($userId);
    }

    /**
     * Удалить все истекшие токены
     */
    public static function deleteExpired()
    {
        return self::deleteAll(['<', 'expires_at', time()]);
    }

    /**
     * Проверить, не истек ли токен
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at < time();
    }

    /**
     * Получить все активные токены пользователя
     *
     * @param int $userId
     * @return AccessToken[]
     */
    public static function getUserTokens($userId)
    {
        return self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['>', 'expires_at', time()])
            ->all();
    }

    /**
     * Удалить все токены пользователя (например, при смене пароля)
     *
     * @param int $userId
     * @return int количество удаленных токенов
     */
    public static function revokeUserTokens($userId)
    {
        return self::deleteAll(['user_id' => $userId]);
    }
}