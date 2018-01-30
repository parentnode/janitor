<?
global $model;

$all_check = $model->checkAllSettings();

?>
<div class="scene finish i:finish">
	
<? if(!$all_check): ?>

	<h1>Can't finish until you are done</h1>
	<h2>- and you're not done.</h2>
	<ul class="actions">
		<?= $JML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
			"confirm-value" => "Are you sure you want to start over?",
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/setup"
		)); ?>
	</ul>

	<p>You need to provide more information to finish the installation process.</p>

	<ul class="actions">
		<?= $model->link("Continue", "/janitor/admin/setup/check", array("wrapper" => "li.check", "class" => "button primary")) ?>
	</ul>

<? else: ?>

	<h1>Janitor is almost ready!</h1>
	<h2 class="subheader">The required information has been validated.</h2>
	<ul class="actions reset">
		<?= $JML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
			"confirm-value" => "Are you sure you want to start over?",
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/setup"
		)); ?>
	</ul>

	<div class="ready">
		<p>Click <em>install</em> to finish the installation.</p>

		<ul class="actions">
			<?= $JML->oneButtonForm("Install", "/janitor/admin/setup/finish/finishInstallation", array(
				"static" => true,
				"wrapper" => "li.install",
				"class" => "primary"
			)); ?>
		</ul>
	</div>

	<div class="installing">
		<h2>Installing ...</h2>
		<ul class="tasks"></ul>
	</div>

	<div class="final_touches">

		<h2>Final touches</h2>
<?	if(SETUP_TYPE == "existing"): ?>
		<p>
			If you are deploying a site into production you need to set <span class="system_warning">file permissions</span>
			on your project.
		</p>
<?	else: ?>
		<p>
			If you are deploying a site into production you need to set <span class="system_warning">file permissions</span>
			on your project and <span class="system_warning">restart</span> Apache.
		</p>
<?	endif; ?>

		<h3>Production projects</h3>
		<p>
			Copy this into your terminal to set file permissions to production settings. You want to make
			sure this is done to protect your files from unintended manipulation.
		</p>
		<code>sudo chown -R root:<?= $model->deploy_user ?> <?= $model->project_path ?>

sudo chmod -R 755 <?= $model->project_path ?>


sudo chown -R <?= $model->apache_user ?>:<?= $model->deploy_user ?> <?= LOCAL_PATH ?>/library
sudo chmod -R 770 <?= LOCAL_PATH ?>/library</code>


<?	if(SETUP_TYPE == "existing"): ?>

		<h2>Relaunch your Janitor project</h2>
		<p>When you are done you can click the bottom below to relaunch your Janitor project.</p>

		<ul class="actions">
			<li class="finalize simple"><a href="/" class="button primary">Relaunch</a></li>
		</ul>

<?	else: ?>

		<h3>Restart Apache</h3>
		<p>Finally, restart your Apache:</p>

		<? if($model->apachectls): ?>

			<? foreach($model->apachectls as $apachectl): ?>
				<code><?= $apachectl ?> -k graceful</code>
			<? endforeach; ?>

			<? if(count($model->apachectls) > 1): ?>
			<p class="note">
				Janitor is trying to guess where your apachectl is located, but it found more than one on your system. 
				Please use the command which fits with your installation.
			</p>
			<? endif;?>

		<? else: ?>
			<code>sudo apachectl -k graceful</code>
			<p class="note">Janitor is trying to guess where your apachectl is located, but could not find it.</p>
		<? endif; ?>



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