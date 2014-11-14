<?php
global $model;

$software_ok = $model->checkSoftware();

?>
<div class="scene start i:start">
	<h1>Janitor setup guide</h1>

	<h2>Software requirement test</h2>
	<ul class="requirements">
		<li>Apache: <?= $model->apache ? "Success" : "Failed" ?></li>
		<li>PHP: <?= $model->php ? "Success" : "Failed" ?></li>
		<li>PHP-MySQL: <?= $model->mysql ? "Success" : "Failed" ?></li>
		<li>PHP-ImageMagick: <?= $model->imagemagick ? "Success" : "Failed" ?></li>
		<li>PHP-Session: <?= $model->session ? "Success" : "Failed" ?></li>
		<li>PHP-SimpleXML: <?= $model->simplexml ? "Success" : "Failed" ?></li>
		<li>PHP-DOM: <?= $model->dom ? "Success" : "Failed" ?></li>
		<li>PHP-mbstring: <?= $model->mbstring ? "Success" : "Failed" ?></li>
		<li>Read/Write: <?= $model->readwrite ? "Success" : "Failed" ?></li>
		<li>FFMpeg: <?= $model->ffmpeg ? "Success" : "Failed" ?></li>
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


<?	if($software_ok): ?>
	<h2>Install Janitor</h2>
	<p>
		Your system has passed the test.
	</p>

	<ul class="actions">
<?		if(SETUP_TYPE == "setup"): ?>
		<li class="start"><a href="/setup/config" class="button primary">Start completely new setup</a></li>
<?		else: ?>
		<li class="start"><a href="/setup/database" class="button primary">Initialize existing project</a></li>
<?		endif; ?>
	</ul>
<?	endif; ?>

</div>