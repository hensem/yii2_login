<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\authclient\widgets\AuthChoice;
use app\models\Auth;

$this->title = 'Link Your Account';
$this->params['breadcrumbs'][] = $this->title;
?>
<div>
    <h1><?= Html::encode($this->title) ?></h1>

    <?php 
    $user = \Yii::$app->user->identity;
    $authAuthChoice = yii\authclient\widgets\AuthChoice::begin([
        'baseAuthUrl' => ['site/auth'],
        'popupMode' => false,
    ]) ?>
    <table class="table">
        <?php foreach ($authAuthChoice->getClients() as $client): ?>
            <tr>
                <td style="width: 32px; vertical-align: middle">
                    <?= Html::tag('span', '', ['class' => 'auth-icon ' . $client->getName()]) ?>
                </td>
                <td style="vertical-align: middle">
                    <strong><?= $client->getTitle() ?></strong>
                </td>
                <td style="width: 120px">
                    <?php $auth = Auth::find()->where(['uid' => $user->id, 'source' => $client->id])->one(); ?>
                    <?= $auth ?
                        Html::a('Disconnect', ['/users/disconnect'], [
                            'class' => 'btn btn-danger btn-block',
                            'data' => [
                                'method' => 'post',
                                'params' => ['client' => $client->id],
                            ],
                        ]) :
                        Html::a('Connect', ['/site/auth', 'authclient' => $client->id, 'return' => 'users/link'], [
                            'class' => 'btn btn-success btn-block',
                        ])
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>