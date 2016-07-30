<?php
/**
* @package janitor.users
* This file contains simple user functionality
*
* The User class is supposed to be a minimal interface to User maintenance and the user tables
* It is vital that this class does not expose anything but the current user's information
*
* For extended User manipulator, see SuperUser.
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
*/


/**
* User (simple user / current user)
*/
class UserCore extends Model {


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
		$this->db_password_reset_tokens = SITE_DB.".user_password_reset_tokens";
		$this->db_apitokens = SITE_DB.".user_apitokens";
		$this->db_newsletters = SITE_DB.".user_newsletters";

		// user related item data
		$this->db_subscriptions = SITE_DB.".user_item_subscriptions";
		$this->db_readstates = SITE_DB.".user_item_readstates";

		$this->db_members = SITE_DB.".user_members";



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
			"hint_message" => "Your email",
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
			"label" => "Password",
			"hint_message" => "Type your password - must be 8-20 characters",
			"error_message" => "Invalid password"
		));

		// new_password
		$this->addToModel("new_password", array(
			"type" => "password",
			"label" => "New password",
			"hint_message" => "Type your new password - must be 8-20 characters",
			"error_message" => "Invalid password"
		));

		// old_password
		$this->addToModel("old_password", array(
			"type" => "password",
			"label" => "Existing password",
			"hint_message" => "Type your existing password - must be 8-20 characters",
			"error_message" => "Invalid password"
		));


		// username (for login form)
		$this->addToModel("username", array(
			"type" => "string",
			"label" => "Email or mobile",
			"pattern" => "[\w\.\-\_]+@[\w-\.]+\.\w{2,4}|([\+0-9\-\.\s\(\)]){5,18}", 
			"hint_message" => "Use your emailaddress or mobilenumber to log in.", 
			"error_message" => "The entered value is neither an email or a mobilenumber."
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


		// newsletter
		$this->addToModel("newsletter_id", array(
			"type" => "string",
			"label" => "Newsletter",
			"required" => true,
			"hint_message" => "Newsletter",
			"error_message" => "Invalid newsletter"
		));
		// membership
		$this->addToModel("membership_id", array(
			"type" => "string",
			"label" => "Membership",
			"required" => true,
			"hint_message" => "Please select a membership",
			"error_message" => "Please select a membership"
		));
		// subscription item
		$this->addToModel("payment_method", array(
			"type" => "string",
			"label" => "Payment method",
			"required" => true,
			"hint_message" => "Please select a payment method",
			"error_message" => "Please select a payment method"
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


			$user["mobile"] = "";
			$user["email"] = "";


			$sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				$usernames = $query->results();
				foreach($usernames as $username) {
					$user[$username["type"]] = $username["username"];
				}
			}


			$user["addresses"] = $this->getAddresses();

			$user["newsletters"] = $this->getNewsletters();

			return $user;
		}

		return false;
	}


	/**
	* Get user nickname
	* 
	* TODO: could be extended with email (if user permits)
	*/
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
	// will also add subscription for newsletter if it is sent along with signup
	function newUser($action) {

		global $page;
		$page->addLog("user->newUser: initiated");

		// only attempt user creation if signup are allowed for this site
		if(defined("SITE_SIGNUP") && SITE_SIGNUP) {

			// Get posted values to make them available for models
			$this->getPostedEntities();
			$email = $this->getProperty("email", "value");


			// if user already exists, return error
			if($this->userExists(array("email" => $email))) {
				$page->addLog("user->newUser: user exists ($email)");
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

				// if no nickname were posted, use email
				if(array_search("nickname", $names) === false) {
					$values[] = "nickname='".$email."'";
				}

				// add member user group
				$values[] = "user_group_id=2";


				$sql = "INSERT INTO ".$this->db." SET " . implode(",", $values);
	//			print $sql;
				if($query->sql($sql)) {

					$user_id = $query->lastInsertId();
					$verification_code = randomKey(8);

					// add email to user_usernames
					$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, verification_code = '$verification_code', type = 'email', user_id = $user_id";
	//				print $sql;
					if($query->sql($sql)) {


						// user can send password on signup
						$raw_password = $this->getProperty("password", "value");
						$mail_password = "******** (password is encrypted)";

						// if raw password was not sent - set temp password and include it in activation email
						if(!$raw_password || $raw_password == "Password") {
							// add temp password
							$raw_password = randomKey(8);
							$mail_password = $raw_password." (autogenerated password)";
						}

						// encrypt password
						$password = sha1($raw_password);
						$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$password'";
						if($query->sql($sql)) {
						

							// store signup email for receipt page
							session()->value("signup_email", $email);



							// VERIFICATION EMAIL

							// add log
							$page->addLog("user->newUser: created: " . $email);

							// success
							// send activation email
							if($verification_code) {

								// send verification email to user
								$page->mail(array(
									"values" => array(
										"EMAIL" => $email, 
										"VERIFICATION" => $verification_code
									), 
									"recipients" => $email, 
									"template" => "signup"
								));

								// send notification email to admin
								$page->mail(array(
									"subject" => "New User created: " . $email, 
									"message" => "Check out the new user: " . SITE_URL . "/janitor/admin/user/edit/" . $user_id, 
									"template" => "system"
								));
							}
							// error
							else {
								// send error email notification
								$page->mail(array(
									"recipients" => $email, 
									"template" => "signup_error"
								));

								// send notification email to admin
								$page->mail(array(
									"subject" => "New User created ERROR: " . $email, 
									"message" => "Check out the new user: " . SITE_URL . "/janitor/admin/user/edit/" . $user_id, 
									"template" => "system"
								));
							}



							// Signup can include Newsletter and Membership signups


							// MEMBERSHIP

							// membership?
							$membership = getPost("membership");
							if($membership && $mail_password) {
						
								// make sure newsletter table exist
								$query->checkDbExistance($this->db_memberships);


								// create subscription entry
								$sql = "INSERT INTO $this->db_memberships SET user_id = $user_id, item_id = '$subscription', status = '1'";
								if($query->sql($sql)) {

									$IC = new Items();
									$item = $IC->getItem(array("id" => $membership, "extend" => array("price" => true)));

									$page->mail(array(
										"values" => array(
											"MEMBERID" => $item["item_id"],
											"PRICE" => $item["price"]["formatted_price"],
											"EMAIL" => $email,
											"PASSWORD" => $mail_password
										), 
										"recipients" => $email, 
										"template" => "membership_".$item["classname"]
									));

									// send notification email to admin
									$page->mail(array(
										"subject" => "New membership (".$item["name"].") created: " . $email, 
										"message" => "Do something", //"Check out the subscriber page: " . SITE_URL . "/janitor/admin/user/subscriptions/" . $user_id, 
										"template" => "system"
									));

									// add log
									$page->addLog("user->newUser (".$item["name"]."): created: " . $email);

								}
								else {

									// send error email notification
									$page->mail(array(
										"recipients" => $email, 
										"template" => "signup_membership_error"
									));

									// send notification email to admin
									$page->mail(array(
										"subject" => "New membership creation ERROR: " . $email, 
										"message" => "Do something", //"Check out the new user: " . SITE_URL . "/janitor/admin/user/edit/" . $user_id, 
										"template" => "system"
									));

									// add log
									$page->addLog("user->newUser (w/ membership): creation failed: " . $email);
							
								}
							}



							// NEWSLETTER

							// newsletter subscription?
							$newsletter = getPost("newsletter");
							if($newsletter) {
						
								// make sure newsletter table exist
								$query->checkDbExistance($this->db_newsletters);

								// create subscription entry
								$sql = "INSERT INTO $this->db_newsletters SET user_id = $user_id, newsletter = '$newsletter'";
								$query->sql($sql);

							}





							// return enough information to the frontend
							return array("user_id" => $user_id, "email" => $email);

						}
					}

				}
			}
		}

		$page->addLog("user->newUser failed: " . $sql);
		return false;
	}


	// update current profile
	// /janitor/admin/profile/update (values in POST)
	function update($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		if(count($action) == 1 && $user_id) {

			$query = new Query();

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(firstname|lastname|nickname|language)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($this->validateList($names, $user_id)) {
				if($values) {
					$sql = "UPDATE ".$this->db." SET ".implode(",", $values).",modified_at=CURRENT_TIMESTAMP WHERE id = ".$user_id;
//					print $sql;
				}

				if(!$values || $query->sql($sql)) {

					return true;
				}
			}
		}
		return false;
	}



	// xxx/(email|mobile)/#email|mobile#
	// 
	// will only make alterations if username is not already verified and verification code matches
	// verification code is altered on success to prevent re-activation 
	// (which could teoretically lead to re-activation of disabled accounts)
	function confirmUser($action) {

		// does values validate
		if(count($action) == 4) {

			$query = new Query();
			$type = $action[1];
			$username = $action[2];
			$verification_code = $action[3];

			// only make alterations if not already verified
			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = '$type' AND username = '$username' AND verified = 0 AND verification_code = '$verification_code'";
			if($query->sql($sql)) {

				$user_id = $query->result(0, "user_id");

				$sql = "UPDATE ".$this->db_usernames." SET verified = 1, verification_code = '".randomKey(8)."' WHERE type = '$type' AND username = '$username'";
				if($query->sql($sql)) {

					$query->sql("UPDATE ".$this->db." SET status = 1 WHERE id = $user_id");
					if($query->sql($sql)) {
						return true;
					}
				}
			}
		}
		// confirmation failed
		return false;
	}



	// check if user exists
	// checks if email or mobile already exists for different user_id
	// TODO: could be expanded to cover names and addresses as well
	// Consider if expanded "search" should be kept elsewhere
	function userExists($_options) {

		$email = false;
		$mobile = false;

		// user_id to check is user is same user
		$user_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "email"      : $email        = $_value; break;
					case "mobile"     : $mobile       = $_value; break;

					case "user_id"    : $user_id      = $_value; break;
				}
			}
		}

		$query = new Query();

		// check for users with same email
		if($email) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = 'email' AND username = '$email'".($user_id ? " AND user_id != $user_id" : "");
