<?php
/**
* @package janitor.subscription
* Meant to allow local subscription additions/overrides, with superuser privileges
*/

include_once("classes/shop/supersubscription.core.class.php");


class SuperSubscription extends SuperSubscriptionCore {

	/**
	*
	*/
	function __construct() {

		parent::__construct(get_class());

	}

}

?>