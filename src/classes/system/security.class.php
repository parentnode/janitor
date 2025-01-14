<?php

/**
* This class contains the site security functionality
* 
* It
* - handles logins
* - detects suspicious user behaviour
*/
class Security {


	private $runtime_cache = [];


	function __construct() {


		// $this->crossCheckUserData();

	}


	// TODO: Think about how to implement a general "suspicious activity" handler
	function crossCheckUserData() {
		debug(["crossCheckUserData"]);
		// If IP is blocked


		// If IP is not equal session IP
		// If UA is not equal session UA

		if(
			!cache()->value("blocked-".session()->value("ip"))
			&&
			$_SERVER["REQUEST_METHOD"] == "POST"
			&&
			(
				session()->value("ip") !== security()->getRequestIp()
				||
				session()->value("useragent") !== ((isset($_SERVER["HTTP_USER_AGENT"]) && $_SERVER["HTTP_USER_AGENT"]) ? stripslashes($_SERVER["HTTP_USER_AGENT"]) : "Unknown")
			)
		) {

			// Make sure to block session stored IP and resolved IP
			cache()->value("blocked-".session()->value("ip"), true, 3600);
			cache()->value("blocked-".security()->getRequestIp(), true, 3600);

			// Notify admin
			notify()->send([
				"subject" => "USER BLOCKED DUE TO SUSPICIOUS ACTIVITY",
				"message" => "User attempted a POST while either IP or UA changed during session",
				"tracking" => false,
				"template" => "system",
			]);


		}


		if(cache()->value("blocked-".session()->value("ip"))) {

			if(!defined("CUSTOM_BLOCK_HANDLING") || !CUSTOM_BLOCK_HANDLING) {
				if(defined("USER_BLOCKED_URL") && USER_BLOCKED_URL) {
					header("Location: ".USER_BLOCKED_URL);
				}
				else {
					print "USER HAS BEEN BLOCKED DUE TO SUSPICIOUS ACTIVITY";
				}
			}
			

			session()->reset();
			exit();
		}

	}


	// Avoid multiple session and filesystem lookups by allowing values to be cached per runtime
	function getValue($key) {

		if(!isset($this->runtime_cache[$key])) {
			if($key === "user_group_id") {
				$this->runtime_cache[$key] = session()->value("user_group_id");
			}
			else if($key === "csrf") {
				$this->runtime_cache[$key] = session()->value("csrf");
			}
			else {
				return NULL;
			}
			
		}
		return $this->runtime_cache[$key];
	}
	function setValue($key, $value) {
		$this->runtime_cache[$key] = $value;
	}
	function resetRuntimeValues() {
		$this->runtime_cache = [];
	}


	/**
	* Validate access permission for full /controller/action path
	* Used to check if a path is valid when generating links, form actions, etc
	*
	* @param $path String containing full /controller/action path
	* @return boolean Allowed or not
	*/
	function validatePath($path) {
		// debug(["validatePath", $path]);
		// return true;

		// remove GET parameters from $actions string
		$path = preg_replace("/\?.+$/", "", $path);
		// remove trailing slash
		$path = preg_replace("/\/$/", "", $path);

		// add index to our testing path to catch root controllers (index.php)
		$test_path = $path."/index";


		// create fragments array for controller identification
		$controller = false;
		$fragments = explode("/", $test_path);

		// loop through fragments while removing one fragment in each loop until only one fragment exists
		while($fragments) {

			// create new /controller/action path to check in permissions array
			$path_test = implode("/", $fragments);

			// make theoretic controller path to test
			// if path contains /janitor/admin it is a janitor core controller
			if(preg_match("/^\/janitor\/admin/", $path_test)) {
				$controller_test = FRAMEWORK_PATH."/www".preg_replace("/^\/janitor\/admin/", "", $path_test).".php";
			}
			// path could be setup script
			else if(preg_match("/^\/setup/", $path_test)) {
				$controller_test = FRAMEWORK_PATH."/".$path_test.".php";
			}
			// local controller
			else {
				$controller_test = LOCAL_PATH."/www".$path_test.".php";
			}

			// debug(["controller_test:" . $controller_test]);


			// does controller exist
			if(file_exists($controller_test)) {
				// debug(["Found controller:", $controller_test]);

				// controller is found
				$controller = $path_test;

				$access_item = $this->getValue("access_item_".$controller_test);
				if(!$access_item) {
					// read access_item of controller
					$read_access = true;
					include($controller_test);

					$this->setValue("access_item_".$controller_test, $access_item);
				}

				// end while loop
				break;
			}

			// controller is still not found, pop another fragment off
			array_pop($fragments);
		}

		// debug(["controller", $controller, "access_item", $access_item]);
		// both controller and access_item is found
		if($controller && isset($access_item)) {

			// deduce action
			$action = substr($path, strlen($controller));

			// This will replace multiple occurences of $controller string (should only ever replace first)
			// $action = str_replace($controller, "", $path);

			// check permissions
			$permission = $this->getValue("action_".$controller."_".$action);
			if(!isset($permission)) {
				$permission = $this->checkPermissions($controller, $action, $access_item);
				$this->setValue("action_".$controller."_".$action, $permission);
			}
			return $permission;
		}


//		print "no controller or access_item found<br>\n";
		// no controller or access_item found
		return false;
	}


