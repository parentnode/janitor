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


	<h3>Project settings</h3>

<? if($model->get("config", "invalid_project_path")): ?>

	<p>
		The project path, <em><?= stringOr(PROJECT_PATH, "unknown") ?></em> is not a valid Janitor project path. 
		You can not run the set up on a broken project.
	</p>
	<p>
		Please check the project folder, <em><?= stringOr(PROJECT_PATH, "unknown") ?></em> and make sure it contains the right files.
	</p>
	<p>
		Please check the <em>LOCAL_PATH</em> and <em>FRAMEWORK_PATH</em> specified in <em>apache/httpd-vhosts.conf</em> and
		make sure they point to the right folders.
	</p>

<? else: ?>

<?		if(SETUP_TYPE == "existing"): ?>

	<p>
		It's all good.
	</p>
	<p>
		You are currently setting up an existing project in <em><?= PROJECT_PATH ?></em>. The project configuration values can only be 
		defined on the initial set up of a new project. 
	</p>
	<p>
		The values are stored in theme/config/config.php – you can change them by editing the file manually.
	</p>

	<?= $model->formStart("/janitor/admin/setup/config/updateConfigSettings", array("class" => "config labelstyle:inject")) ?>

		<p>Select the deployment type for this setup.</p>
		<fieldset>
			<?= $model->input("site_deployment", array("value" => $model->get("config", "site_deployment"))) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

	<?= $model->formEnd() ?>

<?		else: ?>

	<p>Define your core Janitor default values.</p>
	<?= $model->formStart("/janitor/admin/setup/config/updateConfigSettings", array("class" => "config labelstyle:inject")) ?>

		<fieldset>
			<?= $model->input("site_name", array("value" => $model->get("config", "site_name"))) ?>
			<?= $model->input("site_uid", array("value" => $model->get("config", "site_uid"))) ?>
			<?= $model->input("site_email", array("value" => $model->get("config", "site_email"))) ?>
			<?= $model->input("site_description", array("value" => $model->get("config", "site_description"))) ?>
		</fieldset>

		<fieldset>
			<?= $model->input("site_deployment", array("value" => $model->get("config", "site_deployment"))) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Update and continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

		<p>These values are written into theme/config/config.php – you can change them later by editing the file manually.</p>

	<?= $model->formEnd() ?>

<?		endif; ?>

<? endif; ?>

</div>
