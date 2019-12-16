<?php
global $model;

$mail_check = $model->checkMailSettings();

?>
<div class="scene mail i:mail">

	<div class="progress">5/7</div>

	<h1>Janitor configuration</h1>
	<h2>Mail gateway (optional)</h2>
	<ul class="actions">
		<?= $HTML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
			"confirm-value" => "Are you sure you want to start over?",
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/setup"
		)); ?>
	</ul>

	<p>
		Janitor will send system notifications to the project administrator automatically. The mail account can also
		be used for sending newsletters and notifications to your users.
	</p>


<? if($model->get("mail", "passed")): ?>

	<h3>Mail status: <?= $model->get("mail", "skipped") ? "SKIPPED" : "OK" ?></h3>
<?		if($model->get("mail", "skipped")): ?>
	<p>The system will be configured without mail support.</p>
<?		else: ?>
	<p>Your mailing system is already configured correctly.</p>
<?		endif; ?>
	<ul class="actions">
		<li class="continue"><a href="/janitor/admin/setup/payment" class="button primary">Continue</a></li>
	</ul>

<? else: ?>

	<?= $model->formStart("/janitor/admin/setup/mail/updateMailSettings", array("class" => "skip labelstyle:inject")) ?>

		<?= $model->input("skip_mail", array("type" => "hidden", "value" => "1")) ?>

		<ul class="actions">
			<?= $model->submit("Skip mail setup", array("wrapper" => "li.skip")) ?>
		</ul>

	<?= $model->formEnd() ?>


<? endif; ?>

	<h3>System mail settings</h3>
	<p>
		Who should receive system notifications? (comma separate multiple recipients)
	</p>

	<?= $model->formStart("/janitor/admin/setup/mail/updateMailSettings", array("class" => "mail labelstyle:inject")) ?>

		<fieldset>
			<?= $model->input("mail_admin", array("value" => $model->get("mail", "mail_admin"))) ?>
		</fieldset>


		<p>
			Which type of mail endpoint will be used?
		</p>
		<fieldset>
			<?= $model->input("mail_type", array("value" => $model->get("mail", "mail_type"))) ?>
		</fieldset>

		<div class="type_smtp">
			<p>Specify SMTP mail account information to enable system notifications and sending newsletters to your users.</p>

			<fieldset>
				<?= $model->input("mail_smtp_host", array("value" => $model->get("mail", "mail_smtp_host"))) ?>
				<?= $model->input("mail_smtp_port", array("value" => $model->get("mail", "mail_smtp_port"))) ?>
				<?= $model->input("mail_smtp_username", array("value" => $model->get("mail", "mail_smtp_username"))) ?>
				<?= $model->input("mail_smtp_password", array("value" => $model->get("mail", "mail_smtp_password"))) ?>
			</fieldset>
		</div>
		<div class="type_mailgun">
			<p>Specify Mailgun account information to enable system notifications and sending newsletters to your users.</p>

			<fieldset>
				<?= $model->input("mail_mailgun_api_key", array("value" => $model->get("mail", "mail_mailgun_api_key"))) ?>
				<?= $model->input("mail_mailgun_domain", array("value" => $model->get("mail", "mail_mailgun_domain"))) ?>
			</fieldset>
		</div>


		<ul class="actions">
			<?= $model->submit("Update and continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

		<p class="note">
			You can use a Google account for the simplest possible setup, but that has some limitations – and you <strong>need to have SMTP connections enabled</strong>. If you want 
			to take it a step further consider setting up a <a href="https://mailgun.com" target="_blank">Mailgun</a> account.
		</p>

	<?= $model->formEnd() ?>

</div>