	/**
	* Access permission check
	*
	* If access_item is false, access is granted
	*
	* On first session validation, get permissions and store in session to avoid excessive DB lookups
	*
	* Iterate action to find match in access_item of the controller
	* If the full action does not exist, one fragment will be removed until a match is found
	*
	* If no match is found, no access is granted. Default restriction when access_item is not false!
	* If no user_group is present, no access is granted.
	*
	* If a access_item is set for path, it will be tested in the access table against the 
	* current users group access.
	*
	* @param String $controller controller to check permissions for
	* @param String $action action to check permissions for
	* @param String $access_item access_item of controller
	* @return boolean Allowed or not
	*/
	function checkPermissions($controller, $action, $access_item) {


		global $mysqli_global;
		// debug(["checkPermissions", "controller:", $controller]);
		// print "controller:" . $controller . "<br>\n";
		// print "action:" . $action . "<br>\n";
		// print_r($access_item);
		// print "<br>\n";


		// all actions are allowed on SITE_INSTALL
		if((defined("SITE_INSTALL") && SITE_INSTALL)) {
//			print "all good";
			return true;
		}


		// no access restrictions
		if($access_item === false) {
			return true;
		}

		// SITE_DB is required to look up access permissions
		else if(!defined("SITE_DB") || !$mysqli_global) {
			print "Your site is not configured yet!";
			exit();
		}

		// get actions fragments as array to make it easier to remove fragments
		// first index in fragments will be empty to indicate controller root
		$fragments = explode("/", $action);


		// loop through fragments while removing one fragment in each loop until only one fragment exists
		while($fragments) {

			// create new /controller/action path to check in permissions array
			$action_test = implode("/", $fragments);

			// does actions test exist in access_item
			if(isset($access_item[$action_test])) {

				// check if access_item points to other access_item
				if($access_item[$action_test] !== true && $access_item[$action_test] !== false) {
					$action_test = $access_item[$action_test];
				}

				// end while loop
				break;
			}

			// /controller/action/ path not found - remove a fragment and try again
			array_pop($fragments);
		}

		// action should be at least a slash
		$action_test = ($action_test ? $action_test : "/");


		// no entry found in access_item while iteration action fragments
		// must be an illegal controller/action path
		// - deny access
		if(!isset($access_item[$action_test])) {
//			print "no access item entry<br>\n";

			return false;
		}
		// no access restrictions for this action
		else if($access_item[$action_test] === false) {
//			print "no restriction<br>\n";

			return true;
		}
		// matching access item requires access check
		else {

			// get group and permissions from session
			$user_group_id = $this->getValue("user_group_id"); //session()->value("user_group_id");
			// debug(["group", $user_group_id]);
			//$permissions = session()->value("user_group_permissions");


			// TEMP
			$permissions = false;

			// any access restriction requires a user to be logged in (optionally as Guest - user_group 1, user 1)
			// no need to do any validation if no user_group_id is found
			if(!$user_group_id) {
//				print "no group<br>\n";

				return false;
			}

			$permissions = cache()->value("user_group_".$user_group_id."_permissions");
			// debug(["permissions", $permissions]);

			// if permissions does not exist for this user_group in cache
			// this requires a database lookup - result is stored in cache 
			// get user_access for user_group
			if(!$permissions) {

				$query = new Query();
				$sql = "SELECT controller, action, permission FROM ".SITE_DB.".user_access WHERE user_group_id = ".$user_group_id;
				// print $sql."<br>\n";

				if($query->sql($sql)) {
					$results = $query->results();

					// parse result in easy queryable structure
					// $permission[controller][action] = 1
					foreach($results as $result) {
						$permissions[$result["controller"]][$result["action"]] = $result["permission"];
					}

				}

				cache()->value("user_group_".$user_group_id."_permissions", $permissions);
				// store permissions in session
				// session()->value("user_group_permissions", $permissions);
			}


			// print_r($permissions);
			// print $controller . " /// " . $action_test . "<br>\n\n";

			// do the actual access check
			if(isset($permissions[$controller]) && isset($permissions[$controller][$action_test]) && $permissions[$controller][$action_test]) {
//				print "!1!<br>\n";
				return true;
			}

		}

//		print "everything failed<br>\n";
		return false;
	}


