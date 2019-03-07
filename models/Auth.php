<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "auth".
 *
 * @property string $id
 * @property string $uid
 * @property string $source
 * @property string $source_id
 */
class Auth extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid', 'source', 'source_id'], 'required'],
            [['uid'], 'integer'],
            [['source', 'source_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'source' => 'Source',
            'source_id' => 'Source ID',
        ];
    }
}
