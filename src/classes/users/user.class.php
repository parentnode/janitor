<?php
/**
* @package janitor.users
* This file contains simple user functionality
*
* Simple user is supposed to be a minimal interface to User maintenance and the user tables
* It is vital that this class does not expose anything but the current user's information
*
*
* Only for NON-Admin creation of users, like
* - signups on website
* - newsletter signup
* - placement of orders by unregistered users
*
*
* Creates a member user (user_group=2), with limited privileges
* - update profile
* - newsletter administration
* - own order view (on shops)
*
* - comments if allowed (not decided how that is to be implemented)
* - ratings if allowed (not decided how that is to be implemented)
*/

/**
* TODO
* compare functionality need with User class
* consider extending Simpleuser from User to avoid duplet functionality
* (only if resonable overlap)
* (requires the ability to overwrite funtions - test it)
*
*
* These updates will require rewriting of Shop class and shop functionality (there is no meaningful way around it)
*/

/**
* Simpleuser
*/
class User extends Model {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// basic usertables
		$this->db = SITE_DB.".users";
		$this->db_usernames = SITE_DB.".user_usernames";
		$this->db_addresses = SITE_DB.".user_addresses";
		$this->db_passwords = SITE_DB.".user_passwords";
		$this->db_newsletters = SITE_DB.".user_newsletters";



		// BASIC INFO

		// Nickname
		$this->addToModel("nickname", array(
			"type" => "string",
			"label" => "Nickname",
			"required" => true,
			"hint_message" => "Write your nickname or whatever you want us to use to greet you", 
			"error_message" => "Nickname must be filled out"
		));
		// Firstname
		$this->addToModel("firstname", array(
			"type" => "string",
			"label" => "Firstname",
			"hint_message" => "Write your first- and middlenames",
			"error_message" => "Write your first- and middlenames"
		));
		// Lastname
		$this->addToModel("lastname", array(
			"type" => "string",
			"label" => "Lastname",
			"hint_message" => "Write your lastname",
			"error_message" => "Write your lastname"
		));
		// Language
		$this->addToModel("language", array(
			"type" => "string",
			"label" => "Your preferred language",
			"hint_message" => "Select your preferred language",
			"error_message" => "Invalid language"
		));


		// USERNAMES AND PASSWORD

		// email
		$this->addToModel("email", array(
			"type" => "email",
			"label" => "Your email",
			"hint_message" => "You can log in using your email",
			"error_message" => "Invalid email"
		));

		// mobile
		$this->addToModel("mobile", array(
			"type" => "tel",
			"label" => "Your mobile",
			"hint_message" => "Write your mobile number",
			"error_message" => "Invalid number"
		));

		// password
		$this->addToModel("password", array(
			"type" => "password",
			"label" => "Your new password",
			"hint_message" => "Type your new password - must be 8-20 characters",
			"error_message" => "Invalid password"
		));


		// ADDRESS INFO

