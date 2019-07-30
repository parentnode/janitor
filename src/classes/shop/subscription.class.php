<?php
/**
* @package janitor.subscription
* This file contains simple subscription extensions
* Meant to allow local subscription additions/overrides
*/

/**
* Subscription customization class
*/
class Subscription extends SubscriptionCore {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


	}


}

?>