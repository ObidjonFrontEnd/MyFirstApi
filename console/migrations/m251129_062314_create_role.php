<?php

use yii\db\Migration;

class m251129_062314_create_role extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Создаём права
        $createProduct = $auth->createPermission('createProduct');
        $createProduct->description = 'Create a product';
        $auth->add($createProduct);

        $updateProduct = $auth->createPermission('updateProduct');
        $updateProduct->description = 'Update product';
        $auth->add($updateProduct);

        $deleteProduct = $auth->createPermission('deleteProduct');
        $deleteProduct->description = 'Delete product';
        $auth->add($deleteProduct);

        // Создаём роли
        $user = $auth->createRole('user');
        $auth->add($user);

        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $createProduct);
        $auth->addChild($admin, $updateProduct);
        $auth->addChild($admin, $deleteProduct);

        // Назначаем роль admin пользователю с ID 1
        $auth->assign($admin, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251129_062314_create_role cannot be reverted.\n";

        return false;
    }
    */
}
