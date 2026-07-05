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

		// payment gateway connection info
		@include_once("config/connect_payment.php");

	}

	function payment_connection($_settings) {

		// set type to default, Stripe, if not defined in configs
		$_settings["type"] = isset($_settings["type"]) ? $_settings["type"] : "stripe";
		$this->_settings = $_settings;

	}

	function init_adapter() {

		if(!$this->adapter) {

			if($this->_settings["type"]) {

				if(file_exists(LOCAL_PATH."/classes/adapters/payment/".$this->_settings["type"].".class.php") || file_exists(FRAMEWORK_PATH."/classes/adapters/payment/".$this->_settings["type"].".class.php")) {

					@include_once("classes/adapters/payment/".$this->_settings["type"].".class.php");
					$adapter_class = "Janitor".ucfirst($this->_settings["type"]);
					$this->adapter = new $adapter_class($this->_settings);

				}

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




	function capturePayment($payment_intent_id, $payment_amount) {
		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->capturePayment($payment_intent_id, $payment_amount);
		}
		return false;
	}

	function capturePaymentWithoutIntent($order_id, $gateway_payment_method_id) {
		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->capturePaymentWithoutIntent($order_id, $gateway_payment_method_id);
		}
		return false;
	}






	function createCartPaymentSession($cart, $success_url, $cancel_url, $_options = false) {
		// debug(["createCartPaymentSession payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->createCartPaymentSession($cart, $success_url, $cancel_url, $_options);
		}

	}

	function createOrderPaymentSession($order, $success_url, $cancel_url, $_options = false) {
		// debug(["createOrderPaymentSession payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->createOrderPaymentSession($order, $success_url, $cancel_url, $_options);
		}

	}

	function createOrdersPaymentSession($orders, $success_url, $cancel_url, $_options = false) {
		// debug(["createOrdersPaymentSession payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->createOrdersPaymentSession($orders, $success_url, $cancel_url, $_options);
		}

	}

	function processPaymentSession($action) {
		// debug(["processPaymentSession payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->processPaymentSession($action);
		}

	}

	function retrieveCheckoutSession($session_id) {
		// debug(["retrieveCheckoutSession payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->retrieveCheckoutSession($session_id);
		}

	}

	function retrieveSetupIntent($intent_id) {
		// debug(["retrieveSetupIntent payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->retrieveSetupIntent($intent_id);
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

	function requestPaymentIntentForOrder($order, $payment_method, $return_url) {
		// debug(["requestPaymentIntentForOrder payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->requestPaymentIntentForOrder($order, $payment_method, $return_url);
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

	function restoreMissingSubscriptionPaymentMethods($user_id, $payment_method_id) {
		// debug(["registerPaymentIntent payments"]);

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->restoreMissingSubscriptionPaymentMethods($user_id, $payment_method_id);
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
