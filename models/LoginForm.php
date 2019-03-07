<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\Html;


/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $email;

    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
                        // email and password are both required
            [['email', 'password'], 'required'],

            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
                        ['email', 'email'],
             'confirmationValidate' => [
                'email',
                function ($attribute) {
                    if ($this->user !== null) {
                        if (!$this->user->getIsConfirmed()) {
                            $this->addError($attribute, 
                                            'You need to confirm your email address. '.Html::a('Click here', ['/users/resend']).' to resend the confirmation email');
                        }
                    }
                }
            ],

        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                                $this->addError($attribute, 'Incorrect email or password.');

            }
        }
    }

    /**
     * Logs in a user using the provided email and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
                        $this->user->last_login_at = date("Y-m-d H:i:s");
            $this->user->last_login_ip = \Yii::$app->request->userIP;
            $this->user->save(0);

            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[email]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
                        $this->_user = Users::findByEmail($this->email);

        }

        return $this->_user;
    }
}