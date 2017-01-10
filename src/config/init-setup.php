<?php
// Project is already initialized - Setting up existing project on new server
if($_SERVER["LOCAL_PATH"] && $_SERVER["FRAMEWORK_PATH"] && file_exists($_SERVER["LOCAL_PATH"]."/config/config.php")) {

//	print "existing project";


	define("SETUP_TYPE", "setup");
	define("PROJECT_PATH", preg_replace("/\/(theme|src)/", "", $_SERVER["LOCAL_PATH"]));

	include_once("init.php");

}
else if($_SERVER["LOCAL_PATH"] && $_SERVER["FRAMEWORK_PATH"]) {

//	print "new project";


	header("Content-type: text/html; charset=UTF-8");
	error_reporting(E_ALL);

	define("SETUP_TYPE", "init");
	define("SITE_INSTALL", true);
	define("PROJECT_PATH", preg_replace("/\/(theme|src)/", "", $_SERVER["LOCAL_PATH"]));
	define("SITE_URL", (isset($_SERVER["HTTPS"]) ? "https" : "http")."://".$_SERVER["SERVER_NAME"]);


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



	// page class + extension
	include_once("classes/system/queryi.class.php");
	include_once("classes/system/filesystem.class.php");
	include_once("classes/system/output.class.php");

	include_once("classes/system/page.core.class.php");
	include_once("classes/system/page.class.php");

	include_once("classes/system/html.core.class.php");
	include_once("classes/system/html.class.php");

	include_once("classes/system/model.class.php");



	include_once("classes/system/html.janitor.class.php");


	include_once("classes/items/itemtype.core.class.php");
	include_once("classes/items/itemtype.class.php");

	include_once("classes/items/tag.class.php");

	include_once("classes/system/navigation.class.php");

	include_once("classes/users/user.core.class.php");
	include_once("classes/users/user.class.php");

	include_once("classes/shop/shop.core.class.php");
	include_once("classes/shop/shop.class.php");

}
// Invalid conditions for setup
else {
	print "Invalid conditions for setup.";
}


?>


<?php
// //session_start();
//
// define("SITE_INSTALL", true);
// define("SITE_URL", (isset($_SERVER["HTTPS"]) ? "https" : "http")."://".$_SERVER["SERVER_NAME"]);
//
//
// // is FRAMEWORK_PATH and LOCAL_PATH already defined?
// if(isset($_SERVER["FRAMEWORK_PATH"]) && isset($_SERVER["LOCAL_PATH"])) {
//
// 	define("FRAMEWORK_PATH", $_SERVER["FRAMEWORK_PATH"]);
// 	define("LOCAL_PATH", $_SERVER["LOCAL_PATH"]);
// 	define("PROJECT_PATH", realpath(LOCAL_PATH."/.."));
// 	define("LOG_FILE_PATH", LOCAL_PATH."/library/log");
//
// 	// setup type - initialization of existing project
// 	define("SETUP_TYPE", "init");
// }
// else {
//
// 	// SETUP VARIABLE FOR LESS USER INPUT
// 	//define("PROJECT_PATH", realpath($_SERVER["DOCUMENT_ROOT"]."/../../../.."));
// 	define("PROJECT_PATH", preg_replace("/\/submodules\/janitor\/src/", "", $_SERVER["DOCUMENT_ROOT"]));
//
// 	// DEFINE FRAMEWORK_PATH
// 	// do it first because the includes need it
// 	define("FRAMEWORK_PATH", realpath($_SERVER["DOCUMENT_ROOT"]));
//
// 	// wait with defining LOCAL_PATH - user will specify it through the setup process
//
// 	// setup type - initialization of existing project
// 	define("SETUP_TYPE", "setup");
// }
//
// // Define include paths for PHP
// $path_colon = DIRECTORY_SEPARATOR == '/' ? ':' : ';';
// $path = "";
// $path .= (defined("LOCAL_PATH") ? ($path_colon.LOCAL_PATH) : "");
// $path .= (defined("FRAMEWORK_PATH") ? ($path_colon.FRAMEWORK_PATH) : "");
//
// ini_set("include_path", "." . $path);
//
//
//
// // include system files
// include_once("classes/system/queryi.class.php");
// include_once("classes/system/filesystem.class.php");
//
// include_once("classes/system/page.core.class.php");
// include_once("classes/system/page.class.php");
//
// include_once("classes/system/html.core.class.php");
// include_once("classes/system/html.class.php");
//
// include_once("classes/system/model.class.php");
// include_once("classes/items/itemtype.core.class.php");
// include_once("classes/items/itemtype.class.php");
//
// include_once("classes/system/output.class.php");
//
// include_once("classes/system/setup.class.php");
//
//
// // DEFINE LOCAL_PATH
// // look for local_path in session to avoid guessing
// if(!defined("LOCAL_PATH")) {
// 	if(isset($_SESSION["project_path"]) && $_SESSION["project_path"]) {
// 		define("LOCAL_PATH", $_SESSION["project_path"]."/src");
// 		define("LOG_FILE_PATH", LOCAL_PATH."/library/log");
// 	}
// 	else {
// 		define("LOCAL_PATH", FRAMEWORK_PATH);
// 		define("LOG_FILE_PATH", LOCAL_PATH."/library/log");
// 	}
// }
//
// // update include_path with LOCAL_PATH
// $path_colon = DIRECTORY_SEPARATOR == '/' ? ':' : ';';
// $path = "";
// $path .= (defined("LOCAL_PATH") ? ($path_colon.LOCAL_PATH) : "");
// $path .= (defined("FRAMEWORK_PATH") ? ($path_colon.FRAMEWORK_PATH) : "");
//
// ini_set("include_path", "." . $path);

?>