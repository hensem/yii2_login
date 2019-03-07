<?php
namespace app\models;

use app\components\Mailer;
use app\models\Token;
use yii\base\Model;

/**
 * ResendForm gets user email address and if user with given email is registered it sends new confirmation message
 * to him in case he did not validate his email.
 *
 */
class ResendForm extends Model
{
    /**
     * @var string
     */
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            [['email'], 'required'],
            ['email', 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'resend-form';
    }

    /**
     * Creates new confirmation token and sends it to the user.
     *
     * @return bool
     */
    public function resend()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = Users::find()->where(['email' => $this->email])->one();

        if ($user && !$user->getIsConfirmed()) {
            /** @var Token $token */
   
            $token = new Token;
            $token->uid = $user->id;
            $token->type = Token::TYPE_CONFIRMATION;
   
            $token->save(false);
            $mailer = new Mailer;
            $mailer->sendWelcomeMessage($user, $token);

            \Yii::$app->session->setFlash(
             'info',
             'A message has been sent to your email address. It contains a confirmation link that you must click to complete registration.'
            );

            return true;
        } else {
            \Yii::$app->session->setFlash('danger', 'Email not found');
            return false;
        }

    }
}