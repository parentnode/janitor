<?
global $model;

$all_check = $model->checkAllSettings();
	
?>
<div class="scene finish i:finish">
	
<? if(!$all_check): ?>

	<h1>Can't finish until you are done</h1>
	<p>You need to provide more information.</p>

<? else: ?>

	<h1>Janitor is almost ready!</h1>

	<h2>The required information has been validated</h2>
	<p>Click install to finish the installation.</p>

	<ul class="actions">
		<?= $model->link("Install", "/setup/finish", array("wrapper" => "li.install", "class" => "button primary")) ?>
	</ul>


	<div class="installing">
		<h2>Installing</h2>
		<ul class="tasks"></ul>
	</div>

	<div class="final_touches">

		<h2>Final touches</h2>
		<p>
			To finish setup you need to set file permissions on your project and restart Apache.
		</p>

		<h3>Production and development projects</h3>
		<p>Copy this into your terminal to set file permissions.</p>
		<code>sudo chown -R <?= $model->deploy_user ?>:<?= $model->apache_user ?> <?= $model->project_path ?> &&
sudo chmod -R 750 <?= $model->project_path ?> &&

sudo chown -R <?= $model->deploy_user ?>:<?= $model->apache_user ?> <?= $model->project_path ?>/src/library &&
sudo chmod -R 770 <?= $model->project_path ?>/src/library</code>

		<h3>Development project with JS+CSS merging</h3>
		<p>
			If you are setting up a development environment and need to merge JS+CSS you also need to
			set permissions for JS+CSS folders.
		</p>

		<code>sudo chown -R <?= $model->deploy_user ?>:<?= $model->apache_user ?> <?= $model->project_path ?>/src/www/js &&
sudo chmod -R 770 <?= $model->project_path ?>/src/www/js &&

sudo chown -R <?= $model->deploy_user ?>:<?= $model->apache_user ?> <?= $model->project_path ?>/src/www/janitor/js &&
sudo chmod -R 770 <?= $model->project_path ?>/src/www/janitor/js &&

sudo chown -R <?= $model->deploy_user ?>:<?= $model->apache_user ?> <?= $model->project_path ?>/src/www/css &&
sudo chmod -R 770 <?= $model->project_path ?>/src/www/css &&

sudo chown -R <?= $model->deploy_user ?>:<?= $model->apache_user ?> <?= $model->project_path ?>/src/www/janitor/css &&
sudo chmod -R 770 <?= $model->project_path ?>/src/www/janitor/css</code>

		<h3>Restart Apache</h3>
		<p>Finally, restart your apache by running the following command in Terminal.</p>
		<code>sudo <?= $model->apache_path ?> restart</code>

		<h2>Relaunch your Janitor project</h2>
		<p>When you are done you can click the bottom below to relaunch your Janitor project.</p>

		<ul class="actions">
			<li class="finalize"><a href="#" class="button primary">Finalize setup</a></li>
		</ul>

		<ul class="building"></ul>

	</div>

</div>
<? endif; ?>