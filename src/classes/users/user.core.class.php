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
* - maillist signup
* - placement of orders by unregistered users
*
*
* Creates a member user (user_group=2), with limited privileges
* - update profile
* - maillist administration
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
		$this->db_maillists = SITE_DB.".user_maillists";

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

		// Terms
		$this->addToModel("terms", array(
			"type" => "checkbox",
			"label" => 'I accept the <a href="/terms" target="_blank">terms and conditions</a>.',
			"required" => true,
			"hint_message" => "Accept the terms and conditions to continue.", 
			"error_message" => "You must accept the terms and conditions to continue"
		));
		


		// USERNAMES AND PASSWORD

		// email
		$this->addToModel("email", array(
			"type" => "email",
			"label" => "Your email",
			"hint_message" => "Your email",
			"error_message" => "Invalid email"
		));
		// confirm email
		$this->addToModel("confirm_email", array(
			"type" => "email",
			"label" => "Confirm your email",
			"compare_to" => "email",
			"hint_message" => "Your email again, to make sure your didin't mistype it.",
			"error_message" => "Email mismatch"
		));

		// mobile
		$this->addToModel("mobile", array(
			"type" => "tel",
			"label" => "Your mobile",
			"hint_message" => "Write your mobile number",
			"error_message" => "Invalid number"
		));
		// confirm mobile
		$this->addToModel("confirm_mobile", array(
			"type" => "tel",
			"label" => "Confirm your mobile",
			"compare_to" => "mobile",
			"hint_message" => "Write your mobile number again, to make sure your didin't mistype it.",
			"error_message" => "Number mismatch"
		));

		// password
		$this->addToModel("password", array(
			"type" => "password",
			"label" => "Password",
			"hint_message" => "Type your password - must be more than 8 characters",
			"error_message" => "Invalid password"
		));

		// new_password
		$this->addToModel("new_password", array(
			"type" => "password",
			"label" => "New password",
			"hint_message" => "Type your new password - must be more than 8 characters",
			"error_message" => "Invalid password"
		));

		// confirm_password
		$this->addToModel("confirm_password", array(
			"type" => "password",
			"label" => "Repeat password",
			"compare_to" => "new_password",
			"hint_message" => "Type your new password again to verify",
			"error_message" => "Passwords do not match"
		));

		// old_password
		$this->addToModel("old_password", array(
			"type" => "password",
			"label" => "Existing password",
			"hint_message" => "Type your existing password - must be more than 8 characters",
			"error_message" => "Invalid password"
		));


		// username (for login form)
		$this->addToModel("username", array(
			"type" => "string",
			"label" => "Email or mobile",
			"autocomplete" => true,
			"pattern" => "[\w\.\-_\+]+@[\w\-\.]+\.\w{2,10}|[\+0-9\-\.\s\(\)]{5,18}", 
			"hint_message" => "Use your emailaddress or mobilenumber to log in.", 
			"error_message" => "The entered value is neither an email or a mobilenumber."
		));

		// verification code
		$this->addToModel("verification_code", array(
			"type" => "string",
			"label" => "Verification code",
			"pattern" => "[0-9A-Za-z]{8}", 
			"hint_message" => "Enter the verification code from the email.", 
			"error_message" => "The entered value is not a valid verification code."
		));

		//verification status
		$this->addToModel("verification_status", array(
			"type" => "checkbox",
			"label" => 'Verified',
			"hint_message" => "Check to verify the user"
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


		// maillist
		$this->addToModel("maillist_id", array(
			"type" => "string",
			"label" => "Maillist",
			"required" => true,
			"hint_message" => "Maillist",
			"error_message" => "Invalid maillist"
		));


		// order id
		$this->addToModel("order_id", array(
			"type" => "integer",
			"label" => "Order",
			"hint_message" => "Order ID",
			"error_message" => "Invalid order"
		));
		// membership
		$this->addToModel("subscription_id", array(
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
		// Upgrade subscription switch
		$this->addToModel("subscription_upgrade", array(
			"type" => "boolean",
			"required" => true
		));
		// Renew subscription switch
		$this->addToModel("subscription_renewal", array(
			"type" => "boolean",
			"required" => true
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

			$user["maillists"] = $this->getMaillists();

			if((defined("SITE_SHOP") && SITE_SHOP)) {
				$user["membership"] = $this->getMembership();
			}

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

			$sql = "SELECT status, nickname FROM ".$this->db." WHERE id = $user_id";
//			print $sql;
			if($query->sql($sql)) {
				$user = $query->result(0);

				// $user["mobile"] = "";
				// $user["email"] = "";
				//
				// $sql = "SELECT * FROM ".$this->db_usernames." WHERE user_id = $user_id";
				// if($query->sql($sql)) {
				// 	$usernames = $query->results();
				// 	foreach($usernames as $username) {
				// 		$user[$username["type"]] = $username["username"];
				// 	}
				// }

				return $user;
			}
		}

		return false;
	}


	// NOTE: All output should be kept in frontend logic because it might need to be served in different language
	// or with specific context


	// create new user
	// email is minimum info to create user at this point (signup to maillist)
	// will also add subscription for maillist if it is sent along with signup
	function newUser($action) {

		global $page;
		$page->addLog("user->newUser: initiated");

		// only attempt user creation if signups are allowed for this site
		if(defined("SITE_SIGNUP") && SITE_SIGNUP) {

			// Get posted values to make them available for models
			$this->getPostedEntities();
			$terms = $this->getProperty("terms", "value");
			$email = $this->getProperty("email", "value");


			// if user already exists, return error
			if(!$terms) {
				$page->addLog("user->newUser: missing terms agreement");
				return array("status" => "MISSING_TERMS");
			}


			// if user already exists, return error
			if($this->userExists(array("email" => $email))) {
				$page->addLog("user->newUser: user exists ($email)");
				return array("status" => "USER_EXISTS");
			}


			// does values validate - minimum is email
			if(count($action) == 1 && $this->validateList(array("email")) && $email) {

				$query = new Query();
				$nickname = $this->getProperty("nickname", "value");
				$firstname = $this->getProperty("firstname", "value");
				$lastname = $this->getProperty("lastname", "value");


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
				if(!$nickname) {
					if($firstname && $lastname) {
						$nickname = $firstname . " " . $lastname;
					}
					else if($firstname) {
						$nickname = $firstname;
					}
					else if($lastname) {
						$nickname = $lastname;
					}
					else {
						$nickname = $email;
					}

					$values[] = "nickname='".$nickname."'";
					$quantity = $this->getProperty("quantity", "value");
					$item_id = $this->getProperty("item_id", "value");
				}

				// add member user group
				$values[] = "user_group_id=2";


				$sql = "INSERT INTO ".$this->db." SET " . implode(",", $values);
				// print $sql."<br>\n";
				if($query->sql($sql)) {

					$user_id = $query->lastInsertId();


					// Gererate verification code
					$verification_code = randomKey(8);

					// add email to user_usernames
					$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, verification_code = '$verification_code', type = 'email', user_id = $user_id";
					// print $sql."<br>\n";
					if($query->sql($sql)) {


						$mobile = $this->getProperty("mobile", "value");
						if($mobile) {
							$sql = "INSERT INTO $this->db_usernames SET username = '$mobile', verified = 1, verification_code = '$verification_code', type = 'mobile', user_id = $user_id";
			//				print $sql;
							$query->sql($sql);
						}


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
						$password = password_hash($raw_password, PASSWORD_DEFAULT);
						$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$password'";
						// password added successfully
						if($query->sql($sql)) {

							// store signup email for receipt page
							session()->value("signup_email", $email);



							// VERIFICATION EMAIL

							// add log
							$page->addLog("user->newUser: created: " . $email . ", user_id:$user_id");

							// success
							// send activation email
							if($verification_code) {

								// send verification email to user
								mailer()->send(array(
									"values" => array(
										"NICKNAME" => $nickname, 
										"EMAIL" => $email, 
										"VERIFICATION" => $verification_code,
										"PASSWORD" => $mail_password
									), 
									"track_clicks" => false,
									"recipients" => $email, 
									"template" => "signup"
								));

								// send notification email to admin
								mailer()->send(array(
									"subject" => SITE_URL . " - New User: " . $email, 
									"message" => "Check out the new user: " . SITE_URL . "/janitor/admin/user/edit/" . $user_id, 
									"tracking" => false
									// "template" => "system"
								));
							}
							// error
							else {
								// send error email notification
								mailer()->send(array(
									"recipients" => $email, 
									"template" => "signup_error"
								));

								// send notification email to admin
								mailer()->send(array(
									"subject" => "New User created ERROR: " . $email, 
									"message" => "Check out the new user: " . SITE_URL . "/janitor/admin/user/edit/" . $user_id, 
									"tracking" => false
									// "template" => "system"
								));
							}


							// Update session values (to allow user to complete signup)
							session()->value("user_id", $user_id);
							session()->value("user_nickname", $nickname);

							// but also reset user group
							// - in case another user was already logged in before signup started
							session()->value("user_group_id", 1);



							// TERMS

							// Add terms agreement
							$this->acceptedTerms();



							// MAILLIST

							// maillist subscription sent as string?
							$maillist = getPost("maillist");
							if($maillist) {
								// check if maillist exists
								$maillists = $page->maillists();
								$maillist_match = arrayKeyValue($maillists, "name", $maillist);
								if($maillist_match !== false) {
									$maillist_id = $maillists[$maillist_match]["id"];
									$_POST["maillist_id"] = $maillist_id;

									// add maillist for current user
									$this->addMaillist(array("addMaillist"));
								}
								
								// ignore subscription if maillist does not exist

							}

							// itemtype post save handler?
							// TODO: Consider if failed postSave should have consequences
							if(method_exists($this, "saved")) {
								$this->saved($user_id);
							}


							message()->resetMessages();

							// return enough information to the frontend
							return array("user_id" => $user_id, "nickname" => $nickname, "email" => $email);

						}
					}

				}
			}
		}

		$page->addLog("user->newUser failed: (missing info)");
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

					// update username and mobile if these were also sent
					$email = $this->getProperty("email", "value");
					if($email) {
						$this->updateEmail(array("updateEmail"));
					}

					$mobile = $this->getProperty("mobile", "value");
					if($email) {
						$this->updateMobile(array("updateMobile"));
					}

					return true;
				}
			}
		}
		return false;
	}


	/**
	 * Verify and activate user (if not already verified).
	 * Deletes verification reminder log for username.
	 *
	 * @param string $username
	 * @param string $verification_code
	 * 
	 * @return int|array|false User id on success. Status code if already verified. False on wrong parameters or error. 
	 */	
	function confirmUsername($username, $verification_code) {

		$query = new Query();

		// only make alterations if not already verified
		$sql = "SELECT id, user_id FROM ".$this->db_usernames." WHERE username = '$username' AND verified = 0 AND verification_code = '$verification_code'";
		if($query->sql($sql)) {

			// get user_id and username_id from sql query
			$user_id = $query->result(0, "user_id");
			$username_id = $query->result(0, "id");

			// update verification state
			$sql = "UPDATE ".$this->db_usernames." SET verified = 1 WHERE user_id = '$user_id' AND id = '$username_id'";

			if($query->sql($sql)) {

				// enable user
				$sql = "UPDATE ".$this->db." SET status = 1 WHERE id = $user_id";
				if($query->sql($sql)) {

					// delete verification reminder logs (not needed after username has been verified)
					$sql = "DELETE FROM ".SITE_DB.".user_log_verification_links WHERE username_id = $username_id";
					$query->sql($sql);

					global $page;
					$page->addLog("User->confirmUsername: user_id:$user_id, username_id:$username_id");

					return $user_id;
				}
			}
		}
		else {
			// If user is already verified
			$sql = "SELECT user_id FROM ".$this->db_usernames." WHERE username = '$username' AND verified = 1 AND verification_code = '$verification_code'";

			if($query->sql($sql)) {
				global $page;
				$page->addLog("user->confirmUsername: username has already been verified ($username)");
				return array("status" => "USER_VERIFIED");
			}
		}

		// confirmation failed
		return false;
	}

	// cancel user
	// /#controller/user/cancel
	function cancel($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		if(count($action) == 1 && $user_id && $this->validateList(array("password"))) {
			$query = new Query();

			// Get user password to check if user is allowed to cancel
			$sql = "SELECT passwords.password as password FROM ".SITE_DB.".user_passwords as passwords WHERE passwords.user_id = $user_id AND passwords.password != ''";
			if($query->sql($sql)) {

				$password = $this->getProperty("password", "value");
				$hashed_password = $query->result(0, "password");

				// Check real hash
				if($hashed_password && password_verify($password, $hashed_password)) {


				// check for unpaid orders
					$unpaid_orders = false;
					if(defined("SITE_SHOP") && SITE_SHOP) {
						include_once("classes/shop/shop.core.class.php");
						$SC = new Shop();
						$unpaid_orders = $SC->getUnpaidOrders();

					}


					// do not allow to cancel users with unpaid orders
					if(!$unpaid_orders) {

						global $page;

						// WHEN UPDATING - ALSO UPDATE SUPERUSER VERSION
						// Update name to "Anonymous" and remove all privileges
						$sql = "UPDATE ".$this->db." SET status=-1,user_group_id=NULL,nickname='Anonymous',firstname='',lastname='',language=NULL,modified_at=CURRENT_TIMESTAMP WHERE id = ".$user_id;
						if($query->sql($sql)) {

							// delete usernames
							$sql = "DELETE FROM ".$this->db_usernames." WHERE user_id = ".$user_id;
							$query->sql($sql);

							// delete activation reminders
							$sql = "DELETE FROM ".SITE_DB.".user_log_verification_links WHERE user_id = ".$user_id;
							$query->sql($sql);

							// delete password
							$sql = "DELETE FROM ".$this->db_passwords." WHERE user_id = ".$user_id;
							$query->sql($sql);
							// delete password reset tokens
							$sql = "DELETE FROM ".$this->db_password_reset_tokens." WHERE user_id = ".$user_id;
							$query->sql($sql);

							// delete addresses
							$sql = "DELETE FROM ".$this->db_addresses." WHERE user_id = ".$user_id;
							$query->sql($sql);

							// delete api tokens
							$sql = "DELETE FROM ".$this->db_apitokens." WHERE user_id = ".$user_id;
							$query->sql($sql);

							// delete maillists
							$sql = "DELETE FROM ".$this->db_maillists." WHERE user_id = ".$user_id;
							$query->sql($sql);

							// delete readstates
							$sql = "DELETE FROM ".$this->db_readstates." WHERE user_id = ".$user_id;
							$query->sql($sql);

							// delete membership
							if(SITE_MEMBERS) {
								$sql = "DELETE FROM ".$this->db_members." WHERE user_id = ".$user_id;
								$query->sql($sql);
							}

							// delete subscriptions
							if(SITE_SUBSCRIPTIONS) {
								$sql = "DELETE FROM ".$this->db_subscriptions." WHERE user_id = ".$user_id;
								$query->sql($sql);
							}

							// delete carts
							if(SITE_SHOP) {
								$sql = "DELETE FROM ".$SC->db_carts." WHERE user_id = ".$user_id;
								$query->sql($sql);


								// we should also delete user account at gateway
								payments()->deleteGatewayUserId($user_id);

								// // TODO: keep updated when more gateways are added
								// include_once("classes/adapters/stripe.class.php");
								// $GC = new JanitorStripe();
								// $payment_methods = $page->paymentMethods();
								//
								// foreach($payment_methods as $payment_method) {
								//
								// 	if($payment_method["gateway"] == "stripe") {
								//
								// 		$GC->deleteCustomer($user_id);
								//
								// 	}
								//
								// }
							}

							// reset user session
							session()->reset();

							global $page;
							$page->addLog("User->cancel: user_id:$user_id");


							return true;

						}

					}


					return array("error" => "unpaid_orders");

				}

			}

			return array("error" => "wrong_password");
		}

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
			$query->checkDbExistence($this->db_usernames);

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

				$nickname = $current_user["nickname"];
				$verification_code = randomKey(8);

				// email has not been set before
				if(!$current_email) {

					$sql = "INSERT INTO $this->db_usernames SET username = '$email', verified = 0, verification_code = '$verification_code', type = 'email', user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
						
						// this has been commented out as it is not yet 
						// possible to create a user that has no email username
						// verify_new_email is currently phrased as an invitation
						// which would not fit this situation 

						// // send verification email to user
						// mailer()->send(array(
						// 	"values" => array(
						// 		"NICKNAME" => $nickname, 
						// 		"EMAIL" => $email, 
						// 		"VERIFICATION" => $verification_code,
						// 		"PASSWORD" => $mail_password
						// 	), 
						// 	"track_clicks" => false,
						// 	"recipients" => $email, 
						// 	"template" => "verify_new_email"
						// ));

						
//						message()->addMessage("Email added");
						return true;
					}
				}

				// email is changed
				else if($email != $current_email) {

					$sql = "UPDATE $this->db_usernames SET username = '$email', verified = 0, verification_code = '$verification_code' WHERE type = 'email' AND user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {


						// send verification email to user
						mailer()->send(array(
							"values" => array(
								"NICKNAME" => $nickname, 
								"EMAIL" => $email, 
								"VERIFICATION" => $verification_code,
								"PASSWORD" => $mail_password
							), 
							"track_clicks" => false,
							"recipients" => $email, 
							"template" => "verify_changed_email"
						));


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
			$query->checkDbExistence($this->db_usernames);

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

				$verification_code = randomKey(8);

				// mobile has not been set before
				if(!$current_mobile) {

					$sql = "INSERT INTO $this->db_usernames SET username = '$mobile', verified = 0, verification_code = '$verification_code', type = 'mobile', user_id = $user_id";
	//				print $sql."<br>";
					if($query->sql($sql)) {
//						message()->addMessage("Mobile added");
						return true;
					}
				}

				// mobile is changed
				else if($mobile != $current_mobile) {

					$sql = "UPDATE $this->db_usernames SET username = '$mobile', verified = 0, verification_code = '$verification_code' WHERE type = 'mobile' AND user_id = $user_id";
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

	// check if password exists
	function hasPassword($_options = false) {

		$user_id = session()->value("user_id");
		$include_empty = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "include_empty"     : $include_empty       = $_value; break;
				}
			}
		}

		$query = new Query();

		if($user_id != 1) {
			$sql = "SELECT id FROM ".$this->db_passwords." WHERE user_id = $user_id" . ($include_empty ? " AND (password != '' OR upgrade_password != '')" : "");
			if($query->sql($sql)) {
				return true;
			}
		}

		return false;
	}

	// set new password for current user
	// /janitor/admin/profile/setPassword
	function setPassword($action) {

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		if(count($action) == 1 && $user_id) {

			// If user already has a password
			if($this->hasPassword()) {

				// does values validate
				if($this->validateList(array("new_password", "old_password"))) {

					$query = new Query();

					// make sure type tables exist
					$query->checkDbExistence($this->db_passwords);

					// Needed for comparison
					$old_password = $this->getProperty("old_password", "value");
					// Hash to inject if old password comparison is successful
					$new_password = password_hash($this->getProperty("new_password", "value"), PASSWORD_DEFAULT);


					$sql = "SELECT password FROM ".$this->db_passwords." WHERE user_id = $user_id";
					if($query->sql($sql)) {
						// print $old_password . "," . $query->result(0, "password")."<br>\n";
						// print "::".password_verify($old_password, $query->result(0, "password"))."<br>\n";
						if(password_verify($old_password, $query->result(0, "password"))) {

							// DELETE OLD PASSWORD
							$sql = "DELETE FROM ".$this->db_passwords." WHERE user_id = $user_id";
							if($query->sql($sql)) {

								// SAVE NEW PASSWORD
								$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$new_password'";
								if($query->sql($sql)) {

									return true;
								}
							}

						}
						
						return array("error" => "wrong_password");
					}

				}

			}
			// user does not have a password
			else {

				// does values validate
				if($this->validateList(array("new_password"))) {

					$query = new Query();

					// make sure type tables exist
					$query->checkDbExistence($this->db_passwords);

					// Hash to inject
					$new_password = password_hash($this->getProperty("new_password", "value"), PASSWORD_DEFAULT);


					// SAVE NEW PASSWORD
					$sql = "INSERT INTO ".$this->db_passwords." SET user_id = $user_id, password = '$new_password'";
					if($query->sql($sql)) {

						return true;
					}
				}

			}

		}

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
			$query->checkDbExistence($this->db_password_reset_tokens);


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
					$sql = "INSERT INTO ".$this->db_password_reset_tokens." VALUES(DEFAULT, $user_id, '$reset_token', '".date("Y-m-d H:i:s")."')";
					if($query->sql($sql)) {
						
						$sql = "SELECT nickname FROM ".$this->db." WHERE id = '$user_id'";
						
						if($query->sql($sql)) {
							
							// nickname 
							$nickname = $query->result(0, "nickname");
						
							// send email
							mailer()->send(array(
								"values" => array(
									"TOKEN" => $reset_token,
									"USERNAME" => $username,
									"NICKNAME" => $nickname
								),
								"track_clicks" => false,
								"recipients" => $email,
								"template" => "reset_password"
							));

							// send notification email to admin
							// TODO: consider disabling this once it has proved itself worthy
							mailer()->send(array(
								"subject" => "Password reset requested: " . $email,
								"message" => "Check out the user: " . SITE_URL . "/janitor/admin/user/edit/" . $user_id,
								"template" => "system"
							));

							return true;
						}

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
		$new_password = password_hash($this->getProperty("new_password", "value"), PASSWORD_DEFAULT);

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

						// send notification email to admin
						// TODO: consider disabling this once it has proved itself worthy
						mailer()->send(array(
							"subject" => "Password was resat: " . $user_id,
							"message" => "Check out the user: " . SITE_URL . "/janitor/admin/user/edit/" . $user_id
						));

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

		$sql = "DELETE FROM ".$this->db_password_reset_tokens." WHERE created_at < '".date("Y-m-d H:i:s", strtotime("-15 minutes"))."'";
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
		$query->checkDbExistence($this->db_apitokens);

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
		$query->checkDbExistence($this->db_apitokens);

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
		$query->checkDbExistence($this->db_apitokens);

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
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");


		if(count($action) == 1 && $user_id && $this->validateList(array("address_label","address_name","address1","postal","city","country"))) {

			$query = new Query();

			// make sure type tables exist
			$query->checkDbExistence($this->db_addresses);

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

					$address_id = $query->lastInsertId();
					$page->addLog("user->addAddress: user added new address ($user_id, $address_id)");

					return array("id" => $address_id);
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




	// MAILLIST

	// get maillist info
	// get all maillists (list of available maillists)
	// get maillists for user
	// get state of specific maillist for specific user
	function getMaillists($_options = false) {

		$user_id = session()->value("user_id");
		$maillist = false;
		$maillist_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "maillist"       : $maillist         = $_value; break;
					case "maillist_id"    : $maillist_id      = $_value; break;
				}
			}
		}

		$query = new Query();


		// check for specific maillist (by nane) for current user
		if($maillist) {
			$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.maillist_id, maillists.name FROM ".$this->db_maillists." as subscribers, ".UT_MAILLISTS." as maillists WHERE subscribers.user_id = $user_id AND subscribers.maillist_id = maillists.id AND maillists.maillist = '$maillist'";
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}
		// check for specific maillist (by id) for current user
		else if($maillist_id) {
			$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.maillist_id, maillists.name FROM ".$this->db_maillists." as subscribers, ".UT_MAILLISTS." as maillists WHERE subscribers.user_id = $user_id AND subscribers.maillist_id = '$maillist_id'";
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}
		// get maillists for current user
		else {
			$sql = "SELECT subscribers.id, subscribers.user_id, subscribers.maillist_id, maillists.name FROM ".$this->db_maillists." as subscribers, ".UT_MAILLISTS." as maillists WHERE subscribers.user_id = $user_id AND subscribers.maillist_id = maillists.id";
			if($query->sql($sql)) {
				return $query->results();
			}
		}

	}

	// /#controller#/addMaillist
	// Maillist info i $_POST
	function addMaillist($action) {
		global $page;

		// Get posted values to make them available for models
		$this->getPostedEntities();
		$user_id = session()->value("user_id");

		if(count($action) == 1 && $user_id && $this->validateList(array("maillist_id"))) {

			$query = new Query();

			$maillist_id = $this->getProperty("maillist_id", "value");
			// already signed up (to avoid faulty double entries)
			$sql = "SELECT id FROM $this->db_maillists WHERE user_id = $user_id AND maillist_id = $maillist_id";
			if(!$query->sql($sql)) {
				$sql = "INSERT INTO ".$this->db_maillists." SET user_id=$user_id, maillist_id=$maillist_id";
				$query->sql($sql);

				$page->addLog("user->addMaillist: user subscribed to maillist, user_id:$user_id, maillist_id:$maillist_id)");
			}
			return true;
		}

		return false;
		
	}

	// /janitor/admin/profile/deleteMaillist/#maillist_id#
	function deleteMaillist($action) {

		$user_id = session()->value("user_id");

		if(count($action) == 2 && $user_id) {
			$query = new Query();
			$maillist_id = $action[1];

			$sql = "DELETE FROM $this->db_maillists WHERE maillist_id = $maillist_id AND user_id = $user_id";
			if($query->sql($sql)) {
				return true;
			}

		}

		return false;
	}


	// #controller#/unsubscribeUserFromMaillist
	function unsubscribeUserFromMaillist($action) {

		global $page;

		$maillist_id = getPost("maillist_id");
		$username = getPost("username");
		$verification_code = getPost("verification_code");

		$maillist = $page->maillists($maillist_id);

		if($maillist && $username && $verification_code) {

			$query = new Query();

			$sql = "SELECT user_id FROM $this->db_usernames WHERE username = '$username' AND verification_code = '$verification_code'";
			if($query->sql($sql)) {

				$user_id = $query->result(0, "user_id");
				$sql = "DELETE FROM $this->db_maillists WHERE maillist_id = $maillist_id AND user_id = $user_id";
				if($query->sql($sql)) {

					// add to log
					$page->addLog("user->unsubscribeUserFromMaillist: maillist_id:$maillist_id, user_id:$user_id");

					return true;
				}

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

				$sql = "SELECT * FROM ".$this->db_readstates." WHERE user_id = $user_id";
				if($query->sql($sql)) {
					return $query->results();
				}
				
			}
		}

		return false;
	}


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

		global $page;

		// get current user
		$user_id = session()->value("user_id");
		$item_id = false;
		$subscription_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"             : $item_id               = $_value; break;
					case "subscription_id"     : $subscription_id       = $_value; break;
				}
			}
		}

		$query = new Query();
		$IC = new Items();
		$SC = new Shop();

		// check for specific subscription for current user
		if($item_id !== false) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id AND item_id = $item_id LIMIT 1";
			if($query->sql($sql)) {
				$subscription = $query->result(0);
				$subscription["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
				$subscription["membership"] = $subscription["item"]["itemtype"] == "membership" ? true : false;

				// extend payment method details
				if($subscription["payment_method"]) {
					$payment_method = $subscription["payment_method"];
					$subscription["payment_method"] = $page->paymentMethods($payment_method);
				}

				// payment status
				if($subscription["order_id"]) {
					$subscription["order"] = $SC->getOrders(array("order_id" => $subscription["order_id"]));
				}

				return $subscription;
			}
		}
		// get subscription by subscription id
		else if($subscription_id !== false) {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id AND id = $subscription_id LIMIT 1";
			if($query->sql($sql)) {
				$subscription = $query->result(0);
				$subscription["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
				$subscription["membership"] = $subscription["item"]["itemtype"] == "membership" ? true : false;

				// extend payment method details
				if($subscription["payment_method"]) {
					$payment_method = $subscription["payment_method"];
					$subscription["payment_method"] = $page->paymentMethods($payment_method);
				}

				// payment status
				if($subscription["order_id"]) {
					$subscription["order"] = $SC->getOrders(array("order_id" => $subscription["order_id"]));
				}

				return $subscription;
			}
		}

		// get list of all subscriptions for current user
		else {
			$sql = "SELECT * FROM ".$this->db_subscriptions." WHERE user_id = $user_id";
			if($query->sql($sql)) {
				$subscriptions = $query->results();
				foreach($subscriptions as $i => $subscription) {
					$subscriptions[$i]["item"] = $IC->getItem(array("id" => $subscription["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
					$subscriptions[$i]["membership"] = $subscriptions[$i]["item"]["itemtype"] == "membership" ? true : false;

					// extend payment method details
					if($subscription["payment_method"]) {
						$payment_method = $subscription["payment_method"];
						$subscriptions[$i]["payment_method"] = $page->paymentMethods($payment_method);
					}

					// payment status
					if($subscription["order_id"]) {
						$subscriptions[$i]["order"] = $SC->getOrders(array("order_id" => $subscription["order_id"]));
					}
				}
				return $subscriptions;
			}
		}

		return false;
	}


	// add a subscription
	// will only add paid subscription if order_id is passed
	// will not add subscription if subscription already exists, but returns existing subscription instead
	# /#controller#/addSubscription
	function addSubscription($action) {

		// get current user
		$user_id = session()->value("user_id");


		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1 && $this->validateList(array("item_id"))) {

			$query = new Query();
			$IC = new Items();

			$item_id = $this->getProperty("item_id", "value");
			$order_id = $this->getProperty("order_id", "value");
			$payment_method = $this->getProperty("payment_method", "value");

			// safety valve
			// check if subscription already exists (somehow something went wrong)
			$subscription = $this->getSubscriptions(array("item_id" => $item_id));
			if($subscription) {
				// forward request to update method
				return $this->updateSubscription(array("updateSubscription", $subscription["id"]));
			}


			// get item prices and subscription method details to create subscription correctly
			$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true, "prices" => true)));
			if($item) {


				// order flag
				$order = false;


				// item has price
				// then we need an order_id
				if(SITE_SHOP && $item["prices"]) {

					// no order_id? - don't do anything else
					if(!$order_id) {
						return false;
					}


					$SC = new Shop();
					// check if order_id is valid
					$order = $SC->getOrders(array("order_id" => $order_id));
					if(!$order) {
						return false;
					}

				}


				// does subscription expire
				$expires_at = false; 

				if($item["subscription_method"] && $item["subscription_method"]["duration"]) {
					$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"]);
				}


				$sql = "INSERT INTO ".$this->db_subscriptions." SET user_id = $user_id, item_id = $item_id";
				if($order_id) {
					$sql .= ", order_id = $order_id";
				}
				if($payment_method) {
					$sql .= ", payment_method = $payment_method";
				}
				if($expires_at) {
					$sql .= ", expires_at = '$expires_at'";
				}


//				print $sql;
				if($query->sql($sql)) {

					// get new subscription
					$subscription = $this->getSubscriptions(array("item_id" => $item_id));

					// if item is membership - update membership/subscription_id information
					if($item["itemtype"] == "membership") {

						// add subscription id to post array
						$_POST["subscription_id"] = $subscription["id"];

						// check if membership exists
						$membership = $this->getMembership();

						// safety valve
						// create membership if it does not exist
						if(!$membership) {
							$membership = $this->addMembership(array("addMembership"));
						}
						// update existing membership
						else {
							$membership = $this->updateMembership(array("updateMembership"));
						}

						// clear post array
						unset($_POST);

					}



					// perform special action on subscribe
					// this must be done after membership has been updated with new subscription id
					$model = $IC->typeObject($item["itemtype"]);
					if(method_exists($model, "subscribed")) {
						$model->subscribed($subscription);
					}


					// add to log
					global $page;
					$page->addLog("user->addSubscription: item_id:$item_id, user_id:$user_id");


					return $subscription;
				}

			}

		}

		return false;
	}

	# /#controller#/updateSubscription/#subscription_id#
	function updateSubscription($action) {

		// get current user
		$user_id = session()->value("user_id");



		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 2) {

			$query = new Query();
			$IC = new Items();

			$subscription_id = $action[1];
			$item_id = $this->getProperty("item_id", "value");
			$order_id = $this->getProperty("order_id", "value");
			$payment_method = $this->getProperty("payment_method", "value");
			$subscription_upgrade = $this->getProperty("subscription_upgrade", "value");
			$subscription_renewal = $this->getProperty("subscription_renewal", "value");



			// get item prices and subscription method details to create subscription correctly
			$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true, "prices" => true)));
			if($item) {


				// order flag
				$order = false;


				// item has price
				// then we need an order_id
				if(SITE_SHOP && $item["prices"]) {

					// no order_id? - don't do anything else
					if(!$order_id) {
						return false;
					}


					$SC = new Shop();
					// check if order_id is valid
					$order = $SC->getOrders(array("order_id" => $order_id));
					if(!$order) {
						return false;
					}

				}


				// get new subscription
				$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));
				$org_item_id = $subscription["item_id"];
				// does subscription expire
				$expires_at = false;

				if($item["subscription_method"] && $item["subscription_method"]["duration"]) {

					// if renewal
					if($subscription_renewal && $subscription["expires_at"]) {
						$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"], $subscription["expires_at"]);
					}
					// if switch or upgrade from non-expiring membership
					else if((!$subscription_upgrade || !$subscription["expires_at"])) {
						$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"]);
					}

					// upgrade does not change exsisting expires_at

				}


				$sql = "UPDATE ".$this->db_subscriptions." SET modified_at = CURRENT_TIMESTAMP, item_id = $item_id";
				if($order_id) {
					$sql .= ", order_id = $order_id";
				}
				if($payment_method) {
					$sql .= ", payment_method = $payment_method";
				}
				if($expires_at) {
					$sql .= ", expires_at = '$expires_at'";

					if($subscription_renewal && $subscription["expires_at"]) {
						$sql .= ", renewed_at = " . $subscription["expires_at"];
					}
					else {
						$sql .= ", renewed_at = CURRENT_TIMESTAMP";
					}

				}
				else if(!$subscription_upgrade) {
					$sql .= ", expires_at = NULL";
				}


				$sql .= " WHERE user_id = $user_id AND id = $subscription_id";