	// simple validate action function to determine whether to write out urls for data attributes
	function validPath($path) {
		if($this->validatePath($path)) {
			return $path;
		}
		return "";
	}


	// validate csrf token
	function validateCsrfToken() {

		// validate csrf-token on all requests? - Csrf token should always be validated (I think)
//		if(!(defined("SITE_INSTALL") && SITE_INSTALL)) {

			// if POST, check csrf token
			if($_SERVER["REQUEST_METHOD"] == "POST" &&
				(
					!isset($_POST["csrf-token"]) || 
					!$_POST["csrf-token"] || 
					$_POST["csrf-token"] != session()->value("csrf")
				)
			) {

				message()->addMessage("CSRF Autorization failed.", array("type" => "error"));

				// make sure the user is logged out (throwoff will exit)
				if(session()->value("user_id") > 1) {
					$this->throwOff();
					
				}
				// user wasn't logged in, it's probably a timeout issue
				else if($_SERVER["HTTP_REFERER"]) {
					message()->addMessage("Your session may have expired or it has been confused by multiple simultaneous logins. Please try again.", array("type" => "error"));
					header("Location:". $_SERVER["HTTP_REFERER"]);
					exit();
				}

				return false;
			}
			else if($_SERVER["REQUEST_METHOD"] != "POST") {

				return false;

			}
//		}

		return true;
	}



