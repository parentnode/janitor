<?php
global $model;

$config_check = $model->checkConfigSettings();
?>
<div class="scene config i:config">
	
	<h1>Janitor configuration</h1>
	<?= $model->formStart("/setup/config", array("class" => "labelstyle:inject")) ?>

		<h2>Project settings</h2>

		<p>Verify the absolute path to your project.</p>
		<fieldset>
			<?= $model->input("project_path", array("value" => $model->project_path)) ?>
		</fieldset>

		<p>
			Specify <em>unique ID</em>, <em>name</em> and <em>public email</em> for your project.
		</p>
		<fieldset>
			<?= $model->input("site_uid", array("value" => $model->site_uid)) ?>
			<?= $model->input("site_name", array("value" => $model->site_name)) ?>
			<?= $model->input("site_email", array("value" => $model->site_email)) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

	<?= $model->formEnd() ?>
</div>