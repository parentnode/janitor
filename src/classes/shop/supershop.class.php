<?php
/**
* @package janitor.shop
* Meant to allow local shop additions/overrides
*/

include_once("classes/shop/supershop.core.class.php");


class SuperShop extends SuperShopCore {

	/**
	*
	*/
	function __construct() {

		parent::__construct(get_class());

	}

}

?>