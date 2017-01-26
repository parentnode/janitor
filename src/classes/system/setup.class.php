<?php
/**
* This file contains the site setup functionality.
*/
class Setup extends Itemtype {


	/**
	* Get required information
	*/
	function __construct() {

		parent::__construct(get_class());


		// SOFTWARE CHECKS
		$this->apache = false;
		$this->php = false;
		$this->readwrite = false;
		$this->mysql = false;
		$this->ffmpeg = false;
		$this->wkhtmlto = false;

		// PHP modules
		$this->zip = false;
		$this->curl = false;
		$this->memcached = false;
		$this->imagemagick = false;
		$this->simplexml = false;
		$this->mbstring = false;
		$this->session = false;
		$this->dom = false;

		$this->software_ok = isset($_SESSION["SOFTWARE_INFO"]) ? $_SESSION["SOFTWARE_INFO"] : "";


		// CONFIG VALUES
		$this->project_path = isset($_SESSION["project_path"]) ? $_SESSION["project_path"] : "";
		$this->site_name = isset($_SESSION["site_name"]) ? $_SESSION["site_name"] : "";
		$this->site_uid = isset($_SESSION["site_uid"]) ? $_SESSION["site_uid"] : "";

		$this->site_email = isset($_SESSION["site_email"]) ? $_SESSION["site_email"] : "";
		$this->site_description = isset($_SESSION["site_description"]) ? $_SESSION["site_description"] : "";

		// CONFIG CHECKS
		$this->config_ok = isset($_SESSION["CONFIG_INFO"]) ? $_SESSION["CONFIG_INFO"] : "";



		// DATABASE VALUES
		$this->db_host = isset($_SESSION["db_host"]) ? $_SESSION["db_host"] : "";
		$this->db_root_user = isset($_SESSION["db_root_user"]) ? $_SESSION["db_root_user"] : "";
		$this->db_root_pass = isset($_SESSION["db_root_pass"]) ? $_SESSION["db_root_pass"] : "";

		$this->db_janitor_db = isset($_SESSION["db_janitor_db"]) ? $_SESSION["db_janitor_db"] : "";
		$this->db_janitor_user = isset($_SESSION["db_janitor_user"]) ? $_SESSION["db_janitor_user"] : "";
		$this->db_janitor_pass = isset($_SESSION["db_janitor_pass"]) ? $_SESSION["db_janitor_pass"] : "";

		// DATABASE CHECKS
		$this->db_ok = isset($_SESSION["DATABASE_INFO"]) ? $_SESSION["DATABASE_INFO"] : "";
		$this->db_exists = false;



		// MAIL VALUES
		$this->mail_admin = isset($_SESSION["mail_admin"]) ? $_SESSION["mail_admin"] : "";
		$this->mail_host = isset($_SESSION["mail_host"]) ? $_SESSION["mail_host"] : "";
		$this->mail_port = isset($_SESSION["mail_port"]) ? $_SESSION["mail_port"] : "";
		$this->mail_username = isset($_SESSION["mail_username"]) ? $_SESSION["mail_username"] : "";
		$this->mail_password = isset($_SESSION["mail_password"]) ? $_SESSION["mail_password"] : "";

		// MAIL CHECKS
		$this->mail_ok = isset($_SESSION["MAIL_INFO"]) ? $_SESSION["MAIL_INFO"] : "";




		// CONFIG MODEL

		// project_path
		$this->addToModel("project_path", array(
			"type" => "string",
			"label" => "Project path",
			"required" => true,
			"hint_message" => "Absolute path to your project folder.", 
			"error_message" => "Project path must be filled out."
		));
		// site_uid
		$this->addToModel("site_uid", array(
			"type" => "string",
			"label" => "Unique ID",
			"pattern" => "[A-Z]+",
			"required" => true,
			"hint_message" => "3-8 character ID (A-Z) used to identify your current project. Used for cross-project communication and logging.", 
			"error_message" => "Unique ID can only contain uppercase characters from A-Z."
		));
		// site_name
		$this->addToModel("site_name", array(
			"type" => "string",
			"label" => "Site name",
			"required" => true,
			"hint_message" => "Userfriendly name of your project.", 
			"error_message" => "Site name must be filled out."
		));
		// site_email
		$this->addToModel("site_email", array(
			"type" => "email",
			"label" => "Public email",
			"autocomplete" => true,
			"required" => true,
			"hint_message" => "Email to use to communicate with your users. System/bulk emails will use this address as Reply-To.",
			"error_message" => "Public email must be filled out."
		));
		// site_description
		$this->addToModel("site_description", array(
			"type" => "text",
			"label" => "Site description",
			"hint_message" => "Default SEO description og your project. Will be used when no specific page description is available.", 
			"error_message" => "Invalid description."
		));

		// // site_image
		// $this->addToModel("site_image", array(
		// 	"type" => "string",
		// 	"label" => "Site image",
		// 	"required" => true,
		// 	"hint_message" => "Default SM/sharing image for your site. Will be used to generate OG:metadata when no specific image is available.",
		// 	"error_message" => "Invalid string."
		// ));

		// // site_signup
		// $this->addToModel("site_signup", array(
		// 	"type" => "string",
		// 	"label" => "Enable signup",
		// 	"hint_message" => "Enable signup module."
		// ));
		// // site_shop
		// $this->addToModel("site_shop", array(
		// 	"type" => "string",
		// 	"label" => "Enable shop",
		// 	"hint_message" => "Enable shop module."
		// ));
		// // site_subscriptions
		// $this->addToModel("site_subscriptions", array(
		// 	"type" => "string",
		// 	"label" => "Enable shop",
		// 	"hint_message" => "Enable subscription module."
		// ));
		// // site_shop
		// $this->addToModel("site_members", array(
		// 	"type" => "string",
		// 	"label" => "Enable members",
		// 	"hint_message" => "Enable members module."
		// ));




		// DATABASE MODEL

		// db_host
		$this->addToModel("db_host", array(
			"type" => "string",
			"label" => "Database host",
			"autocomplete" => true,
			"required" => true,
			"hint_message" => "Database host. Could be localhost, 127.0.0.1 or a specific IP.", 
			"error_message" => "Host must be filled out."
		));
		// db_root_user
		$this->addToModel("db_root_user", array(
			"type" => "string",
			"label" => "Database Admin username",
			"autocomplete" => true,
			"hint_message" => "Name of user with priviledges to create a new database, typically root.",
			"error_message" => "Database Admin username must be filled out."
		));
		// db_root_pass
		$this->addToModel("db_root_pass", array(
			"type" => "password",
			"label" => "Password",
			"hint_message" => "Password of database admin user. Leave blank if you're not using a root password - and read up on why that is a bad idea.",
			"error_message" => "Admin password must be filled out."
		));
		// db_janitor_db
		$this->addToModel("db_janitor_db", array(
			"type" => "string",
			"label" => "Project database name",
			"pattern" => "[a-zA-Z0-9_]+",
			"max" => 32,
			"required" => true,
			"hint_message" => "Type the name of the database used for this Janitor project. Max 32 characters and only A-Z, a-z, 0-9 and _ (underscore) allowed.",
			"error_message" => "Project database name must be filled out."
		));
		// db_janitor_user
		$this->addToModel("db_janitor_user", array(
			"type" => "string",
			"label" => "Project database username",
			"pattern" => "[a-zA-Z0-9_]+",
			"max" => 16,
			"required" => true,
			"hint_message" => "Type the username you want to grant access to the new database. Max 16 characters and only A-Z, a-z, 0-9 and _ (underscore) allowed.",
			"error_message" => "Project database username must be filled out."
		));
		// db_janitor_pass
		$this->addToModel("db_janitor_pass", array(
			"type" => "password",
			"label" => "Password",
			"required" => true,
			"hint_message" => "Type password for new database user. Cannot be left blank because empty passwords are a bad habit you should end right now :-)",
			"error_message" => "Project database password must be filled out."
		));




		// MAIL MODEL

		// admin_email
		$this->addToModel("mail_admin", array(
			"type" => "email",
			"label" => "Admin email",
			"autocomplete" => true,
			"required" => true,
			"hint_message" => "Email to send system notifications to.", 
			"error_message" => "Admin email must be filled out."
		));
		// mail_host
		$this->addToModel("mail_host", array(
			"type" => "string",
			"label" => "Mail host",
			"autocomplete" => true,
			"required" => true,
			"hint_message" => "Mail host like smtp.gmail.com or smtp.mailgun.org.", 
			"error_message" => "Mail host must be filled out."
		));
		// mail_port
		$this->addToModel("mail_port", array(
			"type" => "string",
			"label" => "Mail port",
			"autocomplete" => true,
			"required" => true,
			"hint_message" => "Mail connection port like 587 or 465.", 
			"error_message" => "Mail port must be filled out."
		));
		// mail_username
		$this->addToModel("mail_username", array(
			"type" => "string",
			"label" => "Mail username",
			"autocomplete" => true,
			"required" => true,
			"hint_message" => "Username for the outgoing mail account.", 
			"error_message" => "Mail username must be filled out."
		));
		// mail_password
		$this->addToModel("mail_password", array(
			"type" => "password",
			"label" => "Mail password",
			"required" => true,
			"hint_message" => "Password for the outgoing mail account.", 
			"error_message" => "Mail password must be filled out."
		));

	}


