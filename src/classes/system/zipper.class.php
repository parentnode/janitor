<?php
/**
* This file contains zip-functions
*/
class Zipper {


	/**
	* @param $flag ZipArchive flag used when opening Zip-archive
	* @param $path Remove file path from files (true) - or fragment thereof (array of strings to remove, empty folders will also be removed in these paths)
	* @param $delete Delete files after adding to Zip
	*/
	function zip($files, $dest, $_options = false) {

		$flag = ZipArchive::CREATE;
		$paths = true;
		$delete = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "flag"            : $flag = $_value;        break;
					case "paths"           : $paths = $_value;       break;
					case "delete"          : $delete = $_value;      break;
				}
			}
		}


		$zip = new ZipArchive();
		$zip->open($dest, $flag);

		foreach($files as $file) {

			// determine name to store
			if($paths === false) {
				$stored_name = $file;
			}
			else if($paths === true) {
				$stored_name = basename($file);
			}
			else {
				$stored_name = $file;
				foreach($paths as $path) {

					$stored_name = str_replace($path, "", $file);
				}

			}

			if($zip->addFile($file, $stored_name)) {

				// add file to delete array (if files needs to be deleted)
				$delete_files[] = $file;

			}
			// something went wrong
			else {
				$zip->close();
				unlink($dest);
				return false;
			}
		}

		$zip->close();


		// delete after zipping?
		if($delete) {

			$fs = new FileSystem();

			foreach($delete_files as $file) {
				unlink($file);
			}

			// run through all files and remove the folder they were located in (if it is empty)
			if($paths === true) {

				foreach($delete_files as $file) {
					
					$fs->removeEmptyDirRecursively(dirname($file));
				}

			}
			else if($paths != false) {
				// remove empty folders
				foreach($paths as $path) {
					$fs->removeEmptyDirRecursively($path);
				}
			}


		}

		return true;

	}

}