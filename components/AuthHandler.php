<?php
namespace app\components;

use app\models\Auth;
use app\models\Users;
use Yii;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;

/**
 * AuthHandler handles successful authentication via Yii auth component
 */
class AuthHandler
{
    /**
     * @var ClientInterface
     */
    private $client;
    private $client_id;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        $this->client_id = $client->id;
    }

    public function handle()
    {
        $attributes = $this->client->getUserAttributes();
        
        switch($this->client_id) {
            case 'google':
                $email = ArrayHelper::getValue($attributes, 'emails.0.value');
                $id = ArrayHelper::getValue($attributes, 'id');
                break;
            default:
                $email = ArrayHelper::getValue($attributes, 'email');
                $id = ArrayHelper::getValue($attributes, 'id');
        }
        
        /* @var Auth $auth */
        $auth = Auth::find()->where([
            'source' => $this->client->getId(),
            'source_id' => $id,
        ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                /* @var User $user */
                $user = Users::find()->where(['id' => $auth->uid])->one();
                Yii::$app->user->login($user);
            } else { // signup
                if ($email !== null && Users::find()->where(['email' => $email])->exists()) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "User with the same email as in {client} account already exists but isn't linked to it. Login using email first to link it.", ['client' => $this->client->getTitle()]),
                    ]);
                } else {
                    $password = Yii::$app->security->generateRandomString(8);
                    $user = new Users([
                        'email' => $email,
                        'password' => $password,
                        'password_repeat' => $password,
                        'confirmed_at' => date('Y-m-d H:i:s'),
                    ]);
                    
                    $transaction = Users::getDb()->beginTransaction();

                    if ($user->save()) {
                        $auth = new Auth([
                            'uid' => $user->id,
                            'source' => $this->client->getId(),
                            'source_id' => (string)$id,
                        ]);
                        if ($auth->save()) {
                            $transaction->commit();
                            Yii::$app->user->login($user);
                            $user->last_login_at = date('Y-m-d H:i:s');
                            $user->last_login_ip = \Yii::$app->request->userIP;
                            $user->save(0);
                            Yii::$app->getSession()->setFlash('success', 'Thank you, registration is now complete. If you want to login directly, this is your password: '.$password);
                        } else {
                            Yii::$app->getSession()->setFlash('error', 'Unable to save '.$this->client->getTitle().' account: '.json_encode($auth->getErrors()));
                        }
                    } else {
                        Yii::$app->getSession()->setFlash('error', 'Unable to save user: '.json_encode($user->getErrors()));
                    }
                }
            }
        } else { // user already logged in
            if (!$auth) { // add auth provider
                $auth = new Auth([
                    'uid' => Yii::$app->user->id,
                    'source' => $this->client->getId(),
                    'source_id' => (string)$attributes['id'],
                ]);
                if ($auth->save()) {
                    Yii::$app->getSession()->setFlash('success', 'Successfully linked '.$this->client->getTitle().' account.');
                } else {
                    Yii::$app->getSession()->setFlash('error', 'Unable to link '.$this->client->getTitle().' account: '.json_encode($auth->getErrors()));
                }
            } else { // there's existing auth
                Yii::$app->getSession()->setFlash('error', 'Unable to link '.$this->client->getTitle().' account. There is another user using it.');
            }
        }
    }
}