<?php

namespace app\models;

use app\components\Mailer;
use yii\base\Model;

/**
 * Model for collecting data on password recovery.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class RecoveryForm extends Model
{
    const SCENARIO_REQUEST = 'request';
    const SCENARIO_RESET = 'reset';

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;
    public $password_repeat;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'    => 'Email',
            'password' => 'Password',
            'password_repeat' => 'Repeat Password',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_REQUEST => ['email'],
            self::SCENARIO_RESET => ['password', 'password_repeat'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute'=>'password', 'skipOnEmpty' => false, 'message'=>"Passwords do not match"],
        ];
    }

    /**
     * Sends recovery message.
     *
     * @return bool
     */
    public function sendRecoveryMessage()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = Users::find()->where(['email' => $this->email])->one();

        if ($user) {
            /** @var Token $token */
            $token = new Token;
            $token->uid = $user->id;
            $token->type = Token::TYPE_RECOVERY;
            $token->save(false);

            $mailer = new Mailer;

            if (!$mailer->sendRecoveryMessage($user, $token)) {
                return false;
            }
            \Yii::$app->session->setFlash(
                'info',
                'An email has been sent with instructions for resetting your password'
            );

            return true;
        } else {
            \Yii::$app->session->setFlash('danger', 'Email not found');
            return false;
        }
    }

    /**
     * Resets user's password.
     *
     * @param Token $token
     *
     * @return bool
     */
    public function resetPassword(Token $token)
    {
        if (!$this->validate() || $token->users === null) {
            return false;
        }

        if ($token->users->resetPassword($this->password)) {
            \Yii::$app->session->setFlash('success', 'Your password has been changed successfully.');
            $token->delete();
        } else {
            \Yii::$app->session->setFlash(
                'danger',
                'An error occurred and your password has not been changed. Please try again later.'
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'recovery-form';
    }
}