<?php
$access_item["/"] = true;
if(isset($read_access) && $read_access) {
	return;
}

include_once($_SERVER["FRAMEWORK_PATH"]."/config/init.php");


// TODO: To be transformed to a content API ?
// ONLY USED TO RETURN TAG LIST


$action = $page->actions();
$IC = new Items();
$output = new Output();


// any actions
if($page->validateCsrfToken() && isset($action)) {


	// SAVE ITEM
	// Requires exactly to parameters /save/#itemtype#
	// if(count($action) == 2 && $action[0] == "save") {
	//
	// 	$output->screen($IC->saveItem($action[1]));
	// 	exit();
	// }
	//
	// // UPDATE ITEM
	// // Requires exactly two parameters /save/#item_id#
	// else if(count($action) == 2 && $action[0] == "update") {
	//
	// 	$output->screen($IC->updateItem($action[1]));
	// 	exit();
	// }
	//
	// // DELETE ITEM
	// // Requires exactly two parameters /delete/#item_id#
	// else if(count($action) == 2 && $action[0] == "delete") {
	//
	// 	$output->screen($IC->deleteItem($action[1]));
	// 	exit();
	// }
	//
	// // STATUS
	// // changes status of item
	// else if(count($action) == 3 && $action[0] == "status") {
	//
	// 	$output->screen($IC->status($action[1], $action[2]));
	// 	exit();
	// }




	// TAGS
	//
	// ADD TAG
	// Requires exactly 4 parameters /tags/add/#item_id#
	// else if(count($action) == 3 && $action[0] == "tags" && $action[1] == "add") {
	//
	// 	$output->screen($IC->addTag($action));
	// 	exit();
	// }
	// // DELETE TAG
	// // Requires exactly 4 parameters /tags/delete/#item_id#/#tag_id#
	// else if(count($action) == 4 && $action[0] == "tags" && $action[1] == "delete") {
	//
	// 	$output->screen($IC->deleteTag($action));
	// 	exit();
	// }
	// // GET TAGS based on context
	// // Requires just the tags parameter /tags/#context#
	// else if(count($action) == 2 && $action[0] == "tags") {
	//
	// 	$output->screen($IC->getTags(array("context" => $action[1])));
	// 	exit();
	// }
	// GET TAGS
	// Requires just the tags parameter /tags
	if(count($action) >= 1 && $action[0] == "tags") {

		$_options = [];

		if(count($action) == 2) {
			$_options["context"] = $action[1];
		}

		$output->screen($IC->getTags($_options));
		exit();
	}



	// PRICES
	//
	// DELETE PRICE
	// Requires exactly 4 parameters /prices/delete/#item_id#/#price_id#
	// else if(count($action) == 4 && $action[0] == "prices" && $action[1] == "delete") {
	//
	// 	$output->screen($IC->deletePrice($action[2], $action[3]));
	// 	exit();
	// }

}
else {
	$output->screen(false);
	exit();
}

?>
