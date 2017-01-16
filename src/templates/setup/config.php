<?php
global $model;

$config_check = $model->checkConfigSettings();
?>
<div class="scene config i:config">
	
	<h1>Janitor configuration</h1>
	<h2>Project settings</h2>
	<ul class="actions">
		<?= $JML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
			"confirm-value" => "Are you sure you want to start over?",
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/setup"
		)); ?>
	</ul>


	<?= $model->formStart("/janitor/admin/setup/config/updateConfigSettings", array("class" => "config labelstyle:inject")) ?>

		<h3>Verify project location</h3>
		<fieldset>
			<?= $model->input("project_path", array("value" => $model->project_path)) ?>
		</fieldset>

		<h3>Specify custom project settings</h3>
		<p>These values are added to your site config.php – you can always change them later via this page or by editing the config file manually.</p>
		<fieldset>
			<?= $model->input("site_name", array("value" => $model->site_name)) ?>
			<?= $model->input("site_uid", array("value" => $model->site_uid)) ?>
			<?= $model->input("site_email", array("value" => $model->site_email)) ?>
			<?= $model->input("site_description", array("value" => $model->site_description)) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

	<?= $model->formEnd() ?>
</div>