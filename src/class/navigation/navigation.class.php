<?php
/**
* @package janitor.navigation
* This file contains Navigation maintenance functionality
*/

/**
* TypeNews
*/
class Navigation extends Model {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->db = SITE_DB.".navigation";
		$this->db_nodes = SITE_DB.".navigation_nodes";
		$this->level_iterator = 0;


		// Name
		$this->addToModel("name", array(
			"type" => "string",
			"label" => "Navigation name",
			"required" => true,
			"hint_message" => "Give your navigation link list a name - this will only be displayed in the backend", 
			"error_message" => "Name must be filled out"
		));


		// node_name
		$this->addToModel("node_name", array(
			"type" => "string",
			"label" => "Navigation node name",
			"required" => true,
			"hint_message" => "This is the name used to display the node in the link list",
			"error_message" => "A navigation node must have a name"
		));

		// node_link
		$this->addToModel("node_link", array(
			"type" => "string",
			"label" => "Static link",
			"hint_message" => "A node can be a static link",
			"error_message" => ""
		));

		// node_page_id
		$this->addToModel("node_page_id", array(
			"type" => "integer",
			"label" => "Page",
			"hint_message" => "Select an existing page as link for this node",
			"error_message" => ""
		));

		// node_classname
		$this->addToModel("node_classname", array(
			"type" => "string",
			"label" => "Node classname",
			"hint_message" => "Add a classname to this node",
			"error_message" => ""
		));


