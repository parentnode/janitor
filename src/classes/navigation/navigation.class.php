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

		parent::__construct(get_class());


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


	}


	/**
	* CONTROLLER FUNCTIONS
	*
	*/

	// save new user
	// gets values from posted model values
	function save() {

		// Get posted values to make them available for models
		$this->getPostedEntities();

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

		// Get posted values to make them available for models
		$this->getPostedEntities();


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
	// /janitor/admin/navigation/delete/#navigation_id#
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
	* Get list of all navigations/link-lists
	* Get specific navigation based on handle or navigation_id
	*
	* Optional levels setting to define levels of navigation structure to get
	*/
	function getNavigations($_options = false) {

		// default values
		$handle = false;
		$navigation_id = false;
		$levels = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "navigation_id"     : $navigation_id      = $_value; break;
					case "handle"            : $handle             = $_value; break;
					case "levels"            : $levels             = $_value; break;

				}
			}
		}

		$query = new Query();


		// handle is known
		// translate handle into navigation_id
		if($handle) {

			$sql = "SELECT id FROM ".$this->db." WHERE handle = '$handle'";
//			print $sql."<br>";
			if($query->sql($sql)) {

				$navigation_id = $query->result(0, "id");
			}
		}

		// looking for specific navigation id (possibly translated from handle)
		// and get sublevels if required
		if($navigation_id) {

			$navigation = false;

			$sql = "SELECT * FROM ".$this->db." WHERE id = '$navigation_id'";
//			print $sql."<br>";
			if($query->sql($sql)) {

				$navigation = $query->result(0);

				// get children
				if($levels === false || $levels) {
					$navigation["nodes"] = $this->getNavigationNodes($navigation_id, $_options);
				}
			}

			return $navigation;
		}

		// get all navigations
		// and get sublevels if required
		else {

			$navigations = false;

			$sql = "SELECT * FROM ".$this->db;
//			print $sql."<br>";
			if($query->sql($sql)) {
				$navigations = $query->results();

				if($levels === false || $levels) {
					foreach($navigations as $i => $navigation) {

						$navigations[$i]["nodes"] = $this->getNavigationNodes($navigation["id"], $_options);

					}
				}
			}

			return $navigations;

		}

	}


	// recursive function to get navigation node tree
	// optional levels of structure to get
	function getNavigationNodes($navigation_id, $_options = false) {


		// default values
		$levels = false;
		$relation = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "levels"            : $levels             = $_value; break;
					case "relation"          : $relation           = $_value; break;
				}
			}
		}

		$query = new Query();
		$IC = new Items();

		// level iterator checker
		$this->level_iterator++;


		$nodes = false;

		// with or without relations
		if(!$relation) {
			$sql = "SELECT * FROM ".$this->db_nodes." WHERE navigation_id = $navigation_id AND relation = 0 ORDER BY position ASC, id ASC";
		}
		else {
			$sql = "SELECT * FROM ".$this->db_nodes." WHERE navigation_id = $navigation_id AND relation = $relation ORDER BY position ASC, id ASC";
		}
//		print $sql."<br>";

		// get media
		if($query->sql($sql)) {

			$results = $query->results();
			foreach($results as $i => $node) {
				$nodes[$i]["id"] = $node["id"];
				$nodes[$i]["name"] = $node["node_name"];
				$nodes[$i]["link"] = $node["node_link"];
				$nodes[$i]["item_id"] = $node["node_page_id"];
				$nodes[$i]["classname"] = $node["node_classname"];

				// get sindex for page
				if($node["node_page_id"]) {
					$page = $IC->getItem(array("id" => $node["node_page_id"]));
					$nodes[$i]["sindex"] = $page["sindex"];
				}

				// go deeper?
				if($levels === false || $levels > $this->level_iterator) {
					$_options["relation"] = $node["id"];
					$nodes[$i]["nodes"] = $this->getNavigationNodes($navigation_id, $_options);
				}
			}
		}

		$this->level_iterator--;

		return $nodes;
	}


	// get specific navigation node information
	function getNode($id) {

		$query = new Query();
		$sql = "SELECT * FROM ".$this->db_nodes." WHERE id = $id";
//		print $sql."<br>";
		if($query->sql($sql)) {
			return $query->result(0);
		}

	}


	// save node
	function saveNode($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

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

		// Get posted values to make them available for models
		$this->getPostedEntities();

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