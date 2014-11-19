<?php
global $model;

$db_check = $model->checkDatabaseSettings();

?>
<div class="scene database i:database">

	<h1>Setup database</h1>

	<?= $model->formStart("/setup/database", array("class" => "labelstyle:inject")) ?>


<? if($model->db_ok): ?>

	<h2>Database status: OK</h2>
	<p>Your database is already configured correctly.</p>

<? 		if($model->db_exists): ?>

	<p>Are you sure you want to use <em class="warning"><?= $model->db_janitor_db ?></em>. It already exists.</p>
	<?= $model->input("force_db", array("type" => "hidden", "value" => $model->db_janitor_db)) ?>

<? 		endif; ?>

	<ul class="actions">
		<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
	</ul>

<? elseif(isset($model->db_connection_error)): ?>

	<h2>Connection error</h2>
	<p class="system_error">Janitor cannot connect to your local database with the information provided.</p>

<? endif;?>


	<h2>Root database information</h2>
	<p>Setting up a new database requires an Admin user with permission to create the Database.</p>
	<fieldset>

		<?= $model->input("db_host", array("value" => $model->db_host)) ?>
		<?= $model->input("db_root_user", array("value" => $model->db_root_user, "required" => ($db_check ? false : true))) ?>
		<?= $model->input("db_root_pass", array("value" => $model->db_root_pass, "required" => ($db_check ? false : true), "min" => 1)) ?>
	</fieldset>

	<h2>New Janitor database</h2>
	<p>Specify new database name and username and password.</p>
	<fieldset>

		<?= $model->input("db_janitor_db", array("value" => $model->db_janitor_db)) ?>
		<?= $model->input("db_janitor_user", array("value" => $model->db_janitor_user)) ?>
		<?= $model->input("db_janitor_pass", array("value" => $model->db_janitor_pass)) ?>
	</fieldset>

	<ul class="actions">
		<?= $model->submit("Update and continue", array("wrapper" => "li.save", "class" => "primary")) ?>
	</ul>

	<?= $model->formEnd() ?>


</div>