//			print $sql;
			if($query->sql($sql)) {
				return true;
			}
		}

		// check for users with same mobile
		if($mobile) {

			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE type = 'mobile' AND username = '$mobile'".($user_id ? " AND user_id != $user_id" : "");
//			print $sql;
			if($query->sql($sql)) {
				return true;
			}
		}

		return false;
	}




	// USERNAMES


	// Update email from posted values
	// /janitor/admin/profile/updateEmail
	function updateEmail($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		// does action match expected
		if(count($action) == 1 && $user_id) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_usernames);

			$email = $this->getProperty("email", "value");

			// check if email exists
			if($this->userExists(array("email" => $email, "user_id" => $user_id))) {
				return array("status" => "USER_EXISTS");
				// message()->addMessage("Email already exists", array("type" => "error"));
				// return false;
			}


			$current_user = $this->getUser();
			$current_email = $current_user["email"];

			// email is sent
			if($email) {

				// email has not been set before
				if(!$current_email) {

					$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, type = 'email', user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
//						message()->addMessage("Email added");
						return true;
					}
				}

				// email is changed
				else if($email != $current_email) {

					$sql = "UPDATE $this->db_usernames SET username = '$email', verified = 0 WHERE type = 'email' AND user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
//						message()->addMessage("Email updated");
						return true;
					}
				}

				// email is NOT changed
				else if($email == $current_email) {

//					message()->addMessage("Email unchanged");
					return true;
				}
			}

			// email is not sent
			else if(!$email && $current_email !== false) {

				$sql = "DELETE FROM $this->db_usernames WHERE type = 'email' AND user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
//					message()->addMessage("Email deleted");
					return true;
				}
			}

		}

