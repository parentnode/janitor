<?php
/**
* @package janitor.items
* Meant to allow local additions/overrides
*/

class TypeEvent extends TypeEventCore {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


	}

}

?>