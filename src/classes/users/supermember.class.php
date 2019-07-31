<?php
/**
* @package janitor.member
* Meant to allow local member additions/overrides, with superuser privileges
*/

include_once("classes/users/supermember.core.class.php");


class SuperMember extends SuperMemberCore {

	/**
	*
	*/
	function __construct() {

		parent::__construct(get_class());

	}

}

?>