//		message()->addMessage("Could not update email", array("type" => "error"));
		return false;

	}

	// Update mobile from posted values
	// /janitor/admin/profile/updateMobile
	function updateMobile($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		// does action match expected
		if(count($action) == 1 && $user_id) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_usernames);

			$mobile = $this->getProperty("mobile", "value");

			// check if mobile exists
			if($this->userExists(array("mobile" => $mobile, "user_id" => $user_id))) {
				return array("status" => "USER_EXISTS");
				// message()->addMessage("Mobile already exists", array("type" => "error"));
				// return false;
			}


			$current_user = $this->getUser();
			$current_mobile = $current_user["mobile"];

			// mobile is sent
			if($mobile) {

				// mobile has not been set before
				if(!$current_mobile) {

					$sql = "INSERT INTO $this->db_usernames SET username = '$mobile', verified = 0, type = 'mobile', user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
//						message()->addMessage("Mobile added");
						return true;
					}
				}

				// mobile is changed
				else if($mobile != $current_mobile) {

					$sql = "UPDATE $this->db_usernames SET username = '$mobile', verified = 0 WHERE type = 'mobile' AND user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
//						message()->addMessage("Mobile updated");
						return true;
					}
				}

				// mobile is NOT changed
				else if($mobile == $current_mobile) {

//					message()->addMessage("Mobile unchanged");
					return true;
				}
			}

			// mobile is not sent
			else if(!$mobile && $current_mobile !== false) {

				$sql = "DELETE FROM $this->db_usernames WHERE type = 'mobile' AND user_id = $user_id";
//				print $sql."<br>";
				if($query->sql($sql)) {
//					message()->addMessage("Mobile deleted");
					return true;
				}
			}

		}

