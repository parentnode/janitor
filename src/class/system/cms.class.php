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

	function processRequest() {
		global $page;

		$IC = new Item();
		$action = $page->actions();

		// any actions
		if(isset($action)) {

			// SAVE ITEM
			// Requires minimum to parameters /save/#itemtype#
			if(count($action) > 1 && $action[0] == "save") {

				// TODO: Find better way to return values

//				$page->header(array("type" => "admin"));

				$new_item = $IC->saveItem();
				if($new_item) {
					$new_item["cms_status"] = "success";
					print json_encode($new_item);
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}

//				$page->footer(array("type" => "admin"));

				exit();
			}

			// UPDATE ITEM
			// Requires minimum to parameters /save/#item_id#
			else if(count($action) > 1 && $action[0] == "update") {

//				$page->header(array("type" => "admin"));

				if($IC->updateItem($action[1])) {
					$item = $IC->getCompleteItem($action[1]);
					$item["cms_status"] = "success";
					print json_encode($item);
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}

//				$page->footer(array("type" => "admin"));

				exit();
			}

			// DELETE ITEM
			// Requires minimum to parameters /delete/#item_id#
			else if(count($action) == 2 && $action[0] == "delete") {

//				$page->header(array("type" => "admin"));

				if($IC->deleteItem($action[1])) {
					print '{"cms_status":"success", "message":"Item deleted"}';
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}

//				$page->footer(array("type" => "admin"));

				exit();
			}
			else if(count($action) > 1 && $action[0] == "enable") {

//				$page->header(array("type" => "admin"));

				if($IC->enableItem($action[1])) {
					print '{"cms_status":"success", "message":"Item enabled"}';
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}

				// $IC->enableItem($action[1]);
				// print '{"cms_status":"success", "message":"Item deleted"}';
				// 
				// print json_encode(message()->getMessages());
				// 
				// message()->resetMessages();

//				$page->footer(array("type" => "admin"));

				exit();
			}
			else if(count($action) > 1 && $action[0] == "disable") {


//				$page->header(array("type" => "admin"));

				if($IC->disableItem($action[1])) {
					print '{"cms_status":"success", "message":"Item disabled"}';
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}

				// $IC->disableItem($action[1]);
				// print json_encode(message()->getMessages());
				// 
				// message()->resetMessages();

//				$page->footer(array("type" => "admin"));
				exit();
			}


			// else if(count($action) > 2 && $action[0] == "tags" && $action[1] == "add") {
			// 
			// 	$page->header(array("type" => "admin"));
			// 
			// 	if($IC->addTag($action[2], getPost("tag"))) {
			// 
			// 	}
			// 	else {
			// 
			// 	}
			// 	$page->footer(array("type" => "admin"));
			// 
			// 	exit();
			// }
			else if(count($action) > 3 && $action[0] == "tags" && $action[1] == "delete") {

//				$page->header(array("type" => "admin"));

				if($IC->deleteTag($action[2], $action[3])) {
					print '{"cms_status":"success", "message":"Tag deleted"}';
				}
				else {
					print '{"cms_status":"error", "message":"An error occured. Please reload."}';
				}

				// if($IC->deleteTag($action[2], $action[3])) {
				// 
				// }
				// else {
				// 
				// }
//				$page->footer(array("type" => "admin"));

				exit();
			}

		}

		
	}


}

?>
