<?php
/**
* @package janitor.shop
*/


require_once("includes/stripe-php-4.1.1/init.php");


class JanitorStripe {

	/**
	*
	*/
	function __construct() {

		// include stripe connection keys
		@include_once("config/connect_stripe.php");

		$this->stripe = array(
		  "secret_key"      => $stripe_keys["secret"],
		  "publishable_key" => $stripe_keys["public"]
		);

		\Stripe\Stripe::setApiKey($this->stripe['secret_key']);
	
	}


	// Process payment
	function processCardAndPayOrder($order, $card_number, $card_exp_month, $card_exp_year, $card_cvc) {

		if($order && $order["user"] && $order["user"]["email"]) {

			// does customer already exist in Stripe account
			$customer_id = $this->getCustomerId($order["user_id"]);

			// create customer, if it doesn't exist
			if(!$customer_id) {
				$customer_id = $this->createCustomer($order);
			}

			// customer created or updated
			if($customer_id) {

				// create token for card
				$token_id = $this->createToken($card_number, $card_exp_month, $card_exp_year, $card_cvc);
				if($token_id) {

					// Add card to customer
					$response = $this->addCard($customer_id, $token_id);
					if($response) {

						// Charge customer
						if($this->chargeCustomer($order, $customer_id)) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	// Create customer in Stripe account
	function createToken($card_number, $card_exp_month, $card_exp_year, $card_cvc) {

		global $page;

		try {

			$token = \Stripe\Token::create(array(
			  "card" => array(
			    "number" => $card_number,
			    "exp_month" => $card_exp_month,
			    "exp_year" => $card_exp_year,
			    "cvc" => $card_cvc
			  )
			));

			if($token) {

				$page->addLog("Token created: brand:".$token->card->brand.", last4:".$token->card->last4, "stripe");
				return $token->id;

			}

		}
		// Card error
		catch(\Stripe\Error\Card $exception) {

			$this->exceptionHandler("Creating token", $exception);
			return false;
		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Error\RateLimit $exception) {

			$this->exceptionHandler("Creating token", $exception);
			return false;
		} 
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Error\InvalidRequest $exception) {

			$this->exceptionHandler("Creating token", $exception);
			return false;
		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Error\Authentication $exception) {

			$this->exceptionHandler("Creating token", $exception);
			return false;
		}
		// Network communication with Stripe failed
		catch (\Stripe\Error\ApiConnection $exception) {

			$this->exceptionHandler("Creating token", $exception);
			return false;
		}
		// Display a very generic error to the user, and maybe send yourself an email
		catch (\Stripe\Error\Base $exception) {

			$this->exceptionHandler("Creating token", $exception);
			return false;
		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("Creating token", $exception);
			return false;
		}

		return false;
	}


	// Create customer in Stripe account
	function createCustomer($order, $token_id = false) {

		global $page;

		// set meaningful description
		$description = $order["user"]["nickname"];
		if($order["user"]["membership"]) {
			$description .= ", Member " . $order["user"]["membership"]["id"];
		}

		// API communication
		try {

			$create_parameters = array(
				'email'       => $order["user"]["email"],
				'description' => $description,
			);

			if($token_id) {
				$create_parameters['source'] = $token_id;
			}

			$customer = \Stripe\Customer::create($create_parameters);

			if($customer) {

				$customer_id = $customer->id;
				$this->saveCustomerId($order["user_id"], $customer_id);

				$page->addLog("Customer created: user_id:".$order["user"]["id"].", email:".$customer->email.", customer_id:".$customer->id, "stripe");

				return $customer_id;
			}

		}
		// Card error
		catch(\Stripe\Error\Card $exception) {

			$this->exceptionHandler("Creating customer", $exception);
			return false;
		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Error\RateLimit $exception) {

			$this->exceptionHandler("Creating customer", $exception);
			return false;
		} 
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Error\InvalidRequest $exception) {

			$this->exceptionHandler("Creating customer", $exception);
			return false;
		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Error\Authentication $exception) {

			$this->exceptionHandler("Creating customer", $exception);
			return false;
		}
		// Network communication with Stripe failed
		catch (\Stripe\Error\ApiConnection $exception) {

			$this->exceptionHandler("Creating customer", $exception);
			return false;
		}
		// Display a very generic error to the user, and maybe send yourself an email
		catch (\Stripe\Error\Base $exception) {

			$this->exceptionHandler("Creating customer", $exception);
			return false;
		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("Creating customer", $exception);
			return false;
		}


		return false;
	}


	// Add new card to existing customer in Stripe account
	function addCard($customer_id, $token_id) {

		global $page;

		try {

			$customer = \Stripe\Customer::retrieve($customer_id);
			$customer->source = $token_id;
			$response = $customer->save();

			if($response && $response->email && $response->id) {

				$page->addLog("Card added: email:".$response->email.", customer_id:".$response->id, "stripe");
				return true;

			}
		}
		// Card error
		catch(\Stripe\Error\Card $exception) {

			$this->exceptionHandler("Adding card", $exception);
			return false;
		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Error\RateLimit $exception) {

			$this->exceptionHandler("Adding card", $exception);
			return false;
		} 
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Error\InvalidRequest $exception) {

			$this->exceptionHandler("Adding card", $exception);
			return false;
		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Error\Authentication $exception) {

			$this->exceptionHandler("Adding card", $exception);
			return false;
		}
		// Network communication with Stripe failed
		catch (\Stripe\Error\ApiConnection $exception) {

			$this->exceptionHandler("Adding card", $exception);
			return false;
		}
		// Display a very generic error to the user, and maybe send yourself an email
		catch (\Stripe\Error\Base $exception) {

			$this->exceptionHandler("Adding card", $exception);
			return false;
		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("Adding card", $exception);
			return false;
		}


		return false;
	}


	function chargeCustomer($order, $customer_id) {

		global $page;


		try {

			// TODO: amount should be stated in smallest unit. 
			// Currency knows about decimals, which should be used to calculate amount rather than the *100 currently used.

			$charge = \Stripe\Charge::create(array(
				'capture'              => true,
				'customer'             => $customer_id,
				'amount'               => $order["total_price"]["price"]*100,
				'currency'             => $order["currency"],
				'description'          => $order["order_no"],
				'statement_descriptor' => "think.dk - " . $order["order_no"],
				'receipt_email'        => $order["user"]["email"],
			));

			$page->addLog("Customer charged: customer_id:".$customer_id.", status:".$charge->status.", amount:".$charge->amount.", captured:".$charge->captured.", paid:".$charge->paid, "stripe");


			// print_r($charge);
			//
			// $charge->status
			// $charge->captured
			// $charge->paid

			if($charge && $charge->id && $charge->paid && $charge->captured && $charge->status) {

				// add payment

				include_once("classes/shop/supershop.class.php");
				$SC = new SuperShop();

				// find correct payment method id
				$payment_methods = $page->paymentMethods();
				$stripe_index = arrayKeyValue($payment_methods, "gateway", "stripe");
				if($stripe_index !== false) {

					// Add variables for addPayment
					$_POST["payment_amount"] = $order["total_price"]["price"];
					$_POST["currency"] = $order["currency"];
					$_POST["payment_method"] = $payment_methods[$stripe_index]["id"];
					$_POST["order_id"] = $order["id"];
					$_POST["transaction_id"] = $charge->id;
					if($SC->addPayment(array("addPayment"))) {

						$page->addLog("Payment added to Janitor: order_id:".$order["id"].", transaction_id:".$charge->id.", amount:".$order["total_price"]["price"], "stripe");
						return true;

					}
					else {

						$page->addLog("Failed adding payment to Janitor (adding payment): order_id:".$order["id"].", transaction_id:".$charge->id.", amount:".$order["total_price"]["price"], "stripe");
						// Notify admin

						// Send mail to admin
						$page->mail(array(
							"subject" => SITE_URL." - Error adding Stripe payment", 
							"message" => "Failed adding payment from Stripe capture. This needs to be handled manually.\n\nCharge ID: ".$charge->id."\nOrder id: ".$order["id"]."\nOrder No: ".$order["order_no"],
							"template" => "system"
						));

					}
					unset($_POST);

				}
				else {

					$page->addLog("Could not find payment method id for stripe: order_id:".$order["id"].", transaction_id:".$charge->id.", amount:".$order["total_price"]["price"], "stripe");
					// Notify admin

					// Send mail to admin
					$page->mail(array(
						"subject" => SITE_URL." - Error adding Stripe payment", 
						"message" => "Failed adding payment from Stripe capture (no payment method for stripe). This needs to be handled manually.\n\nCharge ID: ".$charge->id."\nOrder id: ".$order["id"]."\nOrder No: ".$order["order_no"],
						"template" => "system"
					));

				}


			}

		}
		// Card error
		catch(\Stripe\Error\Card $exception) {

			$this->exceptionHandler("Charging customer", $exception);
			return false;
		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Error\RateLimit $exception) {

			$this->exceptionHandler("Charging customer", $exception);
			return false;
		} 
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Error\InvalidRequest $exception) {

			$this->exceptionHandler("Charging customer", $exception);
			return false;
		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Error\Authentication $exception) {

			$this->exceptionHandler("Charging customer", $exception);
			return false;
		}
		// Network communication with Stripe failed
		catch (\Stripe\Error\ApiConnection $exception) {

			$this->exceptionHandler("Charging customer", $exception);
			return false;
		}
		// Display a very generic error to the user, and maybe send yourself an email
		catch (\Stripe\Error\Base $exception) {

			$this->exceptionHandler("Charging customer", $exception);
			return false;
		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("Charging customer", $exception);
			return false;
		}


		return false;
	}



	// Handle any stripe exception
	function exceptionHandler($action, $exception) {

		global $page;


		$error_body = $exception->getJsonBody();
		$error = $error_body["error"];
		$http_response = $exception->getHttpStatus();

		// Add log entry
		$page->addLog($action." failed: type:".$error["type"].", http-response:".$http_response.", message:".$error["message"].", code:".(isset($error["code"]) ? $error["code"] : "N/A").", param:".(isset($error["param"]) ? $error["param"] : "N/A"), "stripe");

		// Send mail to admin
		$page->mail(array(
			"subject" => SITE_URL." - $action - Stripe exception (".$error["type"].")", 
			"message" => "Exception thrown when $action: \n" . print_r($error, true),
			"template" => "system"
		));

	}


	// get stripe customer id if it exists
	function getCustomerId($user_id) {
		
		$query = new Query();
		$query->checkDBExistence(SITE_DB.".user_gateway_stripe");

		$sql = "SELECT * FROM ".SITE_DB.".user_gateway_stripe WHERE user_id = $user_id";
		if($query->sql($sql)) {
			return $query->result(0, "customer_id");
		}

		return false;
	}

	// Save stripe customer id
	function saveCustomerId($user_id, $customer_id) {

		$query = new Query();
		$query->checkDBExistence(SITE_DB.".user_gateway_stripe");

		$sql = "INSERT INTO ".SITE_DB.".user_gateway_stripe SET user_id=$user_id, customer_id='$customer_id'";
		if($query->sql($sql)) {
			return true;
		}

		return false;
	}




}

?>