//		message()->addMessage("Could not update mobile", array("type" => "error"));
		return false;

	}




	// PASSWORD


	// set new password for current user
	// /janitor/admin/profile/setPassword
	function setPassword($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		if(count($action) == 1 && $user_id) {

			// does values validate
			if($this->validateList(array("new_password", "old_password"))) {

				$query = new Query();

				// make sure type tables exist
				$query->checkDbExistance($this->db_passwords);

				$old_password = sha1($this->getProperty("old_password", "value"));
				$new_password = sha1($this->getProperty("new_password", "value"));


				$sql = "SELECT password FROM ".$this->db_passwords." WHERE user_id = $user_id";
				if($query->sql($sql)) {
					if($old_password == $query->result(0, "password")) {

						// DELETE OLD PASSWORD
						$sql = "DELETE FROM ".$this->db_passwords." WHERE user_id = $user_id";
						if($query->sql($sql)) {

							// SAVE NEW PASSWORD
							$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$new_password'";
							if($query->sql($sql)) {

								message()->addMessage("Password updated");
								return true;
							}
						}

					}
				}

			}
		}

		message()->addMessage("Password could not be updated", array("type" => "error"));
		return false;
	}


	// start reset password procedure
	function requestPasswordReset($action) {

		// perform cleanup routine
		$this->cleanUpResetRequests();

		// get posted variables
		$this->getPostedEntities();
		$username = $this->getProperty("username", "value");

		// correct information available
		if(count($action) == 1 && $username) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_password_reset_tokens);


			// find the user with specified username
			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE username = '$username'";
			if($query->sql($sql)) {

				// user_id
				$user_id = $query->result(0, "user_id");


				// find email for this user
				$sql = "SELECT username FROM ".$this->db_usernames." WHERE user_id = '$user_id' AND type = 'email'";
				if($query->sql($sql)) {

					// email
					$email = $query->result(0, "username");

					// create reset token
					$reset_token = randomKey(24);

					// insert reset token
					$sql = "INSERT INTO ".$this->db_password_reset_tokens." VALUES(DEFAULT, $user_id, '$reset_token', '".date("Y-m-d H:i:s", strtotime("+15 minutes"))."')";
					if($query->sql($sql)) {

						global $page;

						// send email
						$page->mail(array(
							"values" => array(
								"TOKEN" => $reset_token,
								"USERNAME" => $username
							),
							"recipients" => $email,
							"template" => "reset_password"
						));

						// send notification email to admin
						// TODO: consider disabling this once it has proved itself worthy
						$page->mail(array(
							"subject" => "Password reset requested: " . $email,
							"message" => "Check out the user: " . SITE_URL . "/janitor/admin/user/edit/" . $user_id,
							"template" => "system"
						));

						return true;

					}

				}

			}

		}

		// user could not be found or reset request could not be satisfied
		// - but this is not reflected towards to user to avoid revealing user existence
		// - standard error message created in login-controller
		return false;
	}


	// reset password using reset-token
	function resetPassword($action) {

		// perform cleanup routine
		$this->cleanUpResetRequests();

		// get posted variables
		$this->getPostedEntities();

		$reset_token = getPost("reset-token");
		$new_password = sha1($this->getProperty("new_password", "value"));

		// correct information available
		if(count($action) == 1 && $new_password && $this->checkResetToken($reset_token)) {

			$query = new Query();

			// get user_id for reset token
			$sql = "SELECT user_id FROM ".$this->db_password_reset_tokens." WHERE token = '$reset_token'";
			if($query->sql($sql)) {

				// get user id
				$user_id = $query->result(0, "user_id");


				// delete token (a token can only be used once)
				$sql = "DELETE FROM ".$this->db_password_reset_tokens." WHERE token = '$reset_token'";
				$query->sql($sql);


				// DELETE OLD PASSWORD
				$sql = "DELETE FROM ".$this->db_passwords." WHERE user_id = $user_id";
				if($query->sql($sql)) {

					// SAVE NEW PASSWORD
					$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$new_password'";
					if($query->sql($sql)) {

						message()->addMessage("Password updated");
						return true;
					}
				}

			}

		}

		return false;
	}


	// clean up expired reset requests
	function cleanUpResetRequests() {

		$query = new Query();
		$sql = "DELETE FROM ".$this->db_password_reset_tokens." WHERE created_at < NOW()";
		$query->sql($sql);

	}

	// Check reset token
	function checkResetToken($reset_token) {

		// perform cleanup routine
		$this->cleanUpResetRequests();

		if($reset_token) {
			// check if reset token is valid
			$query = new Query();
			$sql = "SELECT id FROM ".$this->db_password_reset_tokens." WHERE token = '$reset_token'";
			if($query->sql($sql)) {
				return true;
			}
		}
		
		return false;
	}



	// API TOKEN

	// get users api token
	function getToken($user_id = false) {

		$user_id = session()->value("user_id");

		$query = new Query();
		// make sure type tables exist
		$query->checkDbExistance($this->db_apitokens);

		$sql = "SELECT token FROM ".$this->db_apitokens." WHERE user_id = $user_id";
		if($query->sql($sql)) {
			return $query->result(0, "token");
		}
		return false;
	}

	// create new api token
	// /janitor/admin/profile/renewToken
	function renewToken($action) {


		$user_id = session()->value("user_id");

		$token = gen_uuid();
		$query = new Query();

		// make sure type tables exist
		$query->checkDbExistance($this->db_apitokens);

		$sql = "SELECT token FROM ".$this->db_apitokens." WHERE user_id = $user_id";
//		print $sql;
		if($query->sql($sql)) {
			$sql = "UPDATE ".$this->db_apitokens." SET token = '$token' WHERE user_id = $user_id";
		}
		else {
			$sql = "INSERT INTO ".$this->db_apitokens." SET user_id = $user_id, token = '$token'";
		}
//		print $sql;
		if($query->sql($sql)) {
			return $token;
		}

		return false;
	}

	// disable api token
	// /janitor/admin/profile/disableToken
	function disableToken($action) {


		$user_id = session()->value("user_id");

		$query = new Query();

		// make sure type tables exist
		$query->checkDbExistance($this->db_apitokens);

		$sql = "DELETE FROM ".$this->db_apitokens." WHERE user_id = $user_id";
//		print $sql;
		if($query->sql($sql)) {
			return true;
		}

		return false;
	}



	// ADDRESSES


	// return addresses for current user
	// can return all addresses for current user, or a specific address
	// Adds country_name for stored country ISO value
	function getAddresses($_options = false) {

		$user_id = session()->value("user_id");
		$address_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "address_id"  : $address_id    = $_value; break;
				}
			}
		}

		$query = new Query();
		global $page;
		$countries = $page->countries();

		// get specific address
		if($address_id) {
			$sql = "SELECT * FROM ".$this->db_addresses." WHERE id = $address_id AND user_id = $user_id";
//			print $sql;

			if($query->sql($sql)) {
				$result = $query->result(0);
				$result["country_name"] = $countries[arrayKeyValue($countries, "id", $result["country"])]["name"];
				return $result;
			}
		}

		// get alle addresses for user
		else {

			$sql = "SELECT * FROM ".$this->db_addresses." WHERE user_id = $user_id";
//			print $sql;

			if($query->sql($sql)) {
				$results = $query->results();
				foreach($results as $index => $result) {
					$results[$index]["country_name"] = $countries[arrayKeyValue($countries, "id", $result["country"])]["name"];
				}
				return $results;
			}

		}
	}

	// create a new address
	// /janitor/admin/profile/addAddress (values in POST)
	function addAddress($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");


		if(count($action) == 1 && $user_id && $this->validateList(array("address_label","address_name","address1","postal","city","country"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistance($this->db_addresses);

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false && preg_match("/^(address_label|address_name|att|address1|address2|city|postal|state|country)$/", $name)) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "INSERT INTO ".$this->db_addresses." SET user_id=$user_id," . implode(",", $values);
//				print $sql;

				if($query->sql($sql)) {
//					message()->addMessage("Address created");
					return array("item_id" => $user_id);
				}
			}
		}

		return false;
	}

	// update an address
	// /janitor/admin/profile/updateAddress/#address_id# (values in POST)
	function updateAddress($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		if(count($action) == 2 && $user_id) {

			$query = new Query();
			$address_id = $action[1];

			$entities = $this->getModel();
			$names = array();
			$values = array();

			foreach($entities as $name => $entity) {
				if($entity["value"] !== false) {
					$names[] = $name;
					$values[] = $name."='".$entity["value"]."'";
				}
			}

			if($values) {
				$sql = "UPDATE ".$this->db_addresses." SET ".implode(",", $values).",modified_at=CURRENT_TIMESTAMP WHERE id = $address_id AND user_id = $user_id";
//				print $sql;
			}

			if(!$values || $query->sql($sql)) {
//				message()->addMessage("Address updated");
				return true;
			}

		}

//		message()->addMessage("Address could not be updated", array("type" => "error"));
		return false;
	}

	// Delete address
	// /janitor/admin/profile/deleteAddress/#address_id#
	function deleteAddress($action) {
		
		$user_id = session()->value("user_id");

		if(count($action) == 2 && $user_id) {
			$query = new Query();
			$address_id = $action[1];

			$sql = "DELETE FROM $this->db_addresses WHERE id = $address_id AND user_id = $user_id";
//			print $sql;
			if($query->sql($sql)) {
				message()->addMessage("Address deleted");
				return true;
			}

		}

		return false;
	}




	// NEWSLETTER

	// get newsletter info
	// get all newsletters (list of available newsletters)
	// get newsletters for user
	// get state of specific newsletter for specific user
	function getNewsletters($_options = false) {

		$user_id = session()->value("user_id");
		$newsletter = false;
		$newsletter_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "newsletter"       : $newsletter         = $_value; break;
					case "newsletter_id"    : $newsletter_id      = $_value; break;
				}
			}
		}

		$query = new Query();


		// check for specific newsletter (by nane) for current user
		if($newsletter) {
			$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.newsletter_id, newsletters.name FROM ".$this->db_newsletters." as subscribers, ".UT_NEWSLETTERS." as newsletters WHERE subscribers.user_id = $user_id AND subscribers.newsletter_id = newsletters.id AND newsletters.newsletter = '$newsletter'";
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}
		// check for specific newsletter (by id) for current user
		else if($newsletter_id) {
			$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.newsletter_id, newsletters.name FROM ".$this->db_newsletters." as subscribers, ".UT_NEWSLETTERS." as newsletters WHERE subscribers.user_id = $user_id AND subscribers.newsletter_id = '$newsletter_id'";
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}
		// get newsletters for current user
		else {
			$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.newsletter_id, newsletters.name FROM ".$this->db_newsletters." as subscribers, ".UT_NEWSLETTERS." as newsletters WHERE subscribers.user_id = $user_id AND subscribers.newsletter_id = newsletters.id";
			if($query->sql($sql)) {
				return $query->results();
			}
		}

	}

	// /janitor/admin/profile/addNewsletter
	// Newsletter info i $_POST
	function addNewsletter($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		if(count($action) == 1 && $user_id && $this->validateList(array("newsletter_id"))) {

			$query = new Query();

			$newsletter_id = $this->getProperty("newsletter_id", "value");
			// already signed up (to avoid faulty double entries)
			$sql = "SELECT id FROM $this->db_newsletters WHERE user_id = $user_id AND newsletter_id = '$newsletter_id'";
			if(!$query->sql($sql)) {
				$sql = "INSERT INTO ".$this->db_newsletters." SET user_id=$user_id, newsletter_id='$newsletter_id'";
				$query->sql($sql);
			}
			return true;
		}

		return false;
		
	}

	// /janitor/admin/profile/deleteNewsletter/#newsletter_id#
	function deleteNewsletter($action) {

		$user_id = session()->value("user_id");

		if(count($action) == 2 && $user_id) {
			$query = new Query();
			$newsletter_id = $action[1];

			$sql = "DELETE FROM $this->db_newsletters WHERE newsletter_id = $newsletter_id AND user_id = $user_id";
			if($query->sql($sql)) {
				return true;
			}

		}

		return false;
	}




	// READSTATES


	// get readstate, optionally based on item_id or user_id
	// defaults to current user (never for user_id = 1 - guest)
	function getReadstates($_options=false) {

		$item_id = false;
		$user_id = session()->value("user_id");

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"     : $item_id        = $_value; break;
					case "user_id"     : $user_id        = $_value; break;

				}
			}
		}

		if($user_id > 1) {
			$query = new Query();

			// Get readstate for item_id
			if($item_id) {

				$sql = "SELECT read_at FROM ".$this->db_readstates." WHERE item_id = $item_id AND user_id = $user_id";
				if($query->sql($sql)) {
					return $query->result(0, "read_at");
				}

			}
			// get all readstates for user
			else {

				$sql = "SELECT read_at FROM ".$this->db_readstates." WHERE user_id = $user_id";
				if($query->sql($sql)) {
					return $query->results();
				}
				
			}
		}

		return false;
	}


	// READ STATES - INDICATES THAT THE USER HAS READ THE ITEM

	// add readstate for user+item
	// enables adding a button for the user to indicate wheter an item has been read
	// disabled for user_id = 1 (guest)

	// /janitor/[admin/]#itemtype#/addReadstate/#item_id#
	function addReadstate($action) {

		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];
			$user_id = session()->value("user_id");

			if($user_id > 1) {

				if($query->sql("SELECT ".$this->db_readstates." WHERE user_id = $user_id AND item_id = $item_id")) {
					$sql = "UPDATE ".$this->db_readstates." SET read_at = CURRENT_TIMESTAMP WHERE user_id = $user_id AND item_id = $item_id";
				}
				else {
					$sql = "INSERT INTO ".$this->db_readstates." VALUES(DEFAULT, $user_id, $item_id, DEFAULT)";
				}

				if($query->sql($sql)) {
					return true;
				}
			}
		}

		return false;
	}


	// delete Read state
	// /janitor/[admin/]#itemtype#/deleteReadstate/#item_id#
	// disabled for user_id = 1 (guest)
 	function deleteReadstate($action) {

		if(count($action) == 2) {

			$query = new Query();
			$item_id = $action[1];
			$user_id = session()->value("user_id");

			if($user_id > 1) {

				if($query->sql("DELETE FROM ".$this->db_readstates." WHERE item_id = $item_id AND user_id = $user_id")) {
					return true;
				}
			}
		}

		return false;
	}






	// SUBSCRIPTIONS

	// get subscription info for specific subscription 
	// (can be used to check if user has subscription or not)
	// get subscription for user
	function getSubscriptions($_options = false) {

		$user_id = session()->value("user_id");
		$item_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"     : $item_id       = $_value; break;
				}
			}
		}

		$query = new Query();
		$IC = new Items();

		// check for specific subscription for current user
		if($item_id) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id AND item_id = '$item_id' LIMIT 1";
			if($query->sql($sql)) {
				$subscription = $query->result(0);
				$subscription["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
				return $subscription;
			}
			else {
				return false;
			}
		}

		// get list of all subscriptions for current user
		else {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				$subscriptions = $query->results();
				foreach($subscriptions as $i => $subscription) {
					$subscriptions[$i]["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
				}
				return $subscriptions;
			}
			else {
				return false;
			}

		}

	}


	function addSubscription($action) {}

	function deleteSubscription($action) {}

	function renewSubscription($action) {}


	// MEMBERSHIP

	function getMember() {
		
	}

	function addMember($action) {
		
		
	}

	function memberStatus() {}



}

?>