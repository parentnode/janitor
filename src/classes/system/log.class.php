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

		$fs = new FileSystem();

		$timestamp = time();
		$user_ip = getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");
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

		$fs = new FileSystem();

		$collection_path = LOG_FILE_PATH."/notifications/";
		$fs->makeDirRecursively($collection_path);


		// notifications file
		$collection_file = $collection_path.$collection;


		$timestamp = time();
		$user_ip = getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR");
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
		if(count($notifications) >= (defined("SITE_COLLECT_NOTIFICATIONS") ? SITE_COLLECT_NOTIFICATIONS : 10)) {

			$message = implode("\n", $notifications);

			// include formatting template (if it exists)
			@include("templates/mails/notifications/$collection.php");

			// send and reset collection
			if(mailer()->send(array(
				"subject" => "NOTIFICATION: $collection on ".$_SERVER["SERVER_ADDR"], 
				"message" => $message,
				"tracking" => false
			))) {
				$fp = fopen($collection_file, "w");
				fclose($fp);
			}
		}
	}



	// get log entries, 
	// optionally based on date span, log type, item_id or user_id
	function getLogs($_options = false) {

		$from = false;
		$to = false;

		$type = false;

		$item_id = false;

		$user_id = false;


		$fs = new FileSystem();
		$all_log_files = $fs->files(LOG_FILE_PATH);
		$log_files = [];


		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {

					case "from"         : $from        = $value; break;
					case "to"           : $to.         = $value; break;

					case "type"         : $type        = $value; break;

					case "item_id"      : $item_id     = $value; break;
					case "user_id"      : $user_id     = $value; break;

				}
			}
		}


		if($from && $to) {
			
		}



		return $log_files;
	}


	
}