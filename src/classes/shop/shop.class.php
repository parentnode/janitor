<?php
/**
* @package janitor.shop
* Meant to allow local shop additions/overrides
*/


class Shop extends ShopCore {

	/**
	*
	*/
	function __construct() {

		parent::__construct(get_class());

	}

}

?>