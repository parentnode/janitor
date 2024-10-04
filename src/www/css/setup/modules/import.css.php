<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");
header("Content-type: text/css; charset=UTF-8");


if(file_exists(LOCAL_PATH."/www/janitor/css/setup/modules")) {
	$fs = new FileSystem();
	$files = $fs->files(LOCAL_PATH."/www/janitor/css/setup/modules", ["extensions" => ["css"]]);

	foreach($files as $file) {
		print '@import url("/janitor/css/setup/modules/'.basename($file).'");'."\n";
	}
}

