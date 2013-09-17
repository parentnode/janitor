<?php
/**
* Query object
* Handles all query information
* Extended by template
*/

class Query {

	private $result;
	private $result_count;
	private $last_query;

	private $error_messages = array(
		1451 => "Item is in use elsewhere! Cannot delete or update a parent row: a foreign key constraint fails."
	);

	/**
	* Execute SQL query string
	* 
	* @param string $query SQL query
	* @return bool Query success
	*/
	function sql($query) {
		$this->last_query = $query;

		// get result
		$this->result_resource = mysql_query($query);
//		print mysql_errno()."<br>";
		// get number of results
		$this->result_count = (is_resource($this->result_resource)) ? mysql_num_rows($this->result_resource) : ($this->result_resource ? $this->result_resource : 0);

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
					case "WHERE" : $WHERE = $value; break;
					case "GROUP_BY" : $GROUP_BY = $value; break;
					case "ORDER" : $ORDER = $value; break;
				}
			}
		}

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

		if(isset($ORDER) && $ORDER) {
			$sql .= " ORDER BY ";
			$values = "";
			foreach($ORDER as $value) {
				$values .= ($values ? ", " : "") . $value;
			}
			$sql .= $values;
		}

		return $sql;
	}

	/**
	* Get id of last insert
	*
	* @return int|false Insert id
	*/
	function lastInsertId() {
		return mysql_insert_id();
	}

	/**
	* Does the result have field with name = $name
	*
	* @param string $name Field name
	* @return bool
	*/
	/*
	function resultHasField($name) {
		$n = mysql_num_fields($this->result_resource);
		for($i = 0; $i < $n; $i++) {
			if(mysql_field_name($this->result_resource, $i) == $name) {
				return true;
			}
		}
		return false;
	}
	*/

	/**
	* Get result $i from current query result ressource
	*
	* @param int $i Result index
	* @param string $name Field name
	* @return string value|false Result value, with " replaced by &quot; (for HTML display)
	*/
	function result($i, $name) {
		if($i < $this->result_count){
			return mysql_result($this->result_resource, $i, $name);
		}
		else {
			return false;
		}
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
				$fields = array($name);
			}
			// all fields
			else {
				$nfields = mysql_num_fields($this->result_resource);
				for($n = 0; $n < $nfields; $n++) {
					$fields[] = mysql_field_name($this->result_resource, $n);
				}
			}

			for($i = 0; $i < $this->result_count; $i++) {
				foreach($fields as $field) {
					$results[$i][$field] = mysql_result($this->result_resource, $i, $field);
				}
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
	* Create DB-table if it does not already exist
	* Content classes uses this to auto-add tables when new content is added for the first time
	*
	* @param String $table Table to check existance of
	*/
	function checkDbExistance($table) {

		list($db, $table) = explode(".", $table);
//		$query = new Query();

//		print $db."-".$table."<br>";

		// check if database exists
//		print "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$table'";
		if(!$this->sql("SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$table'")) {

//			print "dont exist";

			if(file_exists($_SERVER["LOCAL_PATH"].'/config/db/'.$table.'.sql')) {
				$db_file = $_SERVER["LOCAL_PATH"].'/config/db/'.$table.'.sql';
			}
			else if(file_exists($_SERVER["FRAMEWORK_PATH"].'/config/db/'.$table.'.sql')) {
				$db_file = $_SERVER["FRAMEWORK_PATH"].'/config/db/'.$table.'.sql';
			}


			if($db_file) {
//				print $db_file."<br>";
				$sql = file_get_contents($db_file);
				$sql = str_replace("SITE_DB", SITE_DB, $sql);
				//$sql = str_replace("REGIONAL_DB", DB_REG, $sql);
//				print $sql . "##";
				if($this->sql($sql)) {
					return true;
				}
			}

			print "failed creating database table<br>";
			exit();
		}
	}




	function get($table, $order=false) {

		$items = array();

		$order = $order ? " ORDER BY $order" : "";

		print "SELECT * FROM $table".$order;
		$this->sql("SELECT * FROM $table".$order);
		
		$test = mysql_fetch_array($this->result_resource);
		print_r($test);

		for($i = 0; $i < $this->count(); $i++) {

			$item = array();

			$items["id"][$i] = $this->result($i, "id");
			$items["values"][$i] = $this->result($i, "name");
			for($u = 0; $u < count($extended_values); $u++) {
				$items[$extended_values[$u]][$i] = $this->result($i, $extended_values[$u]);
			}
		}

		if(!count($items)) {
			return false;
		}
		else if($which) {
			return $items[$which];
		}
		else {
			return $items;
		}
	}


	/**
	*	Simple debug function, printing basic result info
	*/
	function debug() {
		print("###DEBUG###\n");
		print("Result:".$this->result_resource."\n");
		print("Result count:".$this->result_count."\n");
		print("Query:".$this->last_query."\n");

		$n = mysql_num_fields($this->result_resource);
		for($i = 0; $i < $n; $i++) {
			print mysql_field_name($this->result_resource, $i)."\n";
		}

	}





	/**
	* Returns a database error message
	*
	* @return string
	*/
	function dbError() {
		$error_id = mysql_errno();
		$_ = 'DB Error ' . $error_id . ': ';

		if(array_key_exists($error_id, $this->error_messages)) {
			$_ .= $this->error_messages[$error_id];
		}
		else {
			$_ .= mysql_error();
		}
		$_ = str_replace('"','&quot;',$_);
		$_ = str_replace("'", '&quot;', $_);
		return $_;
	}

}

?>
