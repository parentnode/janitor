<?php

// TODO: change cms controller to "items"?

$access_item["/"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


$action = $page->actions();
$IC = new Item();
$output = new Output();


// any actions
if($page->validateCsrfToken() && isset($action)) {


	// SAVE ITEM
	// Requires exactly to parameters /save/#itemtype#
	if(count($action) == 2 && $action[0] == "save") {

		$output->screen($IC->saveItem($action[1]));
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
	// changes status of item
	else if(count($action) == 3 && $action[0] == "status") {

		$output->screen($IC->status($action[1], $action[2]));
		exit();
	}




	// TAGS
	//
	// ADD TAG
	// Requires exactly 4 parameters /tags/add/#item_id#
	else if(count($action) == 3 && $action[0] == "tags" && $action[1] == "add") {

		$output->screen($IC->addTag($action));
		exit();
	}
	// DELETE TAG
	// Requires exactly 4 parameters /tags/delete/#item_id#/#tag_id#
	else if(count($action) == 4 && $action[0] == "tags" && $action[1] == "delete") {

		$output->screen($IC->deleteTag($action));
		exit();
	}
	// GET TAGS based on context
	// Requires just the tags parameter /tags/#context#
	else if(count($action) == 2 && $action[0] == "tags") {

		$output->screen($IC->getTags(array("context" => $action[1])));
		exit();
	}
	// GET TAGS
	// Requires just the tags parameter /tags
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
	// TODO: consider removing entirely and only allow loopbacks on type controller
	//
	// custom loopback to itemtype
	// TODO: consider alternative syntax'
	// alternative: /#itemtype#/#action#/#item_id#/#additional_parameters#

	// current alternative: /#itemtype#/#item_id#/#action#/#additional_parameters#
	// Requires minimum 3 parameters /#item_id#/#action#
	else if(count($action) > 2 && preg_match("/[a-z]+\/[0-9]+\/[a-zA-Z]+/", implode("/", $action))) {


		print "DEPRECATED IN CMS CONTROLLER - SHOULD BE ROUTED TO ITEMTYPE CONTROLLER";

		// get type object
		$typeObject = $IC->typeObject($action[0]);
		// check if custom function exists on typeObject
		if($typeObject && method_exists($typeObject, $action[2])) {

			$output->screen($typeObject->$action[2]($action));
			exit();
		}

		$output->screen(false);
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
else {
	$output->screen(false);
	exit();
}


// include_once("class/system/cms.class.php");
//
//
//
//
// $CMS = new CMS();
// $CMS->processRequest();
//
//
//
// // Fallback if CMS controller does not find matching request
// $page->header();
// $page->template("pages/404.php");
// $page->footer();

?>
