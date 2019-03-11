<?php
global $model;

$software_ok = $model->checkSoftware();

?>
<div class="scene software i:software">

	<div class="progress">1/7</div>

	<h1>Janitor setup guide</h1>
	<h2>Software requirements test</h2>
	<ul class="actions">
		<?= $JML->oneButtonForm("Restart setup", "/janitor/admin/setup/reset", array(
			"confirm-value" => "Are you sure you want to start over?",
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/setup"
		)); ?>
	</ul>

	<h3>Required software</h3>
	<ul class="requirements">
		<li<?= !$model->get("software", "apache") ? ' class="error"' : "" ?>>Apache: <?= $model->get("software", "apache") ? "Success" : "Failed" ?></li>
		<li<?= !$model->get("software", "php") ? ' class="error"' : "" ?>>PHP: <?= $model->get("software", "php") ? "Success" : "Failed" ?></li>
		<li<?= !$model->get("software", "mysql") ? ' class="error"' : "" ?>>PHP-MySQL/MariaDB: <?= $model->get("software", "mysql") ? "Success" : "Failed" ?></li>
		<li<?= !$model->get("software", "session") ? ' class="error"' : "" ?>>PHP-Session: <?= $model->get("software", "session") ? "Success" : "Failed" ?></li>
		<li<?= !$model->get("software", "dom") ? ' class="error"' : "" ?>>PHP-DOM: <?= $model->get("software", "dom") ? "Success" : "Failed" ?></li>
		<li<?= !$model->get("software", "simplexml") ? ' class="error"' : "" ?>>PHP-SimpleXML: <?= $model->get("software", "simplexml") ? "Success" : "Failed" ?></li>
		<li<?= !$model->get("software", "mbstring") ? ' class="error"' : "" ?>>PHP-mbstring: <?= $model->get("software", "mbstring") ? "Success" : "Failed" ?></li>
		<li<?= !$model->get("software", "readwrite") ? ' class="error"' : "" ?>>Read/Write: <?= $model->get("software", "readwrite") ? "Success" : "Failed" ?></li>
		<li<?= !$model->get("software", "curl") ? ' class="notice"' : "" ?>>Curl: <?= $model->get("software", "curl") ? "Success" : "Not installed" ?></li>
		<li<?= !$model->get("software", "tar") ? ' class="notice"' : "" ?>>Tar: <?= $model->get("software", "tar") ? "Success" : "Not installed" ?></li>
	</ul>

	<h3>Optional software</h3>
	<ul class="requirements">
		<li<?= !$model->get("software", "imagemagick") ? ' class="notice"' : "" ?>>PHP-ImageMagick: <?= $model->get("software", "imagemagick") ? "Success" : "Not installed" ?></li>
		<li<?= !$model->get("software", "zip") ? ' class="notice"' : "" ?>>Zip: <?= $model->get("software", "zip") ? "Success" : "Not installed" ?></li>
		<li<?= !$model->get("software", "redis") ? ' class="notice"' : "" ?>>Redis: <?= $model->get("software", "redis") ? "Success" : "Not installed" ?></li>
		<li<?= !$model->get("software", "ffmpeg") ? ' class="notice"' : "" ?>>FFMpeg: <?= $model->get("software", "ffmpeg") ? "Success" : "Not installed" ?></li>
		<li<?= !$model->get("software", "wkhtmlto") ? ' class="notice"' : "" ?>>wkhtmlto: <?= $model->get("software", "wkhtmlto") ? "Success" : "Not installed" ?></li>
	</ul>


<?	if(!$model->get("software", "readwrite")): ?>
	<p>You need to allow Apache to modify files in your project folder.<br />Run this command in your terminal to continue:</p>
	<code>sudo chown -R <?= $model->get("system", "apache_user") ?>:<?= $model->get("system", "deploy_user") ?> <?= PROJECT_PATH ?></code>
<?	endif; ?>

<?	if(
		!$model->get("software", "apache") || 
		!$model->get("software", "php") || 
		!$model->get("software", "mysql") ||
		!$model->get("software", "curl") ||
		!$model->get("software", "tar")
	): ?>
	<p>
		Your software does not meet the requirements for running Janitor. Please update your system.
		For more information about installing the required tools on your system, read the 
		<a href="http://janitor.parentnode.dk/getting-started/software-requirements" target="_blank">software requirements</a>.
	</p>
<?	endif; ?>

<?	if(
		!$model->get("software", "ffmpeg") || 
		!$model->get("software", "wkhtmlto") || 
		!$model->get("software", "imagemagick") || 
		!$model->get("software", "redis") || 
		!$model->get("software", "zip")
	): ?>
	<p>
		You should consider installing the optional software components. The playground is more fun with all the toys out.
		Read more about the <a href="http://janitor.parentnode.dk/getting-started/software-requirements" target="_blank">software requirements</a>.
	</p>
<?	endif; ?>

<?	if($model->get("software", "passed")): ?>

	<h2>Install Janitor</h2>
	<p>
		Your system smells like teen spirit. I like it.
	</p>

	<ul class="actions">
		<li class="continue"><a href="/janitor/admin/setup/config" class="button primary">Continue</a></li>
	</ul>

<?	endif; ?>

</div>
