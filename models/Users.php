<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\helpers\Security;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\web\Application as WebApplication;
use yii\helpers\Html;
use app\components\Mailer;

/**
 * This is the model class for table "users".
 *
 * @property string $id
 * @property string $email
 * @property string $password_hash
 */
class Users extends \yii\db\ActiveRecord  implements IdentityInterface
{
 
 public $password;
 public $password_repeat;
 
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }
 
    public function behaviors()
    {
     return [
      [
       'class' => TimestampBehavior::className(),
       'createdAtAttribute' => 'created_at',
       'updatedAtAttribute' => 'updated_at',
       'value' => new Expression('NOW()'),
      ],
     ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'password_hash'], 'string', 'max' => 255],
            ['password_hash', 'safe'],
   ['password', 'string', 'min' => 6],
   [['email', 'password'], 'required'],
   ['email', 'email'],
   ['email', 'unique'],
   ['password_repeat', 'compare', 'compareAttribute'=>'password', 'skipOnEmpty' => false, 'message'=>"Passwords do not match" ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'password' => 'Password',
   'password_repeat' => 'Repeat Password',
        ];
    }
 
 public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->authKey;
    }

    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }
 
 /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */

    public function validatePassword($password)
    {
        return password_verify($password, $this->password_hash);
    }

 /**
     * Finds user by email
     *
     * @param  string      $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }
 
 public function register($post)
    {
  $transaction = $this->getDb()->beginTransaction();
  
  try {
   $this->load($post);
   if ($this->validate()) {
    if ($this->save()) {
     $token = \Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
     $token->link('users', $this);
     $transaction->commit();
     $mailer = new Mailer;
     $mailer->sendWelcomeMessage($this, isset($token) ? $token : null);
     return true;
    } else {
     $transaction->rollBack();
     return false;
    }
   } else {
    $transaction->rollBack();
    $msg = '';
    return false;
   }
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e->getMessage());
            throw $e;
        }
    }
 
    /**
     * @return bool Whether the user is confirmed or not.
     */
    public function getIsConfirmed()
    {
        return $this->confirmed_at != '0000-00-00 00:00:00';
    }

    /**
     * Attempts user confirmation.
     *
     * @param string $code Confirmation code.
     *
     * @return boolean
     */
    public function attemptConfirmation($id, $code)
    {
        $token = Token::find()->where(['uid' =>$id, 'code' => $code, 'type' => Token::TYPE_CONFIRMATION])->one();
  
        if ($token && !$token->isExpired()) {
            $token->delete();
            if (($success = $this->confirm())) {
                \Yii::$app->user->login($this, Yii::$app->params['rememberFor']);
    $this->last_login_at = date("Y-m-d H:i:s");
    $this->last_login_ip = \Yii::$app->request->userIP;
    $this->save(0);
                $message = 'Thank you, registration is now complete.';
            } else {
                $message = 'Something went wrong and your account has not been confirmed.';
            }
        } else {
            $success = false;
            $message = 'The confirmation link is invalid or expired. Please try '.Html::a('requesting', ['/users/resend']).' a new one.';
        }

        Yii::$app->session->setFlash($success ? 'success' : 'danger', $message);

        return $success;
    }

    /**
     * Confirms the user by setting 'confirmed_at' field to current time.
     */
    public function confirm()
    {
        $result = (bool) $this->updateAttributes(['confirmed_at' => date('Y-m-d H:i:s')]);
        return $result;
    }

    /**
     * Displays page where user can request new confirmation token. If resending was successful, displays message.
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionResend()
    {
        /** @var ResendForm $model */
        $model = new ResendForm;
        if ($model->load(\Yii::$app->request->post()) && $model->resend()) {
   
           Yii::$app->session->setFlash('info', 'A new confirmation link has been sent');
           return $this->redirect(['site/index']);
        }

        return $this->render('resend', [
            'model' => $model,
        ]);
    }

    /**
 * Resets password.
 *
 * @param string $password
 *
 * @return bool
 */
public function resetPassword($password)
{
   return (bool)$this->updateAttributes(['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
}


    public function beforeSave($insert)
    {
        if ($insert) {
            $this->setAttribute('registration_ip', \Yii::$app->request->userIP);
        }

        if (!empty($this->password)) {
            $this->setAttribute('password_hash', password_hash($this->password, PASSWORD_DEFAULT));
        }

        return parent::beforeSave($insert);
    }

}