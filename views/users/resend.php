<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


$this->title = 'Request new confirmation message';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'resend-form',
                    'enableAjaxValidation' => false,
                    'enableClientValidation' => true,
                ]); ?>

                <?php echo $form->field($model, 'email')->textInput(['autofocus' => true]); ?>

                <?php echo Html::submitButton('Continue', ['class' => 'btn btn-primary btn-block']); ?><br>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>