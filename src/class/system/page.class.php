<?php
/**
* This file contains the site custom backbone, the Page Class.
*/


/**
* Site custom backbone, the Page class - extends the PageCore base functionality
*/
class Page extends PageCore {

	/**
	* Get required page information
	*/
	function __construct() {
		parent::__construct();
	}

}

$page = new Page();

?>