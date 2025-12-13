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
		$log_location = LOG_FILE_PATH."/".$collection;
		$log_cursor = LOG_FILE_PATH."/".$collection."/".date("Y-m-d", $timestamp).".log";
		$fs->makeDirRecursively($log_location);

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
		$collection_file = $collection_path.$collection.".log";


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

	// get available log types (folders in log path)
	function getLogTypes() {

		$folders = [];

		$handle = opendir(LOG_FILE_PATH);
		while(($file = readdir($handle)) !== false) {
			if($file != "." && $file != ".." && $file != "notifications") {
				$folders[] = $file;
			}
		}

		return $folders;
	}

	// get log entries, 
	// optionally based on date span, log type
	// item_id or user_id could also be options in the future
	function getLogs($_options = false) {

		$from = false;
		$to = false;

		$type = false;

		// $item_id = false;
		// $user_id = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "from"         : $from        = $_value; break;
					case "to"           : $to          = $_value; break;

					case "type"         : $type        = $_value; break;

					// case "item_id"      : $item_id     = $_value; break;
					// case "user_id"      : $user_id     = $_value; break;

				}
			}
		}

		if(!$from) {
			$from = strtotime("-1 month");
		}

		// debug(["getLogs", $from, $to, $type]);

		$fs = new FileSystem();

		// Do not include notification logs in regular log listing
		$all_log_files = $fs->files(LOG_FILE_PATH, [
			"deny_folders" => "notifications",
		]);
		// debug(["all_log_files", $all_log_files]);


		// Filter log files based on from/to and type
		$log_files = [];

		foreach($all_log_files as $log_file) {

			$filename = basename($log_file);
			$log_type = preg_match("/^\/([a-zA-Z0-9_-]+)\//", str_replace(LOG_FILE_PATH, "", $log_file), $matches) ? $matches[1] : "unknown";
			$date = strtotime(str_replace(".log", "", $filename));

			// debug(["log_file", $log_file, $filename, $date, $log_type]);

			if(!$type || $type === $log_type) {

				if($from && $to) {
					
					if($from <= $date && $to >= $date) {
						$log_files[$log_type][] = $log_file;
					}

				}
				else if($from) {

					if($from <= $date) {
						$log_files[$log_type][] = $log_file;
					}
					
				}
				else if($to) {

					if($to >= $date) {
						$log_files[$log_type][] = $log_file;
					}
	
				}
				else {
					$log_files[$log_type][] = $log_file;
				}
			}

		}

		return $log_files;
	}


	
}