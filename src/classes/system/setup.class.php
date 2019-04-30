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

		$this->set("system", "os", preg_match("/Darwin/i", PHP_OS) ? "mac" : (preg_match("/win/i", PHP_OS) ? "win" : "unix"));

		$this->set("system", "current_user", get_current_user());
		$this->set("system", "apache_user", trim(shell_exec('whoami')));
		$this->set("system", "deploy_user", trim(shell_exec('egrep -i "^deploy" /etc/group')) ? "deploy" : (trim(shell_exec('egrep -i "^staff" /etc/group')) ? "staff" : $this->get("system", "current_user")));



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
			"pattern" => "[A-Z0-9]+",
			"required" => true,
			"hint_message" => "3-8 character ID (A-Z, 0-9) used to identify your current project. Used for cross-project communication and logging.", 
			"error_message" => "Unique ID can only contain uppercase characters from A-Z or the numbers 0-9."
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
		// site_deployment
		$this->addToModel("site_deployment", array(
			"type" => "select",
			"label" => "Site deployment environment",
			"options" => ["live" => "Production/live site", "dev" => "Development site"],
			"hint_message" => "Choose the type of site deployment. Is this a live deployment or a development intallation", 
			"error_message" => "Invalid deployment environment."
		));



		// JANITOR ADMIN USER

		// admin_nickname
		$this->addToModel("account_nickname", array(
			"type" => "string",
			"label" => "Janitor Admin nickname",
			"hint_message" => "The nickname of the admin account in Janitor.",
			"error_message" => "The entered value is not valid."
		));

		// account_username
		$this->addToModel("account_username", array(
			"type" => "string",
			"label" => "Janitor Admin email",
			"autocomplete" => true,
			"required" => true,
			"pattern" => "[\w\.\-_\+]+@[\w\-\.]+\.\w{2,10}", 
			"hint_message" => "The email adress which should be used for the initial admin account in Janitor. This email will be used to log in.",
			"error_message" => "The entered value is not a valid email."
		));
		// account_password
		$this->addToModel("account_password", array(
			"type" => "password",
			"label" => "Password",
			"required" => true,
			"min" => 1,
			"hint_message" => "Password of admin account.",
			"error_message" => "Admin password must be filled out."
		));



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
			"min" => 1,
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

		// mail_type
		$this->addToModel("mail_type", array(
			"type" => "select",
			"label" => "Mail type",
			"options" => ["smtp" => "SMTP Service", "mailgun" => "Mailgun API"],
			"required" => true,
			"hint_message" => "Select your type of mail endpoint.", 
			"error_message" => "Mail type must be filled out."
		));

		// SMTP
		// mail_smtp_host
		$this->addToModel("mail_smtp_host", array(
			"type" => "string",
			"label" => "Mail host",
			"autocomplete" => true,
			"required" => true,
			"hint_message" => "Mail host like smtp.gmail.com or smtp.mailgun.org.", 
			"error_message" => "Mail host must be filled out."
		));
		// mail_smtp_port
		$this->addToModel("mail_smtp_port", array(
			"type" => "string",
			"label" => "Mail port",
			"autocomplete" => true,
			"required" => true,
			"hint_message" => "Mail connection port like 587 or 465.", 
			"error_message" => "Mail port must be filled out."
		));
		// mail_smtp_username
		$this->addToModel("mail_smtp_username", array(
			"type" => "string",
			"label" => "Mail username",
			"autocomplete" => true,
			"required" => true,
			"hint_message" => "Username for the outgoing mail account.", 
			"error_message" => "Mail username must be filled out."
		));
		// mail_smtp_password
		$this->addToModel("mail_smtp_password", array(
			"type" => "password",
			"label" => "Mail password",
			"required" => true,
			"hint_message" => "Password for the outgoing mail account.", 
			"error_message" => "Mail password must be filled out."
		));

		// MAILGUN
		// mail_mailgun_api_key
		$this->addToModel("mail_mailgun_api_key", array(
			"type" => "string",
			"label" => "API key",
			"required" => true,
			"hint_message" => "API key for the Mailgun account.", 
			"error_message" => "API key must be filled out."
		));
		// mail_mailgun_domain
		$this->addToModel("mail_mailgun_domain", array(
			"type" => "string",
			"label" => "Mail domain",
			"required" => true,
			"hint_message" => "Mail account domain to use when sending emails.", 
			"error_message" => "API key must be filled out."
		));



		// PAYMENT MODEL

		// payment_type
		$this->addToModel("payment_type", array(
			"type" => "select",
			"label" => "Payment type",
			"options" => ["stripe" => "Stripe"],
			"required" => true,
			"hint_message" => "Select your payment gateway.", 
			"error_message" => "Payment gateway must be filled out."
		));

		// STRIPE
		// payment_stripe_private_key
		$this->addToModel("payment_stripe_private_key", array(
			"type" => "string",
			"label" => "Private API key",
			"required" => true,
			"hint_message" => "Private API key for the Stripe account.", 
			"error_message" => "Private API key must be filled out."
		));
		// payment_stripe_public_key
		$this->addToModel("payment_stripe_public_key", array(
			"type" => "string",
			"label" => "Publishable API key",
			"required" => true,
			"hint_message" => "Publishable API key for the Stripe account.", 
			"error_message" => "Publishable API key must be filled out."
		));


	}


	// reset setup script values
	function reset() {

		foreach($_SESSION as $key => $value) {
			// Don't delete main SV storage to maintain potential login
			if($key != "SV") {
				unset($_SESSION[$key]);
			}
		}

		return true;
 	}


	// Keeping track of data and system checks
	function get($setup_area, $property) {
		if(isset($_SESSION[$setup_area]) && isset($_SESSION[$setup_area][$property])) {
			return $_SESSION[$setup_area][$property];
		}
		return false;
	}
	function set($setup_area, $property, $value) {
		$_SESSION[$setup_area][$property] = $value;
	}



	// SOFTWARE

	// is software installed
	function isInstalled($commands, $valid_responses, $escape = true) {

		// try first possible command
		$command = array_shift($commands);

		// print escapeshellcmd($command)."\n";
		if($escape) {
			$cmd_output = shell_exec(escapeshellcmd($command)." 2>&1");
		}
		else {
			$cmd_output = shell_exec($command." 2>&1");
		}
	
		// print $cmd_output;

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
	// TODO: Test software checks on Windows
	function checkSoftware() {


		// reset software checks
		$this->set("software", "passed", false);


		// check apache
		$this->set("software", 
			"apache", preg_match("/2\.[2345678]{1}/", $_SERVER["SERVER_SOFTWARE"])
		);


		// check PHP
		$this->set("software", 
			"php", preg_match("/5\.[345678]{1}|7\./", phpversion())
		);


		// get PHP modules
		$php_modules = get_loaded_extensions();

		// check if mysqli is available
		$this->set("software", 
			"mysql", (array_search("mysqlnd", $php_modules) !== false)
		);

		// ImageMagick
		$this->set("software", 
			"imagemagick", (array_search("imagick", $php_modules) !== false)
		);

		// Session
		$this->set("software", 
			"session", (array_search("session", $php_modules) !== false)
		);

		// SimpleXML
		$this->set("software", 
			"simplexml", (array_search("SimpleXML", $php_modules) !== false)
		);

		// DOM
		$this->set("software", 
			"dom", (array_search("dom", $php_modules) !== false)
		);

		// mbstring
		$this->set("software", 
			"mbstring", (array_search("mbstring", $php_modules) !== false)
		);

		// Check read/write
		$this->set("software",
			"readwrite", $this->readWriteTest()
		);


		// Zip
		$this->set("software",
			"zip", (array_search("zip", $php_modules) !== false)
		);


		// Curl
		$this->set("software",
			"curl", (
				array_search("curl", $php_modules) !== false && 
				$this->isInstalled(
					array(
						"curl --version"
					),
					array(
						"curl [67]{1}.[0-9]"
					)
				)
			)
		);

		// Tar
		$this->set("software",
			"tar", (
				$this->isInstalled(
					array(
						"tar --version"
					),
					array(
						"bsdtar [23]{1}.[0-9]",
						"tar \(GNU tar\)"
					)
				)
			)
		);

		
		// Redis
		$this->set("software",
			"redis", (array_search("redis", $php_modules) !== false)
		);


		// check FFMPEG
		$this->set("software",
			"ffmpeg", ($this->isInstalled(
				array(
					"ffmpeg -version", 
					"/opt/local/bin/ffmpeg -version", 
					"/usr/local/bin/ffmpeg -version",
					"/srv/ffmpeg/bin/ffmpeg -version",
					"/srv/installed-packages/ffmpeg/bin/ffmpeg -version"
				),
				array(
					"ffmpeg version (2\.[1-9]{1}|3\.|4\.)",
					"ffmpeg version N-[6-9][0-9]"
				)
			) !== false)
		);


		// check WKHTMLTO
		$this->set("software",
			"wkhtmlto", ($this->isInstalled(
				array(
					"/srv/tools/bin/wkhtmltopdf --version",
					"/usr/bin/static_wkhtmltopdf --version",
					"/usr/local/bin/static_wkhtmltopdf --version", 
					"/opt/local/bin/wkhtmltopdf --version",
					"/usr/local/bin/wkhtmltopdf --version", 
					"/usr/bin/wkhtmltopdf --version",
					"/srv/installed-packages/wkhtmltopdf/bin/wkhtmltopdf.exe --version"
				),
				array(
					"wkhtmltopdf 0.1[0-9]{1}"
				)
			) !== false)
		);



		// if everything is fine
		if(
			$this->get("software", "apache") && 
			$this->get("software", "php") && 
			$this->get("software", "mysql") && 
			$this->get("software", "session") &&
			$this->get("software", "simplexml") &&
			$this->get("software", "dom") &&
			$this->get("software", "mbstring") &&
			$this->get("software", "curl") && 
			$this->get("software", "tar") && 
			$this->get("software", "readwrite")
		):

			$this->set("software", "passed", true);
			return true;

		else:

			return false;

		endif;

	}

	// CHECK FOR READ/WRITE ACCESS
	// TODO: Test read/write checks on Windows
	function readWriteTest() {

		// Check if we can write random file in project root file
		$handle = @fopen(PROJECT_PATH."/wr.test", "a+");
		if($handle && file_exists(PROJECT_PATH."/wr.test")) {
			fclose($handle);
			unlink(PROJECT_PATH."/wr.test");
		}



		// Check if we can update existing config file
		$config_handle = true;
		if(file_exists(LOCAL_PATH."/config/config.php")) {
			$config_handle = @fopen(LOCAL_PATH."/config/config.php", "a+");
			if($config_handle) {
				fclose($config_handle);
			}
		}

		// Check if we can update existing db connect file
		$connect_db_handle = true;
		if(file_exists(LOCAL_PATH."/config/connect_db.php")) {
			$connect_db_handle = @fopen(LOCAL_PATH."/config/connect_db.php", "a+");
			if($connect_db_handle) {
				fclose($connect_db_handle);
			}
		}

		// Check if we can update existing mail connect file
		$connect_mail_handle = true;
		if(file_exists(LOCAL_PATH."/config/connect_mail.php")) {
			$connect_mail_handle = @fopen(LOCAL_PATH."/config/connect_mail.php", "a+");
			if($connect_mail_handle) {
				fclose($connect_mail_handle);
			}
		}

		// Check if we can write .git/config file
		$git_handle = true;
		if(file_exists(PROJECT_PATH."/.git/config")) {
			$git_handle = @fopen(PROJECT_PATH."/.git/config", "a+");
			if($git_handle) {
				fclose($git_handle);
			}
		}


		// Don't try to set owner, group and permissions, unless initial permission probing was successful
		if($handle && $config_handle && $connect_db_handle && $connect_mail_handle && $git_handle) {

			// Set owner, group and permissions for all project files and folders
			if(
				$this->get("system", "os") == "win"
				|| 
				$this->recurseFilePermissions(PROJECT_PATH, $this->get("system", "apache_user"), $this->get("system", "deploy_user"), 0777)
			) {
				return true;
			};

		}

		return false;
	}


	// Change owner, group and permissions recursively on $path and any subfolder/file
	function recurseFilePermissions($path, $user, $group, $permissions) {
		$directory = opendir($path) ;
		while(($file = readdir($directory)) !== false) {
			if($file != "." && $file != ".." && $file != ".git") {

				$filepath = $path . "/" . $file ;

				// print $filepath. " : " . filetype($filepath). " : " . $user . "<br>\n";
				if(is_dir($filepath)) {
					if(!$this->recurseFilePermissions($filepath, $user, $group, $permissions)) {
						return false;
					}
				}

				if(!@chown($filepath, $user) || !@chgrp($filepath, $group) || !@chmod($filepath, $permissions)) {
					return false;
				}

			}

		}

		return true;

	}



	// CONFIG

	// check config settings
	function checkConfigSettings() {
//		print "checkConfigSettings<br>\n";


		// reset config checks
		$this->set("config", "passed", false);
		$this->set("config", "invalid_project_path", false);


		// parse existing values from config.php if it already exists
		// config exists but was not loaded
		if(!defined("SITE_UID") && file_exists(LOCAL_PATH."/config/config.php") && (!$this->get("config", "site_uid") && !$this->get("config", "site_name") && !$this->get("config", "site_email"))) {

			$config_info = file_get_contents(LOCAL_PATH."/config/config.php");


			preg_match("/\n[ \t]*define\(\"SITE_UID\",[ ]*\"(.+)\"\);/", $config_info, $matches);
			if($matches) {
				$this->set("config", "site_uid", $matches[1]);
			}

			preg_match("/\n[ \t]*define\(\"SITE_NAME\",[ ]*\"(.+)\"\);/", $config_info, $matches);
			if($matches) {
				$this->set("config", "site_name", $matches[1]);
			}

			preg_match("/\n[ \t]*define\(\"SITE_EMAIL\",[ ]*\"(.+)\"\);/", $config_info, $matches);
			if($matches) {
				$this->set("config", "site_email", $matches[1]);
			}

			preg_match("/\n[ \t]*define\(\"DEFAULT_PAGE_DESCRIPTION\",[ ]*\"(.+)\"\);/", $config_info, $matches);
			if($matches) {
				$this->set("config", "site_description", $matches[1]);
			}

			// deployment setting
			$this->set("config", "site_deployment", stringOr($this->get("config", "site_deployment"), (preg_match("/(^http[s]?\:\/\/test\.)|(\.local$)/", SITE_URL) ? "dev" : "live")));

		}
		// get default or existing values
		else {

//			$this->project_path = stringOr($this->project_path, PROJECT_PATH);
			$this->set("config", "site_name", stringOr($this->get("config", "site_name"), defined("SITE_NAME") ? SITE_NAME : preg_replace("/\.[^\.]*$/", "", $_SERVER["SERVER_NAME"])));
			$this->set("config", "site_uid", stringOr($this->get("config", "site_uid"), defined("SITE_UID") ? SITE_UID : substr(strtoupper(preg_replace("/[AEIOUYaeiouy-]/", "", superNormalize($this->get("config", "site_name")))), 0, 8)));
			$this->set("config", "site_email", stringOr($this->get("config", "site_email"), defined("SITE_EMAIL") ? SITE_EMAIL : ""));
			$this->set("config", "site_description", stringOr($this->get("config", "site_description"), defined("DEFAULT_PAGE_DESCRIPTION") ? DEFAULT_PAGE_DESCRIPTION : ""));

			// deployment setting
			$this->set("config", "site_deployment", stringOr($this->get("config", "site_deployment"), (preg_match("/(^http[s]?\:\/\/test\.)|(\.local$)/", SITE_URL) ? "dev" : "live")));

		}


		// check data
		// Do we have all relevant data
		// Custom message for bad project path
		if(!defined("PROJECT_PATH") || !PROJECT_PATH || !file_exists(PROJECT_PATH) || !(file_exists(PROJECT_PATH."/core") || file_exists(PROJECT_PATH."/submodules"))) {

			$this->set("config", "invalid_project_path", true);

		}
 		else if(
			$this->get("config", "site_uid") && 
			$this->get("config", "site_name") && 
			$this->get("config", "site_email")
		) {
			$this->set("config", "passed", true);
			return true;
		}

		return false;
	}

	// update the config settings
 	function updateConfigSettings() {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if(SETUP_TYPE == "new" && $this->validateList(array("site_uid", "site_name", "site_email"))) {

			$this->set("config", "site_uid", $this->getProperty("site_uid", "value"));
			$this->set("config", "site_name", $this->getProperty("site_name", "value"));
			$this->set("config", "site_email", $this->getProperty("site_email", "value"));

			$this->set("config", "site_description", $this->getProperty("site_description", "value"));

			$this->set("config", "site_deployment", $this->getProperty("site_deployment", "value"));

		}
		// On existing projects, we only allow site_deployment to be changed
		else {

			$this->set("config", "site_deployment", $this->getProperty("site_deployment", "value"));
			
		}

		return $this->checkConfigSettings();

	}



	// DATABASE

	// check for database settings and connection
	function checkDatabaseSettings() {

		
		// reset database checks is done in checkDatabaseConnection
		

		// if we do not have stored db info, attempt to read existing connect_db.php
		if(!$this->get("database", "db_janitor_db") && file_exists(LOCAL_PATH."/config/connect_db.php")) {

			$connection_info = file_get_contents(LOCAL_PATH."/config/connect_db.php");

			preg_match("/\"host\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->set("database", "db_host", $matches[1]);
			}

			preg_match("/\"SITE_DB\", \"([a-zA-Z0-9\.\-\_]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->set("database", "db_janitor_db", $matches[1]);
			}

			preg_match("/\"username\" \=\> \"([a-zA-Z0-9\.\-\_]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->set("database", "db_janitor_user", $matches[1]);
			}

			preg_match("/\"password\" \=\> \"([a-zA-Z0-9\.\-\_]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->set("database", "db_janitor_pass", $matches[1]);
			}

		}
		// set default values
		else {

			$this->set("database", "db_host", stringOr($this->get("database", "db_host"), "127.0.0.1"));
			$this->set("database", "db_root_user", stringOr($this->get("database", "db_root_user"), "root"));

			$this->set("database", "db_janitor_db",  stringOr($this->get("database", "db_janitor_db"), preg_replace("/[-]/", "_", superNormalize($this->get("config", "site_name")))));
			$this->set("database", "db_janitor_user", stringOr($this->get("database", "db_janitor_user"), substr(preg_replace("/[-]/", "", superNormalize($this->get("config", "site_name"))), 0, 16)));

		}

		// check aquired database settings
		return $this->checkDatabaseConnection();

	}


	// check database connection
	// First check if it is possible to log in using the specified user data
	// - if successful login, then check if database exists or if database can be created
	// Then check if root login is possible
	// - if successful login, then check if database exists or if database can be created
	function checkDatabaseConnection() {
//		print "checkDatabaseConnection<br>\n";


		// reset database checks
		$this->set("database", "exists", false);
		$this->set("database", "passed", false);

		$this->set("database", "user_error", false);
		$this->set("database", "wrong_user_password", false);
		$this->set("database", "admin_error", false);


		// Updating database info could have an impact on the admin account
		// so reset account check when ever user returns to database checks to force user to revisit account page
		$this->set("account", "exists", false);
		$this->set("account", "passed", false);


		// get any stored database information in short variable names
		$db_host = $this->get("database", "db_host");
		$db_janitor_db = $this->get("database", "db_janitor_db");
		$db_janitor_user = $this->get("database", "db_janitor_user");
		$db_janitor_pass = $this->get("database", "db_janitor_pass");

		$db_root_user = $this->get("database", "db_root_user");
		$db_root_pass = $this->get("database", "db_root_pass");


		// Start by testing janitor user info
		// we are doing this because you can use an existing account for a new project
		// and to do this you don't need to provide root/admin account info, so try user login first
		if(
			$db_host && 
			$db_janitor_db && 
			$db_janitor_user && 
			$db_janitor_pass
		) {
			// Attempt to make connection
			$mysqli = @new mysqli($db_host, $db_janitor_user, $db_janitor_pass);
			if(!$mysqli->connect_errno) {

				// correct the database connection setting
				$mysqli->query("SET NAMES utf8");
				$mysqli->query("SET CHARACTER SET utf8");
				$mysqli->set_charset("utf8");

				// Make connection available for Query module
				global $mysqli_global;
				$mysqli_global = $mysqli;

				$query = new Query();
				// Did connection stick?
				if($query->connected) {

					// check if database exists
					if($query->sql("USE `".$db_janitor_db."`")) {


						$this->set("database", "exists", true);


						// test if we can create new table in database
						$sql = "CREATE TABLE `janitor_db_test` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//						print $sql."<br>\n";
						if($query->sql($sql)) {

							// Creation was successful, clean up again
							$query->sql("DROP TABLE `".$db_janitor_db."`.`janitor_db_test`");

							$this->set("database", "passed", true);
							return true;

						}

					}
					// otherwise attempt creating it
					else if($query->sql("CREATE DATABASE $db_janitor_db")) {

						// test if we can create new table in database
						$sql = "CREATE TABLE `".$db_janitor_db."`.`janitor_db_test` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//						print $sql."<br>\n";
						if($query->sql($sql)) {

							// Creation successful, clean up again
							$query->sql("DROP TABLE `".$db_janitor_db."`.`janitor_db_test`");
							$query->sql("DROP DATABASE `".$db_janitor_db."`");

							$this->set("database", "passed", true);
							return true;
						}

					}

				}

			}

			// Check can still be 
			$this->set("database", "user_error", true);

		}


		// Database connection still not verified
		// do we have enough information to check root login (possibly with blank password)
		if(
			$db_host && 
			$db_root_user && 
//			$db_root_pass && 
			$db_janitor_db && 
			$db_janitor_user && 
			$db_janitor_pass
		) {

			// Attempt to make connection
			$mysqli = @new mysqli($db_host, $db_root_user, $db_root_pass);
			if(!$mysqli->connect_errno) {

				// correct the database connection setting
				$mysqli->query("SET NAMES utf8");
				$mysqli->query("SET CHARACTER SET utf8");
				$mysqli->set_charset("utf8");

				// Make connection available for Query module
				global $mysqli_global;
				$mysqli_global = $mysqli;

				$query = new Query();
				// Did connection stick?
				if($query->connected) {

					// does user already exist
					if($query->sql("SELECT * FROM mysql.user WHERE user = '".$db_janitor_user."'")) {

						// can the user be used to log in
						$test_mysqli = @new mysqli($db_host, $db_janitor_user, $db_janitor_pass);
						if($test_mysqli->connect_errno) {

							// If not, then return error
							$this->set("database", "wrong_user_password", true);
							return false;
						}

					}


					// check if database exists
					if($query->sql("USE `".$db_janitor_db."`")) {

						$this->set("database", "exists", true);
//						$this->db_exists = true;

						// test if we can create new table in database
						$sql = "CREATE TABLE `janitor_db_test` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//						print $sql."<br>\n";
						if($query->sql($sql)) {

							// Creation successful, clean up again
							$query->sql("DROP TABLE `".$db_janitor_db."`.`janitor_db_test`");


							$this->set("database", "passed", true);
							$this->set("database", "user_error", false);
							return true;
						}

					}
					// otherwise attempt creating it
					else if($query->sql("CREATE DATABASE $db_janitor_db")) {

						// test if we can create new table in database
						$sql = "CREATE TABLE `".$db_janitor_db."`.`janitor_db_test` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//						print $sql."<br>\n";
						if($query->sql($sql)) {

							// Creation successful, clean up again
							$query->sql("DROP TABLE `".$db_janitor_db."`.`janitor_db_test`");
							$query->sql("DROP DATABASE `".$db_janitor_db."`");


							$this->set("database", "user_error", false);
							$this->set("database", "passed", true);
							return true;
						}

					}

				}

			}

			$this->set("database", "admin_error", true);
//			$this->db_admin_error = true;

		}

		$this->set("database", "passed", false);

		// we still need more/correct info
		return false;
	}


	// update the database settings
	// submitted from form
 	function updateDatabaseSettings() {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if($this->validateList(array("db_host", "db_root_user", "db_root_pass", "db_janitor_db", "db_janitor_user", "db_janitor_pass"))) {

			$this->set("database", "db_host", $this->getProperty("db_host", "value"));
			$this->set("database", "db_root_user", $this->getProperty("db_root_user", "value"));
			$this->set("database", "db_root_pass", $this->getProperty("db_root_pass", "value"));
			$this->set("database", "db_janitor_db", $this->getProperty("db_janitor_db", "value"));
			$this->set("database", "db_janitor_user", $this->getProperty("db_janitor_user", "value"));
			$this->set("database", "db_janitor_pass", $this->getProperty("db_janitor_pass", "value"));

		}

		$check_db = $this->checkDatabaseConnection();


		if($check_db && (!$this->get("database", "exists") || getPost("force_db") == $this->get("database", "db_janitor_db"))) {
			return true;
		}
		else if($check_db && $this->get("database", "exists")) {
			return array("status" => "reload", "db_exists" => true);
		}
		else if($this->get("database", "admin_error")) {
			return array("status" => "reload", "db_admin_error" => true);
		}
		else if($this->get("database", "wrong_user_password")) {
			return array("status" => "reload", "wrong_db_user_password" => true);
		}
		else if($this->get("database", "user_error")) {
			return array("status" => "reload", "db_user_error" => true);
		}


		message()->addMessage("Insufficient privileges for database creation", array("type" => "error"));
		return false;

	}



	// ACCOUNT

	// Check if user accounts already exist
	function checkAccountSettings() {
//		print "checkAccountSettings<br>\n";

		// reset account checks
		$this->set("account", "exists", false);
		$this->set("account", "passed", false);

		// We need a database connection to check for users

		// If SITE_DB is not defined
		if(!defined("SITE_DB")) {
			// create temp connection
			$db_check = $this->checkDatabaseConnection();
			if($db_check) {

				// Define temp SITE_DB constant
				define("SITE_DB", $this->get("database", "db_janitor_db"));

			}
			
		}
			
		// Is database connection enabled
		if(defined("SITE_DB") && SITE_DB) {

			include_once("classes/users/superuser.class.php");
			$UC = new SuperUser();

			// check if database already contains users
			$users = $UC->getUsers();
			if($users) {

				$this->set("account", "exists", true);
				$this->set("account", "passed", true);
				return true;

			}

		}

		// Do we have enough info to create admin account
		if($this->get("account", "account_username") && $this->get("account", "account_password")) {

			$this->set("account", "passed", true);
			return true;

		}

		return false;

	}

	// Update the admin account settings
	function updateAccountSettings() {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		if($this->validateList(array("account_username", "account_password", "account_nickname"))) {

			$this->set("account", "account_username", $this->getProperty("account_username", "value"));
			$this->set("account", "account_password", $this->getProperty("account_password", "value"));
			$this->set("account", "account_nickname", $this->getProperty("account_nickname", "value"));

		}
		
		return $this->checkAccountSettings();

	}



	// MAIL

	// check mail settings
	function checkMailSettings() {


		// reset mail checks
		$this->set("mail", "passed", false);
		$this->set("mail", "skipped", false);


		// if we do not have stored mail info, attempt to read existing connect_db.php
		if(!$this->get("mail", "mail_type") && file_exists(LOCAL_PATH."/config/connect_mail.php")) {

			$connection_info = file_get_contents(LOCAL_PATH."/config/connect_mail.php");

			preg_match("/\"ADMIN_EMAIL\", \"([a-zA-Z0-9\.\-\_\@]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->set("mail", "mail_admin", $matches[1]);
			}

			preg_match("/\"type\" \=\> \"([a-zA-Z0-9]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->set("mail", "mail_type", $matches[1]);
			}


			// MAILGUN
			if($this->get("mail", "mail_type") == "mailgun") {

				preg_match("/\"api-key\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
				if($matches) {
					$this->set("mail", "mail_mailgun_api_key", $matches[1]);
				}

				preg_match("/\"domain\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
				if($matches) {
					$this->set("mail", "mail_mailgun_domain", $matches[1]);
				}

			}

			// SMTP
			else {

				preg_match("/\"host\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
				if($matches) {
					$this->set("mail", "mail_smtp_host", $matches[1]);
				}

				preg_match("/\"port\" \=\> \"([0-9]+)\"/", $connection_info, $matches);
				if($matches) {
					$this->set("mail", "mail_smtp_port", $matches[1]);
				}

				preg_match("/\"username\" \=\> \"([a-zA-Z0-9\.\_\@\-]+)\"/", $connection_info, $matches);
				if($matches) {
					$this->set("mail", "mail_smtp_username", $matches[1]);
				}

				preg_match("/\"password\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
				if($matches) {
					$this->set("mail", "mail_smtp_password", $matches[1]);
				}

			}

		}

		// set default values
		else {

			$this->set("mail", "mail_admin", stringOr($this->get("mail", "mail_admin"), $this->get("account", "account_username")));
			$this->set("mail", "mail_type", stringOr($this->get("mail", "mail_type"), "smtp"));

			$this->set("mail", "mail_smtp_host", stringOr($this->get("mail", "mail_smtp_host"), "smtp.gmail.com"));
			$this->set("mail", "mail_smtp_port", stringOr($this->get("mail", "mail_smtp_port"), "587"));

		}


		// check if we have sufficient information
 		if($this->get("mail", "mail_admin") && 
			($this->get("mail", "mail_type") == "smtp" && (	
				$this->get("mail", "mail_smtp_host") && 
				$this->get("mail", "mail_smtp_port") && 
				$this->get("mail", "mail_smtp_username") && 
				$this->get("mail", "mail_smtp_password")
			))
				||
			($this->get("mail", "mail_type") == "mailgun" && (
				$this->get("mail", "mail_mailgun_api_key") && 
				$this->get("mail", "mail_mailgun_domain")
			))
		
		) {

			// set mail check
			$this->set("mail", "passed", true);
			return true;
		}

		return false;
	}

	// update the mail settings
 	function updateMailSettings() {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// Mail set up is optional
		if(getPost("skip_mail")) {

			$this->set("mail", "skipped", true);
			$this->set("mail", "passed", true);
			return true;

		}
		// Did we receive admin mail and setup type?
		else if($this->validateList(array("mail_admin", "mail_type"))) {

			$this->set("mail", "mail_admin", $this->getProperty("mail_admin", "value"));
			$this->set("mail", "mail_type", $this->getProperty("mail_type", "value"));

			// Mailgun account
			if($this->get("mail", "mail_type") == "mailgun") {

				if($this->validateList(array("mail_mailgun_api_key", "mail_mailgun_domain"))) {

					$this->set("mail", "mail_mailgun_api_key", $this->getProperty("mail_mailgun_api_key", "value"));
					$this->set("mail", "mail_mailgun_domain", $this->getProperty("mail_mailgun_domain", "value"));

				}
			}

			// SMTP account
			else {

				if($this->validateList(array("mail_smtp_host", "mail_smtp_port", "mail_smtp_username", "mail_smtp_password"))) {

					$this->set("mail", "mail_smtp_host", $this->getProperty("mail_smtp_host", "value"));
					$this->set("mail", "mail_smtp_port", $this->getProperty("mail_smtp_port", "value"));
					$this->set("mail", "mail_smtp_username", $this->getProperty("mail_smtp_username", "value"));
					$this->set("mail", "mail_smtp_password", $this->getProperty("mail_smtp_password", "value"));

				}

			} 

		}

		return $this->checkMailSettings();

	}



	// PAYMENT GATEWAY

	// check payment settings
	function checkPaymentSettings() {


		// reset payment checks
		$this->set("payment", "passed", false);
		$this->set("payment", "skipped", false);


		// if we do not have stored payment info, attempt to read existing connect_payment.php
		if(!$this->get("payment", "payment_type") && file_exists(LOCAL_PATH."/config/connect_payment.php")) {

			$connection_info = file_get_contents(LOCAL_PATH."/config/connect_payment.php");

			preg_match("/\"type\" \=\> \"([a-zA-Z0-9]+)\"/", $connection_info, $matches);
			if($matches) {
				$this->set("payment", "payment_type", $matches[1]);
			}


			// STRIPE
			if($this->get("payment", "payment_type") == "stripe") {

				preg_match("/\"secret-key\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
				if($matches) {
					$this->set("payment", "payment_stripe_private_key", $matches[1]);
				}

				preg_match("/\"public-key\" \=\> \"([a-zA-Z0-9\.\-]+)\"/", $connection_info, $matches);
				if($matches) {
					$this->set("payment", "payment_stripe_public_key", $matches[1]);
				}

			}

		}

		// set default values
		else {

			$this->set("payment", "payment_type", stringOr($this->get("payment", "payment_type"), "stripe"));

			$this->set("payment", "payment_stripe_private_key", stringOr($this->get("payment", "payment_stripe_private_key"), ""));
			$this->set("payment", "payment_stripe_public_key", stringOr($this->get("payment", "payment_stripe_public_key"), ""));

		}


		// check if we have sufficient information
 		if(
			($this->get("payment", "payment_type") == "stripe" && (	
				$this->get("payment", "payment_stripe_private_key") && 
				$this->get("payment", "payment_stripe_public_key")
			))
		) {

			// set payment check
			$this->set("payment", "passed", true);
			return true;
		}

		return false;
	}

	// update the payment settings
 	function updatePaymentSettings() {

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// Mail set up is optional
		if(getPost("skip_payment")) {

			$this->set("payment", "skipped", true);
			$this->set("payment", "passed", true);
			return true;

		}
		// Did we receive admin mail and setup type?
		else if($this->validateList(array("payment_type"))) {

			$this->set("payment", "payment_type", $this->getProperty("payment_type", "value"));

			// Mailgun account
			if($this->get("payment", "payment_type") == "stripe") {

				if($this->validateList(array("payment_stripe_private_key", "payment_stripe_public_key"))) {

					$this->set("payment", "payment_stripe_private_key", $this->getProperty("payment_stripe_private_key", "value"));
					$this->set("payment", "payment_stripe_public_key", $this->getProperty("payment_stripe_public_key", "value"));

				}
			}

			// Other account
			else {


			} 

		}

		return $this->checkPaymentSettings();

	}



	// FINISH

	// check ALL settings
	function checkAllSettings() {

		if(
			$this->get("software", "passed") &&
			$this->get("config", "passed") &&
			$this->get("database", "passed") &&
			$this->get("account", "passed") &&
			$this->get("mail", "passed") &&
			$this->get("payment", "passed") &&
			defined("LOCAL_PATH") &&
			defined("FRAMEWORK_PATH")
		) {

			// Check if environment looks like parentnode setup
			$this->parentnode_setup = file_exists("/srv/sites/apache/apache.conf");
			// find apachectl's
			$this->apachectls = explode("\n", trim(shell_exec("find /usr /opt /Users/".$this->get("system", "current_user")."/Applications -name 'apachectl' 2>/dev/null")));

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

			include_once("classes/system/upgrade.class.php");
			$UP = new Upgrade();


			// ONLY FOR NEW SETUP
			if(SETUP_TYPE == "new") {

				// INSTALL THEME FROM GITHUB IF THEME DOES NOT EXIST (OR IS EMPTY)
				if(!file_exists(PROJECT_PATH."/theme") || scandir(PROJECT_PATH."/theme") == array(".", "..") || (scandir(PROJECT_PATH."/theme") == array(".", "..", "www") && scandir(PROJECT_PATH."/theme/www") == array(".", "..", "index.php"))) {

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

					// Extraction on Windows
					if($this->get("system", "os") == "win") {
						// Extract
						$output = shell_exec('"C:/Program Files/7-Zip/7z.exe" x "'.PROJECT_PATH.'/theme.tar.gz" -o"'.PROJECT_PATH.'"');
						$output = shell_exec('"C:/Program Files/7-Zip/7z.exe" x "'.PROJECT_PATH.'/theme.tar" -o"'.PROJECT_PATH.'"');
					}
					// Extraction on Mac/Linux
					else {
						$output = shell_exec("tar -xzf ".PROJECT_PATH."/theme.tar.gz -C ".PROJECT_PATH." 2>&1");
					}

					// Replace existing theme
					$fs->removeDirRecursively(PROJECT_PATH."/theme");
					$fs->copy(PROJECT_PATH."/janitor-theme-minimal-master", PROJECT_PATH."/theme");

					// Clean up
					$fs->removeDirRecursively(PROJECT_PATH."/janitor-theme-minimal-master");
					unlink(PROJECT_PATH."/theme.tar.gz");


					// Status for installing theme
					if(file_exists(PROJECT_PATH."/theme") && file_exists(PROJECT_PATH."/theme/www/index.php") && file_exists(PROJECT_PATH."/theme/templates/pages/front.php")) {
						$tasks["completed"][] = "Standard theme installed";
					}
					// Task failed
					else {
						$tasks["failed"][] = "Standard theme (FAILED)";
						return $tasks;
					}

				}
				// Skip theme
				else {

					$tasks["completed"][] = "Skipped theme install (Found existing theme)";

				}


				// APACHE CONF

				// Create Apache conf from template
				if(file_exists(FRAMEWORK_PATH."/config/httpd-vhosts.template.conf")) {

					// apache
					$file_apache = file_get_contents(FRAMEWORK_PATH."/config/httpd-vhosts.template.conf");
					$file_apache = preg_replace("/###LOCAL_PATH###/", LOCAL_PATH, $file_apache);
					$file_apache = preg_replace("/###FRAMEWORK_PATH###/", FRAMEWORK_PATH, $file_apache);
					$file_apache = preg_replace("/###PROJECT_PATH###/", PROJECT_PATH, $file_apache);
					$file_apache = preg_replace("/###SITE_URL###/", $_SERVER["SERVER_NAME"], $file_apache);
					$file_apache = preg_replace("/###LOG_NAME###/", superNormalize($_SERVER["SERVER_NAME"]), $file_apache);
					$file_apache = preg_replace("/###MODULES_PATH###/", (preg_match("/\/submodules\//", FRAMEWORK_PATH) ? "submodules" : "core"), $file_apache);

					$fs->makeDirRecursively(PROJECT_PATH."/apache");
					file_put_contents(PROJECT_PATH."/apache/httpd-vhosts.conf", $file_apache);

//					unlink(LOCAL_PATH."/config/httpd-vhosts.template.conf");

					// Status for updating Apache conf
					$tasks["completed"][] = "Project Apache configuration updated";

				}

			}


			// Special case for old sites with an outdated database table layout
			// Solution is to Upgrade Janitor first (to avoid implementing upgrades in the setup)
			if(SETUP_TYPE == "existing" &&
				file_exists(LOCAL_PATH."/config/config.php") &&
				file_exists(LOCAL_PATH."/config/connect_db.php")
			) {
				$query = new Query();
				// check if outdated database table exists
				$sql = "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '".SITE_DB."' AND TABLE_NAME = 'languages'";
				if($query->sql($sql)) {
					// Status for updating config.php
					$tasks["failed"][] = "Old database table layout detected. You should upgrade your Janitor code and choose the upgrade option under setup, before you run the setup script again.";
					return $tasks;

				}

			}


			// FOR ALL SETUP TYPES

			// Make sure config path exists
			if(!file_exists(LOCAL_PATH."/config")) {
				$fs->makeDirRecursively(LOCAL_PATH."/config");
			}

			// Make sure templates path exists
			if(!file_exists(LOCAL_PATH."/templates")) {
				$fs->makeDirRecursively(LOCAL_PATH."/templates");
			}


			// WRITE CONFIGURATION FILES



			// CONFIG
			// config.php

			// Use existing config.php
			if(file_exists(LOCAL_PATH."/config/config.php")) {

				$file_config = file_get_contents(LOCAL_PATH."/config/config.php");
				$file_config = preg_replace("/(\n)[ \t]*define\(\"SITE_UID\",[ ]*\".+\"\);/", "\ndefine(\"SITE_UID\", \"".$this->get("config", "site_uid")."\");", $file_config);
				$file_config = preg_replace("/(\n)[ \t]*define\(\"SITE_NAME\",[ ]*\".+\"\);/", "\ndefine(\"SITE_NAME\", \"".$this->get("config", "site_name")."\");", $file_config);
				$file_config = preg_replace("/(\n)[ \t]*define\(\"SITE_EMAIL\",[ ]*\".+\"\);/", "\ndefine(\"SITE_EMAIL\", \"".$this->get("config", "site_email")."\");", $file_config);
				$file_config = preg_replace("/(\n)[ \t]*define\(\"DEFAULT_PAGE_DESCRIPTION\",[ ]*\".+\"\);/", "\ndefine(\"DEFAULT_PAGE_DESCRIPTION\", \"".$this->get("config", "site_description")."\");", $file_config);
				file_put_contents(LOCAL_PATH."/config/config.php", $file_config);

				// Make sure file remains writeable even if it is edited manually
				chmod(LOCAL_PATH."/config/config.php", 0777);

				// Status for updating config.php
				$tasks["completed"][] = "Project config.php updated";

			}
			// If template exists, use that
			else if(file_exists(FRAMEWORK_PATH."/config/config.template.php")) {

				// config
				$file_config = file_get_contents(FRAMEWORK_PATH."/config/config.template.php");
				$file_config = preg_replace("/###SITE_UID###/", $this->get("config", "site_uid"), $file_config);
				$file_config = preg_replace("/###SITE_NAME###/", $this->get("config", "site_name"), $file_config);
				$file_config = preg_replace("/###SITE_EMAIL###/", $this->get("config", "site_email"), $file_config);
				$file_config = preg_replace("/###DEFAULT_PAGE_DESCRIPTION###/", $this->get("config", "site_description"), $file_config);
				file_put_contents(LOCAL_PATH."/config/config.php", $file_config);

				// Make sure file remains writeable even if it is edited manually
				chmod(LOCAL_PATH."/config/config.php", 0777);

				// Status for creating config.php
				$tasks["completed"][] = "Project config.php created";

			}
			// Error
			else {

				// Status for updating config.php
				$tasks["failed"][] = "Project config.php not found (FAILED)";
				return $tasks;
			}



			// DATABASE
			// connect_db.php

			// Use existing connect_db.php
			if(file_exists(LOCAL_PATH."/config/connect_db.php")) {

				$file_db = file_get_contents(LOCAL_PATH."/config/connect_db.php");
				$file_db = preg_replace("/(\n)[ \t]*define\(\"SITE_DB\",[ ]*\".+\"\);/", "\ndefine(\"SITE_DB\", \"".$this->get("database", "db_janitor_db")."\");", $file_db);
				$file_db = preg_replace("/(\n)[ \t]*\"host\"[ ]*\=\>[ ]*\".+\"/", "\n\t\t\"host\" => \"".$this->get("database", "db_host")."\"", $file_db);
				$file_db = preg_replace("/(\n)[ \t]*\"username\"[ ]*\=\>[ ]*\".+\"/", "\n\t\t\"username\" => \"".$this->get("database", "db_janitor_user")."\"", $file_db);
				$file_db = preg_replace("/(\n)[ \t]*\"password\"[ ]*\=\>[ ]*\".+\"/", "\n\t\t\"password\" => \"".$this->get("database", "db_janitor_pass")."\"", $file_db);
				file_put_contents(LOCAL_PATH."/config/connect_db.php", $file_db);

				// Make sure file remains writeable even if it is edited manually
				chmod(LOCAL_PATH."/config/connect_db.php", 0777);

				// Status for updating connect_db.php
				$tasks["completed"][] = "Project connect_db.php updated";

			}
			// If template exists, use that
			else if(file_exists(FRAMEWORK_PATH."/config/connect_db.template.php")) {

				// database
				$file_db = file_get_contents(FRAMEWORK_PATH."/config/connect_db.template.php");
				$file_db = preg_replace("/###SITE_DB###/", $this->get("database", "db_janitor_db"), $file_db);
				$file_db = preg_replace("/###HOST###/", $this->get("database", "db_host"), $file_db);
				$file_db = preg_replace("/###USERNAME###/", $this->get("database", "db_janitor_user"), $file_db);
				$file_db = preg_replace("/###PASSWORD###/", $this->get("database", "db_janitor_pass"), $file_db);
				file_put_contents(LOCAL_PATH."/config/connect_db.php", $file_db);

				// Make sure file remains writeable even if it is edited manually
				chmod(LOCAL_PATH."/config/connect_db.php", 0777);

				// Status for creating connect_db.php
				$tasks["completed"][] = "Project connect_db.php created";

			}
			// Error
			else {

				// Status for updating config.php
				$tasks["failed"][] = "Project connect_db.php not found (FAILED)";
				return $tasks;
			}


			// CREATE DB

			// only create if it does not exist
			$check_db = $this->checkDatabaseConnection();
			if($check_db && !$this->get("database", "exists")) {

				$query = new Query();
				if($query->sql("CREATE DATABASE ".$this->get("database", "db_janitor_db"))) {

					$sql = "GRANT ALL PRIVILEGES ON ".$this->get("database", "db_janitor_db").".* TO '".$this->get("database", "db_janitor_user")."'@'".$this->get("database", "db_host")."' IDENTIFIED BY '".$this->get("database", "db_janitor_pass")."' WITH GRANT OPTION;";
					$query->sql($sql);

					// Status for creating database
					$tasks["completed"][] = "Database created";

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



			// MAIL
			// connect_mail.php
			if(!$this->get("mail", "skipped")) {

				// Use existing connect_mail.php
				if(file_exists(LOCAL_PATH."/config/connect_mail.php")) {

					$file_mail = file_get_contents(LOCAL_PATH."/config/connect_mail.php");
					$existing_mail_conf = true;

				}
				// If template exists, use that
				else if(file_exists(FRAMEWORK_PATH."/config/connect_mail.template.php")) {

					$file_mail = file_get_contents(FRAMEWORK_PATH."/config/connect_mail.template.php");
					$existing_mail_conf = false;

				}
				else {

					// Status for updating config.php
					$tasks["failed"][] = "connect_mail.php not found (FAILED)";
					return $tasks;

				}

				// Replace admin email
				$file_mail = preg_replace("/(\n)[ \t]*define\(\"ADMIN_EMAIL\",[ ]*\".*\"\);/", "\ndefine(\"ADMIN_EMAIL\", \"".$this->get("mail", "mail_admin")."\");", $file_mail);

				// Create new settings
				$file_mail_settings = "array(\n";
				$file_mail_settings .= "\t\t\"type\" => \"".$this->get("mail", "mail_type")."\",\n";

				// mailgun settings
				if($this->get("mail", "mail_type") == "mailgun") {
					$file_mail_settings .= "\t\t\"api-key\" => \"".$this->get("mail", "mail_mailgun_api_key")."\",\n";
					$file_mail_settings .= "\t\t\"domain\" => \"".$this->get("mail", "mail_mailgun_domain")."\",\n";
				
				}
				// SMTP settings
				else {
					$file_mail_settings .= "\t\t\"host\" => \"".$this->get("mail", "mail_smtp_host")."\",\n";
					$file_mail_settings .= "\t\t\"username\" => \"".$this->get("mail", "mail_smtp_username")."\",\n";
					$file_mail_settings .= "\t\t\"password\" => \"".$this->get("mail", "mail_smtp_password")."\",\n";
					$file_mail_settings .= "\t\t\"port\" => \"".$this->get("mail", "mail_smtp_port")."\",\n";

					// fixed values
					$file_mail_settings .= "\t\t\"smtpauth\" => true,\n";
					if($this->get("mail", "mail_smtp_port") == "587") {
						$file_mail_settings .= "\t\t\"secure\" => \"tls\",\n";
					}
					else {
						$file_mail_settings .= "\t\t\"secure\" => \"ssl\",\n";
					}
				}

				$file_mail_settings .= "\t)";

				// Replace settings
				$file_mail = preg_replace("/array\([^$]+\t\)/", $file_mail_settings, $file_mail);

				file_put_contents(LOCAL_PATH."/config/connect_mail.php", $file_mail);

				// Make sure file remains writeable even if it is edited manually
				chmod(LOCAL_PATH."/config/connect_mail.php", 0777);


				// Status for creating connect_mail.php
				$tasks["completed"][] = "Project connect_mail.php " . ($existing_mail_conf ? "updated" : "created");

			}
			// Skip mail setup
			else {

				// Delete existing connect_mail.php
				if(file_exists(LOCAL_PATH."/config/connect_mail.php")) {
					unlink(LOCAL_PATH."/config/connect_mail.php");
				}

				$tasks["completed"][] = "Mail setup skipped";

			}



			// PAYMENT
			// connect_payment.php
			if(!$this->get("payment", "skipped")) {

				// Use existing connect_mail.php
				if(file_exists(LOCAL_PATH."/config/connect_payment.php")) {

					$file_payment = file_get_contents(LOCAL_PATH."/config/connect_payment.php");
					$existing_payment_conf = true;

				}
				// If template exists, use that
				else if(file_exists(FRAMEWORK_PATH."/config/connect_payment.template.php")) {

					$file_payment = file_get_contents(FRAMEWORK_PATH."/config/connect_payment.template.php");
					$existing_payment_conf = false;

				}
				else {

					// Status for updating config.php
					$tasks["failed"][] = "connect_payment.php not found (FAILED)";
					return $tasks;

				}

				// Create new settings
				$file_payment_settings = "array(\n";
				$file_payment_settings .= "\t\t\"type\" => \"".$this->get("payment", "payment_type")."\",\n";

				// mailgun settings
				if($this->get("payment", "payment_type") == "stripe") {
					$file_payment_settings .= "\t\t\"secret-key\" => \"".$this->get("payment", "payment_stripe_private_key")."\",\n";
					$file_payment_settings .= "\t\t\"public-key\" => \"".$this->get("payment", "payment_stripe_public_key")."\",\n";
				
				}
				// Other gateway
				else {

				}

				$file_payment_settings .= "\t)";

				// Replace settings
				$file_payment = preg_replace("/array\([^$]+\t\)/", $file_payment_settings, $file_payment);

				file_put_contents(LOCAL_PATH."/config/connect_payment.php", $file_payment);


				// Make sure file remains writeable even if it is edited manually
				chmod(LOCAL_PATH."/config/connect_payment.php", 0777);
			


				// Status for creating connect_payment.php
				$tasks["completed"][] = "Project connect_payment.php " . ($existing_payment_conf ? "updated" : "created");

			}
			// Skip payment setup
			else {

				// Delete existing connect_payment.php
				if(file_exists(LOCAL_PATH."/config/connect_payment.php")) {
					unlink(LOCAL_PATH."/config/connect_payment.php");
				}

				$tasks["completed"][] = "Payment setup skipped";

			}



			// Define SITE_NAME if not already defined and temp Database connection established
			if(!defined("SITE_DB") && $check_db) {
				define("SITE_DB", $this->get("database", "db_janitor_db"));

				// include database constants
				@include_once("config/database.constants.php");
			}

			// Define SITE_NAME if not already defined
			if(!defined("SITE_NAME")) {
				define("SITE_NAME", $this->get("config", "site_name"));
			}

			// Define SITE_EMAiL if not already defined
			if(!defined("SITE_EMAIL")) {
				define("SITE_EMAIL", $this->get("config", "site_email"));
			}


			// Check needed constants
			if(defined("SITE_DB") && SITE_DB == $this->get("database", "db_janitor_db") && defined("SITE_NAME") && defined("SITE_EMAIL")) {
				$tasks["completed"][] = "Constants verified";
			}
			else {
				$tasks["failed"][] = "Constants mismatch";
				return $tasks;
			}



			// Can only be included after SITE_DB has been declared
			include_once("classes/users/superuser.class.php");
			$UC = new SuperUser();



			// DEFAULT DATA

			// always make sure public and private folder exists
			$fs->makeDirRecursively(LOCAL_PATH."/library/private");
			$fs->makeDirRecursively(LOCAL_PATH."/library/public");

			$tasks["completed"][] = "Library created";



			// VERIFY DATABASE TABLES
			$query = new Query();
			$query->checkDbExistence(UT_LANGUAGES);
			$query->checkDbExistence(UT_CURRENCIES);
			$query->checkDbExistence(UT_COUNTRIES);
			$query->checkDbExistence(UT_VATRATES);
			$query->checkDbExistence(UT_PAYMENT_METHODS);
			$query->checkDbExistence($UC->db_user_groups);
			$query->checkDbExistence($UC->db);


			// make sure item tables exist
			$query->checkDbExistence(UT_ITEMS);
			$query->checkDbExistence(UT_TAG);
			$query->checkDbExistence(UT_TAGGINGS);

			$query->checkDbExistence(UT_ITEMS_MEDIAE);
			$query->checkDbExistence(UT_ITEMS_COMMENTS);
			$query->checkDbExistence(UT_ITEMS_RATINGS);
			$query->checkDbExistence(UT_ITEMS_PRICES);

			// navigation requires items - must be run after items
			$query->checkDbExistence(UT_NAV);
			$query->checkDbExistence(UT_NAV_NODES);

			$tasks["completed"][] = "Database tables verified";



			// DEFAULT DATA


			// CREATE LANGUAGE
			$UP->checkDefaultValues(UT_LANGUAGES);

			// CREATE CURRENCY
			$UP->checkDefaultValues(UT_CURRENCIES);

			// CREATE COUNTRY
			$UP->checkDefaultValues(UT_COUNTRIES);


			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS)) {
				$UP->checkDefaultValues(UT_SUBSCRIPTION_METHODS);
			}

			if((defined("SITE_SHOP") && SITE_SHOP)) {
				$UP->checkDefaultValues(UT_VATRATES);
				$UP->checkDefaultValues(UT_PAYMENT_METHODS);
			}

			// CREATE BASE NAVIGATION
			$UP->checkDefaultValues(UT_NAV);
			$UP->checkDefaultValues(UT_NAV_NODES);


			$tasks["completed"][] = "Default data checked";




			//
			// CREATE DEFAULT USER GROUPS AND USERS
			//

			// check user groups
			$user_groups = $UC->getUserGroups();
			if(!$user_groups) {

				$UP->checkDefaultValues($UC->db_user_groups);

				$user_groups = $UC->getUserGroups();
				if($user_groups && count($user_groups) == 3) {
					$tasks["completed"][] = "Default user groups created";
				}
				else {
					$tasks["failed"][] = "Default user groups (FAILED)";
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
				if($UC->updateAccess(array("updateAccess", 3))) {
					$tasks["completed"][] = "Developer permissions added";
				}
				else {
					$tasks["failed"][] = "Developer permissions (FAILED)";
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

				
				$UP->checkDefaultValues($UC->db);


				$users = $UC->getUsers(["user_group_id" => 3]);

				// Anonymous user will not be returned by getUsers()
				if($users && count($users) == 1) {

					// Did user type account nickname
					if($this->get("account", "account_nickname")) {
						unset($_POST);
						$_POST["nickname"] = $this->get("account", "account_nickname");
						$UC->update(array("update", $users[0]["id"]));
					}

					// SET USERNAME
					unset($_POST);
					$_POST["email"] = $this->get("account", "account_username");
					$username = $UC->updateEmail(array("updateEmail", $users[0]["id"]));

					// VERIFY USERNAME
					$UC->setVerificationStatus($username["username_id"], $users[0]["id"], 1);

					// SET PASSWORD
					unset($_POST);
					$_POST["password"] = $this->get("account", "account_password");
					$UC->setPAssword(array("setPassword", $users[0]["id"]));

					// store user_id for content creation
					session()->value("user_id", $users[0]["id"]);

					$tasks["completed"][] = "Default users created";

				}
				else {

					$tasks["failed"][] = "Default users";
					return $tasks;
				}

			}
			else {
				$tasks["completed"][] = "Users: OK";
			}


			// Make sure CMS messages are not waiting
			$messages = message()->getMessages(array("type" => "message"));
			if($messages) {
				// Add messages to completed task list
				foreach($messages as $message) {
					$tasks["completed"][] = $message;
				}
			}

			$errors = message()->getMessages(array("type" => "error"));
			if($errors) {
				// Add error to failed task list
				foreach($errors as $error) {
					$tasks["failed"][] = $error;
				}
			}

			// get rid of messages
			message()->resetMessages();


			//
			// GIT SETTINGS
			//
			// create git ignore
			if(!file_exists(PROJECT_PATH."/.gitignore") && file_exists(FRAMEWORK_PATH."/config/gitignore.template")) {
				copy(FRAMEWORK_PATH."/config/gitignore.template", PROJECT_PATH."/.gitignore");

				// Make sure file remains writeable even if it is edited manually
				chmod(PROJECT_PATH."/.gitignore", 0777);

				$tasks["completed"][] = "Git ignore file added (.gitignore)";
			}

			// Add Git attributes
			if(!file_exists(PROJECT_PATH."/.gitattributes") && file_exists(FRAMEWORK_PATH."/config/gitattributes.template")) {
				copy(FRAMEWORK_PATH."/config/gitattributes.template", PROJECT_PATH."/.gitattributes");

				// Make sure file remains writeable even if it is edited manually
				chmod(PROJECT_PATH."/.gitattributes", 0777);

				$tasks["completed"][] = "Git attributes file added (.gitattributes)";
			}


			// Tell git to ignore file permission changes
			exec("cd ".PROJECT_PATH." && git config core.filemode false");
			exec("cd ".PROJECT_PATH."/submodules/janitor && git config core.filemode false");
			if(file_exists(PROJECT_PATH."/submodules/js-merger")) {
				exec("cd ".PROJECT_PATH."/submodules/js-merger && git config core.filemode false");
			}
			if(file_exists(PROJECT_PATH."/submodules/css-merger")) {
				exec("cd ".PROJECT_PATH."/submodules/css-merger && git config core.filemode false");
			}
			if(file_exists(PROJECT_PATH."/submodules/asset-builder")) {
				exec("cd ".PROJECT_PATH."/submodules/asset-builder && git config core.filemode false");
			}


			$tasks["completed"][] = "Git filemode updated to false";


			// Set final file permissions
			// Live environment
			if($this->get("config", "site_deployment") == "live") {

				// CANNOT CHANGE FILE OWNER WITHOUT BEING ROOT OR SUDU
				// USER WILL HAVE TO DO THIS ON THEIR OWN

				// if($this->recurseFilePermissions(PROJECT_PATH,
				// 	"root",
				// 	$this->get("system", "deploy_user"),
				// 	0755)
				// ) {
				//
				// 	if($this->recurseFilePermissions(LOCAL_PATH."/library",
				// 		$this->get("system", "apache_user"),
				// 		$this->get("system", "deploy_user"),
				// 		0700)
				// 	) {
				// 		$tasks["completed"][] = "File permissions updated for production environment";
				// 	}
				// 	else {
				// 		$tasks["failed"][] = "File permissions could not be updated for production environment";
				// 		return $tasks;
				// 	}
				//
				// }
				// else {
				// 	$tasks["failed"][] = "File permissions could not be updated for production environment";
				// 	return $tasks;
				// }

			}
			// Dev environment
			else {

				if($this->get("system", "os") == "win") {
					$tasks["completed"][] = "File permissions left untouched for Windows development environment";
				}
				else if($this->recurseFilePermissions(PROJECT_PATH,
					$this->get("system", "apache_user"),
					$this->get("system", "deploy_user"),
					0777)
				) {
					$tasks["completed"][] = "File permissions updated for development environment";
				}
				else {
					$tasks["failed"][] = "File permissions could not be updated for development environment";
					return $tasks;
				}

			}



			// If this is a new setup
			// Send welcome email with password 
			if(SETUP_TYPE == "new" && $this->get("account", "account_username") && $this->get("account", "account_password")) {
				mailer()->send(array(
					"subject" => "Welcome to Janitor", 
					"message" => "Your Janitor project is ready.\n\nLog in to your new Janitor project: ".SITE_URL."/janitor\n\nUsername: ".$this->get("account", "account_username")."\nPassword: ".$this->get("account", "account_password")."\n\nSee you soon,\n\nJanitor",
					"tracking" => false
				));
			}

			
//			$this->reset();

			return $tasks;
		}

		return false;
	}


	// Pull latest updates from repository
	function pull() {

		// only allow pull updates on systems deployed on linux
		// commands will cause permission errors in development environments on win/mac
		if($this->get("system", "os") == "unix") {

			// Get project path
			$project_path = realpath(LOCAL_PATH."/..");

			// Get git origin
			$remote_origin = trim(shell_exec("cd '$project_path' && git config --get remote.origin.url"));
			// Remove any existing username:password from remote url
			$remote_origin = preg_replace("/(http[s]?):\/\/(([^:]+)[:]?([^@]+)@)?/", "$1://", $remote_origin);

			// Get branch
			$branch = trim(shell_exec("cd '$project_path' && git rev-parse --abbrev-ref HEAD"));
			// debug([$remote_origin, $branch]);

			// Was git username and password sent
			$git_username = getPost("git_username");
			$git_password = getPost("git_password");

			// Both username and password was provided
			if($git_username && $git_password) {

				$credentials = preg_replace("/(http[s]?):\/\/([a-zA-Z0-9\-\.]+)[^$]+/", "$1://$git_username:$git_password@$2", $remote_origin);

			}
			// Only username was provided
			else if($git_username) {

				$credentials = preg_replace("/(http[s]?):\/\/([a-zA-Z0-9\-\.]+)[^$]+/", "$1://$git_username@$2", $remote_origin);

			}
			// Nothing was provided
			else {

				$credentials = preg_replace("/(http[s]?):\/\/([a-zA-Z0-9\-\.]+)[^$]+/", "$1://$2", $remote_origin);

			}


			// Update git credentials file to allow pull command to execute without credentials in command-line
			file_put_contents(PRIVATE_FILE_PATH."/.git_credentials", $credentials);

			$command = "cd '$project_path' && sudo git pull '$remote_origin' '$branch' && sudo git submodule update";
			// Local test
			// $command = "cd '$project_path' && git pull '$remote_origin' '$branch' && git submodule update";
			// debug($command);

			$output = shell_exec($command);
			// debug($output);


			// Remove username:password from credential file (storing is temporary on purpose)
			unlink(PRIVATE_FILE_PATH."/.git_credentials");

			// Return response
			return $output;
		}

		return false;

	}

}

?>
