<?php

function isInstalled($command, $valid_responses, $escape = true) {

//	print escapeshellcmd($command);
	if($escape) {
		$cmd_output = shell_exec(escapeshellcmd($command)." 2>&1");
	}
	else {
		$cmd_output = shell_exec($command." 2>&1");
	}
	
//	print $cmd_output;

	foreach($valid_responses as $valid_response) {
		if(preg_match("/".$valid_response."/", $cmd_output)) {
			return true;
		}
	}
	return false;
}

// CHECK FOR READ/WRITE ACCESS
function readWriteTest() {
	$handle = fopen(PROJECT_PATH."/wr.test", "a+");
	if($handle) {
		unlink(PROJECT_PATH."/wr.test");

		return true;
	}
	return false;
}

$apache = isInstalled("apachectl -v", array("Apache\/2\.[23456]{1}"));
$php = isInstalled("php -v", array("PHP 5.[3456]{1}"));
$readwrite = readWriteTest();


//$mysql = isInstalled("mysql5 --version", array("Distrib 5"));
//$ffmpeg = isInstalled("/opt/local/bin/ffmpeg -version", array("ffmpeg version 2.1"));
	
?>
<div class="scene start i:start">
	
	<h1>Janitor setup guide</h1>

	<h2>Software requirements</h2>
	<ul class="requirements">
		<li>Apache: <?= $apache ? "Success" : "Failed" ?></li>
		<li>PHP: <?= $php ? "Success" : "Failed" ?></li>
		<li>Read/Write: <?= $readwrite ? "Success" : "Failed" ?></li>
		<!--li>MySQL: <?= $mysql ? "Success" : "Failed" ?></li>
		<li>FFMpeg: <?= $ffmpeg ? "Success" : "Failed" ?></li-->
	</ul>
<?	if(!$apache || !$php || !$readwrite): // || !$mysql || !$ffmpeg): ?>

	<p>
		Your software does not meet the requirements for running Janitor. Please update your system.
	</p>

<?	else: ?>

	<h2>Install Janitor</h2>

	<ul class="actions">
		<li class="start"><a href="/setup/paths" class="button primary">Start</a></li>
	</ul>
<?	endif; ?>

</div>