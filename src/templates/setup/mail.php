<?php
global $model;

$mail_check = $model->checkMailSettings();

?>
<div class="scene mail i:mail">

	<h1>Janitor configuration</h1>
	<h2>Mail settings</h2>
	<ul class="actions">
		<?= $JML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
			"confirm-value" => "Are you sure you want to start over?",
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/setup"
		)); ?>
	</ul>

	<p>
		Janitor will send system notifications to the project admin automatically. The mail account can also
		be used for sending newsletters and notifications to your users.
	</p>

	<?= $model->formStart("/janitor/admin/setup/mail/updateMailSettings", array("class" => "mail labelstyle:inject")) ?>

<? if($mail_check): ?>

		<h3>Mail status: OK</h3>
		<p>Your mailing system is already configured correctly.</p>
		<ul class="actions">
			<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

<? endif; ?>

		<h3>System mail settings</h3>
		<p>
			Who should receive system notifications? (comma separate multiple recipients)
		</p>
		<fieldset>
			<?= $model->input("mail_admin", array("value" => $model->mail_admin)) ?>
		</fieldset>

		<p>Specify SMTP mail account information to enable system notifications and sending newsletters to your users.</p>

		<fieldset>
			<?= $model->input("mail_host", array("value" => $model->mail_host)) ?>
			<?= $model->input("mail_port", array("value" => $model->mail_port)) ?>
			<?= $model->input("mail_username", array("value" => $model->mail_username)) ?>
			<?= $model->input("mail_password", array("value" => $model->mail_password)) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Update and continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

		<p class="note">
			You can use a Google account for the simplest possible setup, but that has some limitations. If you want 
			to take it a step further consider setting up a <a href="https://mailgun.com" target="_blank">Mailgun</a> account.
		</p>

	<?= $model->formEnd() ?>

</div>