		// address label
		$this->addToModel("address_label", array(
			"type" => "string",
			"label" => "Address label",
			"required" => true,
			"hint_message" => "Give this address a label (home, office, parents, etc.)",
			"error_message" => "Invalid label"
		));
		// address name
		$this->addToModel("address_name", array(
			"type" => "string",
			"label" => "Name/Company",
			"required" => true,
			"hint_message" => "Name on door at address, your name or company name",
			"error_message" => "Invalid name"
		));
		// att
		$this->addToModel("att", array(
			"type" => "string",
			"label" => "Att",
			"hint_message" => "Att for address",
			"error_message" => "Invalid att"
		));
		// address 1
		$this->addToModel("address1", array(
			"type" => "string",
			"label" => "Address",
			"required" => true,
			"hint_message" => "Address",
			"error_message" => "Invalid address"
		));
		// address 2
		$this->addToModel("address2", array(
			"type" => "string",
			"label" => "Additional address",
			"hint_message" => "Additional address info",
			"error_message" => "Invalid address"
		));
		// city
		$this->addToModel("city", array(
			"type" => "string",
			"label" => "City",
			"required" => true,
			"hint_message" => "Write your city",
			"error_message" => "Invalid city"
		));
		// postal code
		$this->addToModel("postal", array(
			"type" => "string",
			"label" => "Postal code",
			"required" => true,
			"hint_message" => "Postalcode of your city",
			"error_message" => "Invalid postal code"
		));
		// state
		$this->addToModel("state", array(
			"type" => "string",
			"label" => "State/region",
			"hint_message" => "Write your state/region, if applicaple",
			"error_message" => "Invalid state/region"
		));
		// country
		$this->addToModel("country", array(
			"type" => "string",
			"label" => "Country",
			"required" => true,
			"hint_message" => "Country",
			"error_message" => "Invalid country"
		));


	}



	/**
	* Get current user
	*
	*/
	function getUser() {

		// default values

		$query = new Query();
		$user_id = session()->value("user_id");

		$sql = "SELECT * FROM ".$this->db." WHERE id = $user_id";
//			print $sql;
		if($query->sql($sql)) {
			$user = $query->result(0);


			$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				$usernames = $query->results();
				foreach($usernames as $username) {
					$user["usernames"][$username["type"]] = $username["username"];
				}
			}

			$sql = "SELECT * FROM ".$this->db_addresses." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				$user["addresses"] = $query->results();
			}

			$sql = "SELECT * FROM ".$this->db_newsletters." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				$user["newsletters"] = $query->results();
			}


			return $user;
		}

		return false;
	}



	function getUserInfo($_options=false) {

		// default values
		$user_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "user_id"        : $user_id          = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific user
		if($user_id) {

			$sql = "SELECT nickname FROM ".$this->db." WHERE id = $user_id";
//			print $sql;
			if($query->sql($sql)) {
				$user = $query->result(0);
				return $user;
			}
		}

		return false;
	}

	// NOTE: All output should be kept in frontend logic because it might need to be served in different language
	// or with specific context


	// create new user
	// email is minimum info to create user at this point (signup to newsletter)
	function newUser($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$email = $this->getProperty("email", "value");

		// if user already exists, return user_id
		if($this->userExists(array("email" => $email))) {
			return array("status" => "USER_EXISTS");
		}


		// does values validate
		if(count($action) == 1 && $this->validateList(array("email")) && $email) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db);

			// get entities for current value
			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(nickname|firstname|lastname|language)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			// if no base values were posted, use email as nickname
			if(!$values) {
				$values[] = "nickname='".$email."'";
			}

			// add member user group
			$values[] = "user_group_id=2";

			$sql = "INSERT INTO ".$this->db." SET " . implode(",", $values);
//				print $sql;

			if($query->sql($sql)) {

				$user_id = $query->lastInsertId();

				// add email to user_usernames
				$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, type = 'email', user_id = $user_id";
				$query->sql($sql);

				// add temp password
				$temp_password = randomKey(8);
				$password = sha1($temp_password);
				$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$password'";
				$query->sql($sql);


				// maybe this is not always a good idea, but it is for now :)
				// let the new user be logged in
				session()->value("user_id", $user_id);
				session()->value("user_group_id", 1);

				// send welcome email
				global $page;
				$page->mail(array("subject" => "signup", "message" => "EMAIL:$email\nPASSWORD:$temp_password", "recipients" => $email, "template" => "signup_newsletter"));


				// return enough information to the frontend
				return array("user_id" => $user_id, "email" => $email);
			}
		}

		return false;
	}

	// xxx/(email|mobile)/#email|mobile#
	function confirmUser($action) {

		// does values validate
		if(count($action) == 3) {

			$query = new Query();
			$type = $action[1];
			$username = $action[2];

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = '$type' AND username = '$username'";
			if($query->sql($sql)) {
				$user_id = $query->result(0, "user_id");

				$sql = "UPDATE ".$this->db_usernames." SET verified = 1 WHERE type = '$type' AND username = '$username'";
				$query->sql($sql);

				$query->sql("UPDATE ".$this->db." SET status = 1 WHERE id = $user_id");
				$query->sql($sql);
			}

		}
	}

	// check if user exists
	function userExists($_options) {

		$email = false;
		$mobile = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "email"      : $email        = $_value; break;
					case "mobile"     : $mobile       = $_value; break;
				}
			}
		}

		$query = new Query();

		// check for users with same email
		if($email) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = 'email' AND username = '$email'";
