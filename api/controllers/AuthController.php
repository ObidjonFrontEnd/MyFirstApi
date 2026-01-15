<?php

namespace api\controllers;

use Yii;
use common\models\User;
use common\models\AccessToken;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class AuthController extends ApiController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Настраиваем аутентификатор на работу с Bearer Token (JWT)
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\CompositeAuth::class,
            'authMethods' => [
                \yii\filters\auth\HttpBearerAuth::class, // Ищем токен в Header Authorization: Bearer ...
            ],
            'except' => ['guest', 'login', 'register', 'options'],
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'login' => ['POST'], 'register' => ['POST'], 'guest' => ['POST'],
                'logout' => ['POST'], 'refresh' => ['POST'], 'me' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    public function actionGuest()
    {
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText(User::JWT_SECRET));
        $now = new \DateTimeImmutable();
        $token = $config->builder()
            ->issuedBy(Yii::$app->request->hostInfo)
            ->issuedAt($now)
            ->expiresAt($now->modify('+2 hours'))
            ->withClaim('role', 'guest')
            ->getToken($config->signer(), $config->signingKey());

        // Мы возвращаем ['data' => ['token' => '...']]
        return $this->success(['token' => $token->toString()], "Guest token generated");
    }

    public function actionLogin()
    {
        $post = Yii::$app->request->post();
        $user = User::findOne(['email' => $post['email'] ?? '']);

        if (!$user || !$user->validatePassword($post['password'] ?? '')) {
            throw new UnauthorizedHttpException('Email yoki parol xato');
        }

        // Конфигурация JWT v4
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText(User::JWT_SECRET)
        );

        $now = new \DateTimeImmutable();
        $token = $config->builder()
            ->issuedBy(Yii::$app->request->hostInfo)
            ->issuedAt($now)
            ->expiresAt($now->modify('+24 hours'))
            ->relatedTo((string)$user->id)
            ->withClaim('role', 'user')
            ->getToken($config->signer(), $config->signingKey());

        return [
            'success' => true,
            'access_token' => $token->toString(),
            'user' => $user
        ];
    }

    public function actionRegister()
    {
        $user = new User(['scenario' => 'create']);
        if ($user->load(Yii::$app->request->post(), '') && $user->save()) {
            return $this->success($user, "Ro'yxatdan o'tdingiz");
        }
        return $this->error("Xatolik", 422, $user->errors);
    }

    public function actionMe()
    {
        return Yii::$app->user->identity;
    }

    public function actionRefresh()
    {
        $token = AccessToken::refreshToken(Yii::$app->request->post('refresh_token'));
        if (!$token) throw new UnauthorizedHttpException('Refresh token xato');
        return ['success' => true, 'access_token' => $token->access_token];
    }

    public function actionLogout()
    {
        $authHeader = Yii::$app->request->headers->get('Authorization');
        if ($authHeader && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $token = AccessToken::findOne(['access_token' => $matches[1]]);
            if ($token) $token->delete();
            return $this->success(null, "Logged out");
        }
        throw new BadRequestHttpException('Token topilmadi');
    }
}