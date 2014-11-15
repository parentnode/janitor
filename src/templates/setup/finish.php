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
<?	if(SETUP_TYPE == "init"): ?>
		<p>
			If you are deploying a site into production you need to set <span class="warning">file permissions</span>
			on your project.
		</p>
<?	else: ?>
		<p>
			If you are deploying a site into production you need to set <span class="warning">file permissions</span>
			on your project and <span class="warning">restart</span> Apache.
		</p>
<?	endif; ?>

		<h3>Production projects</h3>
		<p>
			Copy this into your terminal to set file permissions to production settings. You want to make
			sure this is done to protect your files from unintended manipulation.
		</p>
		<code>sudo chown -R root:<?= $model->deploy_user ?> <?= $model->project_path ?> &&
sudo chmod -R 755 <?= $model->project_path ?> &&

sudo chown -R <?= $model->apache_user ?>:<?= $model->deploy_user ?> <?= $model->project_path ?>/src/library &&
sudo chmod -R 770 <?= $model->project_path ?>/src/library</code>


<?	if(SETUP_TYPE == "init"): ?>

		<h2>Relaunch your Janitor project</h2>
		<p>When you are done you can click the bottom below to relaunch your Janitor project.</p>

		<ul class="actions">
			<li class="finalize simple"><a href="/" class="button primary">Relaunch</a></li>
		</ul>

<?	else: ?>

		<h3>Restart Apache</h3>
		<p>Finally, restart your Apache:</p>
		<code>service apache2 restart</code>
		<p>or</p>
		<code>sudo apachectl restart</code>

		<h2>Relaunch your Janitor project</h2>
		<p>When you are done you can click the bottom below to relaunch your Janitor project.</p>

		<ul class="actions">
			<li class="finalize"><a href="/" class="button primary">Finalize setup</a></li>
		</ul>

		<ul class="building"></ul>

<?	endif; ?>

	</div>

</div>
<? endif; ?>