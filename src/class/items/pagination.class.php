<?php
/**
* This file contains the item custom backbone
* This class basically only exists to make it easy to add custom page functionality or overwrite behaviours.
*/


/**
* Item custom backbone - extends the ItemCore base functionality
*/
class Pagination {

	/**
	* Get required page information
	*/
	function __construct() {}


	function paginate($_options) {

		$direction = false;
		$id = false;
		$sindex = false;
		$pattern = false;

		$limit = 5;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "pattern"              : $pattern         = $_value; break;

					case "limit"                : $limit           = $_value; break;
					case "sindex"               : $sindex          = $_value; break;
					case "id"                   : $id              = $_value; break;

					case "direction"            : $direction       = $_value; break;
				}
			}
		}

		$IC = new Item();

		// get all items as base
		$items = $IC->getItems($pattern);

		# lists the latest N posts
		if(!$sindex) {

			$pattern["limit"] = $limit;
//			print_r($pattern);
			$range_items = $IC->getItems($pattern);

//			print_r($range_items);
		}

		# list based on sindex
		else if($sindex) {

			$item_id = $IC->getIdFromSindex($sindex);

			# Lists the next N posts after sindex
			if($direction == "next") {

				$range_items = $IC->getNext($item_id, array("items" => $items, "count" => $limit));
			}
			# Lists the prev N posts before sindex
			else if($direction == "prev") {

				$range_items = $IC->getPrev($item_id, array("items" => $items, "count" => $limit));
			}
			# Lists the next N posts starting with sindex
			else {

				$item = $IC->getItem(array("id" => $item_id));
				$range_items = $IC->getNext($item_id, array("items" => $items, "count" => $limit-1));

				array_unshift($range_items, $item);
			}

		}

		// find indexes and ids for next/prev
		$first_id = isset($range_items[0]) ? $range_items[0]["id"] : false;
		$first_sindex = isset($range_items[0]) ? $range_items[0]["sindex"] : false;
		$last_id = isset($range_items[count($range_items)-1]) ? $range_items[count($range_items)-1]["id"] : false;
		$last_sindex = isset($range_items[count($range_items)-1]) ? $range_items[count($range_items)-1]["sindex"] : false;

		// look for next/prev item availability
		$next = $last_id ? $IC->getNext($last_id, array("items" => $items)) : false;
		$prev = $first_id ? $IC->getPrev($first_id, array("items" => $items)) : false;


		return array("range_items" => $range_items, "next" => $next, "prev" => $prev, "first_id" => $first_id, "last_id" => $last_id, "first_sindex" => $first_sindex, "last_sindex" => $last_sindex);
	}

}

?>