	// reset setup script values
	function reset() {
		// unset($_SESSION["SOFTWARE_INFO"]);
		// unset($_SESSION["CONFIG_INFO"]);
		// unset($_SESSION["DATABASE_INFO"]);
		// unset($_SESSION["MAIL_INFO"]);

		session()->reset();

		return true;
 	}



	// SOFTWARE

	// is software installed
	function isInstalled($commands, $valid_responses, $escape = true) {

		// try first possible command
		$command = array_shift($commands);

//		print escapeshellcmd($command)."\n";
		if($escape) {
			$cmd_output = shell_exec(escapeshellcmd($command)." 2>&1");
		}
		else {
			$cmd_output = shell_exec($command." 2>&1");
		}
	
//		print $cmd_output;

		foreach($valid_responses as $valid_response) {
			if(preg_match("/".$valid_response."/", $cmd_output)) {
				return $command;
			}
		}

		// still not valid, try next command
		if(count($commands)) {
			return $this->isInstalled($commands, $valid_responses, $escape);
		}

		return false;
	}

	// check software - very simple checks
	// TODO: improve software checks
	function checkSoftware() {

		// check apache
		// $this->apache = $this->isInstalled(array(
		// 	"apachectl -v",
		// 	"/opt/local/apache2/bin/apachectl -v",
		// 	"/usr/sbin/apachectl -v",
		// 	"/opt/sbin/apachectl -v"
		// ), array("Apache\/2\.[23456]{1}"));
		// store identified apache command - used when printing message on finish
		// if($this->apache) {
		// 	$_SESSION["APACHE_COMMAND"] = $this->apache;
		// }
		$this->apache = preg_match("/2\.[2345678]{1}/", $_SERVER["SERVER_SOFTWARE"]);


		// check PHP
		// $this->php = $this->isInstalled(array("php -v"), array("PHP 5.[3456]{1}"));
		$this->php = preg_match("/5\.[345678]{1}|7\./", phpversion());

		// get PHP modules
		$php_modules = get_loaded_extensions();

		// check if mysqli is available
		// $this->mysql = $this->isInstalled(array("/opt/local/bin/mysql5 --version", "/usr/local/bin/mysql5 --version", "/opt/bin/mysql5 --version", "/user/bin/mysql5 --version", "/usr/bin/mysql --version", "/opt/local/lib/mysql56/bin/mysql --version"), array("Distrib 5"));
		$this->mysql = (array_search("mysqlnd", $php_modules) !== false);


		// ImageMagick
		$this->imagemagick = (array_search("imagick", $php_modules) !== false);

		// Session
		$this->session = (array_search("session", $php_modules) !== false);

		// SimpleXML
		$this->simplexml = (array_search("SimpleXML", $php_modules) !== false);

		// DOM
		$this->dom = (array_search("dom", $php_modules) !== false);

		// mbstring
		$this->mbstring = (array_search("mbstring", $php_modules) !== false);


		// Check read/write
		$this->readwrite = $this->readWriteTest();


		// Zip
		$this->zip = (array_search("zip", $php_modules) !== false && $this->isInstalled(array(
			"zip --version"
		), array(
			"This is Zip [3]{1}.[0-9]"
		)));

		// Curl
		$this->curl = (array_search("curl", $php_modules) !== false && $this->isInstalled(array(
			"curl --version"
		), array(
			"curl [67]{1}.[0-9]"
		)));

		
		// Memcached
		$this->memcached = (array_search("memcached", $php_modules) !== false && $this->isInstalled(array(
			"/opt/local/bin/memcached -i",
			"/usr/local/bin/memcached -i",
			"/usr/bin/memcached -i"
		), array(
			"memcached 1.[4-9]"
		)));


		// check ffmpeg
		// wierd version names on windows
		$this->ffmpeg = $this->isInstalled(array(
			"ffmpeg -version", 
			"/opt/local/bin/ffmpeg -version", 
			"/usr/local/bin/ffmpeg -version",
			"/srv/ffmpeg/bin/ffmpeg -version"
		), array(
			"ffmpeg version (2.[1-9]{1}|3.[0-9]{1})",
			"ffmpeg version N-67742-g3f07dd6",
			"ffmpeg version N-67521-g48efe9e"
		));


		// check ffmpeg
		// wierd version names on windows
		$this->wkhtmlto = $this->isInstalled(array(
			"/usr/bin/static_wkhtmltopdf --version",
			"/usr/local/bin/static_wkhtmltopdf --version", 
			"/opt/local/bin/wkhtmltopdf --version",
			"/usr/local/bin/wkhtmltopdf --version", 
			"/usr/bin/wkhtmltopdf --version"
		), array(
			"wkhtmltopdf 0.1[0-9]{1}"
		));



		// if everything is fine
		if(
			$this->apache && 
			$this->php && 
			$this->mysql && 
			$this->imagemagick && 
			$this->session &&
			$this->simplexml &&
			$this->dom &&
			$this->mbstring &&
			$this->readwrite
		):

			$_SESSION["SOFTWARE_INFO"] = true;
			$this->software_ok = true;
			return true;

		else:

			$_SESSION["SOFTWARE_INFO"] = false;
			$this->software_ok = false;
			return false;

		endif;

	}

