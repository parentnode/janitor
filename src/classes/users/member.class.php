<?php
/**
* @package janitor.member
* This file contains simple member extensions
* Meant to allow local member additions/overrides
*/

/**
* Member customization class
*/
class Member extends MemberCore {


	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


	}


}

?>