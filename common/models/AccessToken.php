<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "access_tokens".
 *
 * @property int $id
 * @property int $user_id
 * @property string $access_token
 * @property string|null $refresh_token
 * @property string $expires_at
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class AccessToken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'access_tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['refresh_token'], 'default', 'value' => null],
            [['user_id', 'access_token', 'expires_at'], 'required'],
            [['user_id'], 'integer'],
            [['expires_at', 'created_at', 'updated_at'], 'safe'],
            [['access_token', 'refresh_token'], 'string', 'max' => 512],
            [['access_token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Generirovanie novogo tokena dlya polzovatelya
     *
     * @param int $userId ID polzovatelya
     * @param int $expireTime Vremya zhizni tokena v sekundah (po umolchaniyu 30 dney)
     * @return AccessToken|null
     */
    public static function generateToken($userId, $expireTime = 86400)
    {
        $token = new self();
        $token->user_id = $userId;
        $token->access_token = Yii::$app->security->generateRandomString(64);
        $token->refresh_token = Yii::$app->security->generateRandomString(64);
        $token->expires_at = date('Y-m-d H:i:s', time() + $expireTime);

        if ($token->save()) {
            return $token;
        }

        return null;
    }

    /**
     * Proverka srokov deystviya tokena
     *
     * @return bool
     */
    public function isExpired()
    {
        return strtotime($this->expires_at) < time();
    }

    /**
     * Najti deystvitelnyy token
     *
     * @param string $accessToken
     * @return AccessToken|null
     */
    public static function findValidToken($accessToken)
    {
        $token = self::findOne(['access_token' => $accessToken]);

        if ($token && !$token->isExpired()) {
            return $token;
        }

        // Esli token prosrochen - udalaem ego
        if ($token && $token->isExpired()) {
            $token->delete();
        }

        return null;
    }

    /**
     * Udalit vse tokeny polzovatelya
     *
     * @param int $userId
     * @return int Kolichestvo udalennyh tokenov
     */
    public static function deleteAllUserTokens($userId)
    {
        return self::deleteAll(['user_id' => $userId]);
    }

    /**
     * Udalit prosrochennye tokeny
     *
     * @return int Kolichestvo udalennyh tokenov
     */
    public static function deleteExpiredTokens()
    {
        return self::deleteAll(['<', 'expires_at', date('Y-m-d H:i:s')]);
    }

    /**
     * Obnovit token cherez refresh_token
     *
     * @param string $refreshToken
     * @return AccessToken|null
     */
    public static function refreshToken($refreshToken)
    {
        $oldToken = self::findOne(['refresh_token' => $refreshToken]);

        if (!$oldToken) {
            return null;
        }

        $userId = $oldToken->user_id;

        // Udalaem staryy token
        $oldToken->delete();

        // Sozdaem novyy
        return self::generateToken($userId);
    }
}