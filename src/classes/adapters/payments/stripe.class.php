<?php
/**
* @package janitor.shop
*/


// require_once("includes/payments/stripe-php-4.1.1/init.php");
require_once("includes/payments/stripe-php-7.37.1/init.php");


class JanitorStripe {

	/**
	*
	*/
	function __construct($_settings) {

		$this->secret_key = $_settings["secret-key"];
		$this->publishable_key = $_settings["public-key"];

		\Stripe\Stripe::setApiKey($this->secret_key);
		\Stripe\Stripe::setApiVersion("2020-03-02");
	
	}


	// Get specific payment method from Stripe account
	function getPaymentMethod($user_id, $gateway_payment_method_id) {
		// debug(["getPaymentMethod - stripe", $user_id, $gateway_payment_method_id]);

		$customer_id = $this->getCustomerId($user_id);

		if($customer_id) {
			try {

				$details = \Stripe\PaymentMethod::retrieve(
					$gateway_payment_method_id
				);

				// debug([$details]);
				if($details && $details->customer === $customer_id) {
					return [
						"type" => "card",
						"id" => $details->id,
						"fingerprint" => $details->card->fingerprint,
						"last4" => $details->card->last4,
						"exp_month" => $details->card->exp_month,
						"exp_year" => $details->card->exp_year,
					];

				}

			}
			// Card error
			catch(\Stripe\Exception\CardException $exception) {

				$this->exceptionHandler("PaymentMethod::retrieve", $exception);
				return false;

			}
			// Too many requests made to the API too quickly
			catch (\Stripe\Exception\RateLimitException $exception) {

				$this->exceptionHandler("PaymentMethod::retrieve", $exception);
				return false;

			}
			// Invalid parameters were supplied to Stripe's API
			catch (\Stripe\Exception\InvalidRequestException $exception) {

				$this->exceptionHandler("PaymentMethod::retrieve", $exception);
				return false;

			}
			// Authentication with Stripe's API failed
			catch (\Stripe\Exception\AuthenticationException $exception) {

				$this->exceptionHandler("PaymentMethod::retrieve", $exception);
				return false;

			}
			// Network communication with Stripe failed
			catch (\Stripe\Exception\ApiConnectionException $exception) {

				$this->exceptionHandler("PaymentMethod::retrieve", $exception);
				return false;

			}
			// Generic error
			catch (\Stripe\Exception\ApiErrorException $exception) {

				$this->exceptionHandler("PaymentMethod::retrieve", $exception);
				return false;

			}
			// Something else happened, completely unrelated to Stripe
			catch (Exception $exception) {

				$this->exceptionHandler("PaymentMethod::retrieve", $exception);
				return false;

			}
		}


		return false;
	}

	// Get all payment methods from Stripe account
	function getPaymentMethods($user_id) {
		// debug(["getPaymentMethods - stripe", $user_id]);

		$customer_id = $this->getCustomerId($user_id);

		if($customer_id) {
			try {

				$customer = \Stripe\Customer::retrieve(
					$customer_id
				);
				$default_card = $customer->invoice_settings->default_payment_method ? $customer->invoice_settings->default_payment_method : false;
				// debug(["customer", $customer]);

				$details = \Stripe\PaymentMethod::all([
					"customer" => $customer_id,
					"type" => "card",
				]);

				// debug(["getPaymentMethods", $details]);
				if($details && $details->data) {

					$cards = [];
					foreach($details->data as $card_detail) {
						$card = [];
						$card["type"] = "card";
						$card["id"] = $card_detail->id;
						$card["fingerprint"] = $card_detail->card->fingerprint;
						$card["last4"] = $card_detail->card->last4;
						$card["exp_month"] = $card_detail->card->exp_month;
						$card["exp_year"] = $card_detail->card->exp_year;

						if($card_detail->id === $default_card) {
							$card["default"] = true;
						}
						else {
							$card["default"] = false;
						}

						$cards[] = $card;
					}

					return $cards;

				}

			}
			// Card error
			catch(\Stripe\Exception\CardException $exception) {

				$this->exceptionHandler("PaymentMethod::all", $exception);
				return false;

			}
			// Too many requests made to the API too quickly
			catch (\Stripe\Exception\RateLimitException $exception) {

				$this->exceptionHandler("PaymentMethod::all", $exception);
				return false;

			}
			// Invalid parameters were supplied to Stripe's API
			catch (\Stripe\Exception\InvalidRequestException $exception) {

				$this->exceptionHandler("PaymentMethod::all", $exception);
				return false;

			}
			// Authentication with Stripe's API failed
			catch (\Stripe\Exception\AuthenticationException $exception) {

				$this->exceptionHandler("PaymentMethod::all", $exception);
				return false;

			}
			// Network communication with Stripe failed
			catch (\Stripe\Exception\ApiConnectionException $exception) {

				$this->exceptionHandler("PaymentMethod::all", $exception);
				return false;

			}
			// Generic error
			catch (\Stripe\Exception\ApiErrorException $exception) {

				$this->exceptionHandler("PaymentMethod::all", $exception);
				return false;

			}
			// Something else happened, completely unrelated to Stripe
			catch (Exception $exception) {

				$this->exceptionHandler("PaymentMethod::all", $exception);
				return false;

			}
		}


		return false;
	}

