<?php
/**
* Set file paths constants
* FRAMEWORK_PATH -> Server path to framework files
* LOCAL_PATH -> Server path to files specific for this site
*
* Creates following constants for use with autoconversion
* PUBLIC_FILE_PATH -> Server path to public part of library
* PRIVATE_FILE_PATH -> Server path to private part of library
* LOG_FILE_PATH -> Server path to log file
*/
if(isset($_SERVER["FRAMEWORK_PATH"])) {
	define("FRAMEWORK_PATH", $_SERVER["FRAMEWORK_PATH"]);
}
if(isset($_SERVER["LOCAL_PATH"])) {
	define("LOCAL_PATH", $_SERVER["LOCAL_PATH"]);
}

if(defined("LOCAL_PATH")) {
	define("PUBLIC_FILE_PATH", LOCAL_PATH."/library/public");
	define("PRIVATE_FILE_PATH", LOCAL_PATH."/library/private");
	define("LOG_FILE_PATH", LOCAL_PATH."/library/log");
}

$path = "";
$path .= (defined("LOCAL_PATH") ? (":".LOCAL_PATH) : "");
$path .= (defined("FRAMEWORK_PATH") ? (":".FRAMEWORK_PATH) : "");

ini_set("include_path", "." . $path);



// extremely simple debugging tool - writes debug message to library/debug file
function writeToFile($message) {
	$fp = fopen(LOCAL_PATH."/library/debug", "a+");
	fwrite($fp, $message." - FROM:" . $_SERVER["REQUEST_URI"] ."\n");
	fclose($fp);
}


// page class + extension
include_once("config/config.php");


//include_once("class/system/query.class.php");
include_once("class/system/queryi.class.php");
include_once("class/system/filesystem.class.php");
include_once("class/system/output.class.php");


include_once("class/system/page.core.class.php");
include_once("class/system/page.class.php");
include_once("class/system/html.class.php");
include_once("class/system/model.class.php");


// Extend with items model - not required for static sites
if(defined("SITE_ITEMS") && SITE_ITEMS) {

	include_once("class/items/item.core.class.php");
	include_once("class/items/item.class.php");
	include_once("class/items/tag.class.php");

	include_once("class/navigation/navigation.class.php");

	include_once("class/users/user.class.php");
	include_once("class/users/simpleuser.class.php");

	//include_once("class/system/performance.class.php");


	// Extend with cart and order
	if(defined("SITE_SHOP") && SITE_SHOP) {

		include_once("class/shop/shop.class.php");

	}

}


?>
