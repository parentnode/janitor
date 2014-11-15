<?php
$access_item = false;
if(isset($read_access) && $read_access) {
	return;
}


// determine request type

// JS
if(preg_match("/^\/js|\.js($|\?)/", $_SERVER["REQUEST_URI"])) {

	header("Content-type: text/javascript; charset=UTF-8");
	exit();

}
// CSS
else if(preg_match("/^\/css|\.css($|\?)/", $_SERVER["REQUEST_URI"])) {

	header("Content-type: text/css; charset=UTF-8");
	exit();

}
// MEDIA
else if(preg_match("/^\/(img|images|media|videos|audios)|\.(jpg|png|gif|ogv|mp4|3gp|mp3|ogg)($|\?)/", $_SERVER["REQUEST_URI"])) {

	exit();

}
// HTML RESPONSE
else {

	include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");

	$page->bodyClass("error");
	$page->pageTitle(SITE_NAME." - 404");


	$page->page(array(
		"templates" => "pages/404.php"
	));

}

?>