	// CHECK FOR READ/WRITE ACCESS
	function readWriteTest() {
		$handle = @fopen(PROJECT_PATH."/wr.test", "a+");

		$config_handle = true;
		if(file_exists(LOCAL_PATH."/config/config.php")) {
			$config_handle = @fopen(LOCAL_PATH."/config/config.php", "a+");
			if($config_handle) {
				fclose($config_handle);
			}
		}
		$connect_db_handle = true;
		if(file_exists(LOCAL_PATH."/config/connect_db.php")) {
			$connect_db_handle = @fopen(LOCAL_PATH."/config/connect_db.php", "a+");
			if($connect_db_handle) {
				fclose($connect_db_handle);
			}
		}
		$connect_mail_handle = true;
		if(file_exists(LOCAL_PATH."/config/connect_mail.php")) {
			$connect_mail_handle = @fopen(LOCAL_PATH."/config/connect_mail.php", "a+");
			if($connect_mail_handle) {
				fclose($connect_mail_handle);
			}
		}


		if($handle && $config_handle && $connect_db_handle && $connect_mail_handle) {
			fclose($handle);
			unlink(PROJECT_PATH."/wr.test");


			return true;
		}
		return false;
	}



	// CONFIG

	// check config settings
	function checkConfigSettings() {

 		if(
			$this->project_path && file_exists($this->project_path) &&
			$this->site_uid && 
			$this->site_name && 
			$this->site_email
		) {

			$_SESSION["CONFIG_INFO"] = true;
			$this->config_ok = true;
			return true;
		}
		// config exists but was not loaded
		else if(!defined("SITE_UID") && file_exists(LOCAL_PATH."/config/config.php")) {

			$config_info = file_get_contents(LOCAL_PATH."/config/config.php");


			$this->project_path = stringOr($this->project_path, PROJECT_PATH);

			preg_match("/\n[ \t]*define\(\"SITE_UID\",[ ]*\"(.+)\"\);/", $config_info, $matches);
			if($matches) {
				$this->site_uid = $matches[1];
			}

			preg_match("/\n[ \t]*define\(\"SITE_NAME\",[ ]*\"(.+)\"\);/", $config_info, $matches);
			if($matches) {
				$this->site_name = $matches[1];
			}

			preg_match("/\n[ \t]*define\(\"SITE_EMAIL\",[ ]*\"(.+)\"\);/", $config_info, $matches);
			if($matches) {
				$this->site_email = $matches[1];
			}

			preg_match("/\n[ \t]*define\(\"DEFAULT_PAGE_DESCRIPTION\",[ ]*\"(.+)\"\);/", $config_info, $matches);
			if($matches) {
				$this->site_description = $matches[1];
			}

		}
		// get default or existing values
		else {

			$this->project_path = stringOr($this->project_path, PROJECT_PATH);
			$this->site_name = stringOr($this->site_name, defined("SITE_NAME") ? SITE_NAME : preg_replace("/\.[^\.]*$/", "", $_SERVER["SERVER_NAME"]));
			$this->site_uid = stringOr($this->site_uid, defined("SITE_UID") ? SITE_UID : substr(strtoupper(preg_replace("/[AEIOUYaeiouy-]/", "", superNormalize($this->site_name))), 0, 8));
			$this->site_email = stringOr($this->site_email, defined("SITE_EMAIL") ? SITE_EMAIL : "");
			$this->site_description = stringOr($this->site_description, defined("DEFAULT_PAGE_DESCRIPTION") ? DEFAULT_PAGE_DESCRIPTION : "");

		}

		if(!file_exists($this->project_path)) {
			message()->addMessage("Invalid project path", array("type" => "error"));
		}

		return false;
	}

	// update the config settings
 	function updateConfigSettings() {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if($this->validateList(array("project_path", "site_uid", "site_name", "site_email"))) {

			$this->project_path       = $_SESSION["project_path"]       = $this->getProperty("project_path", "value"); 

			$this->site_uid           = $_SESSION["site_uid"]           = $this->getProperty("site_uid", "value");
			$this->site_name          = $_SESSION["site_name"]          = $this->getProperty("site_name", "value");
			$this->site_email         = $_SESSION["site_email"]         = $this->getProperty("site_email", "value");

			$this->site_description   = $_SESSION["site_description"]   = $this->getProperty("site_description", "value");

		}

		return $this->checkConfigSettings();

	}



	// DATABASE

