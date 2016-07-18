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
		$this->ffmpeg = false;
		$this->mysql = false;

		$this->software_ok = isset($_SESSION["SOFTWARE_INFO"]) ? $_SESSION["SOFTWARE_INFO"] : "";


		// CONFIG VALUES
		$this->project_path = isset($_SESSION["project_path"]) ? $_SESSION["project_path"] : PROJECT_PATH;
		$this->site_uid = isset($_SESSION["site_uid"]) ? $_SESSION["site_uid"] : "";
		$this->site_name = isset($_SESSION["site_name"]) ? $_SESSION["site_name"] : "";
		$this->site_email = isset($_SESSION["site_email"]) ? $_SESSION["site_email"] : "";

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
			"hint_message" => "Absolute path to your project folder", 
			"error_message" => "Project path must be filled out"
		));
		// site_uid
		$this->addToModel("site_uid", array(
			"type" => "string",
			"label" => "Unique ID",
			"required" => true,
			"hint_message" => "3-5 character ID used to identify your current project.", 
			"error_message" => "Unique ID must be filled out"
		));
		// site_name
		$this->addToModel("site_name", array(
			"type" => "string",
			"label" => "Site name",
			"required" => true,
			"hint_message" => "Userfriendly name of your project.", 
			"error_message" => "Site name must be filled out"
		));
		// site_email
		$this->addToModel("site_email", array(
			"type" => "email",
			"label" => "Public email",
			"required" => true,
			"hint_message" => "Email to use to communicate to your users.", 
			"error_message" => "Public email must be filled out"
		));



		// DATABASE MODEL

		// db_host
		$this->addToModel("db_host", array(
			"type" => "string",
			"label" => "Database host",
			"required" => true,
			"hint_message" => "Database host. Could be localhost or 127.0.0.1.", 
			"error_message" => "Host must be filled out"
		));
		// db_root_user
		$this->addToModel("db_root_user", array(
			"type" => "string",
			"label" => "Database Admin username",
			"hint_message" => "Name of user with priviledges to create a new database.",
			"error_message" => "Database Admin username must be filled out"
		));
		// db_root_pass
		$this->addToModel("db_root_pass", array(
			"type" => "password",
			"label" => "Admin password",
			"hint_message" => "Password of database admin user.",
			"error_message" => "Admin password must be filled out"
		));
		// db_janitor_db
		$this->addToModel("db_janitor_db", array(
			"type" => "string",
			"label" => "Project database name",
			"required" => true,
			"hint_message" => "Type the name of the database used for this Janitor project",
			"error_message" => "Project database name must be filled out"
		));
		// db_janitor_user
		$this->addToModel("db_janitor_user", array(
			"type" => "string",
			"label" => "Project database username",
			"max" => 16,
			"required" => true,
			"hint_message" => "Type the username you want to grant access to the new database",
			"error_message" => "Project database username must be filled out"
		));
		// db_janitor_pass
		$this->addToModel("db_janitor_pass", array(
			"type" => "password",
			"label" => "Project database password",
			"required" => true,
			"hint_message" => "Type password for new database user",
			"error_message" => "Project database password must be filled out"
		));



		// MAIL MODEL

		// admin_email
		$this->addToModel("mail_admin", array(
			"type" => "email",
			"label" => "Admin email",
			"required" => true,
			"hint_message" => "Email the system uses to communicate with the site Admin.", 
			"error_message" => "Admin email must be filled out"
		));
		// mail_host
		$this->addToModel("mail_host", array(
			"type" => "string",
			"label" => "Mail host",
			"required" => true,
			"hint_message" => "Mail host like smtp.gmail.com.", 
			"error_message" => "Mail host must be filled out"
		));
		// mail_port
		$this->addToModel("mail_port", array(
			"type" => "string",
			"label" => "Mail port",
			"required" => true,
			"hint_message" => "Mail connection port like 465.", 
			"error_message" => "Mail port must be filled out"
		));
		// mail_username
		$this->addToModel("mail_username", array(
			"type" => "string",
			"label" => "Mail username",
			"required" => true,
			"hint_message" => "Username for your mail account.", 
			"error_message" => "Mail username must be filled out"
		));
		// mail_password
		$this->addToModel("mail_password", array(
			"type" => "password",
			"label" => "Mail password",
			"required" => true,
			"hint_message" => "Password for your mail account.", 
			"error_message" => "Mail password must be filled out"
		));

	}

	// reset setup script values
	function reset() {
		unset($_SESSION["SOFTWARE_INFO"]);
		unset($_SESSION["CONFIG_INFO"]);
		unset($_SESSION["DATABASE_INFO"]);
		unset($_SESSION["MAIL_INFO"]);

//		session()->reset();
 	}



	// SOFTWARE

	// is software installed
	function isInstalled($commands, $valid_responses, $escape = true) {

		// try first possible command
		$command = array_shift($commands);

	//	print escapeshellcmd($command)."\n";
		if($escape) {
			$cmd_output = shell_exec(escapeshellcmd($command)." 2>&1");
		}
		else {
			$cmd_output = shell_exec($command." 2>&1");
		}
	
	//	print $cmd_output;

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
		$this->php = preg_match("/5\.[345678]{1}/", phpversion());

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


		// check ffmpeg
		// wierd version names on windows
		$this->ffmpeg = $this->isInstalled(array(
			"/opt/local/bin/ffmpeg -version", 
			"/usr/local/bin/ffmpeg -version",
			"/srv/ffmpeg/bin/ffmpeg -version"
		), array(
			"ffmpeg version 2.[1-9]{1}",
			"ffmpeg version N-67742-g3f07dd6",
			"ffmpeg version N-67521-g48efe9e"
		));

		// If use ffmpeg as a php module:
		// $this->ffmpeg = (array_search("ffmpeg", $php_modules) !== false);


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
			$this->readwrite && 
			$this->ffmpeg
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
		if($handle) {
			fclose($handle);
			unlink(PROJECT_PATH."/wr.test");

			return true;
		}
		return false;
	}



	// CONFIG

	// check config settings
	function checkConfigSettings() {

 		if($this->site_uid && $this->site_name && $this->site_email && $this->project_path && file_exists($this->project_path)) {

			$_SESSION["CONFIG_INFO"] = true;
			$this->config_ok = true;
			return true;
		}

		return false;
	}

	// update the config settings
 	function updateConfigSettings() {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if($this->validateList(array("project_path", "site_uid", "site_name", "site_email"))) {

			$entities = $this->data_entities;

			$this->project_path = $_SESSION["project_path"] = $entities["project_path"]["value"];
			$this->site_uid     = $_SESSION["site_uid"]     = $entities["site_uid"]["value"];
			$this->site_name    = $_SESSION["site_name"]    = $entities["site_name"]["value"];
			$this->site_email   = $_SESSION["site_email"]   = $entities["site_email"]["value"];

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

		// check aquired database settings
		return $this->checkDatabaseConnection();

	}

	// check database connection
	function checkDatabaseConnection() {


		// do we have enough information to check root login
		if(
			$this->db_host && 
			$this->db_root_user && 
			$this->db_root_pass && 
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
			else {

				$this->db_connection_error = true;
			}
		}


		// check connection
		$query = new Query();

		// is connection information valid
		if($this->db_janitor_db && $query->connected) {

			$db_temp_create = false;


			// test if DB exists
			$sql = "USE `".$this->db_janitor_db."`";
	//		print $sql."<br>".$query->sql($sql)."<br>";
			if($query->sql($sql)) {

				$this->db_exists = true;

			}
			// otherwise attempt creating it
			else {

				if($query->sql("CREATE DATABASE $this->db_janitor_db")) {

					$sql = "USE `".$this->db_janitor_db."`";
			//		print $sql."<br>".$query->sql($sql)."<br>";
					$query->sql($sql);

					$db_temp_create = true;
				}

			}

			if($this->db_exists || $db_temp_create) {

				// test if we can create new table in database
				// otherwise we still need more info
				$sql = "CREATE TABLE `janitor_db_test` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	//			print $sql."<br>".$query->sql($sql)."<br>";
				if($query->sql($sql)) {

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
			($this->config_ok || SETUP_TYPE == "init") &&
			$this->software_ok &&
			$this->db_ok &&
			$this->mail_ok
		) {

			$this->project_path = PROJECT_PATH;
			$this->local_path =  $this->project_path."/src";
			$this->framework_path = $this->project_path."/submodules/janitor/src";

//			$this->apache_path = isset($_SESSION["apache_path"]) ? $_SESSION["apache_path"] : "apachectl";

			// get apache user to set permissions
			$this->current_user = get_current_user();
			$this->apache_user = trim(shell_exec('whoami'));
			$this->deploy_user = trim(shell_exec('egrep -i "^deploy" /etc/group')) ? "deploy" : $this->current_user;

			return true;
		}

		return false;
	}
	
	// finish installation
	function finishInstallation() {

		$tasks = array();


		if($this->checkAllSettings()) {

			$fs = new FileSystem();


			// NEW SETUP
			if(SETUP_TYPE == "setup") {


				//
				// CREATE FOLDER STRUCTURE
				//

				 if(!file_exists($this->project_path."/src")) {
					// create file structure
					$tasks[] = "Creating folders";

					$fs->makeDirRecursively($this->project_path."/src/www/img");
					$fs->makeDirRecursively($this->project_path."/src/www/js/lib/desktop");
					$fs->makeDirRecursively($this->project_path."/src/www/css/lib/desktop");
					$fs->makeDirRecursively($this->project_path."/src/www/janitor/img");
					$fs->makeDirRecursively($this->project_path."/src/www/janitor/js/lib");
					$fs->makeDirRecursively($this->project_path."/src/www/janitor/css/lib");

					$fs->makeDirRecursively($this->project_path."/src/config/db");

					$fs->makeDirRecursively($this->project_path."/src/classes/items");

					$fs->makeDirRecursively($this->project_path."/src/templates/janitor/post");

					$fs->makeDirRecursively($this->project_path."/src/www/janitor/img");
					$fs->makeDirRecursively($this->project_path."/src/www/janitor/js/lib/desktop");
					$fs->makeDirRecursively($this->project_path."/src/www/janitor/css/lib/desktop");


					// copy test files
					$tasks[] = "Copying files";

					$fs->copy($this->framework_path."/setup/defaults/www", $this->local_path."/www");
					$fs->copy($this->framework_path."/setup/defaults/templates", $this->local_path."/templates");

					copy($this->framework_path."/templates/janitor/post/new.php", $this->local_path."/templates/janitor/post/new.php");
					copy($this->framework_path."/templates/janitor/post/edit.php", $this->local_path."/templates/janitor/post/edit.php");
					copy($this->framework_path."/templates/janitor/post/list.php", $this->local_path."/templates/janitor/post/list.php");

					copy($this->framework_path."/config/db/items/item_post.sql", $this->local_path."/config/db/item_post.sql");
//					copy($this->framework_path."/config/db/items/item_post_mediae.sql", $this->local_path."/config/db/item_post_mediae.sql");

					copy($this->framework_path."/classes/items/type.post.class.php", $this->local_path."/classes/items/type.post.class.php");

				}



				//
				// CREATE CONF FILES
				//

				define("SITE_UID", $this->site_uid);
				define("SITE_NAME", $this->site_name);
//				define("SITE_URL", $_SERVER["SERVER_NAME"]);
				define("SITE_EMAIL", $this->site_email);

				// create conf files
				$tasks[] = "Creating config files";

				// config
				$file_config = file_get_contents($this->framework_path."/setup/defaults/config/config.template.php");
				$file_config = preg_replace("/###SITE_UID###/", $this->site_uid, $file_config);
				$file_config = preg_replace("/###SITE_NAME###/", $this->site_name, $file_config);
				$file_config = preg_replace("/###SITE_EMAIL###/", $this->site_email, $file_config);
				file_put_contents($this->local_path."/config/config.php", $file_config);

				// apache
				$file_apache = file_get_contents($this->framework_path."/setup/defaults/config/httpd-vhosts.template.conf");
				$file_apache = preg_replace("/###LOCAL_PATH###/", $this->local_path, $file_apache);
				$file_apache = preg_replace("/###FRAMEWORK_PATH###/", $this->framework_path, $file_apache);
				$file_apache = preg_replace("/###PROJECT_PATH###/", $this->project_path, $file_apache);
				$file_apache = preg_replace("/###SITE_URL###/", $_SERVER["SERVER_NAME"], $file_apache);
				$file_apache = preg_replace("/###LOG_NAME###/", superNormalize($_SERVER["SERVER_NAME"]), $file_apache);
				file_put_contents($this->project_path."/apache/httpd-vhosts.conf", $file_apache);

				// copy segments overwriting
				copy($this->framework_path."/setup/defaults/config/segments.php", $this->local_path."/config/segments.php");

			}



			//
			// LIBRARY
			//
			if(!file_exists($this->project_path."/src/library")) {

				// create library
				$tasks[] = "Creating library";

				// copy library including dummy images in 0/
				$fs->copy($this->framework_path."/setup/defaults/library", $this->local_path."/library");
	
			}
			// always make sure public and private folder exists
			$fs->makeDirRecursively($this->project_path."/src/library/private");
			$fs->makeDirRecursively($this->project_path."/src/library/public");



			//
			// DATABASE SETUP
			//
			$tasks[] = "Creating database configuration";

			// database
			$file_db = file_get_contents($this->framework_path."/setup/defaults/config/connect_db.template.php");
			$file_db = preg_replace("/###SITE_DB###/", $this->db_janitor_db, $file_db);
			$file_db = preg_replace("/###HOST###/", $this->db_host, $file_db);
			$file_db = preg_replace("/###USERNAME###/", $this->db_janitor_user, $file_db);
			$file_db = preg_replace("/###PASSWORD###/", $this->db_janitor_pass, $file_db);
			file_put_contents($this->local_path."/config/connect_db.php", $file_db);


			// only create if it does not exist
			if($this->checkDatabaseConnection() && !$this->db_exists) {

				// create db
				$tasks[] = "Creating database";

				$query = new Query();
				$query->sql("CREATE DATABASE $this->db_janitor_db");

				$query->sql("GRANT ALL PRIVILEGES ON ".$this->db_janitor_db.".* TO '".$this->db_janitor_user."'@'".$this->db_host."' IDENTIFIED BY '".$this->db_janitor_pass."' WITH GRANT OPTION;");

			}

			global $page;
			// load database configuration
			$page->loadDBConfiguration();



			//
			// MAIL SETUP
			//

			$tasks[] = "Setup mail";

			// mail
			$file_mail = file_get_contents($this->framework_path."/setup/defaults/config/connect_mail.template.php");
			$file_mail = preg_replace("/###ADMIN_EMAIL###/", $this->mail_admin, $file_mail);
			$file_mail = preg_replace("/###HOST###/", $this->mail_host, $file_mail);
			$file_mail = preg_replace("/###PORT###/", $this->mail_port, $file_mail);
			$file_mail = preg_replace("/###USERNAME###/", $this->mail_username, $file_mail);
			$file_mail = preg_replace("/###PASSWORD###/", $this->mail_password, $file_mail);
			$file_mail = preg_replace("/###SITE_NAME###/", $this->site_name, $file_mail);
			$file_mail = preg_replace("/###SITE_EMAIL###/", $this->site_email, $file_mail);
			file_put_contents($this->local_path."/config/connect_mail.php", $file_mail);


			// load mail configuration
			$page->loadMailConfiguration();


			// in some case the old config files were loaded, but contained bad setup info
			// check and break install if that is the case

			if(!defined("SITE_DB") || SITE_DB != $this->db_janitor_db) {
				$tasks[] = "ERROR: THE PROCESS REQUIRES A PAGE REFRESH. PLEASE REFRESH AND CLICK INSTALL AGAIN!";
				return $tasks;
			}
			if(!defined("ADMIN_EMAIL") || ADMIN_EMAIL != $this->mail_admin) {
				$tasks[] = "ERROR: THE PROCESS REQUIRES A PAGE REFRESH. PLEASE REFRESH AND CLICK INSTALL AGAIN!";
				return $tasks;
			}

			// DEFAULT DATA


			//
			// VERIFY DATABASE TABLES
			//
			$tasks[] = "Verifying database tables";


			$query = new Query();
			$query->checkDbExistance($this->db_janitor_db.".user_groups");
			$query->checkDbExistance($this->db_janitor_db.".system_languages");
			$query->checkDbExistance($this->db_janitor_db.".system_currencies");
			$query->checkDbExistance($this->db_janitor_db.".system_countries");
			$query->checkDbExistance($this->db_janitor_db.".system_vatrates");
			$query->checkDbExistance($this->db_janitor_db.".users");

			$query->checkDbExistance($this->db_janitor_db.".items");
			$query->checkDbExistance($this->db_janitor_db.".tags");
			$query->checkDbExistance($this->db_janitor_db.".taggings");

			$query->checkDbExistance($this->db_janitor_db.".items_mediae");
			$query->checkDbExistance($this->db_janitor_db.".items_comments");
			$query->checkDbExistance($this->db_janitor_db.".items_prices");



			//
			// CREATE LANGUAGE
			//
			$sql = "SELECT id FROM ".$this->db_janitor_db.".system_languages WHERE name = 'English'";
			if(!$query->sql($sql)) {

				$tasks[] = "Installing language: EN";
				$sql = "INSERT INTO ".$this->db_janitor_db.".system_languages set id = 'EN', name = 'English'";
//				print $sql."<br>";
				$query->sql($sql);

				$sql = "INSERT INTO ".$this->db_janitor_db.".system_languages set id = 'DA', name = 'Dansk'";
				$query->sql($sql);

			}
			else {
				$tasks[] = "Language: OK";
			}


			//
			// CREATE CURRENCY
			//
			$sql = "SELECT id FROM ".$this->db_janitor_db.".system_currencies WHERE id = 'DKK'";
			if(!$query->sql($sql)) {

				$tasks[] = "Installing currency: DKK";
				$sql = "INSERT INTO ".$this->db_janitor_db.".system_currencies set id = 'DKK', name = 'Kroner (Denmark)', abbreviation = 'DKK', abbreviation_position = 'after', decimals = 2, decimal_separator = ',', grouping_separator = '.'";
				// print $sql."<br>";
				$query->sql($sql);

			}
			else {
				$tasks[] = "Currency: OK";
			}


			//
			// CREATE COUNTRY
			//
			$sql = "SELECT id FROM ".$this->db_janitor_db.".system_countries WHERE id = 'DK'";
			if(!$query->sql($sql)) {

				$tasks[] = "Installing country: DK";
				$sql = "INSERT INTO ".$this->db_janitor_db.".system_countries set id = 'DK', name = 'Danmark', phone_countrycode = '45', phone_format = '#### ####', language = 'EN', currency = 'DKK'";
				// print $sql."<br>";
				$query->sql($sql);

			}
			else {
				$tasks[] = "Country: OK";
			}

			//
			// CREATE VATRATE
			//
			$sql = "SELECT id FROM ".$this->db_janitor_db.".system_vatrates WHERE country = 'DK'";
			if(!$query->sql($sql)) {

				$tasks[] = "Installing vatrate: No VAT, DK";
				$sql = "INSERT INTO ".$this->db_janitor_db.".system_vatrates set country = 'DK', name = 'No Vat', vatrate = 0";
				// print $sql."<br>";
				$query->sql($sql);

			}
			else {
				$tasks[] = "Vatrate: OK";
			}


			//
			// CREATE DEFAULT USER GROUPS AND USERS
			//
			include_once("classes/users/user.core.class.php");
			include_once("classes/users/user.class.php");
			include_once("classes/users/superuser.class.php");
			$UC = new SuperUser();

			$user_groups = $UC->getUserGroups(array("user_group_id" => 1));
			if(!$user_groups) {

				$tasks[] = "Creating default user groups";

				// Create Guest user group
				unset($_POST);
				$_POST["user_group"] = "Guest";

				$UC->getPostedEntities();
				$user_group = $UC->saveUserGroup(array("saveUserGroup"));

				// Create Member user group
				unset($_POST);
				$_POST["user_group"] = "Member";

				$UC->getPostedEntities();
				$user_group = $UC->saveUserGroup(array("saveUserGroup"));

				// Create Developer user group
				unset($_POST);
				$_POST["user_group"] = "Developer";

				$UC->getPostedEntities();
				$user_group = $UC->saveUserGroup(array("saveUserGroup"));

			}
			else {
				$tasks[] = "User groups: OK";
			}


			//
			// DEVELOPER PERMISSIONS
			//
			$tasks[] = "Adding Developer permissions";

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
			$UC->updateAccess(array("updateAccess", 3));


			//
			// DEFAULT USERS
			//

			// check for anonymous user
			$users = $UC->getUsers(array("user_id" => 1));
			if(!$users) {

				$tasks[] = "Creating default users";

				// create anonymous user
				unset($_POST);
				$_POST["nickname"] = "Anonymous";
				$_POST["user_group_id"] = 1;
				$_POST["status"] = 1;
				$_POST["language"] = "EN";

				$UC->getPostedEntities();
				$user = $UC->save(array("save"));


				// create developer user
				unset($_POST);
				$_POST["nickname"] = "Dummy developer";
				$_POST["user_group_id"] = 3;
				$_POST["status"] = 1;
				$_POST["language"] = "EN";

				$UC->getPostedEntities();
				$user = $UC->save(array("save"));
	
				if($user) {
					$user_id = $user["item_id"];


					$UC->status(array("status", $user_id, 1));

					// SET USERNAME
					unset($_POST);
					$_POST["email"] = ADMIN_EMAIL;
					$UC->getPostedEntities();
					$UC->updateEmail(array("updateEmail", $user_id));

					// SET PASSWORD
					unset($_POST);
					$_POST["password"] = "123rotinaj";
					$UC->getPostedEntities();
					$UC->setPAssword(array("setPassword", $user_id));

					// store user_id for content creation
					session()->value("user_id", $user_id);
				}
			}
			else if($users["nickname"] == "Anonymous") {
				$tasks[] = "Users: OK";
			}


			include_once("classes/items/items.core.class.php");
			include_once("classes/items/items.class.php");
			$IC = new Items();


			//
			// CREATE TEST CONTENT
			//
			if(!$IC->getItems() && session()->value("user_id")) {

				include_once("classes/items/type.post.class.php");
				$PC = new TypePost();

				$tasks[] = "Creating test content";

				unset($_POST);
				$_POST["name"] = "Welcome to the basement";
				$_POST["html"] = "<p>This is a test post made by the setup script. You can delete this post.</p>";
				$_POST["status"] = 1;
				$item = $PC->save(array("save", "post"));

				// add a tag
				unset($_POST);
				$_POST["tags"] = "post:My first tag";
				$PC->addTag(array("tags", "add", $item["id"]));

			}


			// TODO: make sure messages are not stacked


			//
			// GIT SETTINGS
			//
			// create git ignore
			if(!file_exists($this->project_path."/.gitignore")) {
				$handle = fopen($this->project_path."/.gitignore", "w+");
				fwrite($handle, "src/library/*\n.DS_Store\nsrc/config/connect_*.php");
				fclose($handle);
			}

			// Tell git to ignore file permission changes
			exec("cd ".$this->project_path." && git config core.filemode false");
			exec("cd ".$this->project_path."/submodules/janitor && git config core.filemode false");
			exec("cd ".$this->project_path."/submodules/js-merger && git config core.filemode false");
			exec("cd ".$this->project_path."/submodules/css-merger && git config core.filemode false");


			if(SETUP_TYPE == "setup") {
				$page->mail(array("subject" => "Welcome to janitor", "message" => "Your Janitor project is ready.\n\nLog in to your admin system: http://".SITE_URL."/janitor\n\nUsername: ".ADMIN_EMAIL."\nPassword: 123rotinaj\n\nSee you soon,\n\nJanitor"));
			}

			// TODO: delete session when done testing
			//session_unset();

//			print_r($tasks);

			return $tasks;
		}

		return false;
	}

}

?>
