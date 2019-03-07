<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\Users */
/* @var $form ActiveForm */

$this->title = 'Sign up';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin(); ?>

                <?= $form->field($model, 'email') ?>

                <?= $form->field($model, 'password')->passwordInput() ?>
    
                <?= $form->field($model, 'password_repeat')->passwordInput() ?>

                <?= Html::submitButton('Sign up', ['class' => 'btn btn-success btn-block']) ?>

                <?php ActiveForm::end(); ?>
            </div>
			<?= yii\authclient\widgets\AuthChoice::widget([

                 'baseAuthUrl' => ['site/auth'],

                 'popupMode' => false,

            ]) ?>        </div>
        <p class="text-center">
            <?= Html::a('Already registered? Sign in!', ['/site/login']) ?>
        </p>
    </div>
</div>