	// check for database settings and connection
	function checkDatabaseSettings() {

		// if we do not have stored db info, attempt to read existing connect_db.php
		if(!$this->db_janitor_db && file_exists(LOCAL_PATH."/config/connect_db.php")) {

			$connection_info = file_get_contents(LOCAL_PATH."/config/connect_db.php");

			preg_match("/\"host\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->db_host = $matches[1];
			}

			preg_match("/\"SITE_DB\", \"([a-zA-Z0-9\.\-\_]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->db_janitor_db = $matches[1];
			}

			preg_match("/\"username\" \=\> \"([a-zA-Z0-9\.\-\_]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->db_janitor_user = $matches[1];
			}

			preg_match("/\"password\" \=\> \"([a-zA-Z0-9\.\-\_]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->db_janitor_pass = $matches[1];
			}

		}
		// set default values
		else {

			$this->db_host = stringOr($this->db_host, "127.0.0.1");
			$this->db_root_user = stringOr($this->db_root_user, "root");

			$this->db_janitor_db = stringOr($this->db_janitor_db, preg_replace("/[-]/", "_", superNormalize($this->site_name)));
			$this->db_janitor_user = stringOr($this->db_janitor_user, substr(preg_replace("/[-]/", "", superNormalize($this->site_name)), 0, 16));

		}

		// check aquired database settings
		return $this->checkDatabaseConnection();

	}

	// check database connection
	function checkDatabaseConnection() {
//		unset($_SESSION["DATABASE_INFO"]);

		// do we have enough information to check root login
		if(
			$this->db_host && 
			$this->db_root_user && 
//			$this->db_root_pass && 
			$this->db_janitor_db && 
			$this->db_janitor_user && 
			$this->db_janitor_pass
		) {

			$mysqli = @new mysqli($this->db_host, $this->db_root_user, $this->db_root_pass);
			if(!$mysqli->connect_errno) {

				// correct the database connection setting
				$mysqli->query("SET NAMES utf8");
				$mysqli->query("SET CHARACTER SET utf8");
				$mysqli->set_charset("utf8");

				global $mysqli_global;
				$mysqli_global = $mysqli;

			}
			// check user login
			else {

				$mysqli = @new mysqli($this->db_host, $this->db_janitor_user, $this->db_janitor_pass);
				if(!$mysqli->connect_errno) {

					// correct the database connection setting
					$mysqli->query("SET NAMES utf8");
					$mysqli->query("SET CHARACTER SET utf8");
					$mysqli->set_charset("utf8");

					global $mysqli_global;
					$mysqli_global = $mysqli;

				}
				else {

					$this->db_connection_error = true;
				}

			}

		}


		// check connection
		$query = new Query();

		// is connection information valid
		if($this->db_janitor_db && $query->connected && (!isset($this->db_connection_error) || !$this->db_connection_error)) {

			$db_temp_create = false;
			$this->db_exists = false;


			// test if DB exists
			$sql = "USE `".$this->db_janitor_db."`";
	//		print $sql."<br>".$query->sql($sql)."<br>";
			if($query->sql($sql)) {

//				print "exists";
				$this->db_exists = true;

			}
			// otherwise attempt creating it
			else if($query->sql("CREATE DATABASE $this->db_janitor_db")) {

				$sql = "USE `".$this->db_janitor_db."`";
		//		print $sql."<br>".$query->sql($sql)."<br>";
				$query->sql($sql);

				$db_temp_create = true;

			}

			// if db exists (either because it existed or because temp db was successfully created)
			if($this->db_exists || $db_temp_create) {

//				print "we have a db";
				// test if we can create new table in database
				$sql = "CREATE TABLE `janitor_db_test` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	//			print $sql."<br>".$query->sql($sql)."<br>";
				if($query->sql($sql)) {

//					print "temp table created";

					$query->sql("DROP TABLE `".$this->db_janitor_db."`.`janitor_db_test`");

					$this->db_ok = true;
					$_SESSION["DATABASE_INFO"] = true;

					// delete temporary database again
					if($db_temp_create) {

						$query->sql("DROP DATABASE `".$this->db_janitor_db."`");

					}

//					print "database is just fine<br>\n";
					return true;
				}
				
			}

		}

		// we still need more/correct info
		$this->db_ok = false;
		$_SESSION["DATABASE_INFO"] = false;

		return false;
	}

	// update the database settings
 	function updateDatabaseSettings() {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if($this->validateList(array("db_host", "db_root_user", "db_root_pass", "db_janitor_db", "db_janitor_user", "db_janitor_pass"))) {

			$entities = $this->data_entities;

			$this->db_host         = $_SESSION["db_host"]         = $entities["db_host"]["value"];
			$this->db_root_user    = $_SESSION["db_root_user"]    = $entities["db_root_user"]["value"];
			$this->db_root_pass    = $_SESSION["db_root_pass"]    = $entities["db_root_pass"]["value"];
			$this->db_janitor_db   = $_SESSION["db_janitor_db"]   = $entities["db_janitor_db"]["value"];
			$this->db_janitor_user = $_SESSION["db_janitor_user"] = $entities["db_janitor_user"]["value"];
			$this->db_janitor_pass = $_SESSION["db_janitor_pass"] = $entities["db_janitor_pass"]["value"];

		}

		$check_db = $this->checkDatabaseConnection();

//		print "check_db:" . $check_db . ", exists:". $this->db_exists. ", force:" . getPost("force_db") . ", db_janitor_db:" . $_SESSION["db_janitor_db"] . "<br>\n";

		if($check_db && (!$this->db_exists || getPost("force_db") == $_SESSION["db_janitor_db"])) {
			return true;
		}
		else if($check_db && $this->db_exists) {
			return array("db_exists" => true);
		}

		if(isset($this->db_connection_error) && $this->db_connection_error) {
			message()->addMessage("Cannot connect to your local MySQL/MariaDB with the information provided", array("type" => "error"));
		}
		else {
			message()->addMessage("Insufficient privileges for database creation", array("type" => "error"));
		}
		return false;

	}



	// MAIL

	// check mail settings
	function checkMailSettings() {

		// if we do not have stored db info, attempt to read existing connect_db.php
		if(!$this->mail_host && file_exists(LOCAL_PATH."/config/connect_mail.php")) {

			$connection_info = file_get_contents(LOCAL_PATH."/config/connect_mail.php");

			preg_match("/\"ADMIN_EMAIL\", \"([a-zA-Z0-9\.\-\_\@]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->mail_admin = $matches[1];
			}

			preg_match("/\"host\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->mail_host = $matches[1];
			}

			preg_match("/\"port\" \=\> \"([0-9]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->mail_port = $matches[1];
			}

			preg_match("/\"username\" \=\> \"([a-zA-Z0-9\.\_\@\-]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->mail_username = $matches[1];
			}

			preg_match("/\"password\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->mail_password = $matches[1];
			}

		}
		else {

			$this->mail_admin = stringOr($this->mail_admin, $this->site_email);
			$this->mail_host = stringOr($this->mail_host, "smtp.gmail.com");
			$this->mail_port = stringOr($this->mail_port, "587");
			
		}

 		if($this->mail_admin && $this->mail_host && $this->mail_port && $this->mail_username && $this->mail_password) {

			$_SESSION["MAIL_INFO"] = true;
			$this->mail_ok = true;
			return true;
		}

		return false;
	}

