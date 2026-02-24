<?php

namespace app\models;

use Yii;

class UrlLog extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'url_log';
    }

    public function rules()
    {
        return [
            [['url_id', 'created_at'], 'integer'],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 255],
        ];
    }
}