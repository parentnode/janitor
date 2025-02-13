<?php
/**
* @package janitor.system
*/
/**
* This class holds Admin notification functionallity.
*
*/


class Admin {


	public $db_admin_notifications;

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->db_admin_notifications = SITE_DB.".system_admin_notifications";

	}


	// Notify admin if conditions allow
	function notify($_options) {
		// debug(["notify", $_options]);

		// Do noting if ADMIN_NOTIFICATIONS are set to false
		if(defined("ADMIN_NOTIFICATIONS") && ADMIN_NOTIFICATIONS === false) {
			return false;
		}


		// Add all admin notifications to log
		logger()->addLog(implode(", ", $_options), "admin-messages");


		$halted_notifications = cache()->value("admin-notifications-halted");
		// debug(["halted-state", $halted_notifications]);

		// Open for notifications
		if(!$halted_notifications) {

			// Check notification threshold to avoid message overload (with potential account loss)
			$query = new Query();
			$query->checkDbExistence($this->db_admin_notifications);

			// Clean up old entries (older than one minute)
			$sql = "DELETE FROM ".$this->db_admin_notifications." WHERE notified_at < '".date("Y-m-d H:i:s", strtotime("- 1 minute"))."'";
			$query->sql($sql);

			// How many messages has been sent within the last minute then
			$query->sql("SELECT count(*) AS notifications FROM ".$this->db_admin_notifications);
			$notifications = $query->result(0, "notifications");

			$threshold = (defined("SITE_ADMIN_NOTIFICATION_THRESHOLD") && is_integer(SITE_ADMIN_NOTIFICATION_THRESHOLD) ? SITE_ADMIN_NOTIFICATION_THRESHOLD : 10);


			// Threshold exceeded
			if($notifications >= $threshold) {

				// debug(["halt the notification"]);
				cache()->value("admin-notifications-halted", "true", 60);

				// TODO: finalize halted message
				$_halted_options = [
					"subject" => "Notification from ".SITE_URL." halted due to overload risk",
					"message" => "Notifications from ".SITE_URL." exceeded the specified threshold of $threshold pr. minute. Notifications have been halted to avoid mailserver exhaustion."
				];

				if(defined("ADMIN_NOTIFICATIONS") && ADMIN_NOTIFICATIONS === "instantmessage") {
					return instantmessage()->send($_halted_options);
				}
				else if(defined("ADMIN_NOTIFICATIONS") && ADMIN_NOTIFICATIONS === "sms") {
					return sms()->send($_halted_options);
				}
				else {
					return email()->send($_halted_options);
				}

			}
			// Notify admin
			else {

				// debug(["do the notification"]);
				$query->sql("INSERT INTO ".$this->db_admin_notifications." SET invoked_by_ip = '".getRequestIp()."'");

				if(defined("ADMIN_NOTIFICATIONS") && ADMIN_NOTIFICATIONS === "instantmessage") {
					return instantmessage()->send($_options);
				}
				else if(defined("ADMIN_NOTIFICATIONS") && ADMIN_NOTIFICATIONS === "sms") {
					return sms()->send($_options);
				}
				else {
					return email()->send($_options);
				}

			}

		}
		// extend halt period on continuous errors
		else {

			// debug(["notifications halted"]);
			cache()->value("admin-notifications-halted", "true", 60);

		}

	}

}