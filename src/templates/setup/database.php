<?php
$model = new Model();

// attempt to read existing connect_db.php

$site_db = "";
$host = "";
$username = "";
$password = "";
$db_ok = false;


if(file_exists(LOCAL_PATH."/config/connect_db.php")) {

	$connection_info = file_get_contents(LOCAL_PATH."/config/connect_db.php");

	preg_match("/\"SITE_DB\", \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
	if($matches) {
		$site_db = $matches[1];
	}

	preg_match("/\"host\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
	if($matches) {
		$host = $matches[1];
	}

	preg_match("/\"username\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
	if($matches) {
		$username = $matches[1];
	}

	preg_match("/\"password\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
	if($matches) {
		$password = $matches[1];
	}

	$query = new Query();
	$sql = "CREATE TABLE `$site_db`.`janitor_db_test` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//	print $sql."<br>";
	if($query->sql($sql)) {

		$query->sql("DROP TABLE `$site_db`.`janitor_db_test`");
		$db_ok = true;
		$_SESSION["db_ok"] = true;

	}
}


$db_host = isset($_SESSION["db_host"]) ? $_SESSION["db_host"] : $host;
$db_root_user = isset($_SESSION["db_root_user"]) ? $_SESSION["db_root_user"] : "";
$db_root_pass = isset($_SESSION["db_root_pass"]) ? $_SESSION["db_root_pass"] : "";
$db_janitor_db = isset($_SESSION["db_janitor_db"]) ? $_SESSION["db_janitor_db"] : $site_db;
$db_janitor_user = isset($_SESSION["db_janitor_user"]) ? $_SESSION["db_janitor_user"] : $username;
$db_janitor_pass = isset($_SESSION["db_janitor_pass"]) ? $_SESSION["db_janitor_pass"] : $password;

?>
<div class="scene database i:database">

	<h1>Setup database</h1>

<? if(!$db_ok): ?>
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
<? else: ?>

	<h2>Database status: OK</h2>
	<p>Your database is already configured correctly.</p>
	<ul class="actions">
		<?= $model->link("Continue", "/setup/mail", array("wrapper" => "li.save", "class" => "button primary")) ?>
	</ul>

<? endif;?>
</div>