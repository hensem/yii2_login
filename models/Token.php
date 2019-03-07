<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * This is the model class for table "token".
 *
 * @property string $uid
 * @property string $code
 * @property string $created_at
 * @property int $type
 */
class Token extends \yii\db\ActiveRecord
{
    const TYPE_CONFIRMATION      = 0;
    const TYPE_RECOVERY          = 1;

 
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid', 'code', 'type'], 'required'],
            [['uid', 'type'], 'integer'],
            [['created_at'], 'safe'],
            [['code'], 'string', 'max' => 32],
            [['uid', 'code', 'type'], 'unique', 'targetAttribute' => ['uid', 'code', 'type']],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['uid' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uid' => 'Uid',
            'code' => 'Code',
            'created_at' => 'Created At',
            'type' => 'Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasOne(Users::className(), ['id' => 'uid']);
    }
 
    /**
     * @return bool Whether token has expired.
     */
    public function isExpired()
    {
        switch ($this->type) {
            case self::TYPE_CONFIRMATION:
                $expirationTime = Yii::$app->params['confirmWithin'];
                break;
            case self::TYPE_RECOVERY:
    $expirationTime = Yii::$app->params['recoverWithin'];
    break;

            default:
                throw new \RuntimeException();
        }

        return (strtotime($this->created_at) + $expirationTime) < time();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        switch ($this->type) {
            case self::TYPE_CONFIRMATION:
                $route = '/users/confirm';
                break;
            case self::TYPE_RECOVERY:
                $route = '/users/reset';
                break;
            default:
                throw new \RuntimeException();
        }

        return Url::to([$route, 'id' => $this->uid, 'code' => $this->code], true);
    }

 public function beforeSave($insert)
    {
        if ($insert) {
            static::deleteAll(['uid' => $this->uid, 'type' => $this->type]);
            $this->setAttribute('created_at', date('Y-m-d H:i:s'));
            $this->setAttribute('code', Yii::$app->security->generateRandomString());
        }

        return parent::beforeSave($insert);
    }

}