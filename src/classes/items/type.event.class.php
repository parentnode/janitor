<?php
/**
* @package janitor.items
* This file contains item type functionality
*/

class TypeEvent extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_event";
		$this->db_locations = SITE_DB.".item_event_locations";
		$this->db_performers = SITE_DB.".item_event_performers";

		// Event details
		$this->event_status_options = [
			0 => "Cancelled",
			1 => "Scheduled", 
			2 => "Moved online", 
			3 => "Postponed", 
			4 => "Rescheduled"
		];
		$this->event_status_schema_values = [
			0 => "EventCancelled",
			1 => "EventScheduled",
			2 => "EventMovedOnline",
			3 => "EventPostponed",
			4 => "EventRescheduled"
		];
		$this->event_attendance_options = [
			1 => "Physical",
			2 => "Physical and Online",
			3 => "Online"
		];
		$this->event_attendance_schema_values = [
			1 => "OfflineEventAttendanceMode",
			2 => "MixedEventAttendanceMode",
			3 => "OnlineEventAttendanceMode"
		];


		$this->event_location_type_options = [
			1 => "Physical",
			2 => "Online"
		];

		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"hint_message" => "Event name", 
			"error_message" => "Event needs a name."
		));

		// Class
		$this->addToModel("classname", array(
			"type" => "string",
			"label" => "CSS Class",
			"hint_message" => "CSS class for custom styling. If you don't know what this is, just leave it empty"
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the event for SEO and listings.",
			"error_message" => "Your event needs a description – max 155 characters."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full description",
			"allowed_tags" => "p,h2,h3,h4,ul,ol,code,download,jpg,png", //,mp4,vimeo,youtube",
			"hint_message" => "Write a full description of the event.",
			"error_message" => "A full description without any words? How weird."
		));

		// Single media
		$this->addToModel("single_media", array(
			"type" => "files",
			"label" => "Add media here",
			"max" => 1,
			"allowed_formats" => "png,jpg",
			"hint_message" => "Add single image by dragging it here. PNG or JPG allowed",
			"error_message" => "Media does not fit requirements."
		));

		// Start datetime
		$this->addToModel("starting_at", array(
			"type" => "datetime",
			"label" => "Starts at",
			"requied" => true,
			"hint_message" => "When does the event start.",
			"error_message" => "You need to enter a valid date/time."
		));
		// End datetime
		$this->addToModel("ending_at", array(
			"type" => "datetime",
			"label" => "Ends at",
			"hint_message" => "When does the event end.",
			"error_message" => "You need to enter a valid date/time."
		));

		// Event status
		$this->addToModel("event_status", array(
			"type" => "select",
			"label" => "Event status",
			"options" => $this->event_status_options,
			"hint_message" => "Status of the event.",
			"error_message" => "Indicated the status of the event."
		));

		// Event attendance
		$this->addToModel("event_attendance", array(
			"type" => "select",
			"label" => "Event attendance",
			"options" => $this->event_attendance_options,
			"hint_message" => "Attendence option of the event.",
			"error_message" => "Indicated the attendance option of the event."
		));


		// Location
		$this->addToModel("location", array(
			"type" => "string",
			"label" => "Location",
			"required" => true,
			"hint_message" => "Name of the location.",
			"error_message" => "You need to enter a valid location name."
		));

		// Location type
		$this->addToModel("location_type", array(
			"type" => "select",
			"label" => "Location type",
			"options" => $this->event_location_type_options,
			"hint_message" => "Type of location.",
			"error_message" => "Indicated the type of the location."
		));


		// Location url
		$this->addToModel("location_url", array(
			"type" => "string",
			"label" => "Location url",
			"pattern" => "http[s]?:\/\/[^$]+",
			"hint_message" => "Url of location.",
			"error_message" => "State the url of the location if available (including http:// or https://)."
		));


		// Location address 1
		$this->addToModel("location_address1", array(
			"type" => "string",
			"label" => "Streetname and number",
			"hint_message" => "Streetname and number.",
			"error_message" => "You need to enter a valid streetname and number."
		));
		// Location address 2
		$this->addToModel("location_address2", array(
			"type" => "string",
			"label" => "Additional address info",
			"hint_message" => "Additional address info.",
			"error_message" => "Invalid address"
		));
		// Location city
		$this->addToModel("location_city", array(
			"type" => "string",
			"label" => "City",
			"hint_message" => "Write your city",
			"error_message" => "Invalid city"
		));
		// Location postal code
		$this->addToModel("location_postal", array(
			"type" => "string",
			"label" => "Postal code",
			"hint_message" => "Postalcode of your city",
			"error_message" => "Invalid postal code"
		));
		// Location country
		$this->addToModel("location_country", array(
			"type" => "string",
			"label" => "Country",
			"hint_message" => "Country",
			"error_message" => "Invalid country"
		));
		// Location google maps link
		$this->addToModel("location_googlemaps", array(
			"type" => "string",
			"label" => "Link to Google Maps",
			"pattern" => "http[s]?:\/\/[^$]+",
			"hint_message" => "Link to Google Maps",
			"error_message" => "Invalid link"
		));
		// Location comment
		$this->addToModel("location_comment", array(
			"type" => "text",
			"label" => "Location comment",
			"hint_message" => "Directions or other comments.",
			"error_message" => "Location comment error."
		));

	}


	// get all locations
	function getLocations($_options = false) {

		$name = false;
		$id = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "name"           : $name            = $_value; break;
					case "id"             : $id              = $_value; break;
				}
			}
		}

		$query = new Query();
		$query->checkDbExistence($this->db_locations);


		// get location by id
		if($id) {

			$sql = "SELECT * FROM ".$this->db_locations." WHERE id = ".$id;
			if($query->sql($sql)) {
				return $query->result(0);
			}

		}
		// get location by name
		else if($name) {

			$sql = "SELECT * FROM ".$this->db_locations." WHERE name = '".$name."'";
			if($query->sql($sql)) {
				return $query->result(0);
			}

		}
		else {

			$sql = "SELECT * FROM ".$this->db_locations." ORDER BY location";
			if($query->sql($sql)) {
				return $query->results();
			}

		}

	}


	// CMS 

	// create a new location
	// /janitor/admin/events/addLocation (values in POST)
	function addLocation($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 1 && $this->validateList(array("location","location_address1","location_postal","location_city","location_country"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistence($this->db_locations);

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(location|location_address1|location_address2|location_city|location_postal|location_country|location_googlemaps|location_comment)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db_locations." SET " . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("Location created");
					return true;
				}
			}
		}

		message()->addMessage("Location could not be saved", array("type" => "error"));
		return false;
	}


	// update an address
	// /janitor/admin/event/updateLocation/#location_id# (values in POST)
	function updateLocation($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {
			$query = new Query();
			$location_id = $action[1];

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "UPDATE ".$this->db_locations." SET ".implode(",", $values).",modified_at=CURRENT_TIMESTAMP WHERE id = ".$location_id;
//				print $sql;
			}

			if(!$values || $query->sql($sql)) {
				message()->addMessage("Location updated");
				return true;
			}

		}

		message()->addMessage("Location could not be updated", array("type" => "error"));
		return false;
	}

	// Delete location
	// /janitor/admin/event/deleteLocation/#location_id#
	function deleteLocation($action) {

		$location_id = $action[1];
		
		if(count($action) == 2) {
			$query = new Query();

			$sql = "DELETE FROM $this->db_locations WHERE id = ".$location_id;
//			print $sql;
			if($query->sql($sql)) {
				message()->addMessage("Location deleted");
				return true;
			}

		}

		return false;
	}

	function ordered($order_item, $order) {

		$order_item_id = $order_item["id"];
		// print "\n<br>###$order_item_id### ordered (event item)\n<br>";

	}

	function subscribed($subscription) {
		
		// print "\n<br>###$subscription["item_id"]### subscribed\n<br>";

	}




}

?>