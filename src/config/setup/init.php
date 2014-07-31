<?php
//session_start();

define("SITE_INSTALL", true);


// SETUP VARIABLE FOR LESS USER INPUT
//define("PROJECT_PATH", realpath($_SERVER["DOCUMENT_ROOT"]."/../../../.."));
define("PROJECT_PATH", preg_replace("/\/submodules\/janitor\/src\/www/", "", $_SERVER["DOCUMENT_ROOT"]));


// DEFINE FRAMEWORK_PATH
// do it first because the includes need it
define("FRAMEWORK_PATH", realpath($_SERVER["DOCUMENT_ROOT"]."/.."));
$path = "";
$path .= (defined("FRAMEWORK_PATH") ? (":".FRAMEWORK_PATH) : "");
ini_set("include_path", "." . $path);


// include system files
include_once("class/system/queryi.class.php");
include_once("class/system/filesystem.class.php");

include_once("class/system/page.core.class.php");
include_once("class/system/page.class.php");
include_once("class/system/html.class.php");
include_once("class/system/model.class.php");


// DEFINE LOCAL_PATH
// look for local_path in session to avoid guessing
if(isset($_SESSION["project_path"]) && $_SESSION["project_path"]) {
	define("LOCAL_PATH", $_SESSION["project_path"]."/src");
}
else {
	define("LOCAL_PATH", FRAMEWORK_PATH);
}

// update include_path with LOCAL_PATH 
$path = "";
$path .= (defined("LOCAL_PATH") ? (":".LOCAL_PATH) : "");
$path .= (defined("FRAMEWORK_PATH") ? (":".FRAMEWORK_PATH) : "");

ini_set("include_path", "." . $path);

?>
