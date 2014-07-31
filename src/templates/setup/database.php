<?php
$model = new Model();

$db_host = isset($_SESSION["db_host"]) ? $_SESSION["db_host"] : "";
$db_root_user = isset($_SESSION["db_root_user"]) ? $_SESSION["db_root_user"] : "";
$db_root_pass = isset($_SESSION["db_root_pass"]) ? $_SESSION["db_root_pass"] : "";
$db_janitor_db = isset($_SESSION["db_janitor_db"]) ? $_SESSION["db_janitor_db"] : "";
$db_janitor_user = isset($_SESSION["db_janitor_user"]) ? $_SESSION["db_janitor_user"] : "";
$db_janitor_pass = isset($_SESSION["db_janitor_pass"]) ? $_SESSION["db_janitor_pass"] : "";

?>
<div class="scene database i:database">
	
	<h1>Setup database</h1>
	<?= $model->formStart("/setup/database", array("class" => "labelstyle:inject")) ?>

	<h2>Root database information</h2>
	<p>Setting up the new database requires an Admin user with permission to create a new Database.</p>
	<fieldset>

		<?= $model->input("db_host", array("type" => "string", "required" => true, "value" => $db_host, "label" => "Database host", "hint_message" => "Database host. Could be localhost or 127.0.0.1.")) ?>
		<?= $model->input("db_root_user", array("type" => "string", "required" => true, "value" => $db_root_user, "label" => "Admin username", "hint_message" => "Name of user with priviledges to create a new database.")) ?>
		<?= $model->input("db_root_pass", array("type" => "password", "required" => true, "value" => $db_root_pass, "label" => "Admin password", "hint_message" => "Password of database admin user.")) ?>
	</fieldset>

	<h2>New Janitor database</h2>
	<p>Specify new database name and username and password.</p>
	<fieldset>

		<?= $model->input("db_janitor_db", array("type" => "string", "required" => true, "value" => $db_janitor_db, "label" => "New Janitor database name", "hint_message" => "Type the name of the database used for this Janitor project")) ?>
		<?= $model->input("db_janitor_user", array("type" => "string", "required" => true, "value" => $db_janitor_user, "label" => "Janitor database username", "hint_message" => "Type the username you want to grant access to the new database")) ?>
		<?= $model->input("db_janitor_pass", array("type" => "password", "required" => true, "value" => $db_janitor_pass, "label" => "Janitor database password", "hint_message" => "Type password for new database user")) ?>
	</fieldset>

	<ul class="actions">
		<?= $model->submit("Continue", array("wrapper" => "li.save", "class" => "primary")) ?>
	</ul>

	<?= $model->formEnd() ?>
</div>