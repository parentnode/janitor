<?php
/**
* This file contains the item custom backbone
* This class basically only exists to make it easy to add custom page functionality or overwrite behaviours.
*/


/**
* Item custom backbone - extends the ItemCore base functionality
*/
class CMS {

	/**
	* Get required page information
	*/
	function __construct() {

	}


	// TODO: Find better way to return values


	function processRequest() {
		global $page;

		$IC = new Item();
		$action = $page->actions();

		// any actions
		if(isset($action)) {

			// SAVE ITEM
			// Requires minimum to parameters /save/#itemtype#
			if(count($action) > 1 && $action[0] == "save") {

				$new_item = $IC->saveItem();
				if($new_item) {
					$new_item["cms_status"] = "success";
					print json_encode($new_item);
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}
				exit();
			}

			// UPDATE ITEM
			// Requires minimum two parameters /save/#item_id#
			else if(count($action) > 1 && $action[0] == "update") {

				if($IC->updateItem($action[1])) {
					$item = $IC->getCompleteItem($action[1]);
					$item["cms_status"] = "success";
					print json_encode($item);
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}
				exit();
			}

			// DELETE ITEM
			// Requires minimum two parameters /delete/#item_id#
			else if(count($action) == 2 && $action[0] == "delete") {

				if($IC->deleteItem($action[1])) {
					print '{"cms_status":"success", "message":"Item deleted"}';
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}
				exit();
			}

			// ENABLE ITEM
			// Requires minimum two parameters /enable/#item_id#
			else if(count($action) > 1 && $action[0] == "enable") {

				if($IC->enableItem($action[1])) {
					print '{"cms_status":"success", "message":"Item enabled"}';
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}
				exit();
			}

			// DISABLE ITEM
			// Requires minimum two parameters /enable/#item_id#
			else if(count($action) > 1 && $action[0] == "disable") {

				if($IC->disableItem($action[1])) {
					print '{"cms_status":"success", "message":"Item disabled"}';
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}
				exit();
			}

			// DELETE TAG
			// Requires minimum 4 parameters /tags/delete/#item_id#/#tag_id#
			else if(count($action) > 3 && $action[0] == "tags" && $action[1] == "delete") {

				if($IC->deleteTag($action[2], $action[3])) {
					print '{"cms_status":"success", "message":"Tag deleted"}';
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}
				exit();
			}

			// DELETE PRICE
			// Requires minimum 4 parameters /prices/delete/#item_id#/#price_id#
			else if(count($action) > 3 && $action[0] == "prices" && $action[1] == "delete") {

				if($IC->deleteTag($action[2], $action[3])) {
					print '{"cms_status":"success", "message":"Price deleted"}';
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}
				exit();
			}



		}

		
	}


}

?>
