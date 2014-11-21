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
	function files($path, $options = false) {
//		print $path."<br>";
		// options only used to pass on to valid()
		// if($options !== false) {
		// 	foreach($options as $option => $value) {
		// 		switch($option) {
		// 			case "deny_folders" 		: $deny_folders = $value; 		break;
		// 			case "allow_folders" 		: $allow_folders = $value; 		break;
		// 			case "deny_extensions" 	: $deny_extensions = $value; 	break;
		// 			case "allow_extensions" 	: $allow_extensions = $value; 		break;
		// 		}
		// 	}
		// }


		$files = array();

		$handle = opendir("$path");
		while(($file = readdir($handle)) !== false) {

//			print $file . "<br>";
			$current_path = "$path/$file";

			if($this->valid($current_path, $options)) {

				// file is a directory - iterate
				if(is_dir("$current_path")) {
					$files = array_merge($files, $this->files($current_path, $options));
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
	* @return boolean It the folder/file valid or not
	*/
//	function validFolder($file, $exclude_folders = array(), $allowed_extensions=false) {
	function valid($file, $options = false) {

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "deny_folders" 		: $deny_folders = explode(",", $value);		break;
					case "allow_folders" 		: $allow_folders = explode(",", $value);		break;
					case "deny_extensions"		: $deny_extensions = explode(",", $value);	break;
					case "allow_extensions" 	: $allow_extensions = explode(",", $value);	break;
				}
			}
		}

		$file_name = basename($file);

		if(
			// ignore files starting with . and _ (conf, temp and directory links)
			substr($file_name, 0, 1) != "." && 
			substr($file_name, 0, 1) != "_" && 

			(
				!is_dir($file) || 
				(	
					!isset($deny_folders) || array_search($file_name, $deny_folders) === false
				) &&
				(
					!isset($allow_folders) || array_search($file_name, $allow_folders) !== false
				)
			) &&
			(
				!is_file($file) || 
				(
					!isset($deny_extensions) || array_search(substr($file_name, -3), $deny_extensions) === false
				) &&
				(
					!isset($allow_extensions) || array_search(substr($file_name, -3), $allow_extensions) !== false
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
	* @return string
	*/
	function removeEmptyDirRecursively($path, $options = false) {

//		print "PATH:$path<br>";

		$delete_tempfiles = false;

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "delete_tempfiles" : $delete_tempfiles = $value; break;
				}
			}
		}

		if($this->valid($path, $options)) {

			$is_empty = true;
//			print "Empty is true<br>";

			$dir = opendir($path);
			while($entry = readdir($dir)) {

//				print "entry:" . $entry."<br>";
				$is_valid = true;

				if(is_file("$path/$entry") && (strpos($entry, "_") === 0 || strpos("$entry", ".") === 0)) {

					if(!$delete_tempfiles) {
//						print "temp/hidden file found: $entry<br>";
						$is_empty = false;
					}
				}

 				if($this->valid("$path/$entry", $options)) {
	
					if(is_dir("$path/$entry")) {
//						print "trying to delete sub: $entry<br>";
						$rm = $this->removeEmptyDirRecursively("$path/$entry", $options);
//						print "rm for $entry: #$rm#<br>";
						if(!$rm) {
//							print "BAD RM<br>";
							$is_empty = false;
						}
					}
					else {
//						print "file found: $entry<br>";
						$is_empty = false;
					}
				}
				// invalid but folder ref
				else if(is_dir("$path/$entry") && ($entry == "." || $entry == "..")) {

					$is_valid = false;

				}
				// invalid file
				else {
//					print "invalid path/entry: $path/$entry<br>";
					$is_empty = false;
					$is_valid = false;
				}

				if($is_empty && $is_valid) {
//					print "DELETE $path/$entry<br>";
					rmdir("$path/$entry");
				}
				else {
//					print "DONT DELETE $path/$entry ($is_empty) ($is_valid)<br>";
				}
			}
			closedir($dir);

			if($is_empty) {
				rmdir("$path/$entry");
//			 	print "Folder is empty: $path/$entry<br>";
			}
			else {
//			 	print "Folder is not empty: $path/$entry<br>";
				
			}

//			print "$path/$entry IS EMPTY: $is_empty, IS VALID: $is_valid<br>";
			return $is_empty;
		}
		else {
//			print "invalid path: $path<br>";
			return false;
		}

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