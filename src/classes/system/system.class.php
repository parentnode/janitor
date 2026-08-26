<?php
/**
* This file contains System maintenance functionality
*/
class System extends Model {


	public $db_data;


	function __construct() {

		parent::__construct(get_class());


		$this->db_data = SITE_DB.".system_data";


		// Usergroup
		$this->addToModel("maillist", array(
			"type" => "string",
			"label" => "List title",
			"required" => true,
			"hint_message" => "Make it clear",
			"error_message" => "Invalid maillist name"
		));

		// Site data
		$this->addToModel("data", array(
			"type" => "json",
			"label" => "Dataset",
			"required" => true,
			"hint_message" => "Make it clear",
			"error_message" => "Invalid maillist name"
		));

	}


	// flush entry from cache
	function flushFromCache($action) {

		$cache_key = getPost("cache-key");

		if(count($action) == 1 && $cache_key) {

			cache()->reset($cache_key);

			message()->addMessage("$cache_key flushed from cache");
			return true;
		
		}

		message()->addMessage("Key could not be flushed", array("type" => "error"));
		return false;

	}

	function getDataset($id) {
		// debug(["getDataset", $id, LOCAL_PATH."/templates/janitor/system/data/".$id.".php"]);

		$complete_model = [];

		if(file_exists(LOCAL_PATH."/templates/janitor/system/data/".$id.".php")) {
			include(LOCAL_PATH."/templates/janitor/system/data/".$id.".php");


			$data = $this->getData($id);
			// debug(["getData", $data]);

			foreach($dataset["model"] as $entity => $properties) {

				if(!is_array($dataset["model"][$entity])) {
					$entity = $properties;
					$complete_model[$entity] = [];
				}
				else {
					$complete_model[$entity] = $properties;
				}

				if($data && isset($data[$entity])) {
					$complete_model[$entity]["value"] = $data;
				}

				if(!isset($dataset["model"][$entity]["type"])) {
					$complete_model[$entity]["type"] = "string";
				}
				if(!isset($dataset["model"][$entity]["label"])) {
					$complete_model[$entity]["label"] = $entity;
				}

				if(!isset($dataset["model"][$entity]["hint_message"])) {
					$complete_model[$entity]["hint_message"] = "$entity";
				}
				if(!isset($dataset["model"][$entity]["error_message"])) {
					$complete_model[$entity]["error_message"] = "Invalid $entity";
				}

				if(isset($data[$entity])) {
					$complete_model[$entity]["value"] = $data[$entity];
				}
			}

			$dataset["model"] = $complete_model;

		}
		

		return $dataset;
	}

	function getData($id) {

		$data = cache()->value("dataset-".$id);

		if(!$data) {

			$data = [];

			$query = new Query();
			$sql = "SELECT * FROM ".$this->db_data." WHERE id = '".$id."'";

			if($query->sql($sql)) {
				$data_string = $query->result(0, "data");
				$data = json_decode($data_string, true);

				cache()->value("dataset-".$id, $data);
			}

		}

		return $data;

	}

	function API_updateData($action) {
		// debug([$action]);

		if(count($action) === 1) {

			$id = getPost("id");

			$dataset = $this->getDataset($id);
			$this->data_entities = $dataset["model"];

			$this->getPostedEntities();
		
			if($this->validateAll()) {

				$data = [];
				foreach($dataset["model"] as $entity => $properties) {
					$data[$entity] = $this->data_entities[$entity]["value"];
				}

				$result = $this->updateData($id, $data);

				if($result) {
					message()->addMessage("Data updated");
					return $result;
				}

			}

		}

		message()->addMessage("Data could not be updated", ["type" => "error"]);
		return false;
	}


	function updateData($id, $data) {
		// debug(["updateData", $id, $data]);

		$query = new Query();
		$query->checkDbExistence($this->db_data);

		// Convert $data to JSON if passed as array
		if(is_array($data)) {
			$data = json_encode($data);
		}

		// Check if data row already exists
		$sql = "SELECT id FROM ".$this->db_data." WHERE id = '$id'";
		if($query->sql($sql)) {
			$id = $query->result(0, "id");
			$sql = "UPDATE ".$this->db_data." SET data = '$data', modified_at = CURRENT_TIMESTAMP WHERE id = '$id'";
		}
		else {
			$sql = "INSERT INTO ".$this->db_data." SET id = '$id', data = '$data'";
		}

		// debug([$sql]);
		if($query->sql($sql)) {

			cache()->reset("dataset-".$id);
			return true;
		}

		return false;
	}


	// TODO: add language, country, currency, vatrate, etc maintenance functions here


	function addMaillist($action) {
		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("maillist"))) {

			$query = new Query();

			$maillist = $this->getProperty("maillist", "value");

			$sql = "SELECT * FROM UT_MAILLISTS WHERE name = '$maillist'";
			if(!$query->sql($sql)) {
				$sql = "INSERT INTO ".UT_MAILLISTS." SET name='$maillist'";
				$query->sql($sql);

				cache()->reset("maillists");
			}

			message()->addMessage("Maillist added");
			return array("item_id" => $query->lastInsertId());
		}

		message()->addMessage("Could not add maillist", array("type" => "error"));
		return false;
		
	}


}

?>