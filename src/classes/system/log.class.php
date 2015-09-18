<?php
/**
* @package janitor.system
*/
/**
* This class holds Log functionallity.
*
*/


class Log {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

	}



	// get tag, optionally based on item_id, limited to context, or just check if specific tag exists
	function getLogs() {

		$fs = new FileSystem();
		$log_files = $fs->files(LOG_FILE_PATH);

// 		$query = new Query();
// 		$sql = "SELECT tags.id as id, tags.context as context, tags.value as value, count(taggings.id) as tag_count FROM ".UT_TAG." as tags LEFT JOIN ".UT_TAGGINGS."  as taggings ON tags.id = taggings.tag_id GROUP BY tags.id ORDER BY tags.context, tags.value";
// //		print $sql;
// 		if($query->sql($sql)) {
//
// 			return $query->results();
// 		}

		return $log_files;
	}
	
}