	/**
	* Log in
	*/
	function logIn() {

		$username = getPost("username");
		$password = getPostPassword("password");

		if(cache()->value("login-blocked-".session()->value("ip"))) {
			message()->addMessage("You have been blocked, due to repeated invalid login attempts. Try again in 5 minutes", array("type" => "error"));
			return false;
		}

		if($username && $password) {
			$query = new Query();

			// password table check
			// password table has not been upgraded
			if(!$query->sql("SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name = 'upgrade_password' AND TABLE_NAME = 'user_passwords' AND TABLE_SCHEMA = '".SITE_DB."'")) {

				include_once("classes/system/upgrade.class.php");
				$UG = new Upgrade();

				// move password to password_upgrade
				$UG->renameColumn(SITE_DB.".user_passwords", "password", "upgrade_password");
				
				// add new password column
				$UG->addColumn(SITE_DB.".user_passwords", "password", "varchar(255) NOT NULL DEFAULT ''", "user_id");

			}


			// Get user password
			$sql = "SELECT passwords.password as password, passwords.upgrade_password as upgrade_password, passwords.id as password_id FROM ".SITE_DB.".user_usernames as usernames, ".SITE_DB.".user_passwords as passwords WHERE usernames.user_id = passwords.user_id AND (passwords.password != '' OR passwords.upgrade_password != '') AND usernames.username='$username'";
			// debug(["sql", $sql]);
			if($query->sql($sql)) {

				$hashed_password = $query->result(0, "password");
				$sha1_password = $query->result(0, "upgrade_password");
				$password_id = $query->result(0, "password_id");

				// old sha1 password exists and matches
				// User password should be upgraded
				if($sha1_password && sha1($password) === $sha1_password) {

					// create new hash 
					$hashed_password = password_hash($password, PASSWORD_DEFAULT);
					if($hashed_password) {
						// and add it to password table and delete old sha1 password
						$sql = "UPDATE ".SITE_DB.".user_passwords SET upgrade_password = '', password = '$hashed_password' WHERE id = $password_id";
						$query->sql($sql);
					}

				}

				// hashed password corresponds to posted password
				if($hashed_password && password_verify($password, $hashed_password)) {

					// make login query
					// look for active user with verified username and password
					$sql = "SELECT users.id as id, users.user_group_id as user_group_id, users.nickname as nickname FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames, ".SITE_DB.".user_passwords as passwords WHERE users.status = 1 AND usernames.verified = 1 AND users.id = usernames.user_id AND usernames.user_id = passwords.user_id AND passwords.id = $password_id AND usernames.username='$username'";
					// debug([$sql]);
					if($query->sql($sql)) {

						// add user_id and user_group_id to session
						session()->value("user_id", intval($query->result(0, "id")));
						session()->value("user_group_id", intval($query->result(0, "user_group_id")));
						session()->value("user_nickname", $query->result(0, "nickname"));
						session()->value("last_login_at", date("Y-m-d H:i:s"));
						// session()->reset("user_group_permissions");

						// Update login timestamp
						$sql = "UPDATE ".SITE_DB.".users SET last_login_at=CURRENT_TIMESTAMP WHERE users.id = ".session()->value("user_id");
						$query->sql($sql);

						logger()->addLog("Login: ".$username .", user_id:".session()->value("user_id"));

						// set new csrf token for user
						session()->value("csrf", gen_uuid());


						$this->resetRuntimeValues();


						// regerate Session id
						sessionStart();
						session_regenerate_id(true);
						sessionEnd();


						// Special return for ajax logins
						if(getPost("ajaxlogin")) {
							$output = new Output();
							$output->screen(array("csrf-token" => session()->value("csrf")));

						}
						else {

							// redirect to originally requested page
							$login_forward = stringOr(getVar("login_forward"), session()->value("login_forward"));
							// print "login_forward:" . $login_forward."<br>";


							// TODO: Regex is temp quickfix to avoid being redirected to API endpoints after login
							// TODO: will be an easy fix when API methods are prefixed
							if(!$login_forward || !$this->validatePath($login_forward) || preg_match("/\/(save|update|add|remove|delete|upload|duplicate|keepAlive)/", $login_forward)) {
								$login_forward = "/";
							}

							session()->reset("login_forward");

							header("Location: " . $login_forward);
						}
						exit();
					}

					// User could not be logged in

					// is the reason, that the user has not been verified yet?
					// make login query and
					// look for user with status 0, verified = 0, password exists
					$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames, ".SITE_DB.".user_passwords as passwords WHERE users.id = usernames.user_id AND usernames.user_id = passwords.user_id AND passwords.id = $password_id AND username='$username' AND verified = 0";
					// print $sql;
					if($query->sql($sql)) {

						// Make sure we have the email username
						$login_user = $query->result(0);
						if($login_user["type"] != "email") {

							// Look for user email
							$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE users.id = usernames.user_id AND usernames.type='email' AND users.id = ".$login_user["id"];
		//					print "$sql<br />\n";
							if($query->sql($sql)) {
								$login_user = $query->result(0);
							}
					
						}

						// Did we find user email
						if($login_user["type"] == "email") {

							$user_id = $query->result(0, "id");
							$nickname = $query->result(0, "nickname");
							$email = $query->result(0, "username");
							$verification_code = $query->result(0, "verification_code");

							// send verification reminder email
							mailer()->send(array(
								"values" => array(
									"NICKNAME" => $nickname, 
									"EMAIL" => $email, 
									"VERIFICATION" => $verification_code,
								), 
								"recipients" => $email, 
								"template" => "signup_reminder"
							));

							$username_id = $this->getUsernameId($email, $user_id);

							// Add to user log
							$sql = "INSERT INTO ".SITE_DB.".user_log_verification_links SET user_id = ".$user_id.", username_id = ".$username_id;
				//			print $sql;
							$query->sql($sql);


							message()->addMessage("User has not been verified yet – did you forget to activate your account? We just sent you a new verification email in case the other one got lost.", array("type" => "error"));
							return ["status" => "NOT_VERIFIED", "email" => $email];

						}

					}

				}

			}
			
			// is the reason, that the user doesn't have a password yet?
			// make login query and
			// look for user without password
			$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE users.id = usernames.user_id AND usernames.user_id NOT IN (SELECT user_id FROM ".SITE_DB.".user_passwords as passwords) AND usernames.username='$username'";
//					print $sql;
			if($query->sql($sql)) {
				$login_user = $query->result(0);
				
				// Make sure we have the email username
				if($login_user["type"] != "email") {
					
					// Look for user email
					$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE users.id = usernames.user_id AND usernames.type='email' AND users.id = ".$login_user["id"];
					// print "$sql<br />\n";
					if($query->sql($sql)) {
						$login_user = $query->result(0);
					}
					
				}
				
				// Did we find user email
				if($login_user["type"] == "email") {
					
					$user_id = $query->result(0, "id");
					$nickname = $query->result(0, "nickname");
					$email = $query->result(0, "username");
					$verification_code = $query->result(0, "verification_code");
					
				}
				
				// has the user not been verified yet?
				// look for user with status 0, verified = 0
				$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE users.status = 0 AND users.id = usernames.user_id AND username='$username' AND verified = 0";

				if($query->sql($sql)) {
					// send verification reminder email
					mailer()->send(array(
						"values" => array(
							"NICKNAME" => $nickname, 
							"EMAIL" => $email, 
							"VERIFICATION" => $verification_code,
						), 
						"recipients" => $email, 
						"template" => "signup_reminder"
					));				
					
					$username_id = $this->getUsernameId($email, $user_id);
					// print_r($user	name_id);

					
					// Add to user log
					$sql = "INSERT INTO ".SITE_DB.".user_log_verification_links SET user_id = ".$user_id.", username_id = ".$username_id;
		//			print $sql;
					$query->sql($sql);

					message()->addMessage("The account has not yet been verified. We have re-sent the verification email just now.", array("type" => "error"));
					return ["status" => "NOT_VERIFIED", "email" => $email];

				}

				// has the user been verified and subsequently deactivated?
				// look for user with status 0, verified = 1
				$sql = "SELECT users.id, users.nickname, usernames.username, usernames.type, usernames.verification_code FROM ".SITE_DB.".users as users, ".SITE_DB.".user_usernames as usernames WHERE users.status = 0 AND users.id = usernames.user_id AND username='$username' AND verified = 1";

				if($query->sql($sql)) {
					logger()->addLog("Login error: ".$username);

					message()->addMessage("You could not be logged in. Contact your administrator on ".ADMIN_EMAIL." to resolve the issue.", array("type" => "error"));
					return false;

				}

				$username_id = $this->getUsernameId($email, $user_id);
				
				// Add to user log
				$sql = "INSERT INTO ".SITE_DB.".user_log_verification_links SET user_id = ".$user_id.", username_id = ".$username_id;
	//			print $sql;
				$query->sql($sql);

				message()->addMessage("The account does not have a password yet. Create one via the <em>Forgot passord</em> link below.", array("type" => "error"));
				return ["status" => "NO_PASSWORD", "email" => $email];

			}

		}

		logger()->addLog("Login error: ".$username);


		$retry_status = cache()->value("login-attempts-".session()->value("ip"));
		if($retry_status) {
			$retry_status++;
		}
		else {
			$retry_status = 1;
		}

		if($retry_status < 3) {
			cache()->value("login-attempts-".session()->value("ip"), $retry_status, 300);
		}
		else {
			cache()->reset("login-attempts-".session()->value("ip"));
			cache()->value("login-blocked-".session()->value("ip"), true, 300);


			message()->addMessage("You have been blocked, due to repeated invalid login attempts. Try again in 5 minutes", array("type" => "error"));
			logger()->addLog("User blocked for 5 minutes: ".$username);
		}
		

		message()->addMessage("Login was unsuccessful.", array("type" => "error"));
		return false;
	}


