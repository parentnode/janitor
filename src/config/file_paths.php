<?php

/**
* Set file paths constants
* FRAMEWORK_PATH -> Server path to framework files
* GLOBAL_PATH -> Server path to global files
* REGIONAL_PATH -> Server path to regional files
* LOCAL_PATH -> Server path to files specific for this site
* ADMIN_PATH -> Server path to adminroot
* WWW_PATH -> Server path to webroot
* API_PATH -> Server path to apiroot
*
* PUBLIC_FILE_PATH -> Server path to public part of library
* BACKUP_FILE_PATH -> Server path to private part of library
*/
if(isset($_SERVER["FRAMEWORK_PATH"])) {
	define("FRAMEWORK_PATH", $_SERVER["FRAMEWORK_PATH"]);
}
if(isset($_SERVER["GLOBAL_PATH"])) {
	define("GLOBAL_PATH", $_SERVER["GLOBAL_PATH"]);
}
if(isset($_SERVER["REGIONAL_PATH"])) {
	define("REGIONAL_PATH", $_SERVER["REGIONAL_PATH"]);
}
if(isset($_SERVER["LOCAL_PATH"])) {
	define("LOCAL_PATH", $_SERVER["LOCAL_PATH"]);
}

//define("ADMIN_PATH",LOCAL_PATH."/admin");
//define("WWW_PATH",LOCAL_PATH."/www");
//define("API_PATH",LOCAL_PATH."/api");

if(defined("LOCAL_PATH")) {
	define("PUBLIC_FILE_PATH", LOCAL_PATH."/library/public");
	define("PRIVATE_FILE_PATH", LOCAL_PATH."/library/private");
	define("LOG_FILE_PATH", LOCAL_PATH."/library/log");
}

$path = "";
$path .= (defined("LOCAL_PATH") ? (":".LOCAL_PATH) : "");
$path .= (defined("REGIONAL_PATH") ? (":".REGIONAL_PATH) : "");
$path .= (defined("GLOBAL_PATH") ? (":".GLOBAL_PATH) : "");
$path .= (defined("FRAMEWORK_PATH") ? (":".FRAMEWORK_PATH) : "");

ini_set("include_path", "." . $path);



// 
// // TODO: Check if we really need code below
// 
// $known_paths = array(LOCAL_PATH, REGIONAL_PATH, GLOBAL_PATH, FRAMEWORK_PATH);
// 
// /**
// * Separates framework/path part from file/path part
// *
// * @param String $raw_file File to be processed
// * @param String $file_only return file part only
// * @return array Array two indexes 0 and 1, containing $path and $file
// */
// function removeKnownPaths($raw_file, $file_only=false) {
// 	global $known_paths;
// 
// 	foreach($known_paths as $known_path) {
// 		$path = preg_replace("/\A[A-Z]:/", "", $known_path);
// //		print "path:".$path."<br><br>";
// 		$file = str_replace($path, "", $raw_file);
// 		if($file != $raw_file) {
// //			print "file:".$file."<br>###<br>path:". $path."<br>###<br>raw:". $raw_file."<br><br>";
// 			if($file_only) {
// 				return $file;
// 			}
// 			return array($path, $file);
// 		}
// 	}
// 	$raw_file = realpath($raw_file);
// 	foreach($known_paths as $known_path) {
// 		$path = realpath(preg_replace("/\A[A-Z]:/", "", $known_path));
// 		$file = str_replace($path, "", $raw_file);
// 		if($file != $raw_file) {
// 			if($file_only) {
// 				return $file;
// 			}
// 			return array($path, $file);
// 		}
// 	}
// 
// }
// 
// function matchKnownPaths($file) {
// 	global $known_paths;
// 
// 	foreach($known_paths as $known_path) {
// 		$path = preg_replace("/\A[A-Z]:/", "", $known_path);
// 		if(strpos($file, $path) !== false) {
// 			return true;
// 		}
// 	}
// 	return false;
// }
?>
