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
		$this->db_hosts = SITE_DB.".item_event_hosts";
		$this->db_performers = SITE_DB.".item_event_performers";


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
			"hint_message" => "If you don't know what this is, just leave it empty"
		));

		// Description
		$this->addToModel("description", array(
			"type" => "text",
			"label" => "Short description",
			"hint_message" => "Write a short description of the event for SEO.",
			"error_message" => "A short description without any words? How weird."
		));

		// HTML
		$this->addToModel("html", array(
			"type" => "html",
			"label" => "Full description",
			"hint_message" => "Write a full description of the event.",
			"error_message" => "A full description without any words? How weird."
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


		// Host
		$this->addToModel("host", array(
			"type" => "string",
			"label" => "Host",
			"required" => true,
			"hint_message" => "Name of the host.",
			"error_message" => "You need to enter a valid host name."
		));
		// Host address 1
		$this->addToModel("host_address1", array(
			"type" => "string",
			"label" => "Streetname and number",
			"required" => true,
			"hint_message" => "Streetname and number.",
			"error_message" => "You need to enter a valid streetname and number."
		));
		// Host address 2
		$this->addToModel("host_address2", array(
			"type" => "string",
			"label" => "Additional address info",
			"hint_message" => "Additional address info.",
			"error_message" => "Invalid address"
		));
		// Host city
		$this->addToModel("host_city", array(
			"type" => "string",
			"label" => "City",
			"required" => true,
			"hint_message" => "Write your city",
			"error_message" => "Invalid city"
		));
		// Host postal code
		$this->addToModel("host_postal", array(
			"type" => "string",
			"label" => "Postal code",
			"required" => true,
			"hint_message" => "Postalcode of your city",
			"error_message" => "Invalid postal code"
		));
		// Host country
		$this->addToModel("host_country", array(
			"type" => "string",
			"label" => "Country",
			"required" => true,
			"hint_message" => "Country",
			"error_message" => "Invalid country"
		));
		// Host google maps link
		$this->addToModel("host_googlemaps", array(
			"type" => "string",
			"label" => "Link to Google Maps",
			"pattern" => "http[s]?:\/\/[^$]+",
			"hint_message" => "Link to Google Maps",
			"error_message" => "Invalid link"
		));
		// Host comment
		$this->addToModel("host_comment", array(
			"type" => "text",
			"label" => "Host comment",
			"hint_message" => "Directions or other comments.",
			"error_message" => "Host comment error."
		));

	}


	// get all hosts
	function getHosts($_options = false) {

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
		$query->checkDbExistence($this->db_hosts);


		// get host by id
		if($id) {

			$sql = "SELECT * FROM ".$this->db_hosts." WHERE id = ".$id;
			if($query->sql($sql)) {
				return $query->result(0);
			}

		}
		// get host by name
		else if($name) {

			$sql = "SELECT * FROM ".$this->db_hosts." WHERE name = '".$name."'";
			if($query->sql($sql)) {
				return $query->result(0);
			}

		}
		else {

			$sql = "SELECT * FROM ".$this->db_hosts." ORDER BY host";
			if($query->sql($sql)) {
				return $query->results();
			}

		}

	}


	// CMS 

	// create a new host
	// /janitor/admin/events/addHost (values in POST)
	function addHost($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 1 && $this->validateList(array("host","host_address1","host_postal","host_city","host_country"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistence($this->db_hosts);

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(host|host_address1|host_address2|host_city|host_postal|host_country|host_googlemaps|host_comment)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db_hosts." SET " . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("Host created");
					return true;
				}
			}
		}

		message()->addMessage("Host could not be saved", array("type" => "error"));
		return false;
	}


	// update an address
	// /janitor/admin/event/updateHost/#host_id# (values in POST)
	function updateHost($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(count($action) == 2) {
			$query = new Query();
			$host_id = $action[1];

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
				$sql = "UPDATE ".$this->db_hosts." SET ".implode(",", $values).",modified_at=CURRENT_TIMESTAMP WHERE id = ".$host_id;
//				print $sql;
			}

			if(!$values || $query->sql($sql)) {
				message()->addMessage("Host updated");
				return true;
			}

		}

		message()->addMessage("Host could not be updated", array("type" => "error"));
		return false;
	}

	// Delete host
	// /janitor/admin/event/deleteHost/#host_id#
	function deleteHost($action) {

		$host_id = $action[1];
		
		if(count($action) == 2) {
			$query = new Query();

			$sql = "DELETE FROM $this->db_hosts WHERE id = ".$host_id;
//			print $sql;
			if($query->sql($sql)) {
				message()->addMessage("Host deleted");
				return true;
			}

		}

		return false;
	}




}

?>