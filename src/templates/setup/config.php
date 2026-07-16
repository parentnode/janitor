<?php
global $model;

$config_check = $model->checkConfigSettings();
?>
<div class="scene config i:config">

	<div class="progress">2/7</div>

	<h1>Janitor configuration</h1>
	<h2>Project settings</h2>
	<ul class="actions">
		<?= $HTML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
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
		You are currently editing the configuration of an <strong>existing project</strong> in <br /><em><?= PROJECT_PATH ?></em>. 
	</p>
	
<?		else: ?>

	<p>
		You are currently setting up a <strong>new project</strong> in <br /><em><?= PROJECT_PATH ?></em>. 
	</p>

<?		endif; ?>


	<h3>Define your core Janitor default values.</h3>
	<?= $model->formStart("/janitor/admin/setup/config/updateConfigSettings", array("class" => "config labelstyle:inject")) ?>

		<fieldset>
			<?= $model->input("site_name", array("value" => $model->get("config", "site_name"))) ?>
			<?= $model->input("site_uid", array("value" => $model->get("config", "site_uid"))) ?>
			<?= $model->input("site_email", array("value" => $model->get("config", "site_email"))) ?>
			<?= $model->input("site_description", array("value" => $model->get("config", "site_description"))) ?>
		</fieldset>

		<? /*fieldset>
			<h3>Janitor core</h3>
			<?= $model->input("site_signup", array("value" => $model->get("config", "site_signup"))) ?>
			<?= $model->input("site_items", array("value" => $model->get("config", "site_items"))) ?>
			<?= $model->input("site_shop", array("value" => $model->get("config", "site_shop"))) ?>
			<?= $model->input("site_subscriptions", array("value" => $model->get("config", "site_subscriptions"))) ?>
			<?= $model->input("site_members", array("value" => $model->get("config", "site_members"))) ?>
		</fieldset */?>

		<fieldset>
			<h3>Environment</h3>
			<?= $model->input("site_deployment", array("value" => $model->get("config", "site_deployment"))) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Update and continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

	<?= $model->formEnd() ?>


<? endif; ?>

</div>