	// update the mail settings
 	function updateMailSettings() {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if($this->validateList(array("mail_admin", "mail_host", "mail_port", "mail_username", "mail_password"))) {

			$entities = $this->data_entities;

			$this->mail_admin    = $_SESSION["mail_admin"]    = $entities["mail_admin"]["value"];
			$this->mail_host     = $_SESSION["mail_host"]     = $entities["mail_host"]["value"];
			$this->mail_port     = $_SESSION["mail_port"]     = $entities["mail_port"]["value"];
			$this->mail_username = $_SESSION["mail_username"] = $entities["mail_username"]["value"];
			$this->mail_password = $_SESSION["mail_password"] = $entities["mail_password"]["value"];

		}

		return $this->checkMailSettings();

	}



	// FINISH

	// check ALL settings
	function checkAllSettings() {

		if(
			$this->config_ok &&
//			($this->config_ok || SETUP_TYPE == "init") &&
			$this->software_ok &&
			$this->db_ok &&
			$this->mail_ok &&
			defined("LOCAL_PATH") &&
			defined("FRAMEWORK_PATH")
		) {

			$this->project_path = PROJECT_PATH;
			$this->local_path =  LOCAL_PATH;
			$this->framework_path = FRAMEWORK_PATH;


			// get apache user to set permissions
			$this->current_user = get_current_user();
			$this->apache_user = trim(shell_exec('whoami'));
			$this->deploy_user = trim(shell_exec('egrep -i "^deploy" /etc/group')) ? "deploy" : (trim(shell_exec('egrep -i "^staff" /etc/group')) ? "staff" : $this->current_user);

			// find apachectl's
			$this->apachectls = explode("\n", trim(shell_exec("find /usr /opt /Users/".$this->current_user."/Applications -name 'apachectl' 2>/dev/null")));

			return true;
		}

		return false;
	}