	// Add a new payment method to Stripe account
	function addPaymentMethod($user_id, $card_number, $card_exp_month, $card_exp_year, $card_cvc) {

		global $page;

		include_once("classes/users/superuser.class.php");
		$UC = new SuperUser();

		// does customer already exist in Stripe account
		$customer_id = $this->getCustomerId($user_id);

		try {

			$payment_method = \Stripe\PaymentMethod::create([
				"type" => "card",
				"card" => [
					"number" => $card_number,
					"exp_month" => $card_exp_month,
					"exp_year" => $card_exp_year,
					"cvc" => $card_cvc,
				],
			]);

			if($payment_method && $payment_method->id) {

				// Check if this card already exists
				$existing_payment_methods = $this->getPaymentMethods($user_id);
				$card_exists = false;

				if($existing_payment_methods) {
					foreach($existing_payment_methods as $card) {
						if($card["fingerprint"] === $payment_method->card->fingerprint) {

							// Return matching card
							return [
								"status" => "success",
								"type" => "card",
								"card" => $card,
								"existing_card" => true
							];

						}
					}
				}

				// Attach method to customer, if it didn't exist already
				$attached = $payment_method->attach([
					"customer" => $customer_id,
				]);

				// Add user payment method
				$UC->addPaymentMethod([
					"payment_method_id" => $this->getStripePaymentMethodId(),
					"user_id" => $user_id,
				]);

				// debug(["new payment_method", $payment_method]);

				$page->addLog("New payment method added: customer_id:".$customer_id, "stripe");
				return [
					"status" => "success", 
					"type" => "card",
					"card" => [
						"id" => $payment_method->id,
						"fingerprint" => $payment_method->card->fingerprint,
						"last4" => $payment_method->card->last4,
						"exp_month" => $payment_method->card->exp_month,
						"exp_year" => $payment_method->card->exp_year,
					], 
				];
			}

		}
		// Card error
		catch(\Stripe\Exception\CardException $exception) {

			$this->exceptionHandler("PaymentMethod::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Exception\RateLimitException $exception) {

			$this->exceptionHandler("PaymentMethod::create", $exception);
			return false;

		}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Exception\InvalidRequestException $exception) {

			$this->exceptionHandler("PaymentMethod::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Exception\AuthenticationException $exception) {

			$this->exceptionHandler("PaymentMethod::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Network communication with Stripe failed
		catch (\Stripe\Exception\ApiConnectionException $exception) {

			$this->exceptionHandler("PaymentMethod::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Generic error
		catch (\Stripe\Exception\ApiErrorException $exception) {

			$this->exceptionHandler("PaymentMethod::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("PaymentMethod::create", $exception);
			return $this->exceptionResponder($exception);

		}


		return false;
	}

	// Delete payment method from Stripe account
	function deletePaymentMethod($user_id, $user_payment_method_id) {
		// debug(["deletePaymentMethod stripe", $user_payment_method_id]);
		// does customer already exist in Stripe account
		$customer_id = $this->getCustomerId($user_id);

		try {

			$payment_method = \Stripe\PaymentMethod::retrieve(
				$user_payment_method_id
			);

			if($payment_method && $payment_method->customer === $customer_id) {

				$result = $payment_method->detach();

				if(!$result->customer) {

					$query = new Query();
					// Delete this payment method from subscriptions
					$sql = "DELETE FROM ".SITE_DB.".user_gateway_stripe_subscription_payment_method WHERE payment_method_id = ".$user_payment_method_id;
					$query->sql($sql);

					
					global $page;
					$page->addLog("Payment method removed: customer_id:".$customer_id.", user_payment_method_id:".$user_payment_method_id, "stripe");
					return true;
				}

			}

		}
		// Card error
		catch(\Stripe\Exception\CardException $exception) {

			$this->exceptionHandler("PaymentMethod::detach", $exception);
			return $this->exceptionResponder($exception);

		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Exception\RateLimitException $exception) {

			$this->exceptionHandler("PaymentMethod::detach", $exception);
			return false;

		}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Exception\InvalidRequestException $exception) {

			$this->exceptionHandler("PaymentMethod::detach", $exception);
			return $this->exceptionResponder($exception);

		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Exception\AuthenticationException $exception) {

			$this->exceptionHandler("PaymentMethod::detach", $exception);
			return $this->exceptionResponder($exception);

		}
		// Network communication with Stripe failed
		catch (\Stripe\Exception\ApiConnectionException $exception) {

			$this->exceptionHandler("PaymentMethod::detach", $exception);
			return $this->exceptionResponder($exception);

		}
		// Generic error
		catch (\Stripe\Exception\ApiErrorException $exception) {

			$this->exceptionHandler("PaymentMethod::detach", $exception);
			return $this->exceptionResponder($exception);

		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("PaymentMethod::detach", $exception);
			return $this->exceptionResponder($exception);

		}


		return false;
	}

	// Get payment method for subscription – look for stripe payment method
	function getPaymentMethodForSubscription($user_id, $subscription_id) {

		$query = new Query();

		$sql = "SELECT payment_method_id FROM ".SITE_DB.".user_gateway_stripe_subscription_payment_method WHERE user_id = $user_id AND subscription_id = $subscription_id";
		// debug(["sql", $sql]);
		if($query->sql($sql)) {
			$gateway_payment_method_id = $query->result(0, "payment_method_id");
			$payment_method_id = $this->getStripePaymentMethodId();
			return [
				"payment_method_id" => $payment_method_id,
				"gateway_payment_method_id" => $gateway_payment_method_id,
			];
		}
		return false;
	}


	// Get Payment intent from Stripe API
	function getPaymentIntent($payment_intent_id) {
		// debug(["getPaymentIntent - stripe", $payment_intent_id]);

		try {

			$details = \Stripe\PaymentIntent::retrieve(
				$payment_intent_id
			);

			// debug([$details]);
			if($details) {

				return $details;

			}

		}
		// Card error
		catch(\Stripe\Exception\CardException $exception) {

			$this->exceptionHandler("PaymentIntent::retrieve", $exception);
			return $this->exceptionResponder($exception);
		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Exception\RateLimitException $exception) {

			$this->exceptionHandler("PaymentIntent::retrieve", $exception);
			return $this->exceptionResponder($exception);
		}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Exception\InvalidRequestException $exception) {

			$this->exceptionHandler("PaymentIntent::retrieve", $exception);
			return $this->exceptionResponder($exception);
		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Exception\AuthenticationException $exception) {

			$this->exceptionHandler("PaymentIntent::retrieve", $exception);
			return $this->exceptionResponder($exception);
		}
		// Network communication with Stripe failed
		catch (\Stripe\Exception\ApiConnectionException $exception) {

			$this->exceptionHandler("PaymentIntent::retrieve", $exception);
			return $this->exceptionResponder($exception);
		}
		// Generic error
		catch (\Stripe\Exception\ApiErrorException $exception) {

			$this->exceptionHandler("PaymentIntent::retrieve", $exception);
			return $this->exceptionResponder($exception);
		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("PaymentIntent::retrieve", $exception);
			return $this->exceptionResponder($exception);
		}

		return false;
	}

	// check if we have a payment intent for order
	function canBeCaptured($_options = false) {

		$user_id = false;
		$order_id = false;
		$amount = false;

		$check_validity = true;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "order_id"          : $order_id            = $_value; break;
					case "user_id"           : $user_id             = $_value; break;
					case "amount"            : $amount              = $_value; break;

					case "check_validity"    : $check_validity      = $_value; break;
				}
			}
		}

		if($user_id && $order_id) {
			$query = new Query();

			$sql = "SELECT payment_intent_id FROM ".SITE_DB.".user_gateway_stripe_order_intent WHERE user_id = $user_id AND order_id = $order_id";
			if($query->sql($sql)) {

				$payment_intent_id = $query->result(0, "payment_intent_id");

				if($check_validity) {

					$payment_intent = $this->getPaymentIntent($payment_intent_id);
					if($payment_intent && $payment_intent->status === "requires_capture") {
						if($amount <= $payment_intent->amount_capturable/100) {
							return [
								"payment_intent_id" => $payment_intent_id,
								"last4" => $payment_intent->charges->data[0]->payment_method_details->card->last4
							];
						}
					}
					// Intent not available for capturing – delete it
					else if($payment_intent) {

						$sql = "DELETE FROM ".SITE_DB.".user_gateway_stripe_order_intent WHERE payment_intent_id = '$payment_intent_id'";
						$query->sql($sql);

					}
				}
				else {
					return [
						"payment_intent_id" => $payment_intent_id,
					];
				}

			}

		}

		return false;
	}


	// Process payment method for cart
	function processCardForCart($cart, $card_number, $card_exp_month, $card_exp_year, $card_cvc) {
		// debug(["processCartAndPayment stripe"]);

		if($cart && $cart["user_id"]) {

			// does customer already exist in Stripe account
			$customer_id = $this->getCustomerId($cart["user_id"]);

			// create customer, if it doesn't exist
			if(!$customer_id) {
				$customer_id = $this->createCustomer($cart["user_id"]);
			}

			// customer created or found
			if($customer_id) {

				// create payment method for card
				$payment_method = $this->addPaymentMethod($cart["user_id"], $card_number, $card_exp_month, $card_exp_year, $card_cvc);

				// if payment method was created (or found)
				if($payment_method && $payment_method["status"] == "success") {

					$payment_method["cart"] = $cart;
					return $payment_method;

				}
				else {

					return ["status" => "CARD_ERROR", "message" => $payment_method["message"], "code" => $payment_method["code"]];

				}
			}
			else {

				return ["status" => "STRIPE_ERROR", "message" => "There was an error processing your payment"];

			}
		}

		return ["status" => "CART_NOT_FOUND", "message" => "Cart not found"];
	}

	// Request payment intent for cart
	function requestPaymentIntentForCart($cart, $payment_method_id, $return_url) {

		$SC = new Shop();
		$amount = $SC->getTotalCartPrice($cart["id"]);
		$currency = $cart["currency"];
		$customer_id = $this->getCustomerId($cart["user_id"]);

		try {

			$payment_intent = \Stripe\PaymentIntent::create([
				"amount" => $amount["price"]*100,
				"currency" => $currency,

				"confirm" => true,

				"description" => "think.dk–".$cart["cart_reference"],
				"statement_descriptor" => "think.dk–".$cart["cart_reference"],
				"statement_descriptor_suffix" => $cart["cart_reference"],

				"customer" => "$customer_id",

				"payment_method" => $payment_method_id,
				"setup_future_usage" => "off_session",
				"capture_method" => "manual",

				// User should be returned to this url upon SCA confirmation
				"return_url" => $return_url,

				"metadata" => [
					"cart_reference" => $cart["cart_reference"]
				],

				// Does this make any sense?
				"mandate_data" => [
					"customer_acceptance" => [
						"type" => "online",
						"online" => [
							"ip_address" => session()->value("ip"),
							"user_agent" => session()->value("useragent"),
						],
					],
				],
				
			]);

			if($payment_intent) {

				if($payment_intent->status === "succeeded" || $payment_intent->status === "requires_capture") {
					return [
						"status" => "PAYMENT_READY", 
						"payment_intent_id" => $payment_intent->id, 
						"client_secret" => $payment_intent->client_secret
					];
				}
				else if($payment_intent->status === "requires_action") {
					return [
						"status" => "ACTION_REQUIRED", 
						"action" => $payment_intent->next_action->redirect_to_url->url
					];
				}
				else if($payment_intent->status === "requires_source_action") {
					return [
						"status" => "ACTION_REQUIRED", 
						"action" => $payment_intent->next_source_action->authorize_with_url->url
					];
				}

			}

		}
		// Card error
		catch(\Stripe\Exception\CardException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Exception\RateLimitException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Exception\InvalidRequestException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Exception\AuthenticationException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Network communication with Stripe failed
		catch (\Stripe\Exception\ApiConnectionException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Generic error
		catch (\Stripe\Exception\ApiErrorException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}

		return false;

	}



	// Process payment method for order
	function processCardForOrder($order, $card_number, $card_exp_month, $card_exp_year, $card_cvc) {
		// debug(["processCartAndPayment stripe"]);

		if($order && $order["user_id"]) {

			// does customer already exist in Stripe account
			$customer_id = $this->getCustomerId($order["user_id"]);

			// create customer, if it doesn't exist
			if(!$customer_id) {
				$customer_id = $this->createCustomer($order["user_id"]);
			}

			// customer created or found
			if($customer_id) {

				// create payment method for card
				$payment_method = $this->addPaymentMethod($order["user_id"], $card_number, $card_exp_month, $card_exp_year, $card_cvc);

				// if payment method was created (or found)
				if($payment_method && $payment_method["status"] == "success") {

					$payment_method["order"] = $order;
					return $payment_method;

				}
				else {

					return ["status" => "CARD_ERROR", "message" => $payment_method["message"], "code" => $payment_method["code"]];

				}
			}
			else {

				return ["status" => "STRIPE_ERROR", "message" => "There was an error processing your payment"];

			}
		}

		return ["status" => "ORDER_NOT_FOUND", "message" => "Order not found"];
	}

	// Request payment intent for order
	// Charges now – allows for future charging
	function requestPaymentIntentForOrder($order, $gateway_payment_method_id, $return_url) {

		include_once("classes/shop/supershop.class.php");
		$SC = new SuperShop();

		// Get intent for full order price – to be used for re-occuring subscriptions
		$amount = $SC->getTotalOrderPrice($order["id"]);
		$currency = $order["currency"];
		$customer_id = $this->getCustomerId($order["user_id"]);

		try {

			$payment_intent = \Stripe\PaymentIntent::create([
				"amount" => $amount["price"]*100,
				"currency" => $currency,

				"confirm" => true,

				"description" => "think.dk–".$order["order_no"],
				"statement_descriptor" => cutString("think.dk–".$order["order_no"], 22),
				"statement_descriptor_suffix" => cutString($order["order_no"], 22),

				"customer" => "$customer_id",

				"payment_method" => "$gateway_payment_method_id",
				"setup_future_usage" => "off_session",

				// User should be returned to this url upon SCA confirmation
				"return_url" => $return_url,

				"metadata" => [
					"order_no" => $order["order_no"]
				],

				// Does this make any sense?
				"mandate_data" => [
					"customer_acceptance" => [
						"type" => "online",
						"online" => [
							"ip_address" => session()->value("ip"),
							"user_agent" => session()->value("useragent"),
						],
					],
				],
				
			]);

			// debug(["payment_intent2", $payment_intent]);
			if($payment_intent) {

				if($payment_intent->status === "succeeded") {

					return [
						"status" => "PAYMENT_CAPTURED",
						"payment_intent_id" => $payment_intent->id, 
					];

				}
				else if($payment_intent->status === "requires_action") {
					return [
						"status" => "ACTION_REQUIRED", 
						"payment_intent_id" => $payment_intent->id, 
						"action" => $payment_intent->next_action->redirect_to_url->url
					];
				}
				else if($payment_intent->status === "requires_source_action") {
					return [
						"status" => "ACTION_REQUIRED", 
						"payment_intent_id" => $payment_intent->id, 
						"action" => $payment_intent->next_source_action->authorize_with_url->url
					];
				}

			}

		}
		// Card error
		catch(\Stripe\Exception\CardException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Exception\RateLimitException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Exception\InvalidRequestException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Exception\AuthenticationException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Network communication with Stripe failed
		catch (\Stripe\Exception\ApiConnectionException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Generic error
		catch (\Stripe\Exception\ApiErrorException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}

		return false;

	}



	// Process payment method for orders
	function processCardForOrders($orders, $card_number, $card_exp_month, $card_exp_year, $card_cvc) {
		// debug(["processCartAndPayment stripe"]);

		if($orders && $orders[0]["user_id"]) {

			// does customer already exist in Stripe account
			$customer_id = $this->getCustomerId($orders[0]["user_id"]);

			// create customer, if it doesn't exist
			if(!$customer_id) {
				$customer_id = $this->createCustomer($orders[0]["user_id"]);
			}

			// customer created or found
			if($customer_id) {

				// create payment method for card
				$payment_method = $this->addPaymentMethod($orders[0]["user_id"], $card_number, $card_exp_month, $card_exp_year, $card_cvc);

				// if payment method was created (or found)
				if($payment_method && $payment_method["status"] == "success") {

					$payment_method["orders"] = $orders;
					return $payment_method;

				}
				else {

					return ["status" => "CARD_ERROR", "message" => $payment_method["message"], "code" => $payment_method["code"]];

				}
			}
			else {

				return ["status" => "STRIPE_ERROR", "message" => "There was an error processing your payment"];

			}
		}

		return ["status" => "ORDER_NOT_FOUND", "message" => "Orders not found"];
	}

	// Request payment intent for orders
	// Charges now
	function requestPaymentIntentForOrders($orders, $gateway_payment_method_id, $return_url) {

		include_once("classes/shop/supershop.class.php");
		$SC = new SuperShop();
		// Get intent for full order price – to be used for re-occuring subscriptions

		$amount = 0;
		foreach($orders as $order) {

			$remaining_order_price = $SC->getRemainingOrderPrice($order["id"]);
			$order_no_list[] = $order["order_no"];
			$amount += $remaining_order_price["price"];

		}


		$currency = $orders[0]["currency"];
		$customer_id = $this->getCustomerId($orders[0]["user_id"]);
		$payment_method_id = $this->getStripePaymentMethodId();

		try {

			$payment_intent = \Stripe\PaymentIntent::create([
				"amount" => $amount*100,
				"currency" => $currency,

				"confirm" => true,

				"description" => "think.dk-".implode(",", $order_no_list),
				"statement_descriptor" => cutString("think.dk–".implode(",", $order_no_list), 22),
				"statement_descriptor_suffix" => cutString(implode(",", $order_no_list), 22),

				"customer" => "$customer_id",

				"payment_method" => $gateway_payment_method_id,

				// User should be returned to this url upon SCA confirmation
				"return_url" => $return_url,

				"metadata" => [
					"order_nos" => implode(",", $order_no_list)
				],

				// Does this make any sense?
				"mandate_data" => [
					"customer_acceptance" => [
						"type" => "online",
						"online" => [
							"ip_address" => session()->value("ip"),
							"user_agent" => session()->value("useragent"),
						],
					],
				],
				
			]);

			if($payment_intent) {

				if($payment_intent->status === "succeeded") {

					return [
						"status" => "PAYMENT_CAPTURED",
						"payment_intent_id" => $payment_intent->id, 
					];


				}
				else if($payment_intent->status === "requires_action") {
					return [
						"status" => "ACTION_REQUIRED", 
						"payment_intent_id" => $payment_intent->id, 
						"action" => $payment_intent->next_action->redirect_to_url->url,
					];
				}
				else if($payment_intent->status === "requires_source_action") {
					return [
						"status" => "ACTION_REQUIRED", 
						"payment_intent_id" => $payment_intent->id, 
						"action" => $payment_intent->next_source_action->authorize_with_url->url
					];
				}

			}

		}
		// Card error
		catch(\Stripe\Exception\CardException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Exception\RateLimitException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Exception\InvalidRequestException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Exception\AuthenticationException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Network communication with Stripe failed
		catch (\Stripe\Exception\ApiConnectionException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Generic error
		catch (\Stripe\Exception\ApiErrorException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}

		return false;

	}




	// Identify payment intent relation based on its metadata
	function identifyPaymentIntent($payment_intent_id) {

		$payment_intent = $this->getPaymentIntent($payment_intent_id);
		// debug(["iding payment_intent", $payment_intent]);

		$cart_reference = false;
		$order_no = false;
		$order_nos = false;

		if($payment_intent) {

			// Check metadata for cart or order reference
			if($payment_intent->metadata) {
				if($payment_intent->metadata["cart_reference"]) {
					$cart_reference = $payment_intent->metadata["cart_reference"];
				}
				else if($payment_intent->metadata["order_no"]) {
					$order_no = $payment_intent->metadata["order_no"];
				}
				else if($payment_intent->metadata["order_nos"]) {
					$order_nos = $payment_intent->metadata["order_nos"];
				}
			}

			if($cart_reference || $order_no || $order_nos) {

				if($payment_intent->status === "requires_capture" || $payment_intent->status === "succeeded") {

					return [
						"status" => "success",
						"payment_status" => $payment_intent->status,
						"payment_intent_id" => $payment_intent->id,
						"payment_intent" => $payment_intent,
						"gateway" => "stripe",
						"cart_reference" => $cart_reference,
						"order_no" => $order_no,
						"order_nos" => $order_nos,
					];

				}
				// Payment intent not valid for capture
				else if($payment_intent && $payment_intent->status) {

					return [
						"status" => "error", 
						"payment_status" => $payment_intent->status,
						"payment_intent_id" => $payment_intent->id,
						"payment_intent" => $payment_intent,
						"code" => $payment_intent->last_payment_error->code,
						"message" => $payment_intent->last_payment_error->message,
						"gateway" => "stripe",
						"cart_reference" => $cart_reference,
						"order_no" => $order_no,
						"order_nos" => $order_nos,
					];

				}

			}
			else {
				
				// Add log entry
				global $page;
				$page->addLog("identifyPaymentIntent failed: No cart or order identifier, payment_intent_id: $payment_intent_id", "stripe");

				// Send mail to admin
				mailer()->send([
					"subject" => SITE_URL." - identifyPaymentIntent failed (".$payment_intent_id.")", 
					"message" => "identifyPaymentIntent failed: No cart or order identifier, payment_intent_id: $payment_intent_id",
					"template" => "system"
				]);

			}

		}

		return false;
	}

	// Register a payment intent on related order / subscription
	// If payment has not been captured, the intent is registeret on order for later capturing
	// If payment has been captured, the intent is registeret on related subscriptions for later capturing
	function registerPaymentIntent($payment_intent_id, $order) {
		// debug(["registerPaymentIntent", $payment_intent_id, $order]);


		try {

			// update metadata on the payment intent (remove cart_reference, add order_no)
			$payment_intent = \Stripe\PaymentIntent::update(
				$payment_intent_id,
				[

					"description" => "think.dk–".$order["order_no"],
					"statement_descriptor" => "think.dk–".$order["order_no"],
					"statement_descriptor_suffix" => $order["order_no"],

					"metadata" => [
						"cart_reference" => "",
						"order_no" => $order["order_no"]
					]
				]
			);

			// debug([$payment_intent]);

			if($payment_intent) {

				// Get payment method from intent
				$payment_method_id = $payment_intent->payment_method;

				$query = new Query();

				// Save intent for order and any subscriptions within order
				$query->checkDBExistence(SITE_DB.".user_gateway_stripe_order_intent");

				if($payment_intent->status !== "succeeded") {

					// Save intent for order
					$sql = "SELECT id FROM ".SITE_DB.".user_gateway_stripe_order_intent WHERE order_id = ".$order["id"];
					if($query->sql($sql)) {
						$sql = "UPDATE ".SITE_DB.".user_gateway_stripe_order_intent SET payment_intent_id = '$payment_intent_id' WHERE order_id=".$order["id"];
						$query->sql($sql);
					}
					else {
						$sql = "INSERT INTO ".SITE_DB.".user_gateway_stripe_order_intent SET user_id=".$order["user_id"].", order_id=".$order["id"].", payment_intent_id = '$payment_intent_id'";
						$query->sql($sql);
					}

				}

				// Save payment_method for subscriptions
				$query->checkDBExistence(SITE_DB.".user_gateway_stripe_subscription_payment_method");
				$sql = "SELECT id FROM ".SITE_DB.".user_item_subscriptions WHERE order_id = ".$order["id"];
				if($query->sql($sql)) {
					$subscriptions = $query->results();
					foreach($subscriptions as $subscription) {

						$sql = "SELECT id FROM ".SITE_DB.".user_gateway_stripe_subscription_payment_method WHERE subscription_id = ".$subscription["id"];
						if($query->sql($sql)) {
							$sql = "UPDATE ".SITE_DB.".user_gateway_stripe_subscription_payment_method SET payment_method_id = '$payment_method_id' WHERE subscription_id=".$subscription["id"];
							$query->sql($sql);
						}
						else {
							$sql = "INSERT INTO ".SITE_DB.".user_gateway_stripe_subscription_payment_method SET user_id=".$order["user_id"].", subscription_id=".$subscription["id"].", payment_method_id = '$payment_method_id'";
							$query->sql($sql);
						}

					}
				}

				return ["status" => "success"];

			}

		}
		// Card error
		catch(\Stripe\Exception\CardException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Exception\RateLimitException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Exception\InvalidRequestException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Exception\AuthenticationException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Network communication with Stripe failed
		catch (\Stripe\Exception\ApiConnectionException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Generic error
		catch (\Stripe\Exception\ApiErrorException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}

		return false;

	}

	// Update payment intent on relevant subscriptions (used when paying existing order)
	function updatePaymentIntent($payment_intent_id, $order) {
		// debug(["updatePaymentIntent", $payment_intent_id, $order]);

		try {

			// update metadata on the payment intent (remove cart_reference, add order_no)
			$payment_intent = \Stripe\PaymentIntent::retrieve(
				$payment_intent_id
			);

			// debug([$payment_intent]);

			if($payment_intent) {

				// Get payment method from intent
				$payment_method_id = $payment_intent->payment_method;

				$query = new Query();


				// Save payment_method for subscriptions
				$query->checkDBExistence(SITE_DB.".user_gateway_stripe_subscription_payment_method");
				$sql = "SELECT id FROM ".SITE_DB.".user_item_subscriptions WHERE order_id = ".$order["id"];
				if($query->sql($sql)) {
					$subscriptions = $query->results();
					foreach($subscriptions as $subscription) {

						$sql = "SELECT id FROM ".SITE_DB.".user_gateway_stripe_subscription_payment_method WHERE subscription_id = ".$subscription["id"];
						if($query->sql($sql)) {
							$sql = "UPDATE ".SITE_DB.".user_gateway_stripe_subscription_payment_method SET payment_method_id = '$payment_method_id' WHERE subscription_id=".$subscription["id"];
							$query->sql($sql);
						}
						else {
							$sql = "INSERT INTO ".SITE_DB.".user_gateway_stripe_subscription_payment_method SET user_id=".$order["user_id"].", subscription_id=".$subscription["id"].", payment_method_id = '$payment_method_id'";
							$query->sql($sql);
						}

					}
				}

				return ["status" => "success", "payment_intent" => $payment_intent];

			}

		}
		// Card error
		catch(\Stripe\Exception\CardException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Exception\RateLimitException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Exception\InvalidRequestException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Exception\AuthenticationException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Network communication with Stripe failed
		catch (\Stripe\Exception\ApiConnectionException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Generic error
		catch (\Stripe\Exception\ApiErrorException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}

		return false;

	}


	// Register captured payment
	function registerPayment($order, $payment_intent) {

		global $page;

		include_once("classes/shop/supershop.class.php");
		$SC = new SuperShop();

		$payment_method_id = $this->getStripePaymentMethodId();
		$remaining_order_price = $SC->getRemainingOrderPrice($order["id"]);

		// Add variables for addPayment
		$_POST["payment_amount"] = $payment_intent->amount_received/100;
		$_POST["payment_method_id"] = $payment_method_id;
		$_POST["order_id"] = $order["id"];
		$_POST["transaction_id"] = $payment_intent->charges->data[0]["id"];
		$payment_id = $SC->registerPayment(array("registerPayment"));
		if($payment_id) {

			$page->addLog("Payment added to Janitor: order_id:".$order["id"].", transaction_id:".$payment_intent->charges->data[0]["id"].", amount:".($payment_intent->amount_received/100), "stripe");

			return [
				"status" => "REGISTERED", 
				"payment_id" => $payment_id,
			];

		}
		else {

			$page->addLog("Failed adding payment to Janitor (adding payment): order_id:".$order["id"].", payment_intent_id:".$payment_intent->id.", amount:".($payment_intent->amount_received/100), "stripe");

			// Send mail to admin
			mailer()->send([
				"subject" => SITE_URL." - Error adding Stripe payment", 
				"message" => "Failed adding payment from Stripe capture. This needs to be handled manually.\n\npayment_intent_id: ".$payment_intent->id."\norder_id: ".$order["id"]."\norder_no: ".$order["order_no"],
				"template" => "system"
			]);

			return [
				"status" => "REGISTRATION_FAILED",
			];
		}
		unset($_POST);

	}

	// Register captured payment
	function registerPayments($orders, $payment_intent) {

		global $page;

		include_once("classes/shop/supershop.class.php");
		$SC = new SuperShop();

		$payment_method_id = $this->getStripePaymentMethodId();
		$payment_ids = [];
		$order_no_list = [];
		$accounting = $payment_intent->amount_received/100;

		foreach($orders as $order) {

			$order_no_list[] = $order["order_no"];
			$remaining_order_price = $SC->getRemainingOrderPrice($order["id"]);

			// Add variables for addPayment
			$_POST["payment_amount"] = $remaining_order_price["price"];
			$_POST["payment_method_id"] = $payment_method_id;
			$_POST["order_id"] = $order["id"];
			$_POST["transaction_id"] = $payment_intent->charges->data[0]["id"]." (".($payment_intent->amount_received/100).")";
			$payment_id = $SC->registerPayment(array("registerPayment"));
			if($payment_id) {
				$payment_ids[] = $payment_id;

				$accounting -= $remaining_order_price["price"];
				$page->addLog("Payment added to Janitor: order_id:".$order["id"].", transaction_id:".$payment_intent->charges->data[0]["id"].", amount:".$remaining_order_price["price"], "stripe");

			}
			else {

				$page->addLog("Failed adding payment to Janitor (adding payment): order_id:".$order["id"].", payment_intent_id:".$payment_intent->id.", amount:".($payment_intent->amount_received/100), "stripe");
				// Notify admin

				// Send mail to admin
				mailer()->send([
					"subject" => SITE_URL." - Error adding Stripe payment", 
					"message" => "Failed adding payments from Stripe capture. This needs to be handled manually.\n\npayment_intent_id: ".$payment_intent->id."\norder_id: ".$order["id"]."\norder_no: ".$order["order_no"],
					"template" => "system"
				]);

			}

			unset($_POST);

		}

		// Accounting adds up
		if($accounting === floatval(0)) {
			return [
				"status" => "REGISTERED", 
				"payment_ids" => implode(",", $payment_ids),
			];
		}


		// Send mail to admin
		mailer()->send([
			"subject" => SITE_URL." - registerPayments accounting error", 
			"message" => "Failed adding payments from Stripe capture. This needs to be handled manually.\n\npayment_intent_id: ".$payment_intent->id."\norder_nos: ".implode(",", $order_no_list),
			"template" => "system"
		]);

		return [
			"status" => "REGISTRATION ERROR",
			"payment_id" => implode(",", $payment_ids),
		];

	}

	// Capture an existing payment intent
	function capturePayment($payment_intent_id, $payment_amount) {

		global $page;
		$query = new Query();

		try {

			// update metadata on the payment intent (remove cart_reference, add order_no)
			$payment_intent = $this->getPaymentIntent($payment_intent_id);

			if($payment_intent && $payment_intent->status === "requires_capture" && $payment_intent->metadata->order_no) {

				include_once("classes/shop/supershop.class.php");
				$SC = new SuperShop();

				$order = $SC->getOrders(["order_no" => $payment_intent->metadata->order_no]);
				if($order) {

					$payment_intent = $payment_intent->capture(
						[
							"amount_to_capture" => round($payment_amount*100),
						]
					);

					if($payment_intent && $payment_intent->status === "succeeded") {

						// Make sure intent is deleted
						$sql = "DELETE FROM ".SITE_DB.".user_gateway_stripe_order_intent WHERE payment_intent_id = '$payment_intent_id'";
						$query->sql($sql);


						$page->addLog("Captured payment intent: order_id:".$order["id"].", payment_intent_id:".$payment_intent_id.", amount:".$payment_amount, "stripe");

						$registration = $this->registerPayment($order, $payment_intent);
						if($registration && $registration["status"] === "REGISTERED") {

							return [
								"status" => "success",
								"order" => $SC->getOrders(["order_no" => $order["order_no"]])

							];

						}

					}

				}

			}
			// If payment intent exists, but is not capturable
			else if($payment_intent_id) {

				// Make sure intent is deleted
				$sql = "DELETE FROM ".SITE_DB.".user_gateway_stripe_order_intent WHERE payment_intent_id = '$payment_intent_id'";
				$query->sql($sql);


				return [
					"status" => "NOT_CAPTURABLE",
				];
			}

			$page->addLog("Failed capturing payment intent: payment_intent_id:".$payment_intent_id.", amount:".$payment_amount, "stripe");

		}
		// Card error
		catch(\Stripe\Exception\CardException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Exception\RateLimitException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Exception\InvalidRequestException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Exception\AuthenticationException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Network communication with Stripe failed
		catch (\Stripe\Exception\ApiConnectionException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Generic error
		catch (\Stripe\Exception\ApiErrorException $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("PaymentIntent::update", $exception);
			return $this->exceptionResponder($exception);
		}

		return false;
		
	}


	// Capture payment without an available intent
	function capturePaymentWithoutIntent($order_id, $gateway_payment_method_id) {

		include_once("classes/shop/supershop.class.php");
		$SC = new SuperShop();

		$order = $SC->getOrders(["order_id" => $order_id]);


		// Get intent for full order price – to be used for re-occuring subscriptions
		$amount = $SC->getTotalOrderPrice($order["id"]);
		$currency = $order["currency"];
		$customer_id = $this->getCustomerId($order["user_id"]);

		try {

			$payment_intent = \Stripe\PaymentIntent::create([
				"amount" => $amount["price"]*100,
				"currency" => $currency,

				"confirm" => true,

				"description" => "think.dk–".$order["order_no"],
				"statement_descriptor" => cutString("think.dk–".$order["order_no"], 22),
				"statement_descriptor_suffix" => cutString($order["order_no"], 22),

				"customer" => "$customer_id",

				"payment_method" => "$gateway_payment_method_id",
				"setup_future_usage" => "off_session",

				// User should be returned to this url upon SCA confirmation
				// "return_url" => $return_url,

				"metadata" => [
					"order_no" => $order["order_no"]
				],

				// Does this make any sense?
				"mandate_data" => [
					"customer_acceptance" => [
						"type" => "online",
						"online" => [
							"ip_address" => session()->value("ip"),
							"user_agent" => session()->value("useragent"),
						],
					],
				],
			
			]);

			// debug(["payment_intent2", $payment_intent]);
			if($payment_intent) {

				if($payment_intent->status === "succeeded") {

					// Register payment
					$registration = $this->registerPayment($order, $payment_intent);
					if($registration && $registration["status"] === "REGISTERED") {

						$order = $SC->getOrders(["order_id" => $order_id]);

						return [
							"status" => "success",
							"order" => $order, 
						];

					}

				}

			}

			// Could not be captured
			return [
				"status" => "NOT_CAPTURABLE"
			];

		}
		// Card error
		catch(\Stripe\Exception\CardException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Too many requests made to the API too quickly
		catch (\Stripe\Exception\RateLimitException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Invalid parameters were supplied to Stripe's API
		catch (\Stripe\Exception\InvalidRequestException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Authentication with Stripe's API failed
		catch (\Stripe\Exception\AuthenticationException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Network communication with Stripe failed
		catch (\Stripe\Exception\ApiConnectionException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Generic error
		catch (\Stripe\Exception\ApiErrorException $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}
		// Something else happened, completely unrelated to Stripe
		catch (Exception $exception) {

			$this->exceptionHandler("PaymentIntent::create", $exception);
			return $this->exceptionResponder($exception);

		}

		return false;

	}


	// Create customer in Stripe account
	function createCustomer($user_id) {

		global $page;

		include_once("classes/users/superuser.class.php");
		$UC = new SuperUser();

		include_once("classes/users/supermember.class.php");
		$MC = new SuperMember();

		// $UC = new User();
		$user = $UC->getUsers(["user_id" => $user_id]);
		$email = $UC->getUsernames(["user_id" => $user_id, "type" => "email"]);
		$membership = $MC->getMembers(["user_id" => $user_id]);

		if($user && $email) {


			// API communication
			try {

				$create_parameters = array(
					'email'       => $email["username"],
					'description' => $user["nickname"] . ($membership ? ", Member ". $membership["id"] : ""),
				);

				$customer = \Stripe\Customer::create($create_parameters);

				if($customer) {

					$customer_id = $customer->id;
					$this->saveCustomerId($user_id, $customer_id);

					$page->addLog("Customer created: user_id:".$user_id.", email:".$customer->email.", customer_id:".$customer->id, "stripe");

					return $customer_id;
				}

			}
			// Card error
			catch(\Stripe\Exception\CardException $exception) {

				$this->exceptionHandler("PaymentIntent::update", $exception);
				return $this->exceptionResponder($exception);
			}
			// Too many requests made to the API too quickly
			catch (\Stripe\Exception\RateLimitException $exception) {

				$this->exceptionHandler("PaymentIntent::update", $exception);
				return $this->exceptionResponder($exception);
			}
			// Invalid parameters were supplied to Stripe's API
			catch (\Stripe\Exception\InvalidRequestException $exception) {

				$this->exceptionHandler("PaymentIntent::update", $exception);
				return $this->exceptionResponder($exception);
			}
			// Authentication with Stripe's API failed
			catch (\Stripe\Exception\AuthenticationException $exception) {

				$this->exceptionHandler("PaymentIntent::update", $exception);
				return $this->exceptionResponder($exception);
			}
			// Network communication with Stripe failed
			catch (\Stripe\Exception\ApiConnectionException $exception) {

				$this->exceptionHandler("PaymentIntent::update", $exception);
				return $this->exceptionResponder($exception);
			}
			// Generic error
			catch (\Stripe\Exception\ApiErrorException $exception) {

				$this->exceptionHandler("PaymentIntent::update", $exception);
				return $this->exceptionResponder($exception);
			}
			// Something else happened, completely unrelated to Stripe
			catch (Exception $exception) {

				$this->exceptionHandler("PaymentIntent::update", $exception);
				return $this->exceptionResponder($exception);
			}

		}

		return false;
	}


	// TODO – must be tested
	// Delete customer (when user account is being cancelled)
	function deleteCustomer($user_id) {

		$customer_id = $this->getCustomerId($user_id);
		if($customer_id) {

			// API communication
			try {

				$customer = \Stripe\Customer::retrieve($customer_id);
				$response = $customer->delete();

				if($response && $response->deleted && $response->id) {

					// delete customer info after 
					$this->deleteCustomerInfo($user_id, $customer_id);

					global $page;
					$page->addLog("Customer deleted: user_id:".$user_id.", customer_id:".$response->id, "stripe");
					return true;

				}

			}
			// Card error
			catch(\Stripe\Exception\CardException $exception) {

				$this->exceptionHandler("Customer::retrieve/delete", $exception);
				return false;

			}
			// Too many requests made to the API too quickly
			catch (\Stripe\Exception\RateLimitException $exception) {

				$this->exceptionHandler("Customer::retrieve/delete", $exception);
				return false;

			}
			// Invalid parameters were supplied to Stripe's API
			catch (\Stripe\Exception\InvalidRequestException $exception) {

				$this->exceptionHandler("Customer::retrieve/delete", $exception);
				return false;

			}
			// Authentication with Stripe's API failed
			catch (\Stripe\Exception\AuthenticationException $exception) {

				$this->exceptionHandler("Customer::retrieve/delete", $exception);
				return false;

			}
			// Network communication with Stripe failed
			catch (\Stripe\Exception\ApiConnectionException $exception) {

				$this->exceptionHandler("Customer::retrieve/delete", $exception);
				return false;

			}
			// Generic error
			catch (\Stripe\Exception\ApiErrorException $exception) {

				$this->exceptionHandler("Customer::retrieve/delete", $exception);
				return false;

			}
			// Something else happened, completely unrelated to Stripe
			catch (Exception $exception) {

				$this->exceptionHandler("Customer::retrieve/delete", $exception);
				return false;

			}

		}

		return false;

	}





	// get stripe customer id if it exists
	function getCustomerId($user_id) {
		
		$query = new Query();
		// $query->checkDBExistence(SITE_DB.".user_gateway_stripe");
		$query->checkDBExistence(SITE_DB.".user_gateway_stripe_customer");

		$sql = "SELECT * FROM ".SITE_DB.".user_gateway_stripe_customer WHERE user_id = $user_id";
		if($query->sql($sql)) {
			return $query->result(0, "customer_id");
		}

		return false;
	}

	// Save stripe customer id
	function saveCustomerId($user_id, $customer_id) {

		$query = new Query();
		$query->checkDBExistence(SITE_DB.".user_gateway_stripe_customer");

		$sql = "INSERT INTO ".SITE_DB.".user_gateway_stripe_customer SET user_id=$user_id, customer_id='$customer_id'";
		if($query->sql($sql)) {
			return true;
		}

		return false;
	}

	// TODO: must be tested
	// Delete stripe customer info (local DBs)
	function deleteCustomerInfo($user_id, $customer_id) {

		$query = new Query();
		$query->checkDBExistence(SITE_DB.".user_gateway_stripe_customer");

		$sql = "DELETE FROM ".SITE_DB.".user_gateway_stripe_customer WHERE user_id=$user_id AND customer_id='$customer_id'";
		if($query->sql($sql)) {

			// Delete order payment intents
			$sql = "DELETE FROM ".SITE_DB.".user_gateway_stripe_order_intent WHERE user_id=$user_id";
			$query->sql($sql);

			// Delete subscription payment intents
			$sql = "DELETE FROM ".SITE_DB.".user_gateway_stripe_subscription_payment_method WHERE user_id=$user_id";
			$query->sql($sql);

			// Delete stripe payment method from user
			$stripe_payment_method_id = $this->getStripePaymentMethodId();
			if($stripe_payment_method_id !== false) {
				$sql = "DELETE FROM ".SITE_DB.".user_payment_methods WHERE user_id=$user_id AND payment_method_id = $stripe_payment_method_id";
				$query->sql($sql);
			}

			return true;

		}

		return false;
	}

	// Get system payment method id for Stripe
	function getStripePaymentMethodId() {
		global $page;

		$payment_methods = $page->paymentMethods();
		$stripe_index = arrayKeyValue($payment_methods, "gateway", "stripe");
		if($stripe_index !== false) {
			return $payment_methods[$stripe_index]["id"];
		}

		return false;
	}



	// Respond with exception data
	function exceptionResponder($exception) {
		$error_body = $exception->getJsonBody();
		if($error_body && isset($error_body["error"])) {
			$error = $error_body["error"];
		}
		else {
			$error["type"] = "Unknown";
			$error["message"] = $exception->getMessage();
			$error["code"] = $exception->getCode();
		}

		return ["status" => "error", "message" => $error["message"], "code" => $error["code"]];
	}

	// Handle any stripe exception and notify Admin
	function exceptionHandler($action, $exception) {

		$error_body = $exception->getJsonBody();
		if($error_body && isset($error_body["error"])) {
			$error = $error_body["error"];
		}
		else {
			$error["type"] = "Unknown";
			$error["message"] = $exception->getMessage();
		}
		// debug([$action, "exception", $exception, "error_body", $error_body, "em", $exception->getMessage()]);
		$http_response = $exception->getHttpStatus();

		// Add log entry
		global $page;
		$page->addLog($action." failed: type:".$error["type"].", http-response:".$http_response.", message:".$error["message"].", code:".(isset($error["code"]) ? $error["code"] : "N/A").", param:".(isset($error["param"]) ? $error["param"] : "N/A"), "stripe");

		// Send mail to admin
		mailer()->send([
			"subject" => SITE_URL." - $action - Stripe exception (".$error["type"].")", 
			"message" => "Exception thrown when $action: \n" . print_r($error, true),
			"template" => "system"
		]);

	}


}

?>