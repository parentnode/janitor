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
		$delete_files = array();

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
		$zip_is_open = false;
		$zip_file_count = 0;

		// only add 100 files at the time to keep below system file-limit
		$zip_file_limit = 200;

		// opening and closing zip-file can be really slow, because the files is recompressed - so do that as little as possible
		// also be aware of system "open-file" limit - so don't add all files in one go

		foreach($files as $file) {


			// open zip destination if not already open
			if($zip_is_open || $zip->open($dest, $flag) === true) {

				// current state values
				$zip_is_open = true;
				$zip_file_count++;


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

				// add file to 
				if($zip->addFile($file, $stored_name)) {

					// add file to delete array (if files needs to be deleted)
					$delete_files[] = $file;

				}
				// something went wrong
				// close archive and exit
				else {

					$zip->close();
					return false;

				}

				// zip file limit reached, close zip and reset current status flags
				if($zip_file_count >= $zip_file_limit) {
					$zip->close();
					$zip_file_count = 0;
					$zip_is_open = false;
				}

			}

			// could not open zip file
			// do not attempt to continue
			else {

				return false;

			}


		}

		// close zip after all is done
		if($zip_is_open) {
			$zip->close();
		}


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