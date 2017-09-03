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

		<h3>Custom project settings</h3>
		<p>These values exist in theme/config/config.php – you can change them by editing the file manually.</p>
		<fieldset>
		<? if(SETUP_TYPE == "new"): ?>
			<?= $model->input("site_name", array("value" => $model->site_name)) ?>
			<?= $model->input("site_uid", array("value" => $model->site_uid)) ?>
			<?= $model->input("site_email", array("value" => $model->site_email)) ?>
			<?= $model->input("site_description", array("value" => $model->site_description)) ?>
		<? else: ?>
			<?= $model->output("site_name", array("type" => "paragraph", "value" => $model->site_name)) ?>
			<?= $model->output("site_uid", array("type" => "paragraph", "value" => $model->site_uid)) ?>
			<?= $model->output("site_email", array("type" => "paragraph", "value" => $model->site_email)) ?>
			<?= $model->output("site_description", array("type" => "paragraph", "value" => $model->site_description)) ?>
		<? endif; ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

	<?= $model->formEnd() ?>
</div>