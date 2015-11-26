<?php
/**
* This file contains filesystem-functions
*/
class FileSystem {

	/**
	* Iterate recursively through folder
	*
	* @param string $start_path Start path for folder iteration
	* @param string $iteration_path Current progress of folder iteration
	* @param Array $exclude Exclude these folders in iteration
	* @param Array $extensions Only allow these file extensions. Optional.
	* @param Array $files The matching files. Optional. Should be skipped when starting a folder iteration
	* @return Array Array of matching files
	*
	* @uses FileSystem::valid
	*/
//	function folderIterator($start_path, $iteration_path="", $exclude=array(), $extensions=false, $files=false) {
	function files($path, $_options = false) {
//		print $path."<br>";

		// options only used to pass on to valid()

		$files = array();

		$handle = opendir("$path");
		while(($file = readdir($handle)) !== false) {

//			print $file . "<br>";
			$current_path = "$path/$file";

			if($this->valid($current_path, $_options)) {

				// file is a directory - iterate
				if(is_dir("$current_path")) {
					$files = array_merge($files, $this->files($current_path, $_options));
				}
				// index file
				else {
					$files[] = "$current_path";
				}
			}
		}
		return $files;
	}

	/**
	* Is this folder/file valid
	*
	* @param String $file Absolute file path
	* @param String $deny_folders Comma-separated list of denied folders
	* @param String $allow_folders Comma-separated list of allowed extensions
	* @param String $deny_extensions Comma-separated list of denied extensions
	* @param String $allow_extensions Comma-separated list of allowed extensions
	* @return boolean Is the folder/file valid or not
	*/
//	function validFolder($file, $exclude_folders = array(), $allowed_extensions=false) {
	function valid($file, $_options = false) {

		$include_tempfiles = false;
		$deny_folders = false;
		$allow_folders = false;
		$deny_extensions = false;
		$allow_extensions = false;

	
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "include_tempfiles" 	: $include_tempfiles = $_value; 				break;
					case "deny_folders" 		: $deny_folders = explode(",", $_value);		break;
					case "allow_folders" 		: $allow_folders = explode(",", $_value);		break;
					case "deny_extensions"		: $deny_extensions = explode(",", $_value);		break;
					case "allow_extensions" 	: $allow_extensions = explode(",", $_value);	break;
				}
			}
		}

		$file_name = basename($file);

		// if($file == "/srv/sites/parentnode/janitor_parentnode_dk/src/library/public/filesystem-test/level2/level23/") {
		// 	print_r($_options);
		// 	print "filename:" . $file_name;
		// 	print "is_file:".is_file($file)."\n";
		// 	print "is_dir:".is_dir($file)."\n";
		// 	print "deny_folders:" . (!$deny_folders || array_search($file_name, $deny_folders) === false)."\n";
		// 	print "allow_folders (".is_array($allow_folders)."):" . (!$allow_folders || array_search($file_name, $allow_folders) !== false)."\n";
		// }

		if(
			// ignore files starting with . and _ (conf, temp and directory links)
			$file_name !== "." && 
			$file_name !== ".." &&
			($include_tempfiles || !preg_match("/^[\.\_]+/", $file_name)) &&

			(
				!is_dir($file) || 
				(	
					!$deny_folders || count(array_intersect(explode("/", $file), $deny_folders)) === 0
				) &&
				(
					!$allow_folders || count(array_intersect(explode("/", $file), $allow_folders)) > 0
				)
			) &&
			(
				!is_file($file) || 
				(
					!$deny_extensions || array_search(substr($file_name, -3), $deny_extensions) === false
				) &&
				(
					!$allow_extensions || array_search(substr($file_name, -3), $allow_extensions) !== false
				) &&
				(
					!$deny_folders || count(array_intersect(explode("/", $file), $deny_folders)) === 0
				) &&
				(
					!$allow_folders || count(array_intersect(explode("/", $file), $allow_folders)) > 0
				)
			)

		) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* Recursively delete folder and all content
	*
	* @param string $path Path to start deletion
	* @return string
	*/
	function removeDirRecursively($path) {
		if(basename($path) != "." && basename($path) != ".." && file_exists($path)) {
			$dir = opendir($path);
			while($entry = readdir($dir)) {
				if(is_file("$path/$entry")) {
					unlink("$path/$entry");
				}
				else if(is_dir("$path/$entry") && $entry != '.' && $entry != '..') {
					$this->removeDirRecursively("$path/$entry");
				}
			}
			closedir($dir);
			return rmdir($path);
		}
		else {
			return true;
		}
	}

	/**
	* Recursively delete folder and subfolders if empty
	*
	* @param string $path Path to start deletion
	* @param array $_options Optional options to pass to valid and option to delete temp/system files 
	* @return string
	*/
	function removeEmptyDirRecursively($path, $_options = false) {

//		print "PATH:$path\n";

		$delete_tempfiles = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "delete_tempfiles" : $delete_tempfiles = $_value; break;
				}
			}
		}

		// include tempfiles in options if they should be deleted
		if($delete_tempfiles) {
			$_options["include_tempfiles"] = true;
		}

		$is_empty = true;
