<?php
/**
* This file contains the site module override functionality.
*/
include_once("classes/system/module.core.class.php");

class Module extends ModuleCore {

	function __construct() {
		parent::__construct(get_class());
	}

}

?>