//				print $sql;
				if($query->sql($sql)) {

					// get new subscription
					$subscription = $this->getSubscriptions(array("item_id" => $item_id));

					// if item is membership - update membership/subscription_id information
					if($item["itemtype"] == "membership") {

						// add subscription id to post array
						$_POST["subscription_id"] = $subscription["id"];

						// check if membership exists
						$membership = $this->getMembership();

						// safety valve
						// create membership if it does not exist
						if(!$membership) {
							$membership = $this->addMembership(array("addMembership"));
						}
						// update existing membership
						else {
							$membership = $this->updateMembership(array("updateMembership"));
						}

						// clear post array
						unset($_POST);

					}

					// perform special action on subscribe to new item
					if($item_id != $org_item_id) {
						$model = $IC->typeObject($item["itemtype"]);
						if(method_exists($model, "subscribed")) {
							$model->subscribed($subscription);
						}
					}


					// add to log
					global $page;
					$page->addLog("user->updateSubscription: item_id:$item_id, user_id:$user_id");


				}

				return $subscription;

			}

		}

		return false;
	}


	// /#controller#/deleteSubscription/#subscription_id#
	function deleteSubscription($action) {


		// does values validate
		if(count($action) == 2) {

			$user_id = session()->value("user_id");
			$subscription_id = $action[1];

			$query = new Query();

			// check membership dependency
			$sql = "SELECT id FROM ".$this->db_members." WHERE subscription_id = $subscription_id";
			if(!$query->sql($sql)) {

				// get item id from subscription, before deleting it
				$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));


				// perform special action on unsubscribe
				// before removing subscription (because unsubscribe uses it as information source)
				$IC = new Items();
				$unsubscribed_item = $IC->getItem(array("id" => $subscription["item_id"]));
				if($unsubscribed_item) {
					$model = $IC->typeObject($unsubscribed_item["itemtype"]);
					if(method_exists($model, "unsubscribed")) {
//						$model->unsubscribed($unsubscribed_item["item_id"], $user_id);
						$model->unsubscribed($subscription);
					}
				}


				$sql = "DELETE FROM ".$this->db_subscriptions." WHERE id = $subscription_id AND user_id = $user_id";
				if($query->sql($sql)) {

					global $page;
					$page->addLog("user->deleteSubscription: $subscription_id ($user_id)");

					return true;
				}
			}
		}

		return false;
	}


	// calculate expery date for subscription
	// TODO: enable more flexible duration "settings"
	function calculateSubscriptionExpiry($duration, $start_time = false) {
//		print "calculateSubscriptionExpiry:" . $duration;

		$expires_at = false;

		if($start_time) {
			$timestamp = strtotime($start_time);
		}
		else {
			$timestamp = time();
		}


		// annually
		if($duration == "annually") {

			$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp), date("j", $timestamp), date("Y", $timestamp)+1));
		}

		// monthly
		else if($duration == "monthly") {

			$days_of_month = date("t", $timestamp);
			$date_of_month = date("j", $timestamp);

			$days_of_next_month = date("t", mktime(0, 0, 0, date("n", $timestamp)+1, 1, date("Y", $timestamp)));
			
			// if current date doesn't exist in next month (fx. 30 or 31/01)
			// if current date is last date in month 
			// - choose last day of next month
			if($date_of_month > $days_of_next_month || $date_of_month == $days_of_month) {

				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp)+1, $days_of_next_month, date("Y", $timestamp)));
			}
			// just use same date next month
			else {

				$expires_at = date("Y-m-d 00:00:00", mktime(0, 0, 0, date("n", $timestamp)+1, date("j", $timestamp), date("Y", $timestamp)));
			}

		}

		// weekly
		else if($duration == "weekly") {

			$expires_at = date("Y-m-d 00:00:00", $timestamp + (7*24*60*60));
		}

		return $expires_at;
	}






	// MEMBERSHIP

	// get membership for current user
	// includes membership item and order
	function getMembership() {

		// get current user
		$user_id = session()->value("user_id");

		$query = new Query();
		$IC = new Items();
		$SC = new Shop();


		// membership with subscription
		$sql = "SELECT members.id as id, subscriptions.id as subscription_id, subscriptions.item_id as item_id, subscriptions.order_id as order_id, members.user_id as user_id, members.created_at as created_at, members.modified_at as modified_at, subscriptions.renewed_at as renewed_at, subscriptions.expires_at as expires_at FROM ".$this->db_subscriptions." as subscriptions, ".$this->db_members." as members WHERE members.user_id = $user_id AND members.subscription_id = subscriptions.id LIMIT 1";
		if($query->sql($sql)) {
			$membership = $query->result(0);
			$membership["item"] = $IC->getItem(array("id" => $membership["item_id"], "extend" => array("prices" => true, "subscription_method" => true)));
			if($membership["order_id"]) {
				$membership["order"] = $SC->getOrders(array("order_id" => $membership["order_id"]));
			}
			else {
				$membership["order"] = false;
			}

			return $membership;
		}
		// membership without subscription
		else {
			$sql = "SELECT * FROM ".$this->db_members." WHERE user_id = $user_id LIMIT 1";
			if($query->sql($sql)) {
				$membership = $query->result(0);

				$membership["item"] = false;
				$membership["order"] = false;
				$membership["order_id"] = false;
				$membership["item_id"] = false;
				$membership["expires_at"] = false;
				$membership["renewed_at"] = false;

				return $membership;
			}
		}

		return false;
	}



	// Add membership
	# /#controller#/addMembership
	function addMembership($action) {

		// get current user
		$user_id = session()->value("user_id");

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1) {

			$query = new Query();
			$subscription_id = $this->getProperty("subscription_id", "value");

			// safety valve
			// does user already have membership
			$membership = $this->getMembership();
			if($membership) {
				return $this->updateMembership(array("updateMembership"));
			}

			// create new membership
			$sql = "INSERT INTO ".$this->db_members." SET user_id = $user_id";

			// Add subscription id if passed
			if($subscription_id) {

				// make sure subscription is valid
				$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));
				if($subscription) {
					$sql .= ", subscription_id = $subscription_id";
				}

			}

			// creating sucess
			if($query->sql($sql)) {

				$membership = $this->getMembership();

				global $page;
				$page->addLog("user->addMembership: member_id:".$membership["id"].", user_id:$user_id");

				return $membership;
			}

		}

		return false;
	}

	// Add membership
	# /#controller#/updateMembership
	function updateMembership($action) {

		// get current user
		$user_id = session()->value("user_id");

		// Get posted values to make them available for models
		$this->getPostedEntities();

		// does values validate
		if(count($action) == 1) {


			$query = new Query();
			$subscription_id = $this->getProperty("subscription_id", "value");


			$sql = "UPDATE ".$this->db_members." SET modified_at = CURRENT_TIMESTAMP";

			// Add subscription id if passed
			if($subscription_id) {

				// make sure subscription is valid
				$subscription = $this->getSubscriptions(array("subscription_id" => $subscription_id));
				if($subscription) {
					$sql .= ", subscription_id = $subscription_id";
				}

			}

			// Add condition
			$sql .= " WHERE user_id = $user_id";


			// creating sucess
			if($query->sql($sql)) {

				$membership = $this->getMembership();

				global $page;
				$page->addLog("user->updateMembership: member_id".$membership["id"].", user_id:$user_id, subscription_id:".($subscription_id ? $subscription_id : "N/A"));

				return $membership;
			}


		}
	}


	// cancel membership
	// removes subscription_id from membership and deletes related subscription
	# /#controller#/cancelMembership/#member_id#
	function cancelMembership($action) {

		// get current user
		$user_id = session()->value("user_id");

		// does values validate
		if(count($action) == 2) {
			$member_id = $action[1];

			$query = new Query();
			$member = $this->getMembership();
//			print_r($member);

			if($member && $member["user_id"] == $user_id) {

				// set subscription_id to NULL - maintains member in system
				$sql = "UPDATE ".$this->db_members. " SET subscription_id = NULL, modified_at = CURRENT_TIMESTAMP WHERE id = ".$member_id;
				if($query->sql($sql)) {

					// delete subscription
					$this->deleteSubscription(array("deleteSubscription", $member["subscription_id"]));


					global $page;
					$page->addLog("User->cancelMembership: member_id:".$member["id"]);


					// send notification email to admin
					mailer()->send(array(
						"recipients" => SHOP_ORDER_NOTIFIES,
						"subject" => SITE_URL . " - Membership cancelled ($user_id)",
						"message" => "Check out the user: " . SITE_URL . "/janitor/admin/user/" . $user_id,
						// "template" => "system"
					));


					return true;

				}

			}

		}

		return false;
	}


	// change membership type
	// info i $_POST
	// TODO: only changes item_id reference in subscription
	// - should also calculate cost difference and create new order to pay.
	// - this requires the ability to add custom order-lines with calculated price

	# /#controller#/switchMembership
	function switchMembership($action) {

		// get current user
		$user_id = session()->value("user_id");

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 1 && $this->validateList(array("item_id"))) {

			$query = new Query();
			$IC = new Items();
			$SC = new Shop();

			$item_id = $this->getProperty("item_id", "value");

			$member = $this->getMembership();
			if($member) {

				// add item to cart
				$_POST["quantity"] = 1;
				$_POST["item_id"] = $item_id;
				$cart = $SC->addToCart(array("addToCart"));
				unset($_POST);

				// convert to order
				// adding a membership to an order will automatically change the membership
				$order = $SC->newOrderFromCart(array("newOrderFromCart", $cart["cart_reference"]));

				if($order) {
					return $order;
				}
			}

		}

		return false;
	}


	// TODO: Creating new custom order based on existing order, should be done by shop class
	// add new order with custom price (new_price - current_orice)
	// get current order and copy info to new order, then add manual order line

	# /#controller#/upgradeMembership
	function upgradeMembership($action) {

		// get current user
		$user_id = session()->value("user_id");

		// Get posted values to make them available for models
		$this->getPostedEntities();


		// does values validate
		if(count($action) == 1 && $this->validateList(array("item_id"))) {

			$query = new Query();
			$IC = new Items();
			$SC = new Shop();

			$item_id = $this->getProperty("item_id", "value");

			$member = $this->getMembership();
			if($member && $member["item_id"]) {


				include_once("classes/shop/supershop.class.php");
				$SC = new SuperShop();


				$_POST["user_id"] = $user_id;
				$_POST["order_comment"] = "Membership upgraded";
				$order = $SC->addOrder(array("addOrder"));
				unset($_POST);

				// get existing membership price
				$current_price = $SC->getPrice($member["item_id"]);

				// get new item and price
				$item = $IC->getItem(array("id" => $item_id, "extend" => array("subscription_method" => true)));
				$new_price = $SC->getPrice($item_id);


				// add item to cart
				$_POST["quantity"] = 1;
				$_POST["item_id"] = $item_id;
				$_POST["item_price"] = $new_price["price"] - $current_price["price"];
				$_POST["item_name"] = $item["name"] . " (Upgrade)";
				$_POST["subscription_upgrade"] = 1;


				// adding a membership to an order will automatically change the membership
				$order = $SC->addToOrder(array("addToOrder", $order["id"]));
				unset($_POST);

				if($order) {

					global $page;
					$page->addLog("User->upgradeMembership: member_id:".$member["id"].",item_id:$item_id, subscription_id:".$member["subscription_id"]);


					// send notification email to admin
					mailer()->send(array(
						"recipients" => SHOP_ORDER_NOTIFIES,
						"subject" => SITE_URL . " - Membership upgraded to ".$item["name"]." ($user_id)",
						"message" => "Check out the user: " . SITE_URL . "/janitor/admin/user/" . $user_id,
						// "template" => "system"
					));


					return true;
				}

// 				// only perform membership upgrade if it is an actual upgrade
// 				if($new_price["price"] > $current_price["price"]) {
//
// 					// find price difference
// 					$order_price["price"] = $new_price["price"] - $current_price["price"];
// 					$order_price["vat"] = $new_price["price"] * (1 - (1 / (1 + ($new_price["vatrate"]/100))));
//
//
// 					// Start creating custom difference order
//
// 					// get existing order to copy data for new order
// 					$sql = "SELECT * FROM ".$SC->db_orders." WHERE id = ".$member["order_id"]." LIMIT 1";
// 					if($query->sql($sql)) {
// 						$order = $query->result(0);
//
// 						// get new order number
// 						$order_no = $SC->getNewOrderNumber();
// 						if($order_no) {
//
// 							// create base data update sql
// 							$sql = "UPDATE ".$SC->db_orders." SET comment = 'Membership upgrade'";
//
// 							foreach($order as $key => $value) {
// //								print $key . " = " . $value . "<br>\n";
// 								// filter out order specific values
// 								if(!preg_match("/(^order_no$|^id$|status$|^comment$|ed_at$)/", $key) && $value) {
// 									$sql .= ", $key = '$value'";
// 								}
//
// 							}
//
// 							$sql .= " WHERE order_no = '$order_no'";
// //							print $sql."<br>\n";
//
// 							if($query->sql($sql)) {
//
// 								// get the new order
// 								$order = $SC->getOrders(array("order_no" => $order_no));
//
// 								// add custom order line
// 								$sql = "INSERT INTO ".$SC->db_order_items." SET order_id=".$order["id"].", item_id=$item_id, name='".$item["name"]." (Upgrade)', quantity=1, unit_price=".$order_price["price"].", unit_vat=".$order_price["vat"].", total_price=".$order_price["price"].", total_vat=".$order_price["vat"];
// //								print $sql."<br>\n";
//
// 								if($query->sql($sql)) {
//
// 									// update subscription data (item id, order_id, expires_at)
//
// 									// get current subscription
// 									$subscription = $this->getSubscriptions(array("subscription_id" => $member["subscription_id"]));
//
// 									$sql = "UPDATE ".$this->db_subscriptions. " SET item_id = $item_id, order_id = ".$order["id"];
//
// 									$expires_at = false;
// 									if($item["subscription_method"]) {
// 										$start_time = $subscription["renewed_at"] ? $subscription["renewed_at"] : $subscription["created_at"];
// 										$expires_at = $this->calculateSubscriptionExpiry($item["subscription_method"]["duration"], $start_time);
// 									}
//
// 									if($expires_at) {
// 										$sql .= ", expires_at = '$expires_at'";
// 									}
// 									else {
// 										$sql .= ", expires_at = NULL";
// 									}
//
// 									$sql .= " WHERE id = ".$member["subscription_id"];
// //									print $sql."<br>\n";
//
// 									if($query->sql($sql)) {
//
// 										global $page;
// 										$page->addLog("User->upgradeMembership: member_id:".$member["id"].",item_id:$item_id, subscription_id:".$member["subscription_id"]);
//
//
// 										return true;
// 									}
//
// 								}
//
// 							}
//
// 						}
//
// 					}
//
// 				}

			}

		}

		return false;
	}


	// User has accepted terms
	// Add to database
	function acceptedTerms() {

		$query = new Query();
		$user_id = session()->value("user_id");

		$query->checkDbExistence(SITE_DB.".user_log_agreements");
		$sql = "INSERT INTO ".SITE_DB.".user_log_agreements SET user_id = $user_id, name = 'terms'";
		$query->sql($sql);

	}
	
	// Check if user has accepted terms
	function hasAcceptedTerms() {

		$query = new Query();
		$user_id = session()->value("user_id");

		$query->checkDbExistence(SITE_DB.".user_log_agreements");
		$sql = "SELECT user_id FROM ".SITE_DB.".user_log_agreements WHERE user_id = $user_id";
		if($query->sql($sql)) {
			return true;
		}

		return false;
	}

}

?>