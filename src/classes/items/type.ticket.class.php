<?php
/**
* @package janitor.items
* Meant to allow local additions/overrides
*/

class TypeTicket extends TypeTicketCore {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


	}

}

?>