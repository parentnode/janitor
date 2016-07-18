<?php
/**
* @package janitor.users
* This file contains simple user extensions
*/

/**
* User customization class
*/
class User extends UserCore {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


	}


}

?>