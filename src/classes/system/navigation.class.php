<?php
/**
* @package janitor.navigation
* This file contains Navigation maintenance functionality
*/

/**
* TypeNews
*/
class Navigation extends Model {


	public $db;
	public $db_nodes;
	private $level_iterator;


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		$this->db = UT_NAV; //SITE_DB.".navigation";
		$this->db_nodes = UT_NAV_NODES; // SITE_DB.".navigation_nodes";
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
			"hint_message" => "Type absolute url, starting with / or http://",
			"error_message" => ""
		));


		// node_destination
		$this->addToModel("node_destination", array(
			"type" => "select",
			"label" => "Destination",
			"hint_message" => "Select destination",
			"error_message" => ""
		));

		// // node_path (navigation path to be used as identification for this navigation node)
		// $this->addToModel("node_path", array(
		// 	"type" => "string",
		// 	"label" => "Navigation path",
		// 	"hint_message" => "",
		// 	"error_message" => ""
		// ));


		// node_classname
		$this->addToModel("node_classname", array(
			"type" => "string",
			"label" => "Node classname",
			"hint_message" => "Add a classname to this node",
			"error_message" => ""
		));
		// node_target
		$this->addToModel("node_target", array(
			"type" => "string",
			"label" => "Open link in new window",
			"hint_message" => "Add a target to this link",
			"error_message" => ""
		));
		// node_fallback
		$this->addToModel("node_fallback", array(
			"type" => "string",
			"label" => "Fallback url",
			"hint_message" => "You can provide a fallback link, in case the end user does not have access to the given page. Leave empty to hide link for unautorized users.",
			"error_message" => ""
		));

	}


	/**
	* CONTROLLER FUNCTIONS
	*
	*/

	// save new navigation
	// /janitor/admin/navigation/save
	// gets values from posted model values
	function API_save($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) === 1 && $this->validateList(array("name"))) {

			$name = $this->getProperty("name", "value");


			$result = $this->save([
				"name" => $name,
			]);

			if($result) {
				message()->addMessage("Navigation added");
				return $result;
			}

		}

		message()->addMessage("Navigation could not be saved", array("type" => "error"));
		return false;
	}

	function save($_options = false) {

		$name = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "name"              : $name                 = $_value; break;

				}
			}
		}


		$query = new Query();

		// make sure type tables exist
		// Tables are handled at setup or upgrade to speed up runtime
		// $query->checkDbExistence($this->db);
		// $query->checkDbExistence($this->db_nodes);



			// $entities = $this->data_entities;
		// $names = array();
		// $values = array();
		if($name) {
			// $name = $entities["name"]["value"];
			$handle = superNormalize($name);


			if($handle) {


				$sql = "INSERT INTO ".$this->db." SET name = '$name', handle = '$handle'";
				// debug([$sql]);

				if($query->sql($sql)) {
					return array("item_id" => $query->lastInsertId());
				}
			}

		}

		message()->addMessage("Creating navigation failed", array("type" => "error"));
		return false;
	}

	// delete navigation API
	function API_delete($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) === 1) {

			$navigation_id = getPost("navigation_id");
			if($navigation_id) {


				$result = $this->delete([
					"navigation_id" => $navigation_id,
				]);

				if($result) {
					message()->addMessage("Navigation deleted");
					return $result;
				}

			}

		}

		message()->addMessage("Navigation could not be deleted", array("type" => "error"));
		return false;
	}

	// delete navigation
	function delete($_options = false) {

		$navigation_id = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "navigation_id"              : $navigation_id                 = $_value; break;

				}
			}
		}

		if($navigation_id) {

			$query = new Query();

			// delete from cache
			$sql = "SELECT handle FROM $this->db WHERE id = ".$navigation_id;
			// debug([$sql]);

			if($query->sql($sql)) {

				$handle = $query->result(0, "handle");
				cache()->reset("navigation-".$handle);
			}

			// Delete 
			$sql = "DELETE FROM ".$this->db." WHERE id = ".$navigation_id;
			// debug([$sql]);

			if($query->sql($sql)) {
				return true;
			}
		}

		return false;
	}



	// get specific navigation node information (for edit_node template)
	function getNode($id) {

		$query = new Query();
		$sql = "SELECT * FROM ".$this->db_nodes." WHERE id = $id";
		// debug([$sql]);

		if($query->sql($sql)) {
			return $query->result(0);
		}

	}


	// save node
	// /janitor/admin/navigation/saveNode/#$navigation_id#
	function API_saveNode($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 2 && $this->validateList(array("node_name"))) {

			$node_name = $this->getProperty("node_name", "value");


		}
	}

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
				if($name == "node_target") {
					if($entity["value"]) {
						$values[] = $name."='_blank'";
					}
					
				}
				else if($name == "node_destination") {
					if($entity["value"]) {
						$values[] = $name."='".$entity["value"]."'";
					}
				}
				else if($entity["value"] !== false) {
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db_nodes." SET id = DEFAULT, navigation_id = $navigation_id, "  . implode(",", $values);
				// debug([$sql]);

				if($query->sql($sql)) {

					// delete from cache (will be respawned on next request)
					$sql = "SELECT handle FROM ".$this->db." WHERE id = ".$navigation_id;
					if($query->sql($sql)) {
						$handle = $query->result(0, "handle");
						cache()->reset("navigation-".$handle);
					}


					message()->addMessage("Navigation node created");
					return array("item_id" => $navigation_id);
				}
			}
		}

		message()->addMessage("Navigation node could not be created", array("type" => "error"));
		return false;

	}


	// update node
	// /janitor/admin/navigation/updateNode/#node_id#
	function updateNode($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$node_id = $action[1];
		
		// does values validate
		if(count($action) == 2 && $this->validateList(array("node_name"), $node_id)) {

			// does values validate
			$entities = $this->data_entities;
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				// specific values for target and page references
				if($name == "node_target") {
					if($entity["value"]) {
						$names[] = $name;
						$values[] = $name."='_blank'";
					}
					else {
						$names[] = $name;
						$values[] = $name."=NULL";
					}
				}
				else if($name == "node_item_id") {
					if($entity["value"]) {
						$names[] = $name;
						$values[] = $name."='".$entity["value"]."'";
					}
					else {
						$names[] = $name;
						$values[] = $name."=NULL";
					}
				}
				else if($entity["value"] !== false) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$query = new Query();
				$sql = "UPDATE ".$this->db_nodes." SET ".implode(",", $values)." WHERE id = ".$node_id;
				// debug([$sql]);

				if($query->sql($sql)) {


					// delete from cache (will be respawned on next request)
					$sql = "SELECT ".$this->db.".handle as handle FROM ".$this->db.", ".$this->db_nodes." WHERE ".$this->db_nodes.".id = ".$node_id." AND ".$this->db_nodes.".navigation_id = ".$this->db.".id";
					if($query->sql($sql)) {
						$handle = $query->result(0, "handle");
						cache()->reset("navigation-".$handle);
					}

					message()->addMessage("Navigation node updated");
					return array("item_id" => $query->lastInsertId());
				}
			}
		}

		message()->addMessage("Navigation node could not be updated", array("type" => "error"));
		return false;
	}


	// delete navigation node - 2 parameters exactly
	// /janitor/admin/navigation/deleteNode/#node_id#
	function deleteNode($action) {

		if(count($action) == 2) {

			$query = new Query();
			$node_id = $action[1];

			if($query->sql("DELETE FROM ".$this->db_nodes." WHERE id = ".$node_id)) {

				// delete from cache (will be respawned on next request)
				$sql = "SELECT ".$this->db.".handle as handle FROM ".$this->db.", ".$this->db_nodes." WHERE ".$this->db_nodes.".id = ".$node_id." AND ".$this->db_nodes.".navigation_id = ".$this->db.".id";
				// debug([$sql]);

				if($query->sql($sql)) {
					$handle = $query->result(0, "handle");
					cache()->reset("navigation-".$handle);
				}

				message()->addMessage("Navigation node deleted");
				return true;
			}

		}

		message()->addMessage("Navigation node could not be deleted - refresh your browser", array("type" => "error"));
		return false;

	}


	// update navigation node order
	// /janitor/admin/navigation/updateOrder/".$navigation_id
	function updateOrder($action) {

		if(count($action) == 2) {

			$query = new Query();
			$navigation_id = $action[1];
			$structure = json_decode(prepareForHTML(getPost("structure")), true);

			foreach($structure as $node) {
				
				$sql = "UPDATE ".$this->db_nodes." SET relation = ".$node["relation"].", position = ".$node["position"]." WHERE id = ".$node["id"];
				if(!$query->sql($sql)) {
					message()->addMessage("Node order update failed", array("type" => "error"));
					return false;
				}
			}

			// delete from cache (will be respawned on next request)
			$sql = "SELECT handle FROM ".$this->db." WHERE id = ".$navigation_id;
			if($query->sql($sql)) {
				$handle = $query->result(0, "handle");
				cache()->reset("navigation-".$handle);
			}

			message()->addMessage("Node order updated");
			return true;
		}

		message()->addMessage("Node order could not be updated", array("type" => "error"));
		return false;

	}




	/**
	* Get navigations
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
		// and get sublevels if required
		if($handle) {

			$navigation = false;

			$sql = "SELECT * FROM ".$this->db." WHERE handle = '$handle'";
			// debug([$sql]);
			if($query->sql($sql)) {

				$navigation = $query->result(0);

				// get children
				if($levels === false || $levels) {
					$navigation["nodes"] = $this->getNavigationNodes($navigation["id"], $_options);
				}

			}

			// return navigation
			return $navigation;

		}

		// looking for specific navigation id
		// and get sublevels if required
		else if($navigation_id) {

			$navigation = false;

			$sql = "SELECT * FROM ".$this->db." WHERE id = '$navigation_id'";
			// debug([$sql]);
			if($query->sql($sql)) {

				$navigation = $query->result(0);

				// get children
				if($levels === false || $levels) {
					$navigation["nodes"] = $this->getNavigationNodes($navigation_id, $_options);
				}
			}

			// return navigation
			return $navigation;

		}

		// get all navigations
		// and get sublevels if required
		else if(!$handle && !$navigation_id) {

			$navigations = false;

			$sql = "SELECT * FROM ".$this->db;
			// debug([$sql]);
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
		$nested_path = "";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "levels"            : $levels             = $_value; break;
					case "relation"          : $relation           = $_value; break;

					case "nested_path"       : $nested_path        = $_value; break;
				}
			}
		}

		$query = new Query();
		$IC = new Items();

		// level iterator checker
		$this->level_iterator++;


		$nodes = [];

		// with or without relations
		if(!$relation) {
			$sql = "SELECT * FROM ".$this->db_nodes." WHERE navigation_id = $navigation_id AND relation = 0 ORDER BY position ASC, id ASC";
		}
		else {
			$sql = "SELECT * FROM ".$this->db_nodes." WHERE navigation_id = $navigation_id AND relation = $relation ORDER BY position ASC, id ASC";
		}
		// debug([$sql]);

		// get media
		if($query->sql($sql)) {

			$results = $query->results();
			if($results) {
				// $nodes = [];

				foreach($results as $i => $node) {
					$nodes[$i]["id"] = $node["id"];
					$nodes[$i]["name"] = $node["node_name"];

					$nodes[$i]["target"] = $node["node_target"];
					$nodes[$i]["classname"] = $node["node_classname"];
					$nodes[$i]["fallback"] = $node["node_fallback"];

					// $nodes[$i]["item_id"] = $node["node_item_id"];
					// $nodes[$i]["controller"] = $node["node_item_controller"];

					// get create link for page
					if($node["node_item_id"]) {
						$page = $IC->getItem(array("id" => $node["node_item_id"]));

						// create nested link structure
						$nodes[$i]["link"] = $node["node_item_controller"].$nested_path."/".$page["sindex"];
					}
					// absolute static link
					else {
						$nodes[$i]["link"] = $node["node_link"];
					}

					// go deeper?
					if($levels === false || $levels > $this->level_iterator) {
						$_options["relation"] = $node["id"];

						// update nested paths
						$_options["nested_path"] = $nested_path."/".superNormalize($node["node_name"]);

						// get child nodes
						$nodes[$i]["nodes"] = $this->getNavigationNodes($navigation_id, $_options);
					}
				}
			}

		}

		$this->level_iterator--;

		return $nodes;
	}



	/**
	* Get navigation
	*
	* Get specific navigation based on handle to be used in website
	* Will be returned from cache if available
	*/
	function get($handle) {
 
		// is navigation handle specified and navigation already cached?
		if(cache()->value("navigation-".$handle)) {
			return cache()->value("navigation-".$handle);
		}


		$navigation = $this->getNavigations(["handle" => $handle]);
		if($navigation) {
			// update cache
			cache()->value("navigation-".$handle, $navigation);
		}

		return $navigation;

	}

}

?>