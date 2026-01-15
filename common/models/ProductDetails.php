<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "product_details".
 *
 * @property int $id
 * @property int $product_id
 * @property string $attribute_key
 * @property string $attribute_value
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Products $product
 */
class ProductDetails extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_details';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'attribute_key', 'attribute_value', 'created_at', 'updated_at'], 'required'],
            [['product_id', 'created_at', 'updated_at'], 'integer'],
            [['attribute_key', 'attribute_value'], 'string', 'max' => 255],
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
            'attribute_key' => Yii::t('app', 'Attribute Key'),
            'attribute_value' => Yii::t('app', 'Attribute Value'),
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

}
