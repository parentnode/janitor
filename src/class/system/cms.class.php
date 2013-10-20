<?php
/**
* This file contains the item custom backbone
* This class basically only exists to make it easy to add custom page functionality or overwrite behaviours.
*/

// include the output class for output method support
include_once("class/system/output.class.php");


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
		$output = new Output();

		// any actions
		if(isset($action)) {

			// SAVE ITEM
			// Requires exactly to parameters /save/#itemtype#
			if(count($action) == 2 && $action[0] == "save") {

				$output->screen($IC->saveItem());
				exit();
			}

			// UPDATE ITEM
			// Requires exactly two parameters /save/#item_id#
			else if(count($action) == 2 && $action[0] == "update") {

				$output->screen($IC->updateItem($action[1]));
				exit();
			}

			// DELETE ITEM
			// Requires exactly two parameters /delete/#item_id#
			else if(count($action) == 2 && $action[0] == "delete") {

				$output->screen($IC->deleteItem($action[1]));
				exit();
			}


			// STATUS
			//
			// ENABLE ITEM
			// Requires exactly two parameters /enable/#item_id#
			else if(count($action) == 2 && $action[0] == "enable") {

				$output->screen($IC->enableItem($action[1]));
				exit();
			}
			// DISABLE ITEM
			// Requires exactly two parameters /enable/#item_id#
			else if(count($action) == 2 && $action[0] == "disable") {

				$output->screen($IC->disableItem($action[1]));
				exit();
			}


			// TAGS
			//
			// DELETE TAG
			// Requires exactly 4 parameters /tags/delete/#item_id#/#tag_id#
			else if(count($action) == 4 && $action[0] == "tags" && $action[1] == "delete") {

				$output->screen($IC->deleteTag($action[2], $action[3]));
				exit();
			}
			// GET TAGS based on context
			// Requires just the tags parameter /tags/#context#
			else if(count($action) == 2 && $action && $action[0] == "tags") {

				$output->screen($IC->getTags(array("context" => $action[1])));
				exit();
			}
			// GET TAGS
			// Requires just the tags parameter /tags[/#context#/#value#]
			else if(count($action) == 1 && $action[0] == "tags") {

				$output->screen($IC->getTags());
				exit();
			}


			// PRICES
			//
			// DELETE PRICE
			// Requires exactly 4 parameters /prices/delete/#item_id#/#price_id#
			else if(count($action) == 4 && $action[0] == "prices" && $action[1] == "delete") {

				$output->screen($IC->deletePrice($action[2], $action[3]));
				exit();
			}


			// CUSTOM
			//
			// custom loopback to itemtype
			// TODO: consider alternative syntax'
			// alternative: /#itemtype#/#action#/#item_id#/#additional_parameters#

			// current alternative: /#itemtype#/#item_id#/#action#/#additional_parameters#
			// Requires minimum 3 parameters /#item_id#/#action#
			else if(count($action) > 2 && preg_match("/[a-z]+\/[0-9]+\/[a-zA-Z]+/", implode("/", $action))) {

				// get type object
				$typeObject = $IC->typeObject($action[0]);
				// check if custom function exists on typeObject
				if($typeObject && method_exists($typeObject, $action[2])) {

					$output->screen($typeObject->$action[2]($action));
					exit();
				}

				$output->screen();
				exit();
			}

			// INITIAL VERSION
			// alternative: /#item_id#/#action#/#additional_parameters#
			// Requires minimum 2 parameters /#item_id#/#action#
			// else if(count($action) > 1 && is_numeric($action[0])) {
			// 
			// 	// attempt to get Item
			// 	$item = $IC->getItem($action[0]);
			// 	if($item) {
			// 		$typeObject = $IC->typeObject($item["itemtype"]);
			// 		// check if custom function exists on typeObject
			// 		if($typeObject && method_exists($typeObject, $action[1])) {

			//			$output->screen($typeObject->$action[1]($action));
			// 			
			// 			if($typeObject->$action[1]($action)) {
			// 				print '{"cms_status":"success", "message":"something correct"}';
			// 				exit();
			// 			}
			// 		}
			// 	}

			// $output->screen();
			// exit();

			// 	$errors = message()->getMessages(array("type"=>"error"));
			// 	message()->resetMessages();
			// 	print '{"cms_status":"error", "message":'.($errors ? '"'.implode(", ", $errors).'"' : '"An error occured. Please reload."').'}';
			// 	exit();
			// }


		}

		
	}


}

?>
