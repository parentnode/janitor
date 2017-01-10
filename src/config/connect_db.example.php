<?php

/**
* This file contains generel database connection information and creates a permanent connection to the 
* specified database
*
* Run this Query in your database, to grant the user full privileges to the database
*
* GRANT ALL PRIVILEGES ON database.* TO 'username'@'localhost' IDENTIFIED BY 'password' WITH GRANT OPTION;
*
* @package Config
*/
define("SITE_DB", "");

$page->db_connection(
	array(
		"host" => "", 
		"username" => "", 
		"password" => ""
	)
);

?>