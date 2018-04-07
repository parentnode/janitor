<?php
global $model;

$account_check = $model->checkAccountSettings();

?>
<div class="scene account i:account">
	
	<h1>Janitor configuration</h1>
	<h2>Admin account</h2>
	<ul class="actions">
		<?= $JML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
			"confirm-value" => "Are you sure you want to start over?",
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/setup"
		)); ?>
	</ul>


<?	if($model->get("account", "exists")): ?>

	<h3>Admin account status: OK</h3>
	<p>Your janitor account is already set up correctly. You cannot modify existing accounts during the set up process.</p>

	<ul class="actions">
		<li class="continue"><a href="/janitor/admin/setup/mail" class="button primary">Continue</a></li>
	</ul>

<? 	else: ?>

	<h3>Admin account settings</h3>
	<p>
		Create an admin account for this Janitor project. This account will be granted administrator 
		permissions and allows you to log in to Janitor after the set up is complete.
	</p>

	<?= $model->formStart("/janitor/admin/setup/database/updateAccountSettings", array("class" => "account labelstyle:inject")) ?>

		<fieldset>
			<?= $model->input("account_nickname", array("value" => $model->get("account", "account_nickname"))) ?>
			<?= $model->input("account_username", array("value" => $model->get("account", "account_username"))) ?>
			<?= $model->input("account_password", array("value" => $model->get("account", "account_password"))) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

	<?= $model->formEnd() ?>

<? endif; ?>

</div>
