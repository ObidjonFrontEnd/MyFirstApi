<?php

namespace frontend\models;

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
 *
 * @property Categories $category
 * @property OrderItems[] $orderItems
 * @property ProductDetails[] $productDetails
 * @property ProductStock[] $productStocks
 */
class Product extends \yii\db\ActiveRecord
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
            [['category_id', 'name', 'price', 'created_at', 'updated_at'], 'required'],
            [['category_id', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['price', 'discount_price'], 'number'],
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
            'id' => 'ID',
            'category_id' => 'Category ID',
            'name' => 'Name',
            'description' => 'Description',
            'price' => 'Price',
            'discount_price' => 'Discount Price',
            'images' => 'Images',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
     * Gets query for [[ProductStocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductStocks()
    {
        return $this->hasMany(ProductStock::class, ['product_id' => 'id']);
    }

}
