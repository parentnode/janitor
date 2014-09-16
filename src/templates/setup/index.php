<?php

function isInstalled($commands, $valid_responses, $escape = true) {

	// try first possible command
	$command = array_shift($commands);

//	print escapeshellcmd($command)."\n";
	if($escape) {
		$cmd_output = shell_exec(escapeshellcmd($command)." 2>&1");
	}
	else {
		$cmd_output = shell_exec($command." 2>&1");
	}
	
//	print $cmd_output;

	foreach($valid_responses as $valid_response) {
		if(preg_match("/".$valid_response."/", $cmd_output)) {
			return $command;
		}
	}

	// still not valid, try next command
	if(count($commands)) {
		return isInstalled($commands, $valid_responses, $escape);
	}

	return false;
}

// CHECK FOR READ/WRITE ACCESS
function readWriteTest() {
	$handle = @fopen(PROJECT_PATH."/wr.test", "a+");
	if($handle) {
		unlink(PROJECT_PATH."/wr.test");

		return true;
	}
	return false;
}

$apache = isInstalled(array("apachectl -v", "/usr/sbin/apachectl -v"), array("Apache\/2\.[23456]{1}"));
$php = isInstalled(array("php -v"), array("PHP 5.[3456]{1}"));
$readwrite = readWriteTest();

if($apache) {
	$_SESSION["apache command"] = $apache;
}

//$mysql = isInstalled("mysql5 --version", array("Distrib 5"));
$ffmpeg = isInstalled(array("/opt/local/bin/ffmpeg -version", "/usr/local/bin/ffmpeg -version"), array("ffmpeg version 2.1","ffmpeg version 2.2","ffmpeg version 2.3"));
	
?>
<div class="scene start i:start">
	
	<h1>Janitor setup guide</h1>

	<h2>Software requirements</h2>
	<ul class="requirements">
		<li>Apache: <?= $apache ? "Success" : "Failed" ?></li>
		<li>PHP: <?= $php ? "Success" : "Failed" ?></li>
		<li>Read/Write: <?= $readwrite ? "Success" : "Failed" ?></li>
		<!--li>MySQL: <?= $mysql ? "Success" : "Failed" ?></li-->
		<li>FFMpeg: <?= $ffmpeg ? "Success" : "Failed" ?></li>
	</ul>

<?	if(!$readwrite): ?>
	<p>You need to allow Apache R/W access to your project folder.</p>
	<code>$ sudo chmod -R 777 <?= PROJECT_PATH ?></code>
<?	endif; ?>

<?	if(!$apache || !$php): // || !$mysql || !$ffmpeg): ?>
	<p>
		Your software does not meet the requirements for running Janitor. Please update your system.
		For more information about installing the required tools on your system, read the 
		<a href="http://janitor.parentnode.dk/blog/prepare_for_janitor" target="_blank">setup guide</a>.
	</p>
<?	endif; ?>

<?	if($apache && $php && $readwrite): // || !$mysql || !$ffmpeg): ?>
	<h2>Install Janitor</h2>
	<ul class="actions">
<?		if(SETUP_TYPE == "setup"): ?>
		<li class="start"><a href="/setup/config" class="button primary">Start completely new setup</a></li>
<?		else: ?>
		<li class="start"><a href="/setup/database" class="button primary">Initialize existing project</a></li>
<?		endif; ?>
	</ul>
<?	endif; ?>

</div>