	/**
	* Log in using token
	*/
	function tokenLogIn() {

		// Allow GET parameters
		$token = getVar("token");
		$username = getVar("username");

		if($token && $username) {
			$query = new Query();

			// make login query
			// look for user with username and password
			$sql = "SELECT users.id as id, users.user_group_id as user_group_id, users.nickname as nickname FROM ".SITE_DB.".users as users, ".SITE_DB.".user_apitokens as tokens, ".SITE_DB.".user_usernames as usernames WHERE users.status = 1 AND users.id = usernames.user_id AND usernames.user_id = tokens.user_id AND tokens.token='$token' AND usernames.username='$username'";
//			print $sql;
			if($query->sql($sql)) {


				// add user_id and user_group_id to session
				session()->value("user_id", $query->result(0, "id"));
				session()->value("user_group_id", $query->result(0, "user_group_id"));
				// session()->reset("user_group_permissions");
				session()->value("user_nickname", $query->result(0, "nickname"));

				logger()->addLog("Token login, username: ".$username .", user_id:".session()->value("user_id"));

				// set new csrf token for user
				session()->value("csrf", gen_uuid());


				$this->resetRuntimeValues();


				// regerate Session id
				session_start();
				session_regenerate_id(true);
				session_write_close();


				if(getVar("credentials")) {
					$output = new Output();
					$output->screen(array("csrf-token" => session()->value("csrf")));
					exit;
				}

				return;
			}
		}

		logger()->addLog("Token login error, username: ".$username);

		message()->addMessage("Computer says NO!", array("type" => "error"));
		return false;
	}

