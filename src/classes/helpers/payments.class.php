<?php
	


class PaymentGateway {


	// Payment gateway settings
	private $_settings;
	private $adapter;

	/**
	*
	*/
	function __construct() {

		// no adapter selected yet
		$this->adapter = false;

		// mailer connection info
		@include_once("config/connect_payment.php");

	}

	function payment_connection($_settings) {

		// set type to default, Stripe, if not defined in configs
		$_settings["type"] = isset($_settings["type"]) ? $_settings["type"] : "stripe";
		$this->_settings = $_settings;

	}

	function init_adapter() {

		if(!$this->adapter) {

			if(preg_match("/^stripe$/i", $this->_settings["type"])) {

				@include_once("classes/adapters/payments/stripe.class.php");
				$this->adapter = new JanitorStripe($this->_settings);

			}
			// Other options
			else {


			}

		}

	}



	function getPaymentMethod($user_id, $gateway_payment_method_id) {
		// debug(["getPaymentMethods", $$user_id, $gateway_payment_method_id]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {

			if($user_id && $gateway_payment_method_id) {

				return $this->adapter->getPaymentMethod($user_id, $gateway_payment_method_id);

			}

		}
	}

	function getPaymentMethods($user_id) {
		// debug(["getPaymentMethods", $$user_id]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {

			if($user_id) {
				return $this->adapter->getPaymentMethods($user_id);
			}

		}

	}

	function deletePaymentMethod($user_id, $gateway_payment_method_id) {
		// debug(["deletePaymentMethod", $user_id, $gateway_payment_method_id]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {

			if($user_id && $gateway_payment_method_id) {
				return $this->adapter->deletePaymentMethod($user_id, $gateway_payment_method_id);
			}
		}
		return false;
	}

	function getPaymentMethodForSubscription($user_id, $subscription_id) {
		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->getPaymentMethodForSubscription($user_id, $subscription_id);
		}
		return false;
	}

	function canBeCaptured($_options) {
		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->canBeCaptured($_options);
		}
		return false;
	}


	function capturePayment($payment_intent_id, $payment_amoutn) {
		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->capturePayment($payment_intent_id, $payment_amoutn);
		}
		return false;
	}



	function processCardForCart($cart, $card_number, $card_exp_month, $card_exp_year, $card_cvc) {
		// debug(["processCardForCart payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->processCardForCart($cart, $card_number, $card_exp_month, $card_exp_year, $card_cvc);
		}

	}

	function requestPaymentIntentForCart($cart, $payment_method, $return_url) {
		// debug(["requestPaymentIntentForCart payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->requestPaymentIntentForCart($cart, $payment_method, $return_url);
		}

	}



	function processCardForOrder($order, $card_number, $card_exp_month, $card_exp_year, $card_cvc) {
		// debug(["processCardForOrder payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->processCardForOrder($order, $card_number, $card_exp_month, $card_exp_year, $card_cvc);
		}

	}

	function requestPaymentIntentForOrder($order, $payment_method, $return_url) {
		// debug(["requestPaymentIntentForOrder payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->requestPaymentIntentForOrder($order, $payment_method, $return_url);
		}

	}



	function processCardForOrders($orders, $card_number, $card_exp_month, $card_exp_year, $card_cvc) {
		// debug(["processCardForOrders payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->processCardForOrders($orders, $card_number, $card_exp_month, $card_exp_year, $card_cvc);
		}

	}

	function requestPaymentIntentForOrders($orders, $payment_method, $return_url) {
		// debug(["requestPaymentIntentForOrders payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->requestPaymentIntentForOrders($orders, $payment_method, $return_url);
		}

	}


	function identifyPaymentIntent($payment_intent_id) {
		// debug(["identifyPaymentIntent payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->identifyPaymentIntent($payment_intent_id);
		}

	}

	function registerPaymentIntent($payment_intent_id, $order) {
		// debug(["registerPaymentIntent payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->registerPaymentIntent($payment_intent_id, $order);
		}

	}

	function updatePaymentIntent($payment_intent_id, $order) {
		// debug(["registerPaymentIntent payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->updatePaymentIntent($payment_intent_id, $order);
		}

	}

	function registerPayment($order, $payment_intent) {
		// debug(["registerPayment payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->registerPayment($order, $payment_intent);
		}

	}

	function registerPayments($orders, $payment_intent) {
		// debug(["registerPayment payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->registerPayments($orders, $payment_intent);
		}

	}


	function getGatewayUserId($user_id) {

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {

			return $this->adapter->getCustomerId($user_id);

		}

	}

	function deleteGatewayUserId($user_id) {

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {

			return $this->adapter->deleteCustomer($user_id);

		}

	}


}