	// finish installation
	function finishInstallation() {


		// only continue if all checks OK
		if($this->checkAllSettings()) {

			global $page;

			// process status
			$tasks = array("completed" => array(), "failed" => array());


			$fs = new FileSystem();


			// ONLY FOR NEW SETUP
			if(SETUP_TYPE == "new") {



				// INSTALL THEME FROM GITHUB

				// Download theme
				$url = "https://github.com/parentnode/janitor-theme-minimal/archive/master.tar.gz";
				$zip_file = PROJECT_PATH."/theme.tar.gz";
				$fp = fopen($zip_file, "w");

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_FAILONERROR, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
				curl_setopt($ch, CURLOPT_FILE, $fp);
				$success = curl_exec($ch);
				curl_close($ch);
				fclose($fp);

				// Extract
				$output = shell_exec("tar -xzf ".PROJECT_PATH."/theme.tar.gz -C ".PROJECT_PATH." 2>&1");

				// Replace existing theme
				$fs->removeDirRecursively(PROJECT_PATH."/theme");
				$fs->copy(PROJECT_PATH."/janitor-theme-minimal-master", PROJECT_PATH."/theme");

				// Clean up
				$fs->removeDirRecursively(PROJECT_PATH."/janitor-theme-minimal-master");
				unlink(PROJECT_PATH."/theme.tar.gz");


				// Status for installing theme
				if(file_exists(PROJECT_PATH."/theme") && file_exists(PROJECT_PATH."/theme/config/config.template.php")) {
					$tasks["completed"][] = "Installing standard theme";
				}
				// Task failed
				else {
					$tasks["failed"][] = "Installing standard theme (FAILED)";
					return $tasks;
				}



				// APACHE CONF

				// Create Apache conf from template
				if(file_exists(FRAMEWORK_PATH."/config/httpd-vhosts.template.conf")) {

					// apache
					$file_apache = file_get_contents(FRAMEWORK_PATH."/config/httpd-vhosts.template.conf");
					$file_apache = preg_replace("/###LOCAL_PATH###/", $this->local_path, $file_apache);
					$file_apache = preg_replace("/###FRAMEWORK_PATH###/", $this->framework_path, $file_apache);
					$file_apache = preg_replace("/###PROJECT_PATH###/", $this->project_path, $file_apache);
					$file_apache = preg_replace("/###SITE_URL###/", $_SERVER["SERVER_NAME"], $file_apache);
					$file_apache = preg_replace("/###LOG_NAME###/", superNormalize($_SERVER["SERVER_NAME"]), $file_apache);
					$file_apache = preg_replace("/###MODULES_PATH###/", (preg_match("/\/submodules\//", $this->framework_path) ? "submodules" : "core"), $file_apache);
					file_put_contents(PROJECT_PATH."/apache/httpd-vhosts.conf", $file_apache);

//					unlink(LOCAL_PATH."/config/httpd-vhosts.template.conf");

					// Status for updating Apache conf
					$tasks["completed"][] = "Updating project Apache configuration";

				}

			}


			// FOR ALL SETUP TYPES


			// CONFIG
			// config.php

			// don't re-write configs on reloads 
			if(getPost("setup_type") != "reload") {

				// Use existing config.php
				if(file_exists(LOCAL_PATH."/config/config.php")) {

					$file_config = file_get_contents(LOCAL_PATH."/config/config.php");
					$file_config = preg_replace("/(\n)[ \t]*define\(\"SITE_UID\",[ ]*\".+\"\);/", "\ndefine(\"SITE_UID\", \"".$this->site_uid."\");", $file_config);
					$file_config = preg_replace("/(\n)[ \t]*define\(\"SITE_NAME\",[ ]*\".+\"\);/", "\ndefine(\"SITE_NAME\", \"".$this->site_name."\");", $file_config);
					$file_config = preg_replace("/(\n)[ \t]*define\(\"SITE_EMAIL\",[ ]*\".+\"\);/", "\ndefine(\"SITE_EMAIL\", \"".$this->site_email."\");", $file_config);
					$file_config = preg_replace("/(\n)[ \t]*define\(\"DEFAULT_PAGE_DESCRIPTION\",[ ]*\".+\"\);/", "\ndefine(\"DEFAULT_PAGE_DESCRIPTION\", \"".$this->site_description."\");", $file_config);
					file_put_contents(LOCAL_PATH."/config/config.php", $file_config);

					// Status for updating config.php
					$tasks["completed"][] = "Updating config.php";

				}
				// If template exists, use that
				else if(file_exists(FRAMEWORK_PATH."/config/config.template.php")) {

					// config
					$file_config = file_get_contents(FRAMEWORK_PATH."/config/config.template.php");
					$file_config = preg_replace("/###SITE_UID###/", $this->site_uid, $file_config);
					$file_config = preg_replace("/###SITE_NAME###/", $this->site_name, $file_config);
					$file_config = preg_replace("/###SITE_EMAIL###/", $this->site_email, $file_config);
					$file_config = preg_replace("/###DEFAULT_PAGE_DESCRIPTION###/", $this->site_description, $file_config);
					file_put_contents(LOCAL_PATH."/config/config.php", $file_config);

					// Make sure file remains writeable even if it is edited manually
					chmod(LOCAL_PATH."/config/config.php", 0666);

					// Remove template
//					unlink(LOCAL_PATH."/config/config.template.php");

					// Status for creating config.php
					$tasks["completed"][] = "Creating config.php";

				}
				else {

					// Status for updating config.php
					$tasks["failed"][] = "config.php not found (FAILED)";
					return $tasks;
				}



				// DATABASE
				// connect_db.php

				// Use existing connect_db.php
				if(file_exists(LOCAL_PATH."/config/connect_db.php")) {

					$file_db = file_get_contents(LOCAL_PATH."/config/connect_db.php");
					$file_db = preg_replace("/(\n)[ \t]*define\(\"SITE_DB\",[ ]*\".+\"\);/", "\ndefine(\"SITE_DB\", \"".$this->db_janitor_db."\");", $file_db);
					$file_db = preg_replace("/(\n)[ \t]*\"host\"[ ]*\=\>[ ]*\".+\"/", "\n\t\t\"host\" => \"".$this->db_host."\"", $file_db);
					$file_db = preg_replace("/(\n)[ \t]*\"username\"[ ]*\=\>[ ]*\".+\"/", "\n\t\t\"username\" => \"".$this->db_janitor_user."\"", $file_db);
					$file_db = preg_replace("/(\n)[ \t]*\"password\"[ ]*\=\>[ ]*\".+\"/", "\n\t\t\"password\" => \"".$this->db_janitor_pass."\"", $file_db);
					file_put_contents(LOCAL_PATH."/config/connect_db.php", $file_db);

					// Status for updating connect_db.php
					$tasks["completed"][] = "Updating connect_db.php";

				}
				// If template exists, use that
				else if(file_exists(FRAMEWORK_PATH."/config/connect_db.template.php")) {

					// database
					$file_db = file_get_contents(FRAMEWORK_PATH."/config/connect_db.template.php");
					$file_db = preg_replace("/###SITE_DB###/", $this->db_janitor_db, $file_db);
					$file_db = preg_replace("/###HOST###/", $this->db_host, $file_db);
					$file_db = preg_replace("/###USERNAME###/", $this->db_janitor_user, $file_db);
					$file_db = preg_replace("/###PASSWORD###/", $this->db_janitor_pass, $file_db);
					file_put_contents(LOCAL_PATH."/config/connect_db.php", $file_db);

					// Make sure file remains writeable even if it is edited manually
					chmod(LOCAL_PATH."/config/connect_db.php", 0666);

					// Remove template
//					unlink(LOCAL_PATH."/config/connect_db.template.php");

					// Status for creating connect_db.php
					$tasks["completed"][] = "Creating connect_db.php";

				}
				else {

					// Status for updating config.php
					$tasks["failed"][] = "connect_db.php not found (FAILED)";
					return $tasks;
				}


				// CREATE DB

				// only create if it does not exist
				if($this->checkDatabaseConnection() && !$this->db_exists) {

					$query = new Query();
					if($query->sql("CREATE DATABASE $this->db_janitor_db")) {

						$sql = "GRANT ALL PRIVILEGES ON ".$this->db_janitor_db.".* TO '".$this->db_janitor_user."'@'".$this->db_host."' IDENTIFIED BY '".$this->db_janitor_pass."' WITH GRANT OPTION;";
						$query->sql($sql);

						// Status for creating database
						$tasks["completed"][] = "Creating database";

					}
					else {

						// Status for creating database
						$tasks["failed"][] = "Could not create database (FAILED)";
						return $tasks;
					}

				}
				// use existing database
				else {

					// Status for creating database
					$tasks["completed"][] = "Using existing database";
				}


				// Load database configuration
				$page->loadDBConfiguration();



				// MAIL
				// connect_mail.php

				// Use existing connect_mail.php
				if(file_exists(LOCAL_PATH."/config/connect_mail.php")) {

					$file_mail = file_get_contents(LOCAL_PATH."/config/connect_mail.php");
					$file_mail = preg_replace("/(\n)[ \t]*define\(\"ADMIN_EMAIL\",[ ]*\".*\"\);/", "\ndefine(\"ADMIN_EMAIL\", \"".$this->mail_admin."\");", $file_mail);
					$file_mail = preg_replace("/(\n)[ \t]*\"host\"[ ]*\=\>[ ]*\".*\"/", "\n\t\t\"host\" => \"".$this->mail_host."\"", $file_mail);
					$file_mail = preg_replace("/(\n)[ \t]*\"port\"[ ]*\=\>[ ]*\".*\"/", "\n\t\t\"port\" => \"".$this->mail_port."\"", $file_mail);
					$file_mail = preg_replace("/(\n)[ \t]*\"username\"[ ]*\=\>[ ]*\".*\"/", "\n\t\t\"username\" => \"".$this->mail_username."\"", $file_mail);
					$file_mail = preg_replace("/(\n)[ \t]*\"password\"[ ]*\=\>[ ]*\".*\"/", "\n\t\t\"password\" => \"".$this->mail_password."\"", $file_mail);
					file_put_contents(LOCAL_PATH."/config/connect_mail.php", $file_mail);

					// Status for updating connect_mail.php
					$tasks["completed"][] = "Updating connect_mail.php";

				}
				// If template exists, use that
				else if(file_exists(FRAMEWORK_PATH."/config/connect_mail.template.php")) {

					$file_mail = file_get_contents(FRAMEWORK_PATH."/config/connect_mail.template.php");
					$file_mail = preg_replace("/###ADMIN_EMAIL###/", $this->mail_admin, $file_mail);
					$file_mail = preg_replace("/###HOST###/", $this->mail_host, $file_mail);
					$file_mail = preg_replace("/###PORT###/", $this->mail_port, $file_mail);
					$file_mail = preg_replace("/###USERNAME###/", $this->mail_username, $file_mail);
					$file_mail = preg_replace("/###PASSWORD###/", $this->mail_password, $file_mail);
					file_put_contents(LOCAL_PATH."/config/connect_mail.php", $file_mail);


					// Make sure file remains writeable even if it is edited manually
					chmod(LOCAL_PATH."/config/connect_mail.php", 0666);

					// Remove template
//					unlink(LOCAL_PATH."/config/connect_mail.template.php");

					// Status for creating connect_mail.php
					$tasks["completed"][] = "Creating connect_mail.php";

				}
				else {

					// Status for updating config.php
					$tasks["failed"][] = "connect_mail.php not found (FAILED)";
					return $tasks;
				}


				// load mail configuration
				$page->loadMailConfiguration();

			}



			
			// If setup is run on existing projects loadDBConfiguration and loadMailConfiguration will
			// not be updated, because they are included as include_once.
			// If the core settings has been changes the Constants already declared in config.php, 
			// connect_db.php and connect_mail.php are not reflecting the new settings and 
			// we need a whole new request for those to be reloaded
			if((!defined("SITE_DB") || SITE_DB != $this->db_janitor_db) || (!defined("ADMIN_EMAIL") || ADMIN_EMAIL != $this->mail_admin)) {

				if(getPost("setup_type") != "reload") {
					$tasks["completed"][] = "Flushing constants";
					$tasks["reload_constants"] = true;
					return $tasks;
				}
				else {
					$tasks["failed"][] = "Flushing constants";
					return $tasks;
				}

			}

			$tasks["completed"][] = "Constants verified";


			// Define SITE_NAME if not already defined
			if(!defined("SITE_NAME")) {
				define("SITE_NAME", $this->site_name);
			}


			// DEFAULT DATA

			// always make sure public and private folder exists
			$fs->makeDirRecursively(LOCAL_PATH."/library/private");
			$fs->makeDirRecursively(LOCAL_PATH."/library/public");

			$tasks["completed"][] = "Creating library";



			// VERIFY DATABASE TABLES

			$query = new Query();
			$query->checkDbExistence($this->db_janitor_db.".user_groups");
			$query->checkDbExistence($this->db_janitor_db.".system_languages");
			$query->checkDbExistence($this->db_janitor_db.".system_currencies");
			$query->checkDbExistence($this->db_janitor_db.".system_countries");
			$query->checkDbExistence($this->db_janitor_db.".system_vatrates");
			$query->checkDbExistence($this->db_janitor_db.".users");

			$query->checkDbExistence($this->db_janitor_db.".items");
			$query->checkDbExistence($this->db_janitor_db.".tags");
			$query->checkDbExistence($this->db_janitor_db.".taggings");

			$query->checkDbExistence($this->db_janitor_db.".items_mediae");
			$query->checkDbExistence($this->db_janitor_db.".items_comments");
			$query->checkDbExistence($this->db_janitor_db.".items_prices");

			$tasks["completed"][] = "Verifying database tables";



			// DEFAULT DATA

			include_once("classes/system/upgrade.class.php");
			$UP = new Upgrade();

			// CREATE LANGUAGE
			$UP->checkDefaultValues(UT_LANGUAGES, "'DA','Dansk'", "id = 'DA'");
			$UP->checkDefaultValues(UT_LANGUAGES, "'EN','English'", "id = 'EN'");
			// CREATE CURRENCY
			$UP->checkDefaultValues(UT_CURRENCIES, "'DKK', 'Kroner (Denmark)', 'DKK', 'after', 2, ',', '.'", "id = 'DKK'");
			// CREATE COUNTRY
			$UP->checkDefaultValues(UT_COUNTRIES, "'DK', 'Danmark', '45', '#### ####', 'DA', 'DKK'", "id = 'DK'");

			if((defined("SITE_SHOP") && SITE_SHOP)) {
				$UP->checkDefaultValues(UT_VATRATES, "1, 'No VAT', 0, 'DK'", "id = 1");
				$UP->checkDefaultValues(UT_VATRATES, "2, '25%', 25, 'DK'", "id = 2");
			}

			$tasks["completed"][] = "Checking default data";




			//
			// CREATE DEFAULT USER GROUPS AND USERS
			//
			include_once("classes/users/user.core.class.php");
			include_once("classes/users/user.class.php");
			include_once("classes/users/superuser.class.php");
			$UC = new SuperUser();

			$user_groups = $UC->getUserGroups();
			if(!$user_groups) {

				$UP->checkDefaultValues($UC->db_user_groups, "1,'Guest'", "id = 1");
				$UP->checkDefaultValues($UC->db_user_groups, "2,'Member'", "id = 2");
				$UP->checkDefaultValues($UC->db_user_groups, "3,'Developer'", "id = 3");

				$user_groups = $UC->getUserGroups();
				if(count($user_groups) == 3) {
					$tasks["completed"][] = "Creating default user groups";
				}
				else {
					$tasks["failed"][] = "Creating default user groups (FAILED)";
					return $tasks;
				}



				//
				// DEVELOPER PERMISSIONS
				//

				// SET ACCESS PERMISSIONS
				$access_points = $UC->getAccessPoints();
				foreach($access_points["points"] as $controller => $actions) {
					if($actions) {
						foreach($actions as $access_action => $grant) {
							if($grant == 1) {
								$grants[$controller][$access_action] = $grant;
							}
						}
					}
				}
				unset($_POST);
				$_POST["grant"] = $grants;
				$UC->getPostedEntities();
				if($UC->updateAccess(array("updateAccess", 3))) {
					$tasks["completed"][] = "Adding Developer permissions";
				}
				else {
					$tasks["failed"][] = "Adding Developer permissions (FAILED)";
					return $tasks;
				}

			}
			else {
				$tasks["completed"][] = "User groups: OK";
			}



			//
			// DEFAULT USERS
			//

			// check users
			$users = $UC->getUsers();
			if(!$users) {

				
				$UP->checkDefaultValues($UC->db, "1,1,'','','Anonymous',1,'EN',DEFAULT,DEFAULT", "id = 1");
				// ADD DEVELOPER ACCOUNT
				$UP->checkDefaultValues($UC->db, "2,3,'','','Dummy developer',1,'EN',DEFAULT,DEFAULT", "id = 2");


				$users = $UC->getUsers();
				if(count($users) == 1) {

					// SET USERNAME
					unset($_POST);
					$_POST["email"] = ADMIN_EMAIL;
					$UC->getPostedEntities();
					$UC->updateEmail(array("updateEmail", 2));

					// SET PASSWORD
					unset($_POST);
					$_POST["password"] = "123rotinaj";
					$UC->getPostedEntities();
					$UC->setPAssword(array("setPassword", 2));

					// store user_id for content creation
					session()->value("user_id", 2);

					$tasks["completed"][] = "Creating default users";

				}
				else {

					$tasks["failed"][] = "Creating default users";
					return $tasks;
				}

			}
			else {
				$tasks["completed"][] = "Users: OK";
			}


			include_once("classes/items/items.core.class.php");
			include_once("classes/items/items.class.php");
			$IC = new Items();


			//
			// CREATE TEST NAVIGATION
			//
			$NC = new Navigation();
			if(!$NC->getNavigations()) {

				unset($_POST);
				$_POST["name"] = "main";
				$nav = $NC->save(array("save"));

				if($nav) {

					unset($_POST);
					$_POST["node_name"] = "Frontpage";
					$_POST["node_classname"] = "front";
					$_POST["node_link"] = "/";
					$nav_node = $NC->saveNode(array("saveNode", $nav["item_id"]));

					unset($_POST);
					$_POST["node_name"] = "Posts";
					$_POST["node_classname"] = "posts";
					$_POST["node_link"] = "/posts";
					$nav_node = $NC->saveNode(array("saveNode", $nav["item_id"]));

					// If new theme install and theme has demo class
					if(file_exists(LOCAL_PATH."/classes/items/type.demo.class.php") && file_exists(LOCAL_PATH."/www/janitor/demo.php")) {

						unset($_POST);
						$_POST["node_name"] = "Demo";
						$_POST["node_classname"] = "demo";
						$_POST["node_link"] = "/demo";
						$nav_node = $NC->saveNode(array("saveNode", $nav["item_id"]));

					}

				}

			}
			else {
				$tasks["completed"][] = "Navigation: OK";
			}



			//
			// CREATE TEST CONTENT
			//
			if(!$IC->getItems() && session()->value("user_id")) {

				include_once("classes/items/type.post.class.php");
				$PC = new TypePost();


				unset($_POST);
				$_POST["name"] = "Welcome to the basement";
				$_POST["description"] = "This is a test post made by the setup script. You can delete this post.";
				$_POST["html"] = "<p>This is a test post made by the setup script. You can delete this post.</p>";
				$_POST["status"] = 1;
				$item = $PC->save(array("save", "post"));

				// add a tag
				unset($_POST);
				$_POST["tags"] = "post:My first post tag";
				$PC->addTag(array("addTag", $item["id"]));


				// If new theme install and theme has demo class
				if(file_exists(LOCAL_PATH."/classes/items/type.demo.class.php") && file_exists(LOCAL_PATH."/www/janitor/demo.php")) {

					include_once("classes/items/type.demo.class.php");
					$DC = new TypeDemo();

					unset($_POST);
					$_POST["name"] = "This is a demo item";
					$_POST["v_text"] = "This is a demo item made by the setup script. You can delete it.";
					$_POST["v_html"] = "<p>This is a html snippet made by the setup script. You can delete it.</p>";
					$_POST["status"] = 1;
					$item = $DC->save(array("save", "demo"));

					// add a tag
					unset($_POST);
					$_POST["tags"] = "demo:My first demo tag";
					$DC->addTag(array("addTag", $item["id"]));
				}


				$tasks["completed"][] = "Creating test content";

			}
			else {
				$tasks["completed"][] = "Content: OK";
			}




			// Make sure CMS messages are not waiting
			$messages = message()->getMessages(array("type" => "message"));
			if($messages) {
				foreach($messages as $message) {
					$tasks["completed"][] = $message;
				}
			}

			$errors = message()->getMessages(array("type" => "error"));
			if($errors) {
				foreach($errors as $error) {
					$tasks["failed"][] = $error;
				}
			}



			//
			// GIT SETTINGS
			//
			// create git ignore
			if(!file_exists($this->project_path."/.gitignore")) {
				$handle = fopen($this->project_path."/.gitignore", "w+");
				fwrite($handle, "src/library/log/*\nsrc/library/public/*\nsrc/library/private/*\n!src/library/private/0\n!src/library/private/0/*\ntheme/library/log/*\ntheme/library/public/*\ntheme/library/private/*\n!theme/library/private/0\n!theme/library/private/0/*\n\n.DS_Store\n\nsrc/config/connect_*.php\ntheme/config/connect_*.php");
				fclose($handle);

				$tasks["completed"][] = "Creating .gitignore";
			}


			// Tell git to ignore file permission changes
			exec("cd ".$this->project_path." && git config core.filemode false");
			exec("cd ".$this->project_path."/submodules/janitor && git config core.filemode false");
			exec("cd ".$this->project_path."/submodules/js-merger && git config core.filemode false");
			exec("cd ".$this->project_path."/submodules/css-merger && git config core.filemode false");


			$tasks["completed"][] = "Updating git filemode";


			// If this is a new setup
			// Send welcome email with password
			if(SETUP_TYPE == "new") {
				$page->mail(array(
					"subject" => "Welcome to Janitor", 
					"message" => "Your Janitor project is ready.\n\nLog in to your admin system: ".SITE_URL."/janitor\n\nUsername: ".ADMIN_EMAIL."\nPassword: 123rotinaj\n\nSee you soon,\n\nJanitor"
				));
			}


			// TODO: delete session when done testing

			// unset($_SESSION["SOFTWARE_INFO"]);
			//
			// unset($_SESSION["project_path"]);
			// unset($_SESSION["site_name"]);
			// unset($_SESSION["site_uid"]);
			// unset($_SESSION["site_email"]);
			// unset($_SESSION["site_description"]);
			// unset($_SESSION["CONFIG_INFO"]);
			//
			//
			// unset($_SESSION["db_host"]);
			// unset($_SESSION["db_root_user"]);
			// unset($_SESSION["db_root_pass"]);
			// unset($_SESSION["db_janitor_db"]);
			// unset($_SESSION["db_janitor_user"]);
			// unset($_SESSION["db_janitor_pass"]);
			// unset($_SESSION["DATABASE_INFO"]);
			//
			// unset($_SESSION["mail_admin"]);
			// unset($_SESSION["mail_host"]);
			// unset($_SESSION["mail_port"]);
			// unset($_SESSION["mail_username"]);
			// unset($_SESSION["mail_password"]);
			// unset($_SESSION["MAIL_INFO"]);

			//			print_r($tasks);

			return $tasks;
		}

		return false;
	}

}

?>