		parent::__construct();
	}


	/**
	* CONTROLLER FUNCTIONS
	*
	*/

	// save new user
	// gets values from posted model values
	function save() {

		// does values validate
		if($this->validateList(array("name"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db);
			$query->checkDbExistance($this->db_nodes);

			$entities = $this->data_entities;
			$names = array();
			$values = array();

			$name = $entities["name"]["value"];
			$handle = superNormalize($name);

			if($name && $handle) {
				$sql = "INSERT INTO ".$this->db." SET name = '$name', handle = '$handle'";
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("navigation created");
					return array("item_id" => $query->lastInsertId());
				}
			}
		}

		message()->addMessage("Creating navigation failed", array("type" => "error"));
		return false;
	}


	// update user
	// /user/update/#user_id#
	// post values
	function update($action) {

		if(count($action) == 2) {
			$user_id = $action[1];
			$query = new Query();

			$entities = $this->data_entities;
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(user_group_id|firstname|lastname|nickname|language)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($this->validateList($names, $user_id)) {
				if($values) {
					$sql = "UPDATE ".$this->db." SET ".implode(",", $values)." WHERE id = ".$user_id;
//					print $sql;
				}

				if(!$values || $query->sql($sql)) {
					message()->addMessage("User updated");
					return true;
				}
			}
		}
		message()->addMessage("Updating user failed", array("type" => "error"));
		return false;
	}


	// delete user
	// /admin/navigation/delete/#navigation_id#
	function delete($action) {

		if(count($action) == 2) {
			$query = new Query();
			$sql = "DELETE FROM $this->db WHERE id = ".$action[1];
//			print $sql;
			if($query->sql($sql)) {
				message()->addMessage("Navigation deleted");
				return true;
			}
		}

		message()->addMessage("Deleting navigation failed", array("type" => "error"));
		return false;
	}



	/**
	* Get users
	*
	* get all users
	* Get all users in user_group
	* Get specific user_id
	* Get users with email as username
	* Get users with mobile as username
	*/
	function getNavigations($_options=false) {

		// default values
		$handle = false;
		$navigation_id = false;
		$this->levels = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "navigation_id"     : $navigation_id      = $_value; break;
					case "handle"            : $handle             = $_value; break;
					case "levels"            : $this->levels       = $_value; break;

				}
			}
		}

		$query = new Query();

		// translate handle into navigation_id
		if($handle) {

			$sql = "SELECT id FROM ".$this->db." WHERE handle = '$handle'";
			if($query->sql($sql)) {
				$navigation_id = $query->results(0);
			}
		}

		// looking for specific navigation
		if($navigation_id) {

			$navigation = false;

			$sql = "SELECT * FROM ".$this->db." WHERE id = '$navigation_id'";
//			print $sql."<br>";

			if($query->sql($sql)) {

				$navigation = $query->result(0);

				// get children
				if($this->levels === false || $this->levels > $this->level_iterator) {
					$navigation["nodes"] = $this->getNavigationNodes($navigation_id);
				}
			}

			return $navigation;

		}
		// get all navigations
		else {

			$navigations = false;

			$sql = "SELECT * FROM ".$this->db;
//			print $sql."<br>";

			if($query->sql($sql)) {
				$navigations = $query->results();

				if($this->levels === false || $this->levels > $this->level_iterator) {
					foreach($navigations as $i => $navigation) {

						$navigations[$i]["nodes"] = $this->getNavigationNodes($navigation_id);

					}
				}
			}

			return $navigations;

		}

	}


	// recursive function to get navigation node tree
	// TODO: merge getNode into getNavigationNodes and use options array parameter
	function getNavigationNodes($navigation_id, $relation = false) {

		$this->level_iterator++;

		$query_nodes = new Query();
		$nodes = false;

		if(!$relation) {
			$sql = "SELECT * FROM ".$this->db_nodes." WHERE navigation_id = $navigation_id AND relation = 0 ORDER BY position ASC, id ASC";
		}
		else {
			$sql = "SELECT * FROM ".$this->db_nodes." WHERE navigation_id = $navigation_id AND relation = $relation ORDER BY position ASC, id ASC";
		}


		// get media
		if($query_nodes->sql($sql)) {

			$results = $query_nodes->results();
			foreach($results as $i => $node) {
				$nodes[$i]["id"] = $node["id"];
				$nodes[$i]["name"] = $node["node_name"];
				$nodes[$i]["link"] = $node["node_link"];
				$nodes[$i]["item_id"] = $node["node_page_id"];
				$nodes[$i]["classname"] = $node["node_class"];
				if($this->levels === false || $this->levels > $this->level_iterator) {
					$nodes[$i]["nodes"] = $this->getNavigationNodes($navigation_id, $node["id"]);
				}
			}
		}

		$this->level_iterator--;

		return $nodes;
	}


	// TODO: merge this into getNavigationNodes
	function getNode($id) {

		$query = new Query();

		$sql = "SELECT * FROM ".$this->db_nodes." WHERE id = $id";

//		print $sql."<br>";
		// get node
		if($query->sql($sql)) {
			return $query->result(0);
		}

	}


	// save node
	function saveNode($action) {

		// does values validate
		if(count($action) == 2 && $this->validateList(array("node_name"))) {

			$query = new Query();
			$navigation_id = $action[1];

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
				$sql = "INSERT INTO ".$this->db_nodes." SET id = DEFAULT, navigation_id = $navigation_id, "  . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("Navigation node created");
					return array("item_id" => $navigation_id);
				}
			}
		}

		message()->addMessage("Navigation node could not be created", array("type" => "error"));
		return false;

	}


	// update node
	function updateNode($action) {

		// does values validate
		if(count($action) == 2 && $this->validateList(array("node_name"), $action[1])) {

			// does values validate
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
				$query = new Query();
				$sql = "UPDATE ".$this->db_nodes." SET ".implode(",", $values)." WHERE id = ".$action[1];
//				print $sql;

				if($query->sql($sql)) {
					message()->addMessage("Navigation node updated");
					return array("item_id" => $query->lastInsertId());
				}
			}
		}

		message()->addMessage("Navigation node could not be updated", array("type" => "error"));
		return false;
	}


	// delete navigation node - 2 parameters exactly
	// /deleteNode/#node_id#
	function deleteNode($action) {

		if(count($action) == 2) {

			$query = new Query();
			if($query->sql("DELETE FROM ".$this->db_nodes." WHERE id = ".$action[1])) {
				message()->addMessage("Navigation node deleted");
				return true;
			}

		}

		message()->addMessage("Navigation node could not be deleted - refresh your browser", array("type" => "error"));
		return false;

	}


	// update navigation node order
	function updateOrder($action) {

		if(count($action) == 2) {

			$query = new Query();
			$structure = json_decode(prepareForHTML(getPost("structure")), true);

			foreach($structure as $node) {
				
				$sql = "UPDATE ".$this->db_nodes." SET relation = ".$node["relation"].", position = ".$node["position"]." WHERE id = ".$node["id"];
				if(!$query->sql($sql)) {
					message()->addMessage("Node order update failed", array("type" => "error"));
					return false;
				}
			}

			message()->addMessage("Node order updated");
			return true;
		}

		message()->addMessage("Node order could not be updated", array("type" => "error"));
		return false;

	}

}

?>