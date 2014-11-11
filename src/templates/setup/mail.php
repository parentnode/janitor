<?php
global $model;

$mail_check = $model->checkMailSettings();

?>
<div class="scene mail i:mail">

	<h1>Setup Mail</h1>

	<?= $model->formStart("/setup/mail", array("class" => "labelstyle:inject")) ?>

<? if($mail_check): ?>

		<h2>Mail status: OK</h2>
		<p>Your mailing system is already configured correctly.</p>
		<ul class="actions">
			<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

<? endif; ?>

		<h2>Admin mail settings</h2>
		<p>Specify mail information to enable automatic mailing (response, errors).</p>

		<fieldset>
			<?= $model->input("mail_admin", array("value" => $model->mail_admin)) ?>
			<?= $model->input("mail_host", array("value" => $model->mail_host)) ?>
			<?= $model->input("mail_port", array("value" => $model->mail_port)) ?>
			<?= $model->input("mail_username", array("value" => $model->mail_username)) ?>
			<?= $model->input("mail_password", array("value" => $model->mail_password, "min" => 1)) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Update and continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

	<?= $model->formEnd() ?>

</div>