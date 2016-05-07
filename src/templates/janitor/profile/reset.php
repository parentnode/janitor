<?php
global $action;
global $model;


$reset_token = $action[1];

?>
<div class="scene i:scene defaultEdit userEdit profileEdit">
	<h1>Reset password</h1>

<? if($model->checkResetToken($reset_token)): ?>

	<div class="password">
		<h2>Password</h2>

		<?= $model->formStart("resetPassword", array("class" => "password i:resetPassword")) ?>
			<?= $model->input("reset-token", array("type" => "hidden", "value" => $reset_token)) ?>

			<fieldset>
				<?= $model->input("new_password", array("required" => true)) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Set new password", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

<? else: ?>

	<p>Your request is invalid. Resetting your password must be completed within 15 minutes.</p>

<? endif; ?>

</div>