<?php
/**
* @package janitor.itemtypes
* This file contains itemtype functionality
*
* This can be copied to local project to extend the Core Itemtype functionality
*/
class Itemtype extends ItemtypeCore {

	function __construct($type_class) {

		// required in this class
		// get instantiating class
		// itemtype is passed through extending constructs 
		// to ensure restrictions on itemtype data manipulation
		$this->itemtype = preg_replace("/^type/", "", strtolower($type_class));

		parent::__construct($this->itemtype);
	}


}