<?php
/**
* @package e-types.users
* This file contains simple user functionality
* for NON-Admin creation of users (signups on website)
*/

/**
* Simpleuser
*/
class Simpleuser {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->db = SITE_DB.".users";
		$this->db_usernames = SITE_DB.".user_usernames";
		$this->db_addresses = SITE_DB.".user_addresses";
		$this->db_passwords = SITE_DB.".user_passwords";
		$this->db_newsletters = SITE_DB.".user_newsletters";

		$this->db_user_groups = SITE_DB.".user_groups";

	}



	/**
	* Get users
	*
	* Get specific user_id
	* Get users with email as username and user_group = 99
	* Get users with mobile as username and user_group = 99
	*/
	function getUsers($_options=false) {

		// default values
		$user_id = false;
		$order = "status DESC, id DESC";

		$email = false;
		$mobile = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "user_id"        : $user_id          = $_value; break;
					case "order"          : $order            = $_value; break;

					case "email"          : $email            = $_value; break;
					case "mobile"       : $mobile           = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific user
		if($user_id) {

			$sql = "SELECT * FROM ".$this->db." WHERE id = $user_id";
//			print $sql;
			if($query->sql($sql)) {
				$user = $query->result(0);
				return $user;
			}
		}

		// get users with email as username
		else if($email) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." as names, ".$this->db." as users WHERE type = 'email' AND username = '$email' AND names.user_id = users.id AND users.user_group_id = 99";
//			print $sql;
			if($query->sql($sql)) {
				return $query->results();
			}
		}
		// get users with mobile as username
		else if($mobile) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." as names, ".$this->db." as users WHERE type = 'mobile' AND username = '$mobile' AND names.user_id = users.id AND users.user_group_id = 99";
