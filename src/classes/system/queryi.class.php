<?php
/**
* Query object
* Handles all query information
*/

$mysqli_global = false;


class Query {

	public $connected;

	private $connection;
	private $result_object;
	private $results;
	private $result;
	private $result_count;
	private $last_query;

	private $error_messages = array(
		1451 => "Item is in use elsewhere! Cannot delete or update a parent row: a foreign key constraint fails."
	);


	function __construct() {

		// database connection
		global $mysqli_global;

		if($mysqli_global) {
			$this->connection = $mysqli_global;
			$this->connected = true;
		}
		else {
			$this->connected = false;
		}



		// ALTERNATIVE IMPLEMENTATION - TOO SLOW
		// global $db;
		//
		// $this->con = new mysqli($db["host"], $db["username"], $db["password"]);
		//
		// if($this->con->connect_errno) {
		//     echo "Failed to connect to MySQL: " . $this->con->connect_error;
		// 	exit();
		// }
		//
		// // correct the database connection setting
		// $this->con->query("SET NAMES utf8");
		// $this->con->query("SET CHARACTER SET utf8");
		// $this->con->set_charset("utf8");

//		print "verify";
	}


	/**
	* Execute SQL query string
	* 
	* @param string $query SQL query
	* @return bool Query success
	*/
	function sql($query) {
		$this->last_query = $query;

//		print $query;

		// get result
		$this->result_object = $this->connection->query($query);

		// get number of results as a means of validating query success
		$this->result_count = (is_object($this->result_object)) ? $this->result_object->num_rows : ($this->result_object ? $this->result_object : 0);

		if($this->result_count) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	* Compile query
	* 
	* Simplified query builder to compile query values as you go and finally transform it to a valid query
	*
	* @param Array $SELECT Array of fields to select
	* @param Array $FROM Array of tables to select from
	* @param Array $WHERE Array of conditions to use when selecting
	* @param String $GROUP_BY field to group by
	* @param Array $ORDER Array of fields to order by
	* @return String Compiled query
	*/
	function compileQuery($SELECT, $FROM, $options = false) {
		$sql = "";

		if($options !== false) {
			foreach($options as $option => $value) {
				switch($option) {
					case "LEFTJOIN"   : $LEFTJOIN    = $value; break;
					case "WHERE"      : $WHERE       = $value; break;
					case "GROUP_BY"   : $GROUP_BY    = $value; break;
					case "HAVING"     : $HAVING      = $value; break;
					case "ORDER"      : $ORDER       = $value; break;
					case "LIMIT"      : $LIMIT       = $value; break;
				}
			}
		}


		// TODO: LEFT JOIN


		if($SELECT) {
			$sql .= "SELECT ";
			$values = "";
			foreach($SELECT as $value) {
				$values .= ($values ? ", " : "") . $value;
			}
			$sql .= $values;
		}

		if($FROM) {
			$sql .= " FROM ";
			$values = "";
			foreach($FROM as $value) {
				$values .= ($values ? ", " : "") . $value;
			}
			$sql .= $values;
		}

		if(isset($LEFTJOIN) && $LEFTJOIN) {

			$values = "";
			foreach($LEFTJOIN as $value) {
				$values .= " LEFT JOIN " . $value;
			}
			$sql .= $values;
		}

		if(isset($WHERE) && $WHERE) {
			$sql .= " WHERE ";
			$values = "";
			foreach($WHERE as $value) {
				$values .= ($values ? " AND " : "") . $value;
			}
			$sql .= $values;
		}

		if(isset($GROUP_BY) && $GROUP_BY) {
			$sql .= " GROUP BY $GROUP_BY";
		}

		if(isset($HAVING) && $HAVING) {
			$sql .= " HAVING $HAVING";
		}

		if(isset($ORDER) && $ORDER) {
			$sql .= " ORDER BY ";
			$values = "";
			foreach($ORDER as $value) {
				$values .= ($values ? ", " : "") . $value;
			}
			$sql .= $values;
		}

		if(isset($LIMIT) && $LIMIT) {
			$sql .= " LIMIT $LIMIT";
		}

		return $sql;
	}

	/**
	* Get id of last insert
	*
	* @return int|false Insert id
	*/
	function lastInsertId() {
		return $this->connection->insert_id;
	}



	/**
	* Get result $i from current query result ressource
	*
	* @param int $i Result index
	* @param string $name Field name
	* @return string value|false Result value, with " replaced by &quot; (for HTML display)
	*/
	function result($i, $name=false) {
		if($i < $this->result_count) {
			if($name) {

				$this->result_object->data_seek($i);
				$row = $this->result_object->fetch_array(MYSQLI_ASSOC);
				return $row[$name];
			}
			// all fields
			else {

				$this->result_object->data_seek($i);
				return $this->result_object->fetch_array(MYSQLI_ASSOC);
			}
		}

		return false;
	}
	
	/**
	* Get results array from current query result ressource
	*
	* @param string $name Optional Field name
	* @return array Result array [result_index][field], with " replaced by &quot; (for HTML display)
	*/
	function results($name=false) {
		$results = array();

		if($this->result_count) {
			// one field
			if($name) {
			    while($result = $this->result_object->fetch_array(MYSQLI_ASSOC)) {
					$results[] = $result[$name];
				}
			}
			// all fields
			else {
				$results = $this->result_object->fetch_all(MYSQLI_ASSOC);
			}
		}

		return $results;
	}


	/**
	* Check query response count
	*
	* @return false | count of results
	*/
	function count() {
		return $this->result_count;
	}

	/**
	* Get count for affected rows in insert, delete and update
	*
	* @return false | count of results
	*/
	function affected() {
		return $this->connection ? $this->connection->affected_rows : 0;
	}

	/**
	* Automated version control
	* Creates a new table + _versions and copies existing data to table before updating
	* @param $item_id Item to be versioned
	* @param $new_values Values to be updated to, to check if there are any changes to version
	*/
	function versionControl($item_id, $new_values) {
		// debug(["versionControl"]);


		$IC = new Items();
		$item = $IC->getItem(array("id" => $item_id));
		$itemtype = $item["itemtype"];
		$model = $IC->typeObject($itemtype);

		// custom versionControl for model?
		if(method_exists($model, "versionControl")) {
			$model->versionControl($item_id);
		}

		// standard implementation
		else {
			$db_table = $model->db;
			list($db, $table) = explode(".", $db_table);

			$version_table = $table."_versions";

			// check if versions table exists
			if(!$this->sql("SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$version_table'")) {

				$db_file = false;

				// look for matching db sql file
				if(file_exists(LOCAL_PATH.'/config/db/'.$table.'.sql')) {
					$db_file = LOCAL_PATH.'/config/db/'.$table.'.sql';
				}
				else if(file_exists(FRAMEWORK_PATH.'/config/db/'.$table.'.sql')) {
					$db_file = FRAMEWORK_PATH.'/config/db/'.$table.'.sql';
				}
				else if(file_exists(FRAMEWORK_PATH.'/config/db/items/'.$table.'.sql')) {
					$db_file = FRAMEWORK_PATH.'/config/db/items/'.$table.'.sql';
				}

				if($db_file) {
	//				print $db_file."<br>";
					$sql = file_get_contents($db_file);
					$sql = str_replace("SITE_DB", SITE_DB, $sql);
					// update table name in SQL string
					$sql = str_replace("item_".$itemtype, "item_".$itemtype."_versions", $sql);

					// add versioned column for timestamp
					$sql = str_replace("PRIMARY KEY", "`versioned` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY", $sql);

	//				print $sql . "##";
					$this->sql($sql);
				}
			}

			// get current item
			$sql = "SELECT * FROM $db_table WHERE item_id = $item_id";
			$this->sql($sql);
			$item = $this->result(0);

			// prepare data for injection
			$data = array();
			foreach($item as $name => $value) {
				if($name != "id") {
					if($value) {
						$data[] = $name."='".prepareForDB($value)."'";
					}
					else {
						$data[] = $name."=DEFAULT";
					}
				}
			}

			// don't store "no change" updates
			$sql = "SELECT * FROM `$db`.`$table` WHERE ".implode(" AND ", $new_values);
//			print $sql."\n";
			if(!$this->sql($sql)) {

				// don't store existing versions
				// TODO: consider this again - it breaks version history but gives less entries - what is preferred?
				$sql = "SELECT * FROM `$db`.`$version_table` WHERE ".implode(" AND ", $data);
				if(!$this->sql($sql)) {
					// insert version data
					$sql = "INSERT INTO `$db`.`$version_table` SET ".implode(",", $data);
					// debug([$sql]);
					$this->sql($sql);
				}

				// but then update versioned timestamp
				else {
					$version_id = $this->result(0, "id");
					$sql = "UPDATE `$db`.`$version_table` SET versioned = CURRENT_TIMESTAMP WHERE id = $version_id";
					$this->sql($sql);
				}
			}

			// TODO: make some algoritm to clean up version tables
		}
	}


	/**
	* Create DB-table if it does not already exist
	* Content classes uses this to auto-add tables when new content is added for the first time
	*
	* @param String $table Table to check existance of
	*/
	function checkDbExistence($table) {

		list($db, $table) = explode(".", $table);
//		$query = new Query();

//		print $db."-".$table."<br>";

		// check if database exists
//		print "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$table'";
		$sql = "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$table'";
		// debug([$sql]);
		if(!$this->sql($sql)) {

//			print "dont exist";

			$db_file = false;

			// look for matching db sql file
			if(file_exists(LOCAL_PATH.'/config/db/'.$table.'.sql')) {
				$db_file = LOCAL_PATH.'/config/db/'.$table.'.sql';
			}
			else if(file_exists(FRAMEWORK_PATH.'/config/db/'.$table.'.sql')) {
				$db_file = FRAMEWORK_PATH.'/config/db/'.$table.'.sql';
			}
			else if(file_exists(FRAMEWORK_PATH.'/config/db/items/'.$table.'.sql')) {
				$db_file = FRAMEWORK_PATH.'/config/db/items/'.$table.'.sql';
			}

			// if(file_exists($_SERVER["LOCAL_PATH"].'/config/db/'.$table.'.sql')) {
			// 	$db_file = $_SERVER["LOCAL_PATH"].'/config/db/'.$table.'.sql';
			// }
			// else if(file_exists($_SERVER["FRAMEWORK_PATH"].'/config/db/'.$table.'.sql')) {
			// 	$db_file = $_SERVER["FRAMEWORK_PATH"].'/config/db/'.$table.'.sql';
			// }
			// else if(file_exists($_SERVER["FRAMEWORK_PATH"].'/config/db/items/'.$table.'.sql')) {
			// 	$db_file = $_SERVER["FRAMEWORK_PATH"].'/config/db/items/'.$table.'.sql';
			// }


			if($db_file) {
//				print $db_file."<br>";
				$sql = file_get_contents($db_file);
				$sql = str_replace("SITE_DB", SITE_DB, $sql);
				//$sql = str_replace("REGIONAL_DB", DB_REG, $sql);
				// debug([$sql]);
				if($this->sql($sql)) {
					return true;
				}
			}
			else {
				print "Could not find sql file for $table. Please check that the file exists and spelling is correct.<br>\n";
				exit();
			}

			print "failed creating database table: $db_file: ".$this->dbError()."<br>\n";
			exit();
		}
	}


	/**
	* Get current Janitor DB version
	*/
	function getDbVersion() {

		$sql = "SELECT db_version FROM ".UT_VERSION;
		// debug([$sql]);
		if($this->sql($sql)) {
			return $this->result(0, "db_version");

		}
		else {
			return "0";
		}
	}

	/**
	* Update Janitor DB version
	*/
	function updateDbVersion($version) {

		$sql = "SELECT id FROM ".UT_VERSION." LIMIT 1";
		// debug([$sql]);
		if($this->sql($sql)) {
			$sql = "UPDATE ".UT_VERSION." SET db_version = '$version' WHERE id = " . $this->result(0, "id");
			return $this->sql($sql);
		}
		else {
			$sql = "INSERT INTO ".UT_VERSION." SET db_version = '$version'";
			return $this->sql($sql);
		}
	}


	/**
	*	Simple debug function, printing basic result info
	*/
	function debug() {
		print("###DEBUG###\n");
		print("Result:".$this->result_object."\n");
		print("Result count:".$this->result_count."\n");
		print("Query:".$this->last_query."\n");

		print_r($this->result_object->fetch_all(MYSQLI_ASSOC));
	}


	/**
	* Returns a database error message
	*
	* @return string
	*/
	function dbError() {

		$error_id = $this->connection->errno;
		$_ = 'DB Error ' . $error_id . ': ';

		if(array_key_exists($error_id, $this->error_messages)) {
			$_ .= $this->error_messages[$error_id];
		}
		else {
			$_ .= $this->connection->error;
		}
		$_ = str_replace('"','&quot;',$_);
		$_ = str_replace("'", '&quot;', $_);
		return $_;
	}


}

?>
