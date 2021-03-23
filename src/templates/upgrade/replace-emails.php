<?php
global $model;
global $upgrade_model;

mailer();
?>
<div class="scene i:scene">
	<h1>Replace emails</h1>

	<h3>Settings</h3>

	<?= $model->formStart("/janitor/admin/setup/upgrade/replaceEmails", array("class" => "labelstyle:inject i:replace_emails")) ?>
		<fieldset>
			<?= $model->input("replacement", array("label" => "Replacement email address", "type" => "string", "required" => true,	 "hint_message" => "Which email address do you want to use as a replacement?")) ?>
			<?= $model->input("exclude", array("label" => "Exclude email address", "type" => "string", "hint_message" => "You may want to exclude your own email from being replaced.", "value" => defined(ADMIN_EMAIL) ? ADMIN_EMAIL : "")) ?>
			<?= $model->input("user_id_suffix", array("label" => 'Add user_id suffix' , "type" => "checkbox", "value" => 1, "hint_message" => "If the replacement email is e.g. test.parentnode@gmail.com, then the user_id of each user will be added to the replacement email, like this: test.parentnode+#user_id#.")) ?>
		</fieldset>
		<ul class="actions">
			<?= $model->submit("Replace emails", array("class" => "primary", "wrapper" => "li.replace")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>