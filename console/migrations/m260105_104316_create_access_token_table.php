<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%access_token}}`.
 */
class m260105_104316_create_access_token_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%access_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('ID пользователя'),
            'token' => $this->string(255)->notNull()->unique()->comment('Токен доступа'),
            'type' => $this->string(50)->defaultValue('bearer')->comment('Тип токена'),
            'expires_at' => $this->integer()->notNull()->comment('Время истечения (timestamp)'),
            'created_at' => $this->integer()->notNull()->comment('Время создания'),
            'updated_at' => $this->integer()->notNull()->comment('Время обновления'),
            'ip_address' => $this->string(45)->comment('IP адрес'),
            'user_agent' => $this->string(255)->comment('User Agent'),
            'is_active' => $this->boolean()->defaultValue(1)->comment('Активен ли токен'),
        ]);

        // Индексы
        $this->createIndex('idx-access_token-user_id', '{{%access_token}}', 'user_id');
        $this->createIndex('idx-access_token-token', '{{%access_token}}', 'token');
        $this->createIndex('idx-access_token-expires_at', '{{%access_token}}', 'expires_at');
        $this->createIndex('idx-access_token-is_active', '{{%access_token}}', 'is_active');

        // Внешний ключ
        $this->addForeignKey(
            'fk-access_token-user_id',
            '{{%access_token}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-access_token-user_id', '{{%access_token}}');
        $this->dropTable('{{%access_token}}');
    }
}
