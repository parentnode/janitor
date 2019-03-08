<?php
global $model;

$db_check = $model->checkDatabaseSettings();

?>
<div class="scene database i:database">

	<div class="progress">3/7</div>

	<h1>Janitor configuration</h1>
	<h2>Database settings</h2>
	<ul class="actions">
		<?= $JML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
			"confirm-value" => "Are you sure you want to start over?",
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/setup"
		)); ?>
	</ul>


<?	if($model->get("database", "passed") && SETUP_TYPE == "existing" && (!defined("SITE_INSTALL") || !SITE_INSTALL)): ?>

	<h3>Database connected</h3>
	<p>Your database is happy and doesn't want it to change.</p>

	<ul class="actions">
		<li class="continue"><a href="/janitor/admin/setup/account" class="button primary">Continue</a></li>
	</ul>


<?	else: ?>


<?		if($model->get("database", "passed")): ?>

<?			if($model->get("database", "exists")): ?>

	<h3>Database status: EXISTS</h3>
	<p>
		Are you sure you want to use <em class="system_warning"><?= $model->get("database", "db_janitor_db") ?></em>. 
		It already exists.
	</p>

	<?= $model->formStart("/janitor/admin/setup/database/updateDatabaseSettings", array("class" => "force labelstyle:inject")) ?>

		<?= $model->input("force_db", array("type" => "hidden", "value" => $model->get("database", "db_janitor_db"))) ?>

		<ul class="actions">
			<?= $model->submit("Confirm", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>

	<?= $model->formEnd() ?>


<?			else: ?>


	<h3>Database status: OK</h3>
	<p>Your database is already configured correctly.</p>

	<ul class="actions">
		<li class="continue"><a href="/janitor/admin/setup/account" class="button primary">Continue</a></li>
	</ul>


<?			endif; ?>

<?		endif; ?>


	<h3>New Janitor database</h3>
	<p>Specify new database <em>name</em>, <em>username</em> and <em>password</em>.</p>

	<?= $model->formStart("/janitor/admin/setup/database/updateDatabaseSettings", array("class" => "database labelstyle:inject")) ?>


<? if($model->get("database", "wrong_user_password")): ?>

		<h4>Connection error</h4>
		<p class="system_error"><em><?= $model->get("database", "db_janitor_user") ?></em> already exists – but the password doesn't match.</p>

<? elseif($model->get("database", "user_error")): ?>

		<h4>Connection error</h4>
		<p class="system_error">Janitor could not log in, using the provided information.</p>

<? endif;?>


		<fieldset>
			<?= $model->input("db_janitor_db", array("value" => $model->get("database", "db_janitor_db"))) ?>
			<?= $model->input("db_janitor_user", array("value" => $model->get("database", "db_janitor_user"))) ?>
			<?= $model->input("db_janitor_pass", array("value" => $model->get("database", "db_janitor_pass"))) ?>
		</fieldset>

		<p>
			Feel free to use a random password - the information will be saved in connect_db.php, so you don't need to remember it or write it down.
			It is strongly recommended that you <strong>don't</strong> use your root account for project connections.
		</p>

		<p>
			Setting up a new database requires a database user with permission to create the project database. If your
			project user does not exist or has insufficient permissions, you can enter your root login info below. Then
			your project user will automatically be created/updated with the necessary permissions.
		</p>


		<h3>Root database information</h3>

<?		if($model->get("database", "admin_error") && !$model->get("database", "wrong_user_password")): ?>

		<h4>Connection error</h4>
		<p class="system_error">Janitor cannot connect to your admin account with the information provided.</p>

<?		endif;?>


		<fieldset>
			<?= $model->input("db_host", array("value" => $model->get("database", "db_host"))) ?>
			<?= $model->input("db_root_user", array("value" => $model->get("database", "db_root_user"), "required" => ($db_check ? false : true))) ?>
			<?= $model->input("db_root_pass", array("value" => $model->get("database", "db_root_pass"))) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->submit("Update and continue", array("wrapper" => "li.save", "class" => "primary")) ?>
		</ul>


		<p class="note">Don't let the browser save the passwords used in this page. These passwords are associated with the database connection and not the website.</p>

	<?= $model->formEnd() ?>


<? endif;?>


</div>