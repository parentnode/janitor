<?php
/**
* This file contains the site setup override functionality.
*/
include_once("classes/system/setup.core.class.php");

class Setup extends SetupCore {

	function __construct() {
		parent::__construct(get_class());
	}

}

?>
