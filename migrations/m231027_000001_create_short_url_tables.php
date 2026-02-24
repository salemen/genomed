<?php

use yii\db\Migration;

class m231027_000001_create_short_url_tables extends Migration
{
    public function safeUp()
    {
        // Таблица для хранения ссылок
        $this->createTable('url', [
            'id' => $this->primaryKey(),
            'original_url' => $this->string()->notNull(),
            'short_code' => $this->string(10)->unique()->notNull(),
            'clicks' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer(),
        ]);

        // Таблица для логов переходов
        $this->createTable('url_log', [
            'id' => $this->primaryKey(),
            'url_id' => $this->integer()->notNull(),
            'ip_address' => $this->string(45),
            'user_agent' => $this->string(255),
            'created_at' => $this->integer(),
        ]);

        // Внешний ключ
        $this->addForeignKey(
            'fk-url_log-url_id',
            'url_log',
            'url_id',
            'url',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-url_log-url_id', 'url_log');
        $this->dropTable('url_log');
        $this->dropTable('url');
    }
}