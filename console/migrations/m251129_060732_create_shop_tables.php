<?php

use yii\db\Migration;

class m251129_060732_create_shop_tables extends Migration
{
    public function safeUp()
    {
        // users
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'phone' => $this->string()->null(),
            'email' => $this->string()->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // categories
        $this->createTable('{{%categories}}', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->null(),
            'name' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            'fk-categories-parent_id',
            '{{%categories}}',
            'parent_id',
            '{{%categories}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // products
        $this->createTable('{{%products}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->text()->null(),
            'price' => $this->decimal(10,2)->notNull(),
            'discount_price' => $this->decimal(10,2)->null(),
            'images' => $this->json()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            'fk-products-category_id',
            '{{%products}}',
            'category_id',
            '{{%categories}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // product_stock
        $this->createTable('{{%product_stock}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            'fk-product_stock-product_id',
            '{{%product_stock}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // product_details
        $this->createTable('{{%product_details}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'attribute_key' => $this->string()->notNull(),
            'attribute_value' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            'fk-product_details-product_id',
            '{{%product_details}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // orders
        $this->createTable('{{%orders}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull()->defaultValue(0),
            'total_amount' => $this->decimal(10,2)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            'fk-orders-user_id',
            '{{%orders}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // order_items
        $this->createTable('{{%order_items}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull()->defaultValue(1),
            'price' => $this->decimal(10,2)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey(
            'fk-order_items-order_id',
            '{{%order_items}}',
            'order_id',
            '{{%orders}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-order_items-product_id',
            '{{%order_items}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {

        $this->dropForeignKey('fk-order_items-product_id', '{{%order_items}}');
        $this->dropForeignKey('fk-order_items-order_id', '{{%order_items}}');
        $this->dropTable('{{%order_items}}');

        // drop orders
        $this->dropForeignKey('fk-orders-user_id', '{{%orders}}');
        $this->dropTable('{{%orders}}');


        $this->dropForeignKey('fk-product_details-product_id', '{{%product_details}}');
        $this->dropTable('{{%product_details}}');

        // drop product_stock
        $this->dropForeignKey('fk-product_stock-product_id', '{{%product_stock}}');
        $this->dropTable('{{%product_stock}}');

        // drop products
        $this->dropForeignKey('fk-products-category_id', '{{%products}}');
        $this->dropTable('{{%products}}');

        // drop categories
        $this->dropForeignKey('fk-categories-parent_id', '{{%categories}}');
        $this->dropTable('{{%categories}}');

        // drop users
        $this->dropTable('{{%users}}');
    }
}
