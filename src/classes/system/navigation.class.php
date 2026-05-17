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
			"type" => "dropdown",
			"label" => "Link",
			"hint_message" => "Type or select link, starting with / or http://",
			"error_message" => ""
		));


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
	function getNavigationNode($id) {

		$query = new Query();
		$sql = "SELECT * FROM ".$this->db_nodes." WHERE id = $id";
		// debug([$sql]);

		if($query->sql($sql)) {
			return $query->result(0);
		}

	}


	function getLinkOptions() {
		$IC = new Items();


		$link_options = ["" => "Type the desired url or select an item or items list"];


		$controllers = filesystem()->files(LOCAL_PATH."/www", [
			"allow_extensions" => "php",
			"deny_folders" => "js,css,img,assets,janitor",
		]);

		$IC = new Items();
		$read_access = true;
		foreach($controllers as $controller) {

			$link = preg_replace("/\.php$/", "", str_replace(LOCAL_PATH."/www", "", $controller));

			$access_item = [];
			$controller_itemtype = false;
			$controller_favors = false;

			include($controller);
			if($controller_itemtype && $controller_favors) {

				$type_model = $IC->typeObject($controller_itemtype);
				if($type_model) {

					// debug([$link, $itemtype, $controller_favors]);

					foreach($controller_favors as $view => $label) {

						// Maybe exclude 
						// Edit, view – sindex must be added to identify which
						if($view === "view") {

							$items = $IC->getItems([
								"itemtype" => $controller_itemtype, 
								"status" => 1, 
								"order" => $controller_itemtype.".name ASC", 
								"extend" => true
							]);
							foreach($items as $item) {
								$link_options[$link."/".$item["sindex"]] = $item["name"]. " (".$link."/".$item["sindex"].", itemtype: ".$label.")";
							}

						}
						// List, new
						else if($view === "list") {
							$link_options[$link] = $label . " (".$link.")";
						}

					}

				}

			}

			// Unknown controller, expect it to be self-contained (can be called directly without parameters)
			else {

				if($controller_favors && isset($controller_favors["view"])) {
					$link_options[$link] = $controller_favors["view"]."($link)";
				}
				else {
					$link_options[$link] = $link;
				}

			}

		}

		return $link_options;
	}

	// save node
	// /janitor/admin/navigation/saveNode
	function API_saveNode($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("node_name", "node_classname", "node_target", "node_link", "node_fallback"))) {

			$navigation_id = getPost("navigation_id");

			$node_name = $this->getProperty("node_name", "value");
			$node_classname = $this->getProperty("node_classname", "value");
			$node_target = $this->getProperty("node_target", "value");

			$node_link = $this->getProperty("node_link", "value");

			$node_fallback = $this->getProperty("node_fallback", "value");

			$result = $this->saveNode([
				"navigation_id" => $navigation_id,

				"node_name" => $node_name,
				"node_classname" => $node_classname,
				"node_target" => $node_target,

				"node_link" => $node_link,

				"node_fallback" => $node_fallback
			]);

			if($result) {
				message()->addMessage("Navigation node created");
				return $result;
			}

		}

		message()->addMessage("Navigation node could not be created", array("type" => "error"));
		return false;
	}

	function saveNode($_options = false) {
		// debug(["saveNode", $_options]);

		$navigation_id = false;

		$node_name = false;
		$node_classname = false;
		$node_target = false;

		$node_link = false;

		$node_fallback = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "navigation_id"          : $navigation_id             = $_value; break;

					case "node_name"              : $node_name                 = $_value; break;
					case "node_classname"         : $node_classname            = $_value; break;
					case "node_target"            : $node_target               = $_value; break;

					case "node_link"              : $node_link                 = $_value; break;

					case "node_fallback"          : $node_fallback             = $_value; break;

				}
			}
		}

		if($navigation_id && $node_name) {

			$query = new Query();

			$sql = "INSERT INTO ".$this->db_nodes." SET navigation_id = $navigation_id, node_name = '$node_name'";


			if($node_classname !== false) {
				$sql .= ", node_classname = '$node_classname'";
			}
			if($node_target !== false) {
				$sql .= ", node_target = ".($node_target ? "'_blank'" : "NULL");
			}

			// Add static link if applicable
			if($node_link !== false) {
				$sql .= ", node_link = '$node_link'";
			}
			// Add fallback link if applicable
			if($node_fallback !== false) {
				$sql .= ", node_fallback = '$node_fallback'";
			}

			// debug([$sql]);

			if($query->sql($sql)) {

				// delete from cache (will be respawned on next request)
				$sql = "SELECT handle FROM ".$this->db." WHERE id = ".$navigation_id;
				// debug([$sql]);
				if($query->sql($sql)) {
					$handle = $query->result(0, "handle");
					cache()->reset("navigation-".$handle);
				}

				return array("item_id" => $navigation_id);

			}

		}

		return false;

	}


	// update node
	// /janitor/admin/navigation/updateNode
	function API_updateNode($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		$node_id = getPost("node_id");

		// does values validate
		if(count($action) === 1 && $node_id && $this->validateList(array("node_name", "node_classname", "node_target", "node_link", "node_fallback"), $node_id)) {

			$node_name = $this->getProperty("node_name", "value");
			$node_classname = $this->getProperty("node_classname", "value");
			$node_target = $this->getProperty("node_target", "value");

			$node_link = $this->getProperty("node_link", "value");

			$node_fallback = $this->getProperty("node_fallback", "value");

			$result = $this->updateNode([
				"node_id" => $node_id,

				"node_name" => $node_name,
				"node_classname" => $node_classname,
				"node_target" => $node_target,

				"node_link" => $node_link,

				"node_fallback" => $node_fallback
			]);

			if($result) {
				message()->addMessage("Navigation node updated");
				return $result;
			}
		}

		message()->addMessage("Navigation node could not be updated", array("type" => "error"));
		return false;

	}

	function updateNode($_options = false) {
		// debug(["updateNode", $_options]);

		$node_id = false;

		$node_name = false;
		$node_classname = false;
		$node_target = false;

		$node_link = false;

		$node_fallback = false;

		// $node_path = false;
		$node_relation = false;
		$node_position = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "node_id"                : $node_id                   = $_value; break;

					case "node_name"              : $node_name                 = $_value; break;
					case "node_classname"         : $node_classname            = $_value; break;
					case "node_target"            : $node_target               = $_value; break;

					case "node_link"              : $node_link                 = $_value; break;

					case "node_fallback"          : $node_fallback             = $_value; break;

					case "node_relation"          : $node_relation             = $_value; break;
					case "node_position"          : $node_position             = $_value; break;

				}
			}
		}

		if($node_id) {

			$query = new Query();
			$values = [];


			// Add path to extend identification possibilities of navigation nodes
			if($node_name) {
				$values[] = "node_name = '$node_name'";
			}
			if($node_classname !== false) {
				$values[] = "node_classname = '$node_classname'";
			}
			if($node_target !== false) {
				$values[] = "node_target = ".($node_target ? "'_blank'" : "NULL");
			}


			if($node_link !== false) {
				$values[] = "node_link = '$node_link'";
			}

			if($node_fallback !== false) {
				$values[] = "node_fallback = '$node_fallback'";
			}

			if($node_relation !== false) {
				$values[] = "node_relation = $node_relation";
			}
			if($node_position !== false) {
				$values[] = "node_position = $node_position";
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

					return array("item_id" => $query->lastInsertId());
				}
			}
		}

		return false;
	}


	// delete navigation node - 2 parameters exactly
	// /janitor/admin/navigation/deleteNode/#node_id#
	function API_deleteNode($action) {

		if(count($action) == 1) {

			$node_id = getPost("node_id");

			$result = $this->deleteNode([
				"node_id" => $node_id
			]);
			if($result) {
				message()->addMessage("Navigation node deleted");
				return $result;
			}

		}

		message()->addMessage("Navigation node could not be deleted - refresh your browser", array("type" => "error"));
		return false;
	
	}

	function deleteNode($_options = false) {

		$node_fallback = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "node_id"          : $node_id             = $_value; break;

				}
			}
		}

		if($node_id) {

			$query = new Query();

			// delete from cache (will be respawned on next request)
			$sql = "SELECT ".$this->db.".handle AS handle FROM ".$this->db.", ".$this->db_nodes." WHERE ".$this->db_nodes.".id = ".$node_id." AND ".$this->db_nodes.".navigation_id = ".$this->db.".id";
			// debug([$sql]);
			if($query->sql($sql)) {

				$handle = $query->result(0, "handle");
				cache()->reset("navigation-".$handle);

				$sql = "DELETE FROM ".$this->db_nodes." WHERE id = ".$node_id;
				// debug([$sql]);
				if($query->sql($sql)) {
					return true;
				}

			}

		}

		return false;

	}


	// update navigation node order
	// /janitor/admin/navigation/updateOrder"
	function API_updateOrder($action) {

		if(count($action) == 1) {

			$navigation_id = getPost("navigation_id");
			$structure = json_decode(prepareForHTML(getPost("structure")), true);

			$result = $this->updateOrder([
				"navigation_id" => $navigation_id,
				"structure" => $structure
			]);
			if($result) {
				message()->addMessage("Node order updated");
				return $result;
			}

		}

		message()->addMessage("Node order could not be updated", array("type" => "error"));
		return false;

	}

	function updateOrder($_options = false) {
		// debug(["updateOrder", $_options]);

		$navigation_id = false;
		$structure = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "navigation_id"          : $navigation_id             = $_value; break;
					case "structure"              : $structure                 = $_value; break;

				}
			}
		}

		if($navigation_id && $structure) {

			$query = new Query();

			// Structure must be updated first, to easily resolve node paths based on relations
			foreach($structure as $node) {
				
				$sql = "UPDATE ".$this->db_nodes." SET relation = ".$node["relation"].", position = ".$node["position"]." WHERE id = ".$node["id"];
				// debug([$sql]);
				if(!$query->sql($sql)) {
					return false;
				}
			}

			// delete from cache (will be rebuilt on next request)
			$sql = "SELECT handle FROM ".$this->db." WHERE id = ".$navigation_id;
			if($query->sql($sql)) {
				$handle = $query->result(0, "handle");
				cache()->reset("navigation-".$handle);
			}

			// Reset url index cache to make sure any url changes are applied
			// It will be rebuilt on next request
			cache()->reset("url-index");


			return true;
		}

		return false;

	}




	/**
	* Get navigations, used backend view
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
		// $nested_path = "";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "levels"            : $levels             = $_value; break;
					case "relation"          : $relation           = $_value; break;

					// case "nested_path"       : $nested_path        = $_value; break;
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


					// absolute static link
					$nodes[$i]["link"] = $node["node_link"];

					// $nodes[$i]["path"] = $node["node_path"];
					$nodes[$i]["relation"] = $node["relation"];
					$nodes[$i]["position"] = $node["position"];

					// go deeper?
					if($levels === false || $levels > $this->level_iterator) {
						$_options["relation"] = $node["id"];

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
	function get($handle, $levels = false) {
 
		// is navigation handle specified and navigation already cached?
		if(cache()->value("navigation-".$handle)) {
			return cache()->value("navigation-".$handle);
		}


		$navigation = $this->getNavigations(["handle" => $handle, "levels" => $levels]);
		if($navigation) {
			// update cache
			cache()->value("navigation-".$handle, $navigation);
		}

		return $navigation;

	}

}
