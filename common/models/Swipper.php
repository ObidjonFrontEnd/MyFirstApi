<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "swipper".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $link
 * @property int|null $discount_price
 * @property int|null $price
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $parent_id
 * @property string|null $position
 */
class Swipper extends \yii\db\ActiveRecord
{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'swipper';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'link', 'discount_price', 'price', 'created_at', 'update_at', 'parent_id', 'position'], 'default', 'value' => null],
            [['description'], 'string'],
            [['discount_price', 'price', 'parent_id'], 'integer'],
            [['created_at', 'update_at'], 'safe'],
            [['name', 'link', 'position'], 'string', 'max' => 255],
            [['position'], 'in', 'range' => ['left', 'right', 'back', 'top', 'bottom']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'link' => Yii::t('app', 'Link'),
            'discount_price' => Yii::t('app', 'Discount Price'),
            'price' => Yii::t('app', 'Price'),
            'created_at' => Yii::t('app', 'Created At'),
            'update_at' => Yii::t('app', 'Update At'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'position' => Yii::t('app', 'Position'),
        ];
    }

}
