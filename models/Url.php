<?php

namespace app\models;

use Yii;

class Url extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'url';
    }

    public function rules()
    {
        return [
            [['original_url', 'short_code'], 'required'],
            [['clicks', 'created_at'], 'integer'],
            [['original_url'], 'url'],
            [['short_code'], 'string', 'max' => 10],
            [['short_code'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'original_url' => 'Оригинальная ссылка',
            'short_code' => 'Короткий код',
            'clicks' => 'Переходы',
            'created_at' => 'Дата создания',
        ];
    }
    
    // Связь с логами
    public function getLogs()
    {
        return $this->hasMany(UrlLog::className(), ['url_id' => 'id']);
    }
}