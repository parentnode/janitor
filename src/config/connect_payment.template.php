<?php
/**
* This file contains settings for payment gateway connection
*
* If a payment prefix is added, it will override the default prefix (SITE_UID) that is prepended to payment descriptions in Stripe's overview. 
*
* @package Config
*/

// define("PAYMENT_PREFIX", ###PREFIX###);

$this->payment_connection(
	array(
		// Different settings for different setups (will be added by setup script)
	)
);

?>
