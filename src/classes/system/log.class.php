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





	/**
	* Add log entry.
	* Adds user id and user IP along with message and optional values.
	*
	* @param string $message Log message.
	* @param string $collection Log collection.
	*/
	function addLog($message, $collection="framework") {


		// Do not gather and save information if logging is disabled
		if(defined("SITE_LOGGING_DISABLED") && SITE_LOGGING_DISABLED === true) {
			return;
		}


		$fs = new FileSystem();

		$timestamp = time();
		$user_ip = security()->getRequestIp();
		$user_id = session()->value("user_id");

		$log = date("Y-m-d H:i:s", $timestamp). " $user_id $user_ip $message";

		// year-month as folder
		// day as file
		$log_position = LOG_FILE_PATH."/".$collection."/".date("Y/m", $timestamp);
		$log_cursor = LOG_FILE_PATH."/".$collection."/".date("Y/m/Y-m-d", $timestamp);
		$fs->makeDirRecursively($log_position);

		$fp = fopen($log_cursor, "a+");
		fwrite($fp, $log."\n");
		fclose($fp);

	}


	/**
	* collect message for bundled notification
	* Set collection size in config
	*
	* Automatically formats collection from template (if available) before sending
	*/
	function collectNotification($message, $collection="framework") {


		// Do not gather and save information if collect notifications are turned off OR logging is disabled
		if((defined("SITE_LOGGING_DISABLED") && SITE_LOGGING_DISABLED === true) || (defined("SITE_AUTOCONVERSION_COLLECT_NOTIFICATIONS") && SITE_AUTOCONVERSION_COLLECT_NOTIFICATIONS === false)) {
			return;
		}


		$fs = new FileSystem();

		$collection_path = LOG_FILE_PATH."/notifications/";
		$fs->makeDirRecursively($collection_path);


		// notifications file
		$collection_file = $collection_path.$collection;


		$timestamp = time();
		$user_ip = security()->getRequestIp();
		$user_id = session()->value("user_id");

		$log = date("Y-m-d H:i:s", $timestamp). " $user_id $user_ip $message";

		$fp = fopen($collection_file, "a+");
		fwrite($fp, $log."\n");
		fclose($fp);


		// existing notifications
		$notifications = array();
		if(file_exists($collection_file)) {
			$notifications = file($collection_file);
		}

		// send report and reset collection
		if(count($notifications) >= (defined("SITE_AUTOCONVERSION_COLLECT_NOTIFICATIONS") ? SITE_AUTOCONVERSION_COLLECT_NOTIFICATIONS : 10)) {

			$message = implode("\n", $notifications);

			// include formatting template (if it exists)
			@include("templates/mails/notifications/$collection.php");

			// send and reset collection
			if(admin()->notify(array(
				"subject" => "NOTIFICATION: $collection on ".$_SERVER["SERVER_ADDR"], 
				"message" => $message,
				"tracking" => false
			))) {
				$fp = fopen($collection_file, "w");
				fclose($fp);
			}
		}
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