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

				@include_once("classes/adapters/stripe.class.php");
				$this->adapter = new JanitorStripe($this->_settings);

			}
			// Other options
			else {


			}

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
	
	function chargeUser($order) {

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {

			$customer_id = $this->getGatewayUserId($order["user_id"]);
			if($customer_id) {

				return $this->adapter->chargeCustomer($order, $customer_id);

			}

		}
	}

	function processCardAndPayOrder($order, $card_number, $card_exp_month, $card_exp_year, $card_cvc) {

		// only load payment adapter when needed
		$this->init_adapter();

		// Only attempt with valid adapter
		if($this->adapter) {
			return $this->adapter->processCardAndPayOrder($order, $card_number, $card_exp_month, $card_exp_year, $card_cvc);
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

}

$ppp = false;

function payments() {
	global $ppp;
	if(!$ppp) {
		$ppp = new PaymentGateway();

	}
	return $ppp;
}
