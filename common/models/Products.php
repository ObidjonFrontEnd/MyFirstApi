<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property float|null $discount_price
 * @property string|null $images
 * @property int $created_at
 * @property int $updated_at
 * @property float $rating_avg
 * @property int|null $rating_count
 *
 * @property Categories $category
 * @property OrderItems[] $orderItems
 * @property ProductDetails[] $productDetails
 * @property ProductReviews[] $productReviews
 * @property ProductStock[] $productStocks
 */
class Products extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'discount_price', 'images'], 'default', 'value' => null],
            [['rating_avg'], 'default', 'value' => 0.00],
            [['rating_count'], 'default', 'value' => 0],
            [['category_id', 'name', 'price', 'created_at', 'updated_at'], 'required'],
            [['category_id', 'created_at', 'updated_at', 'rating_count'], 'integer'],
            [['description'], 'string'],
            [['price', 'discount_price', 'rating_avg'], 'number'],
            [['images'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Categories::class, 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_id' => Yii::t('app', 'Category ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'price' => Yii::t('app', 'Price'),
            'discount_price' => Yii::t('app', 'Discount Price'),
            'images' => Yii::t('app', 'Images'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'rating_avg' => Yii::t('app', 'Rating Avg'),
            'rating_count' => Yii::t('app', 'Rating Count'),
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Categories::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItems::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductDetails()
    {
        return $this->hasMany(ProductDetails::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductReviews]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductReviews()
    {
        return $this->hasMany(ProductReviews::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductStocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductStocks()
    {
        return $this->hasMany(ProductStock::class, ['product_id' => 'id']);
    }

}