	/**
	* Simple logoff
	* Logoff user and redirect to login page
	*/
	function logOff() {

		logger()->addLog("Logoff: user_id:".session()->value("user_id"));
		//$this->user_id = "";

		session()->reset("user_id");
		session()->reset("user_group_id");
		// session()->reset("user_group_permissions");

		$dev = session()->value("dev");
		$segment = session()->value("segment");


		// Delete cart reference cookie
		setcookie("cart_reference", "", time() - 3600, "/");
		
		// Reset session (includes destroy, start and regenerate)
		session()->reset();

		security()->resetRuntimeValues();

		// Remember dev and segment even after logout
		session()->value("dev", $dev);
		session()->value("segment", $segment);

		header("Location: /");
		exit();
	}

	/**
	* Throw off if user is caught on page without permission
	*
	* @param String $url Optional url to forward to after login
	*/
	function throwOff($url=false) {

		global $page;

		$url = $url ? $url : $page->url;

		// Log and send in email
		logger()->addLog("Throwoff - insufficient privileges:".$url." by ". session()->value("user_id"));
		notify()->send(array(
			"subject" => "Throwoff - " . SITE_URL, 
			"message" => "insufficient privileges:".$url, 
			"template" => "system"
		));

		// something is fishy, clean up
		unset($_GET);
		unset($_POST);
		unset($_FILES);


		// Preserve messages
		$messages = message()->getMessages();


		session()->reset();


		foreach($messages as $type => $message_type) {
			foreach($message_type as $message) {
				message()->addMessage($message, $type);
			}
		}

		session()->value("login_forward", $url);
		print '<script type="text/javacript">location.href="'.SITE_LOGIN_URL.'?page_status=logoff"</script>';

		header("Location: ".SITE_LOGIN_URL);

		exit();
	}


	/**
	 * Get username id
	 *
	 * @param string $username
	 * @param integer $user_id
	 * 
	 * @return integer|false
	 */
	function getUsernameId($username, $user_id) {

		$query = new Query;

		$sql = "SELECT id FROM ".SITE_DB.".user_usernames WHERE username = '$username' AND user_id = $user_id";

		if($query->sql($sql)) {
			return $query->result(0)["id"];
		}

		return false;

	}

	/**
	* Get IP used for current request
	*/
	function getRequestIp() {
		return (getenv("HTTP_X_FORWARDED_FOR") ? getenv("HTTP_X_FORWARDED_FOR") : getenv("REMOTE_ADDR"));
	}

}