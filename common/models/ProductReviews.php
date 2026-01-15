<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "product_reviews".
 *
 * @property int $id
 * @property int $product_id
 * @property int $user_id
 * @property int $rating
 * @property string $comment
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Products $product
 */
class ProductReviews extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_reviews';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['updated_at'], 'default', 'value' => null],
            [['product_id', 'user_id', 'rating', 'comment'], 'required'],
            [['product_id', 'user_id', 'rating'], 'integer'],
            [['comment'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_id', 'user_id'], 'unique', 'targetAttribute' => ['product_id', 'user_id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'rating' => Yii::t('app', 'Rating'),
            'comment' => Yii::t('app', 'Comment'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Products::class, ['id' => 'product_id']);
    }


    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Получаем все рейтинги для этого продукта
        $stats = self::find()
            ->where(['product_id' => $this->product_id])
            ->select(['AVG(rating) as avg_rating', 'COUNT(*) as rating_count'])
            ->asArray()
            ->one();

        // Обновляем продукт
        $product = $this->product;
        $product->rating_avg = $stats['avg_rating'] ?? 0;
        $product->rating_count = $stats['rating_count'] ?? 0;
        $product->save(false); // без валидации, чтобы не мешало
    }

}