//			print $sql;
			if($query->sql($sql)) {
				return $query->results();
			}
		}

		return false;
	}



	/**
	* Get usernames or specific username
	*
	* @param $_options Array of query settings
	* user_id => Integer User_id (required)
	* type => String email|mobile
	*
	* @return Array set of usernames or specific username type (mobile/email) if available or false on failure
	*/
	function getUsernames($_options) {

		$user_id = false;
		$type = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"  : $user_id    = $_value; break;
					case "type"     : $type       = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id) {

			// return specific username
			if($type) {
				$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id AND type = '$type'";
				if($query->sql($sql)) {
					return $query->result(0, "username");
				}
				return false;
			}
			// return all usernames for user
			else {
				$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id";
				if($query->sql($sql)) {
					return $query->results();
				}
			}

		}

		return false;
	}



	// check if password exists
	function issetPassword($user_id) {
		//
	}

	// set new password for user
	function setPassword($action) {
	
		$password;
	}

	// start reset password procedure
	function resetPassword() {}


	// return addresses
	// can return all addresses for a user, or a specific address
	// TODO: translate country ISO to country text
	// TODO: simplify simpleuser->getAddresses to be restricted to same user actions
	function getAddresses($_options) {

		$user_id = false;
		$address_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "user_id"     : $user_id       = $_value; break;
					case "address_id"  : $address_id    = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id) {

			$sql = "SELECT * FROM ".$this->db_addresses." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				return $query->results();
			}

		}
		else if($address_id) {
			$sql = "SELECT * FROM ".$this->db_addresses." WHERE address_id = $address_id";
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}
		
		
	}





	/**
	* Create a new address for user_id
	*
	* @param $user_id Integer User_id to add address to
	* @param $address Array of address information
	*
	* @return Integer address_id or false on failure
	*/
	function addAddress($user_id, $address) {

		if($address) {
			$query = new Query();

			$values = array();
			foreach($address as $column => $value) {
				if($value && preg_match("/^(address_name|address_label|att|address1|address2|city|postal|state|country)$/", $column)) {
					$values[] = "$column = '$value'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db_addresses." SET user_id = $user_id, ".implode(",", $values);
				if($query->sql($sql)) {
					return array("address_id" => $query->lastInsertId());
				}
			}
		}

		return false;
	}

	/**
	* Create a new address for user_id
	*
	* @param $address_id Integer address.id of address to update
	* @param $address Array of address information
	*
	* @return Integer address_id or false on failure
	*/
	function updateAddress($address_id, $address) {

		if($address) {
			$query = new Query();

			$values = array();
			foreach($address as $column => $value) {
				if($value && preg_match("/^(address_name|address_label|att|address1|address2|city|postal|state|country)$/", $column)) {
					$values[] = "$column = '$value'";
				}
			}

			if($values) {
				$sql = "UPDATE ".$this->db_addresses." SET ".implode(",", $values)." WHERE id = $address_id";
				if($query->sql($sql)) {
					return array("address_id" => $query->lastInsertId());
				}
			}
		}

		return false;
	}




	/**
	* get newsletter info
	*
	* get state of specific newsletter for specific user
	* get newsletters for user
	* get all newsletters (list of available newsletters)
	*
	* @param $_options Array of optional query settings
	* user_id => Integer User_id (if omitted, list of available newsletters will be returned)
	* newsletter => String newsletter to check setting for
	*
	* @return Array set of newsletters or Boolean for specific newsletter setting
	*/
	function getNewsletters($_options=false) {

		$user_id = false;
		$newsletter = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "newsletter"     : $newsletter     = $_value; break;
					case "user_id"        : $user_id        = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id) {

			// check for specific newsletter for specific user
			if($newsletter) {
				$sql = "SELECT * FROM ".$this->db_newsletters." WHERE user_id = $user_id AND newsletter = '$newsletter'";
				if($query->sql($sql)) {
					return true;
				}
			}
			// get newsletters for specific user
			else {
				$sql = "SELECT * FROM ".$this->db_newsletters." WHERE user_id = $user_id";
				if($query->sql($sql)) {
					return $query->results();
				}
			}

		}
		// get list of all newsletters
		else if(!isset($_options["user_id"]) && !isset($_options["newsletter"])){

			$sql = "SELECT newsletter FROM ".$this->db_newsletters." GROUP BY newsletter";
			if($query->sql($sql)) {
				return $query->results();
			}
		}

	}

	/**
	* Update newsletters for individual user
	*
	* @param $user_id Integer User_id to update newsletter setting for
	* @param $newslettes Array Named array of [newsletter] = setting
	*/
	function updateNewsletters($user_id, $newsletters){

		if($newsletters) {

			$query = new Query();

			foreach($newsletters as $newsletter => $setting) {
				// remove newsletter subscription
				if(!$setting) {
					$sql = "DELETE FROM ".$this->db_newsletters." WHERE user_id = $user_id AND newsletter = '$newsletter'";
					$query->sql($sql);
				}
				// add newsletter subscription if not set already
				else {
					$sql = "SELECT id FROM ".$this->db_newsletters." WHERE user_id = $user_id AND newsletter = '$newsletter'";
					if(!$query->sql($sql)) {

						$sql = "INSERT INTO ".$this->db_newsletters." SET user_id = $user_id, newsletter = '$newsletter'";
						$query->sql($sql);
					}
				}
			}
		}
	}





	/**
	* NON CONTROLLER FUNCTIONS
	*/

	// Create simple user - user_group 99
	// used for frontend purposes, to create clients with very limited privileges
	// mobile, email, nickname
	// TODO: improve validation of content, 
	// even though these should be validated before passed to this function
	function createUser($_options) {

		$nickname = false;
		$firstname = false;
		$lastname = false;
		$language = false;

		$email = false;
		$mobile = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "nickname"       : $nickname         = $_value; break;
					case "firstname"      : $firstname        = $_value; break;
					case "lastname"       : $lastname         = $_value; break;
					case "language"       : $language         = $_value; break;

					case "email"          : $email            = $_value; break;
					case "mobile"         : $mobile           = $_value; break;
				}
			}
		}

		$query = new Query();


		// check if simple user_group exists
		// create simple group if it does not exist
		$sql = "SELECT id FROM ".$this->db." WHERE user_group_id = 2";
		if(!$query->sql($sql)) {
			$query->checkDbExistance($this->db_user_groups);
			$sql = "INSERT INTO ".$this->db_user_groups." SET id = 2, user_group = 'Member'";
			$query->sql($sql);
		}

		// create simple user
		if($nickname) {

			$query->checkDbExistance($this->db);

			$values = array();
			$values[] = "nickname='$nickname'";

			if($firstname) {
				$values[] = "firstname='$firstname'";
			}
			if($lastname) {
				$values[] = "lastname='$lastname'";
			}
			if($language) {
				$values[] = "language='$language'";
			}

			// create user
			$sql = "INSERT INTO ".$this->db." SET ".implode(",", $values).", user_group_id = 2";
			if($query->sql($sql)) {
				$user_id = $query->lastInsertId();
			}

			if($user_id) {
				$query->checkDbExistance($user->db_usernames);

				if($email) {
					// set usernames
					$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, type = 'email', user_id = $user_id";
					$query->sql($sql);
				}

				if($mobile) {
					$sql = "INSERT INTO $this->db_usernames SET username = '$mobile', verified = 0, type = 'mobile', user_id = $user_id";
					$query->sql($sql);
				}

				return $user_id;
			}
		}
		return false;
	}


	/**
	* Update basic user data - seamless function with no messages
	* Used to update user details from checkout flows and other situations where
	* user account is not direct action but a secondary effect
	*/
	function updateUser($user_id, $_options) {

		$nickname = false;
		$email = false;
		$mobile = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "nickname"       : $nickname         = $_value; break;
					case "email"          : $email            = $_value; break;
					case "mobile"       : $mobile           = $_value; break;
				}
			}
		}

		$query = new Query();

		// update user nickname
		if($nickname) {
			$sql = "UPDATE ".$this->db." SET nickname='$nickname' WHERE id = ".$user_id;
			$query->sql($sql);
		}

		// update user email
		if($email) {
			$sql = "UPDATE ".$this->db_usernames." SET username='$email' WHERE user_id = $user_id AND type = 'email'";
			$query->sql($sql);
		}

		// update user mobile
		if($mobile) {
			$sql = "UPDATE ".$this->db_usernames." SET username='$mobile' WHERE user_id = $user_id AND type = 'mobile'";
			$query->sql($sql);
		}

	}


	/**
	* Validate username info to avoid too many unneccesary duplet users
	* Look for users with same email and mobile because such combinations indicates same user
	*/
	function matchUsernames($_options) {

		$email = false;
		$mobile = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "email"         : $email          = $_value; break;
					case "mobile"        : $mobile         = $_value; break;

				}
			}
		}

		// user with matching email and mobile
		if($email && $mobile) {

			$email_matches = $this->getUsers(array("email" => $email));
			$mobile_matches = $this->getUsers(array("mobile" => $mobile));

			if($email_matches && $mobile_matches) {
				foreach($email_matches as $user) {
					if(array_search($user, $mobile_matches) !== -1) {
						return $user["user_id"];
					}
				}
			}
		}
		else if($email) {

			$email_matches = $this->getUsers(array("email" => $email));
			if($email_matches) {
				return $email_matches[0]["user_id"];
			}
		}
		else if($mobile) {

			$mobile_matches = $this->getUsers(array("mobile" => $mobile));
			if($mobile_matches) {
				return $mobile_matches[0]["user_id"];
			}
			
		}


		return false;
	}


	/**
	* Validate address info to avoid too many unneccesary duplet addresses
	* Look for addresses with same user_id and label because such combinations indicates same address
	*
	* @param $user_id Integer User_id to find matching address for
	* @param $_options Array of optional fields to use for address comparison
	* address_label => String Address label
	* address1 => String First address line
	*/
	function matchAddress($user_id, $_options) {

		$address_label = false;
		$address1 = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "address_label"  : $address_label    = $_value; break;
					case "address1"       : $address1         = $_value; break;

				}
			}
		}

		$query = new Query();

		// look for matching address_label and address1
		if($address_label && $address1) {

			$sql = "SELECT id FROM ".$this->db_addresses." WHERE user_id = $user_id AND address_label = '$address_label' AND address1 =  '$address1'";
			if($query->sql($sql)) {
				return $query->result(0, "id");
			}
			else {
				return false;
			}
		}
		// matching address_label
		else if($address_label) {

			$sql = "SELECT id FROM ".$this->db_addresses." WHERE user_id = $user_id AND address_label = '$address_label'";
			if($query->sql($sql)) {
				return $query->result(0, "id");
			}
			else {
				return false;
			}
		}

		return false;
	}



}

?>