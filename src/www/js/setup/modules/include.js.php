<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");
header("Content-type: text/javascript; charset=UTF-8");


if(file_exists(LOCAL_PATH."/www/janitor/js/setup/modules")) {
	$fs = new FileSystem();
	$files = $fs->files(LOCAL_PATH."/www/janitor/js/setup/modules", ["extensions" => ["js"]]);

	foreach($files as $file) {
		print 'document.write(\'<script type="text/javascript" src="/janitor/js/setup/modules/'.basename($file).'"></script>\');'."\n";
	}
}