//			print "Empty is true<br>";

		// read directory contents
		if(file_exists($path)) {
			$dir = opendir($path);
			while($entry = readdir($dir)) {

	//			print "entry:" . $entry."\n";

				// is entry a file
				if(is_file("$path/$entry")) {

	//				print "is file ($path/$entry)"."\n";

					// tempfile - allow deletion
					if(!($delete_tempfiles && preg_match("/^[\.\_]+/", $entry))) { 
	//					print "tempfile?\n";
						$is_empty = false;
					}
					else if($this->valid("$path/$entry", $_options)) {
	//					print "delete tempfile: $path/$entry\n";

						unlink("$path/$entry");
					}

				}

				// if entry a directory
				else if(is_dir("$path/$entry")) {

	//				print "is directory ($path/$entry)"."\n";

					// is real directory (not . or ..)
	 				if($this->valid("$path/$entry")) {

	//					print "look for subs to delete: $entry\n";
						if(!$this->removeEmptyDirRecursively("$path/$entry", $_options)) {
							$is_empty = false;
						}

					}

				}

			}
			closedir($dir);

			if($is_empty) {

	//			print "check validity before deleting ($path/$entry)\n";

				if($this->valid("$path/$entry", $_options)) {
	//				print "Remove folder: $path/$entry\n";

					// try to delete folder
					if(!@rmdir("$path/$entry")) {
						$is_empty = false;
					}

				}
				// 
				else {
					$is_empty = false;
				}
			}
			else {
	//		 	print "Folder is not empty: $path/$entry<br>";
			}

		}


//		print "$path/$entry IS EMPTY: $is_empty\n";
		return $is_empty;

	}


	/**
	* Recursively check each part of path and create folders if parts are missing
	*
	* @param string $path Path to verify
	* @return bool
	*/
	function makeDirRecursively($path) {
		if(!file_exists($path)) {
			$parts = explode("/", $path);
			$verify_path = "";
			for($i = 1; $i < count($parts); $i++) {
				$verify_path .= "/".$parts[$i];
				if(!file_exists($verify_path)) {
					mkdir($verify_path);
				}
			}
		}
	}


	// copy function for both files and folders
	function copy($path, $dest) {

		// folder
		if(is_dir($path)) {
			$this->makeDirRecursively($dest);

			$contents = scandir($path);
			foreach($contents as $file) {
				// ignore . and ..
				if($file == "." || $file == "..") {
					continue;
				}

				// folder
				if(is_dir($path."/".$file)) {
					$this->copy($path."/".$file, $dest."/".$file);
				}
				// file
				else {
					copy($path."/".$file, $dest."/".$file);
				}
			}
			return true;
		}
		else if(is_file($path)) {
			// make sure desination path exists
			$this->makeDirRecursively(dirname($dest));
			return copy($path, $dest);
		}
		else {
			return false;
		}
	}


	/**
	* Compares to files, returns difference
	*
	* @param string $file1 path to file
	* @param string $file2 path to file
	* @return string Difference
	*/
	/*
	function compareFiles($file1, $file2) {
		return shell_exec("diff -a -u -d '".$file1."' '".$file2."'");
	}
	*/


	/**
	* Get the entire content of a file  
	*
	* @param string $file File to retreive
	* @return string file
	*/
	/*
	function getFile($file) {
		if(file_exists($file)){
			return file_get_contents($file); 
		}
		return false;
	}
	*/

}

?>