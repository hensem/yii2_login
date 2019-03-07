<?php

use yii\bootstrap\Alert;

$this->title = 'Welcome';
$this->params['breadcrumbs'][] = $this->title;            
?>
<div class="row">
 <div class="col-xs-12">
  Your account has been created and a message with further instructions has been sent to <?php echo $email; ?>.
 </div>
</div>