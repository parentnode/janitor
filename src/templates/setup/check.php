<?php
global $model;

$software_ok = $model->checkSoftware();

?>
<div class="scene check i:check">
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
		<li<?= !$model->apache ? ' class="error"' : "" ?>>Apache: <?= $model->apache ? "Success" : "Failed" ?></li>
		<li<?= !$model->php ? ' class="error"' : "" ?>>PHP: <?= $model->php ? "Success" : "Failed" ?></li>
		<li<?= !$model->mysql ? ' class="error"' : "" ?>>PHP-MySQL/MariaDB: <?= $model->mysql ? "Success" : "Failed" ?></li>
		<li<?= !$model->session ? ' class="error"' : "" ?>>PHP-Session: <?= $model->session ? "Success" : "Failed" ?></li>
		<li<?= !$model->dom ? ' class="error"' : "" ?>>PHP-DOM: <?= $model->dom ? "Success" : "Failed" ?></li>
		<li<?= !$model->simplexml ? ' class="error"' : "" ?>>PHP-SimpleXML: <?= $model->simplexml ? "Success" : "Failed" ?></li>
		<li<?= !$model->mbstring ? ' class="error"' : "" ?>>PHP-mbstring: <?= $model->mbstring ? "Success" : "Failed" ?></li>
		<li<?= !$model->imagemagick ? ' class="error"' : "" ?>>PHP-ImageMagick: <?= $model->imagemagick ? "Success" : "Failed" ?></li>
		<li<?= !$model->readwrite ? ' class="error"' : "" ?>>Read/Write: <?= $model->readwrite ? "Success" : "Failed" ?></li>
	</ul>

	<h3>Optional software</h3>
	<ul class="requirements">
		<li<?= !$model->zip ? ' class="notice"' : "" ?>>Zip: <?= $model->zip ? "Success" : "Failed" ?></li>
		<li<?= !$model->memcached ? ' class="notice"' : "" ?>>Memcached: <?= $model->memcached ? "Success" : "Failed" ?></li>
		<li<?= !$model->curl ? ' class="notice"' : "" ?>>Curl: <?= $model->curl ? "Success" : "Failed" ?></li>
		<li<?= !$model->ffmpeg ? ' class="notice"' : "" ?>>FFMpeg: <?= $model->ffmpeg ? "Success" : "Failed" ?></li>
		<li<?= !$model->wkhtmlto ? ' class="notice"' : "" ?>>wkhtmlto: <?= $model->wkhtmlto ? "Success" : "Failed" ?></li>
	</ul>


<?	if(!$model->readwrite): ?>
	<p>You need to allow Apache R/W access to your project folder.</p>
	<code>$ sudo chmod -R 777 <?= PROJECT_PATH ?></code>
<?	endif; ?>


<?	if(!$model->apache || !$model->php || !$model->mysql || !$model->ffmpeg): ?>
	<p>
		Your software does not meet the requirements for running Janitor. Please update your system.
		For more information about installing the required tools on your system, read the 
		<a href="http://janitor.parentnode.dk/getting-started/software-requirements" target="_blank">software requirements</a>.
	</p>
<?	endif; ?>


<?	if(!$model->ffmpeg || !$model->wkhtmlto || !$model->memcached || !$model->zip || !$model->curl): ?>
	<p>
		You should consider installing the optional software components. The playgound is more fun with all the toys out.
		Read more about the <a href="http://janitor.parentnode.dk/getting-started/software-requirements" target="_blank">software requirements</a>.
	</p>
<?	endif; ?>


<?	if($software_ok): ?>
	<h2>Install Janitor</h2>
	<p>
		Your system smells like teen spirit. I like it.
	</p>

	<ul class="actions">
		<li class="start"><a href="/janitor/admin/setup/config" class="button primary">Continue</a></li>
	</ul>
<?	endif; ?>

</div>