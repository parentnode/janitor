<?php
global $action;
global $model;


$reset_token = $action[1];

if($model->checkResetToken($reset_token)) {
	
}

?>
<div class="scene i:scene defaultEdit userEdit profileEdit">
	<h1>Reset password</h1>


	<div class="password i:resetPassword">
		<h2>Password</h2>

		<?= $model->formStart("resetPassword", array("class" => "password")) ?>
			<fieldset>
				<?= $model->input("new_password", array("required" => true)) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Reset password", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>