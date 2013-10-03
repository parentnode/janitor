<?php
/**
* @package e-types.items
* This file contains item news maintenance functionality
*/

/**
* TypeNews
*/
class UserGroup extends Model {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->db = SITE_DB.".user_groups";
		$this->db_access = SITE_DB.".access";

		// Nickname
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Groupname",
			"required" => true,
			"hint_message" => "Name of user group - Admins, customers, etc", 
			"error_message" => "Name must to be filled out"
		));

		parent::__construct();
	}




	// get carts
	// - optional multiple carts, based on content match
	function getUserGroups($_options=false) {

		$order = "name DESC, id DESC";
		$user_group_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "order"           : $order              = $_value; break;
					case "user_group_id"   : $user_group_id      = $_value; break;
				}
			}
		}

		$query = new Query();

		// get all carts with item_id in it
		if($user_group_id) {

			if($query->sql("SELECT * FROM ".$this->db." WHERE id = $user_group_id")) {
				return $query->result(0);
			}

		}

		// return all carts
		else {
			if($query->sql("SELECT * FROM ".$this->db." ORDER BY $order")) {
				 return $query->results();
			}
		}

		return false;
	}

	
	function save() {

		// does values validate
		if($this->validateAll()) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db);
			$query->checkDbExistance($this->db_access);

			$entities = $this->data_entities;
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db." SET id = DEFAULT," . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					return true;
				}
			}
		}

		return false;


	}
	function update() {}
}

?>