//			print $sql;
			if($query->sql($sql)) {
				return true;
			}
		}

		// check for users with same mobile
		if($mobile) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = 'mobile' AND username = '$mobile'";
//			print $sql;
			if($query->sql($sql)) {
				return true;
			}
		}

		return false;
	}


	// updateNewsletter/#newsletter#/#state#
	function updateNewsletter($action) {

		// does values validate
		if(count($action) == 3) {

			$query = new Query();
			$user_id = session()->value("user_id");
			$newsletter = $action[1];
			$state = $action[2];


			// make sure type tables exist
			$query->checkDbExistance($this->db_newsletters);

			if($state) {
				// already signed up
				$sql = "SELECT id FROM $this->db_newsletters WHERE user_id = $user_id AND newsletter = '$newsletter'";
				if(!$query->sql($sql)) {
					$sql = "INSERT INTO $this->db_newsletters SET user_id = $user_id, newsletter = '$newsletter'";
					$query->sql($sql);
				}
			}
			else {
				$sql = "DELETE FROM $this->db_newsletters WHERE user_id = $user_id, newsletter = '$newsletter'";
				$query->sql($sql);
			}

			return true;
		}

		return false;
	}


	//
	// /**
	// * Create a new address for user_id
	// *
	// * @param $user_id Integer User_id to add address to
	// * @param $address Array of address information
	// *
	// * @return Integer address_id or false on failure
	// */
	// function addAddress($user_id, $address) {
	//
	// 	if($address) {
	// 		$query = new Query();
	//
	// 		$values = array();
	// 		foreach($address as $column => $value) {
	// 			if($value && preg_match("/^(address_name|address_label|att|address1|address2|city|postal|state|country)$/", $column)) {
	// 				$values[] = "$column = '$value'";
	// 			}
	// 		}
	//
	// 		if($values) {
	// 			$sql = "INSERT INTO ".$this->db_addresses." SET user_id = $user_id, ".implode(",", $values);
	// 			if($query->sql($sql)) {
	// 				return array("address_id" => $query->lastInsertId());
	// 			}
	// 		}
	// 	}
	//
	// 	return false;
	// }
	//
	// /**
	// * Create a new address for user_id
	// *
	// * @param $address_id Integer address.id of address to update
	// * @param $address Array of address information
	// *
	// * @return Integer address_id or false on failure
	// */
	// function updateAddress($address_id, $address) {
	//
	// 	if($address) {
	// 		$query = new Query();
	//
	// 		$values = array();
	// 		foreach($address as $column => $value) {
	// 			if($value && preg_match("/^(address_name|address_label|att|address1|address2|city|postal|state|country)$/", $column)) {
	// 				$values[] = "$column = '$value'";
	// 			}
	// 		}
	//
	// 		if($values) {
	// 			$sql = "UPDATE ".$this->db_addresses." SET ".implode(",", $values)." WHERE id = $address_id";
	// 			if($query->sql($sql)) {
	// 				return array("address_id" => $query->lastInsertId());
	// 			}
	// 		}
	// 	}
	//
	// 	return false;
	// }
	//
	//
	//
	// /**
	// * Update newsletters for individual user
	// *
	// * @param $user_id Integer User_id to update newsletter setting for
	// * @param $newslettes Array Named array of [newsletter] = setting
	// */
	// function updateNewsletters($user_id, $newsletters){
	//
	// 	if($newsletters) {
	//
	// 		$query = new Query();
	//
	// 		foreach($newsletters as $newsletter => $setting) {
	// 			// remove newsletter subscription
	// 			if(!$setting) {
	// 				$sql = "DELETE FROM ".$this->db_newsletters." WHERE user_id = $user_id AND newsletter = '$newsletter'";
	// 				$query->sql($sql);
	// 			}
	// 			// add newsletter subscription if not set already
	// 			else {
	// 				$sql = "SELECT id FROM ".$this->db_newsletters." WHERE user_id = $user_id AND newsletter = '$newsletter'";
	// 				if(!$query->sql($sql)) {
	//
	// 					$sql = "INSERT INTO ".$this->db_newsletters." SET user_id = $user_id, newsletter = '$newsletter'";
	// 					$query->sql($sql);
	// 				}
	// 			}
	// 		}
	// 	}
	// }
	//
	//
	//
	//
	//
	// /**
	// * NON CONTROLLER FUNCTIONS
	// */
	//
	// // Create simple user - user_group 99
	// // used for frontend purposes, to create clients with very limited privileges
	// // mobile, email, nickname
	// // TODO: improve validation of content,
	// // even though these should be validated before passed to this function
	// function createUser($_options) {
	//
	// 	$nickname = false;
	// 	$firstname = false;
	// 	$lastname = false;
	// 	$language = false;
	//
	// 	$email = false;
	// 	$mobile = false;
	//
	// 	if($_options !== false) {
	// 		foreach($_options as $_option => $_value) {
	// 			switch($_option) {
	//
	// 				case "nickname"       : $nickname         = $_value; break;
	// 				case "firstname"      : $firstname        = $_value; break;
	// 				case "lastname"       : $lastname         = $_value; break;
	// 				case "language"       : $language         = $_value; break;
	//
	// 				case "email"          : $email            = $_value; break;
	// 				case "mobile"         : $mobile           = $_value; break;
	// 			}
	// 		}
	// 	}
	//
	// 	$query = new Query();
	//
	//
	// 	// check if simple user_group exists
	// 	// create simple group if it does not exist
	// 	$sql = "SELECT id FROM ".$this->db." WHERE user_group_id = 2";
	// 	if(!$query->sql($sql)) {
	// 		$query->checkDbExistance($this->db_user_groups);
	// 		$sql = "INSERT INTO ".$this->db_user_groups." SET id = 2, user_group = 'Member'";
	// 		$query->sql($sql);
	// 	}
	//
	// 	// create simple user
	// 	if($nickname) {
	//
	// 		$query->checkDbExistance($this->db);
	//
	// 		$values = array();
	// 		$values[] = "nickname='$nickname'";
	//
	// 		if($firstname) {
	// 			$values[] = "firstname='$firstname'";
	// 		}
	// 		if($lastname) {
	// 			$values[] = "lastname='$lastname'";
	// 		}
	// 		if($language) {
	// 			$values[] = "language='$language'";
	// 		}
	//
	// 		// create user
	// 		$sql = "INSERT INTO ".$this->db." SET ".implode(",", $values).", user_group_id = 2";
	// 		if($query->sql($sql)) {
	// 			$user_id = $query->lastInsertId();
	// 		}
	//
	// 		if($user_id) {
	// 			$query->checkDbExistance($user->db_usernames);
	//
	// 			if($email) {
	// 				// set usernames
	// 				$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, type = 'email', user_id = $user_id";
	// 				$query->sql($sql);
	// 			}
	//
	// 			if($mobile) {
	// 				$sql = "INSERT INTO $this->db_usernames SET username = '$mobile', verified = 0, type = 'mobile', user_id = $user_id";
	// 				$query->sql($sql);
	// 			}
	//
	// 			return $user_id;
	// 		}
	// 	}
	// 	return false;
	// }
	//
	//
	// /**
	// * Update basic user data - seamless function with no messages
	// * Used to update user details from checkout flows and other situations where
	// * user account is not direct action but a secondary effect
	// */
	// function updateUser($user_id, $_options) {
	//
	// 	$nickname = false;
	// 	$email = false;
	// 	$mobile = false;
	//
	// 	if($_options !== false) {
	// 		foreach($_options as $_option => $_value) {
	// 			switch($_option) {
	//
	// 				case "nickname"       : $nickname         = $_value; break;
	// 				case "email"          : $email            = $_value; break;
	// 				case "mobile"       : $mobile           = $_value; break;
	// 			}
	// 		}
	// 	}
	//
	// 	$query = new Query();
	//
	// 	// update user nickname
	// 	if($nickname) {
	// 		$sql = "UPDATE ".$this->db." SET nickname='$nickname' WHERE id = ".$user_id;
	// 		$query->sql($sql);
	// 	}
	//
	// 	// update user email
	// 	if($email) {
	// 		$sql = "UPDATE ".$this->db_usernames." SET username='$email' WHERE user_id = $user_id AND type = 'email'";
	// 		$query->sql($sql);
	// 	}
	//
	// 	// update user mobile
	// 	if($mobile) {
	// 		$sql = "UPDATE ".$this->db_usernames." SET username='$mobile' WHERE user_id = $user_id AND type = 'mobile'";
	// 		$query->sql($sql);
	// 	}
	//
	// }
	//
	//
	// /**
	// * Validate username info to avoid too many unneccesary duplet users
	// * Look for users with same email and mobile because such combinations indicates same user
	// */
	// function matchUsernames($_options) {
	//
	// 	$email = false;
	// 	$mobile = false;
	//
	// 	if($_options !== false) {
	// 		foreach($_options as $_option => $_value) {
	// 			switch($_option) {
	//
	// 				case "email"         : $email          = $_value; break;
	// 				case "mobile"        : $mobile         = $_value; break;
	//
	// 			}
	// 		}
	// 	}
	//
	// 	// user with matching email and mobile
	// 	if($email && $mobile) {
	//
	// 		$email_matches = $this->getUsers(array("email" => $email));
	// 		$mobile_matches = $this->getUsers(array("mobile" => $mobile));
	//
	// 		if($email_matches && $mobile_matches) {
	// 			foreach($email_matches as $user) {
	// 				if(array_search($user, $mobile_matches) !== -1) {
	// 					return $user["user_id"];
	// 				}
	// 			}
	// 		}
	// 	}
	// 	else if($email) {
	//
	// 		$email_matches = $this->getUsers(array("email" => $email));
	// 		if($email_matches) {
	// 			return $email_matches[0]["user_id"];
	// 		}
	// 	}
	// 	else if($mobile) {
	//
	// 		$mobile_matches = $this->getUsers(array("mobile" => $mobile));
	// 		if($mobile_matches) {
	// 			return $mobile_matches[0]["user_id"];
	// 		}
	//
	// 	}
	//
	//
	// 	return false;
	// }
	//
	//
	// /**
	// * Validate address info to avoid too many unneccesary duplet addresses
	// * Look for addresses with same user_id and label because such combinations indicates same address
	// *
	// * @param $user_id Integer User_id to find matching address for
	// * @param $_options Array of optional fields to use for address comparison
	// * address_label => String Address label
	// * address1 => String First address line
	// */
	// function matchAddress($user_id, $_options) {
	//
	// 	$address_label = false;
	// 	$address1 = false;
	//
	// 	if($_options !== false) {
	// 		foreach($_options as $_option => $_value) {
	// 			switch($_option) {
	//
	// 				case "address_label"  : $address_label    = $_value; break;
	// 				case "address1"       : $address1         = $_value; break;
	//
	// 			}
	// 		}
	// 	}
	//
	// 	$query = new Query();
	//
	// 	// look for matching address_label and address1
	// 	if($address_label && $address1) {
	//
	// 		$sql = "SELECT id FROM ".$this->db_addresses." WHERE user_id = $user_id AND address_label = '$address_label' AND address1 =  '$address1'";
	// 		if($query->sql($sql)) {
	// 			return $query->result(0, "id");
	// 		}
	// 		else {
	// 			return false;
	// 		}
	// 	}
	// 	// matching address_label
	// 	else if($address_label) {
	//
	// 		$sql = "SELECT id FROM ".$this->db_addresses." WHERE user_id = $user_id AND address_label = '$address_label'";
	// 		if($query->sql($sql)) {
	// 			return $query->result(0, "id");
	// 		}
	// 		else {
	// 			return false;
	// 		}
	// 	}
	//
	// 	return false;
	// }



}

?>