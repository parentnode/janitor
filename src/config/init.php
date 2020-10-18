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


// Define include paths for PHP
$path_colon = DIRECTORY_SEPARATOR == '/' ? ':' : ';';
$path = "";
$path .= (defined("LOCAL_PATH") ? ($path_colon.LOCAL_PATH) : "");
$path .= (defined("FRAMEWORK_PATH") ? ($path_colon.FRAMEWORK_PATH) : "");

ini_set("include_path", "." . $path);



// extremely simple debugging tool - writes debug message to library/debug file
function writeToFile($message) {
	$fp = fopen(LOCAL_PATH."/library/debug", "a+");
	fwrite($fp, $message." - FROM:" . $_SERVER["REQUEST_URI"] ."\n");
	fclose($fp);
}


// base configuration
include_once("config/config.php");

// page class + extension
include_once("classes/system/queryi.class.php");
include_once("classes/helpers/filesystem.class.php");
include_once("classes/system/output.class.php");

include_once("classes/system/page.core.class.php");
include_once("classes/system/page.class.php");

include_once("classes/system/html.core.class.php");
include_once("classes/system/html.class.php");
include_once("classes/system/model.class.php");

// initialize new page object
$page = new Page();


// Extend with items model - not required for static sites
if(defined("SITE_ITEMS") && SITE_ITEMS) {

	// SITE_ITEMS needs SITE_DB
	if(!defined("SITE_DB") || !$mysqli_global) {
		print "Your site is not configured yet!";
		exit();
	}

	include_once("classes/system/html.janitor.core.class.php");
	include_once("classes/system/html.janitor.class.php");


	include_once("classes/items/items.core.class.php");
	include_once("classes/items/items.class.php");
	include_once("classes/items/itemtype.core.class.php");
	include_once("classes/items/itemtype.class.php");

	include_once("classes/items/tag.class.php");

	include_once("classes/system/navigation.class.php");

	include_once("classes/users/user.core.class.php");
	include_once("classes/users/user.class.php");

	include_once("classes/users/member.core.class.php");
	include_once("classes/users/member.class.php");

	// now only included for user controller
	// include_once("classes/users/superuser.class.php");

	//include_once("classes/system/performance.class.php");


	// Extend with cart and order
	if(defined("SITE_SHOP") && SITE_SHOP) {

		include_once("classes/shop/shop.core.class.php");
		include_once("classes/shop/shop.class.php");

		include_once("classes/shop/subscription.core.class.php");
		include_once("classes/shop/subscription.class.php");

	}

}


?>
