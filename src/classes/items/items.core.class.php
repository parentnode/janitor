<?php
/**
* @package janitor.items
*/

/**
* This class holds Items query functionallity.
*
*/

class ItemsCore {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		$this->UC = false;

	}

	/**
	* Get matching type object instance
	* TODO: Use of wording: model or item? Why Object in this case - it is an instance you are getting. (a lot of work to rename but has to be done at some point)
	*
	* @return return instance of type object
	*/
	function TypeObject($itemtype) {


		if(!isset($this->itemtypes["class"][$itemtype])) {
			include_once("classes/items/type.$itemtype.class.php");

			$class = "Type".ucfirst($itemtype);
			$this->itemtypes["class"][$itemtype] = new $class();

		}
		return $this->itemtypes["class"][$itemtype];
	}


	/**
	* Helper funtion to get simple user class
	*/
	private function getUserClass() {
		if($this->UC == false) {
			$this->UC = new User();
		}
		return $this->UC;
	}

	/**
	* Global getItem
	* Get item data from items db - does not dig any deeper into type object
	*
	* @param $_options Named Array containing id or sindex to get
	*/
	function getItem($_options = false) {

		$id = false;
		$sindex = false;

		// tags and optional itemtype
		$tags = false;
		$itemtype = false;

		$extend = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "id"        : $id             = $_value; break;
					case "sindex"    : $sindex         = $_value; break;

					case "tags"      : $tags           = $_value; break;
					case "itemtype"  : $itemtype       = $_value; break;
					case "status"    : $status         = $_value; break;

					case "extend"    : $extend         = $_value; break;
				}
			}
		}


		$query = new Query();
		$sql = false;
		if($id) {
			$sql = "SELECT * FROM ".UT_ITEMS." WHERE id = '$id'" . (isset($status) ? " AND status = $status" : "");
		}
		else if($sindex) {
			$sql = "SELECT * FROM ".UT_ITEMS." WHERE sindex = '$sindex'" . (isset($status) ? " AND status = $status" : "");
		}
		else if($tags) {
			
			$SELECT = array();
			$FROM = array();
			$LEFTJOIN = array();
			$WHERE = array();
			$GROUP_BY = "";
			$HAVING = "";

			$SELECT[] = "items.id";
			$SELECT[] = "items.sindex";
			$SELECT[] = "items.status";
			$SELECT[] = "items.itemtype";
			$SELECT[] = "items.user_id";

			$SELECT[] = "items.created_at";
			$SELECT[] = "items.modified_at";
			$SELECT[] = "items.published_at";

		 	$FROM[] = UT_ITEMS." as items";

			$WHERE[] = "items.status = 1";

			if(isset($status)) {
				$WHERE[] = "items.status = $status";
			}

			if($itemtype) {
				$WHERE[] = "items.itemtype = '$itemtype'";
			}

			// tag query
			$LEFTJOIN[] = UT_TAGGINGS." as taggings ON taggings.item_id = items.id";
			$LEFTJOIN[] = UT_TAG." as tags ON tags.id = taggings.tag_id";

			$tag_array = explode(";", $tags);

			// create tag SQL
			$tag_sql = "";
			foreach($tag_array as $tag) {
				list($context, $value) = explode(":", $tag);
				$tag_sql .= ($tag_sql ? " OR " : "") .  "tags.context = '".$context."' AND tags.value = '".$value."'";
			}
			$WHERE[] = "(".$tag_sql.")";
			$HAVING = "count(*) = ".count($tag_array);
			$GROUP_BY = "items.id";


			$sql = $query->compileQuery($SELECT, $FROM, array("LEFTJOIN" => $LEFTJOIN, "WHERE" => $WHERE, "HAVING" => $HAVING, "GROUP_BY" => $GROUP_BY));
		}
		
//		print $sql."<br>";

		if($sql && $query->sql($sql)) {
			$item = $query->result(0);


			if($extend) {
				// only pass on extend settings if they are not empty
				$item = $this->extendItem($item, (is_array($extend) ? $extend : false));
			}
			return $item;
		}

		return false;
	}

	/**
	* Get ID of item based on sindex
	* DEPRECATED
	*/
	function getIdFromSindex($sindex) {

		print "DEPRECATED";
		$query = new Query();
		$sql = "SELECT id FROM ".UT_ITEMS." WHERE sindex = '$sindex'";
		if($query->sql($sql)) {
			return $query->result(0, "id"); 
		}

		return false;
	}


	/**
	* Extend item (already having base information)
	* Defined to be able to limit queries when getting information
	*
	* Default only gets type data
	*
	* Optional data
	*/
	function extendItem($item, $_options = false) {

		if(isset($item["id"]) && isset($item["itemtype"])) {

			$mediae = false;
			$tags = false;
			$prices = false;
			$ratings = false;
			$comments = false;
			$subscription_method = false;

			$user = false;
			$readstate = false;
			$subscription = false;

			// global setting for getting everything
			$all = false;

			if($_options !== false) {
				foreach($_options as $_option => $_value) {
					switch($_option) {

						case "mediae"                : $mediae                  = $_value; break;
						case "tags"                  : $tags                    = $_value; break;
						case "prices"                : $prices                  = $_value; break;
						case "ratings"               : $ratings                 = $_value; break;
						case "comments"              : $comments                = $_value; break;
						case "subscription_method"   : $subscription_method     = $_value; break;

						case "user"                  : $user                    = $_value; break;
						case "readstate"             : $readstate               = $_value; break;
						case "subscription"          : $subscription            = $_value; break;

						case "all"                   : $all                     = $_value; break;
					}
				}
			}


			// get the specific type data
			$typeObject = $this->TypeObject($item["itemtype"]);
			if(method_exists($typeObject, "get")) {
				$item = array_merge($item, $typeObject->get($item["id"]));
			}
			else {
				$item = array_merge($item, $this->getSimpleType($item["id"], $typeObject));
			}

			// add mediae
			if($all || $mediae) {
				$item["mediae"] = $this->getMediae(array("item_id" => $item["id"]));
			}

			// add comments
			if($all || $comments) {
				$item["comments"] = $this->getComments(array("item_id" => $item["id"]));
			}

			// add tags
			if($all || $tags) {
				// custom settings for getTags (order or context)
				if(is_array($tags)) {
					$tags["item_id"] = $item["id"];
				}
				else {
					$tags = array("item_id" => $item["id"]);
				}

				// get tags
				$item["tags"] = $this->getTags($tags);
			}


			// add prices (only prices in current currency)
			if($all || $prices) {
				global $page;
				$item["prices"] = $this->getPrices(array("item_id" => $item["id"], "currency" => $page->currency()));
			}

			// add subscription method (for item)
			if($all || $subscription_method) {
				$item["subscription_method"] = $this->getSubscriptionMethod(array("item_id" => $item["id"]));
			}


			// add user nickname
			if($all || $user) {
				$UC = $this->getUserClass();
				$user = $UC->getUserinfo(array("user_id" => $item["user_id"]));
				$item["user_nickname"] = $user ? $user["nickname"] : "N/A";
			}

			// add readstate (for current user)
			if($all || $readstate) {
				$UC = $this->getUserClass();
				$item["readstate"] = $UC->getReadstates(array("item_id" => $item["id"]));
			}

			// add subscription (for current user)
			if((defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS) && ($all || $subscription)) {
				include_once("classes/shop/subscription.class.php");
				$SubscriptionClass = new Subscription;
				$item["subscription"] = $SubscriptionClass->getSubscriptions(array("item_id" => $item["id"]));
			}


			// TODO: Implement ratings
			// NOT IMPLEMENTED YET
			if($all || $ratings) {
				$item["ratings"] = $this->getRatings(array("item_id" => $item["id"]));
			}

			return $item;
		}
		return false;
	}

	function extendItems($items, $_options = false) {

		if($items) {
			foreach($items as $i => $item) {
				// only pass on extend settings if they are not empty
				$items[$i] = $this->extendItem($item, (is_array($_options) ? $_options : false));
			}
		}

		return $items;
	}



	/**
	* Get simple (flat) item type
	* Defined to handle basic type data
	*
	* When creating complex itemtypes with multiple tables involved in data structure
	* override this by adding a get function to your type object
	*/
	function getSimpleType($item_id, $typeObject) {
		$query = new Query();

		$sql = "SELECT * FROM ".$typeObject->db." WHERE item_id = $item_id";
		if($query->sql($sql)) {
			$item = $query->result(0);
			unset($item["id"]);

			return $item;
		}
		return [];
	}


	/**
	* Related items
	*
	* Looks for items of same itemtype, with the best matching tags
	* Can also filter items with readstate
	* Otherwise find random items
	*/
	function getRelatedItems($_options = false) {

		$related_items = array();
		$autofill = true;
		$limit = 5;
		$extend = false;
		$exclude_array = array();

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "itemtype"      : $itemtype      = $_value; break;
					case "tags"          : $tags          = $_value; break;
					case "autofill"      : $autofill      = $_value; break;
					case "no_readstate"  : $no_readstate  = $_value; break;
					case "where"         : $where         = $_value; break;
					case "limit"         : $limit         = $_value; break;

					case "exclude"       : $exclude       = $_value; break;

					case "extend"        : $extend        = $_value; break;
				}
			}
		}

		if(isset($exclude)) {
			$exclude_array = explode(";", $exclude);
		}

		// Compile query for related items
		$query = new Query();

		$SELECT = array();
		$FROM = array();
		$LEFTJOIN = array();
		$WHERE = array();
		$GROUP_BY = "";
		$ORDER = array();
		$LIMIT = "";

		$SELECT[] = "items.id";
		$SELECT[] = "items.sindex";
		$SELECT[] = "items.status";
		$SELECT[] = "items.itemtype";
		$SELECT[] = "items.user_id";

		$SELECT[] = "items.created_at";
		$SELECT[] = "items.modified_at";
		$SELECT[] = "items.published_at";

	 	$FROM[] = UT_ITEMS." as items";

		if(isset($where)) {
			if(is_array($where)) {
				$WHERE = $where;
			}
			else {
				$WHERE[] = $where;
			}
		}

		$WHERE[] = "items.status = 1";


		// add exclude exceptions
		foreach($exclude_array as $exclude_id) {
			$WHERE[] = "items.id != $exclude_id";
		}

		// add itemtype if available
		if(isset($itemtype)) {
			$WHERE[] = "items.itemtype = '$itemtype'";

			// add main itemtype table to enable sorting based on local values
			$LEFTJOIN[] = $this->typeObject($itemtype)->db." as ".$itemtype." ON items.id = ".$itemtype.".item_id";
		}

		// filter readstates
		if(isset($no_readstate) && $no_readstate) {
			$user_id = session()->value("user_id");
			$WHERE[] = "items.id NOT IN (SELECT item_id FROM ".SITE_DB.".user_item_readstates WHERE user_id = $user_id)";
		}

		// if tags are available make complex query
		if(isset($tags) && $tags) {
			// tag query
			$LEFTJOIN[] = UT_TAGGINGS." as taggings ON taggings.item_id = items.id";
			$LEFTJOIN[] = UT_TAG." as tags ON tags.id = taggings.tag_id";

			// create tag SQL
			$tag_sql = "";
			foreach($tags as $tag) {
				$tag_sql .= ($tag_sql ? " OR " : "") .  "tags.context = '".$tag["context"]."' AND tags.value = '".$tag["value"]."'";
			}
			$WHERE[] = "(".$tag_sql.")";

			// Order result for best matches first
			$ORDER[] = "count(*) DESC";
		}

		// Order by published time
		$ORDER[] = "published_at DESC";
		$GROUP_BY = "items.id";

		// set limit
		if(isset($limit)) {
			$LIMIT = " LIMIT $limit";
		}

		$sql = $query->compileQuery($SELECT, $FROM, array("LEFTJOIN" => $LEFTJOIN, "WHERE" => $WHERE, "GROUP_BY" => $GROUP_BY, "ORDER" => $ORDER)) . $LIMIT;
		// debug([$sql]);

		$query->sql($sql);
		$related_items = $query->results();


		// update exclude values to exlude any tags matches
		if($related_items) {
			foreach($related_items as $item) {
				$exclude_array[] = $item["id"];
			}
		}


		// not enough related items found and autofill is true
		// (or no tags was included in request)
		if($autofill && count($related_items) < $limit) {

			// create search pattern
			$pattern = array("status" => 1);

			// add itemtype to search pattern if possible
			if(isset($itemtype) && $itemtype) {
				$pattern["itemtype"] = $itemtype;
			}
			if(isset($no_readstate) && $no_readstate) {
				$pattern["no_readstate"] = $no_readstate;
			}
			if(isset($where) && $where) {
				$pattern["where"] = $where;
			}
			if($exclude_array) {
				$pattern["exclude"] = implode(";", $exclude_array);
			}

			$pattern["limit"] = $limit - count($related_items);
			$pattern["order"] = "RAND()";

			// find some suitable random items 
			$items = $this->getItems($pattern);

			// merge items to get the combined stack
			$related_items = array_merge($related_items, $items);
		}

		// should items be extended?
		if(isset($extend)) {
			$related_items = $this->extendItems($related_items, $extend);
		}

		return $related_items;
	}


	/**
	* Get all matching items
	*
	* @param String $options
	* $order      String  - 
	* $status     Int 
	* $tags       
	* $sindex
	* $itemtype (match itemtype) 
	* $limit (limit of returned result)
	* $user_id (match user id)
	* $exclude (;-separated item id's to exclude)
	* $extend (automatically extend items with itemtype info before returning)
	*
	* @param String $sindex Optional navigation index - s(earch)index
	*
	* @return Array [id][] + [itemtype][]
	*/
	function getItems($_options = false) {

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "itemtype"      : $itemtype      = $_value; break;
					case "status"        : $status        = $_value; break;
					case "tags"          : $tags          = $_value; break;
					case "sindex"        : $sindex        = $_value; break;

					case "where"         : $where         = $_value; break;

					case "order"         : $order         = $_value; break;
					case "limit"         : $limit         = $_value; break;

					case "no_readstate"  : $no_readstate  = $_value; break;
					case "user_id"       : $user_id       = $_value; break;

					case "exclude"       : $exclude       = $_value; break;

					case "extend"        : $extend        = $_value; break;

					// TODO: implement date ranges

					// TODO: implement search patterns which can also look in local databases - first experiment made in local device search (type.device.class.php)
				}

			}
		}


		$query = new Query();

		// Prepare query parts
		$SELECT = array();
		$FROM = array();
		$LEFTJOIN = array();
		$WHERE = array();
		$GROUP_BY = "";
		$HAVING = "";
		$ORDER = array();

		// Add base select properties
		$SELECT[] = "items.id";
		$SELECT[] = "items.sindex";
		$SELECT[] = "items.status";
		$SELECT[] = "items.itemtype";
		$SELECT[] = "items.user_id";

		$SELECT[] = "items.created_at";
		$SELECT[] = "items.modified_at";
		$SELECT[] = "items.published_at";

	 	$FROM[] = UT_ITEMS." as items";

		if(isset($where)) {
			if(is_array($where)) {
				$WHERE = $where;
			}
			else {
				$WHERE[] = $where;
			}
		}

		if(isset($status)) {
			$WHERE[] = "items.status = $status";
		}

		if(isset($user_id)) {
			$WHERE[] = "items.user_id = $user_id";
		}


		// filter on readstates
		if(isset($no_readstate) && $no_readstate) {
			// if user id is not specified, use current user
			if(!isset($user_id)) {
				$user_id = session()->value("user_id");
			}
			$WHERE[] = "items.id NOT IN (SELECT item_id FROM ".SITE_DB.".user_item_readstates WHERE user_id = $user_id)";
		}


		if(isset($exclude)) {
			$exclude_array = explode(";", $exclude);
			foreach($exclude_array as $exclude_id) {
				$WHERE[] = "items.id != $exclude_id";
			}
		}

		// TODO: implement dateranges
		// if(isset($published_at)) {
		// 	$WHERE[] = "items.published_at = $published_at";
		// }

		if(isset($itemtype)) {
			$WHERE[] = "items.itemtype = '$itemtype'";

			// add main itemtype table to enable sorting based on local values
			$LEFTJOIN[] = $this->typeObject($itemtype)->db." as ".$itemtype." ON items.id = ".$itemtype.".item_id";
		}


		// tag query
		if(isset($tags) && is_string($tags)) {

			$LEFTJOIN[] = UT_TAGGINGS." as taggings ON taggings.item_id = items.id";
			$LEFTJOIN[] = UT_TAG." as tags ON tags.id = taggings.tag_id";


//			$FROM[] = UT_TAGGINGS . " as item_tags";
//			$FROM[] = UT_TAG . " as tags";
//			$tag_array = explode(",", $tags);
			// UPDATED: changed tags separator to ;
			$tag_array = explode(";", $tags);
			$tag_sql = "";


			foreach($tag_array as $tag) {
//				$exclude = false;
				// tag id
				if($tag) {

					// dechipher tag
					$exclude = false;

					// negative tag, exclude
					if(substr($tag, 0, 1) == "!") {
						$tag = substr($tag, 1);
						$exclude = true;
					}

					// if tag has both context and value
					if(strpos($tag, ":")) {
						list($context, $value) = explode(":", $tag);
					}
					// only context present, value false
					else {
						$context = $tag;
						$value = false;
					}

					if($context || $value) {
						// Negative !tag
						if($exclude) {
//							$WHERE[] = "items.id NOT IN (SELECT item_id FROM ".UT_TAGGINGS." as item_tags, ".UT_TAG." as tags WHERE item_tags.tag_id = tags.id" . ($context ? " AND tags.context = '$context'" : "") . ($value ? " AND tags.value = '$value'" : "") . ")";
//							$WHERE[] = "items.id NOT IN (SELECT item_id FROM ".UT_TAGGINGS." as item_tags, ".UT_TAG." as tags WHERE item_tags.tag_id = tags.id" . ($context ? " AND tags.context = '$context'" : "") . ($value ? " AND tags.value = '$value'" : "") . ")";
						}
						// positive tag
						else {
							if($context && $value) {
								$tag_sql .= ($tag_sql ? " OR " : "") .  "tags.context = '$context' AND tags.value = '$value'";
							}
							else if($context) {
								$tag_sql .= ($tag_sql ? " OR " : "") .  "tags.context = '$context'";
							}
//							$WHERE[] = "items.id IN (SELECT item_id FROM ".UT_TAGGINGS." as item_tags, ".UT_TAG." as tags WHERE item_tags.tag_id = tags.id" . ($context ? " AND tags.context = '$context'" : "") . ($value ? " AND tags.value = '$value'" : "") . ")";
	//						$WHERE[] = "items.id IN (SELECT item_id FROM ".UT_TAGGINGS." as item_tags, ".UT_TAG." as tags WHERE item_tags.tag_id = '$tag' OR (item_tags.tag_id = tags.id AND tags.name = '$tag'))";
						}
					}
				}
			}
			$WHERE[] = "(".$tag_sql.")";
			$HAVING = "count(*) = ".count($tag_array);
		}


		$GROUP_BY = "items.id";


		// add item-order specific SQL
		if(isset($order)) {

			// Rating order? 
			if($order === "rating DESC") {

				$LEFTJOIN[] = UT_ITEMS_RATINGS." as ratings ON ratings.item_id = items.id";
				$SELECT[] = "SUM(ratings.rating) as total_rating";
				$SELECT[] = "AVG(ratings.rating) as average_rating";
				$ORDER[] = "average_rating DESC";

			}
			else if($order === "rating ASC") {

				$LEFTJOIN[] = UT_ITEMS_RATINGS." as ratings ON ratings.item_id = items.id";
				$SELECT[] = "SUM(ratings.rating) as total_rating";
				$SELECT[] = "AVG(ratings.rating) as average_rating";
				$ORDER[] = "average_rating ASC";

			}
			// Or any kind of order
			else {

				$ORDER[] = $order;

			}

		}

		$ORDER[] = "items.published_at DESC";

		if(isset($limit)) {
			$limit = " LIMIT $limit";
		}
		else {
			$limit = "";
		}

		$items = array();

		$sql = $query->compileQuery($SELECT, $FROM, array("LEFTJOIN" => $LEFTJOIN, "WHERE" => $WHERE, "HAVING" => $HAVING, "GROUP_BY" => $GROUP_BY, "ORDER" => $ORDER)) . $limit;
		// debug($sql);

		$query->sql($sql);
		$items = $query->results();


		// TODO: consider if this could be integrated in primary query
		// - but might give issues with flexibility and query load on mixed lists
		// needs to be investigated
		if(isset($extend)) {
			$items = $this->extendItems($items, $extend);
		}

		return $items;
	}




	// PAGINATION STUFF


	/**
	* Get next item(s)
	*
	* Can receive items array to use for finding next item(s) 
	* or receive query syntax to perform getItems request on it own
	*
	* @param $item_id item_id to get next from
	*/
	function getNext($item_id, $_options = false) {

		$items = false;
		$limit = 1;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "items"   : $items    = $_value; break;
					case "limit"   : $limit    = $_value; break;

				}
			}
		}

		// debug(["getNext", $item_id, $_options]);


		// avoid extending all items
		$extend = false;
		// but save extend values to extend final range_items
		if(isset($_options["extend"])) {
			$extend = $_options["extend"];
			unset($_options["extend"]);
		}


		// get items if they have not been passed as argument
		if($items === false) {
			if(isset($_options["limit"])) {
				unset($_options["limit"]);
			}
			$items = $this->getItems($_options);
		}


		// filtering variables
		$next_items = array();
		$item_found = false;
		$counted = 0;

		// loop through all items, looking for starting point
		for($i = 0; $i < count($items); $i++) {

			// wait until we find starting point
			if($item_found) {

				// keep an eye on counter
				$counted++;

				// add to next scope
				$next_items[] = $items[$i];

				// end when enough items have been collected
				if($counted == $limit) {
					break;
				}
			}

			// found starting point
			else if($item_id === $items[$i]["id"]) {
				$item_found = true;
			}
		}


		// Extend previous items before returning them
		if($next_items && $extend) {
			$next_items = $this->extendItems($next_items, $extend);
		}


		// return set of next items
		return $next_items;
	}

	/**
	* Get previous item(s)
	*
	* Can receive items array to use for finding previous item(s) 
	* or receive query syntax to perform getItems request on it own
	* TODO: This implementation is far from performance optimized, but works - consider alternate implementations
	*/
	function getPrev($item_id, $_options = false) {

		$items = false;
		$limit = 1;

		// Other getItems patters properties may also be passed

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "items"   : $items    = $_value; break;
					case "limit"   : $limit    = $_value; break;
				}
			}
		}


		// avoid extending all items
		$extend = false;
		// but save extend values to extend final range_items
		if(isset($_options["extend"])) {
			$extend = $_options["extend"];
			unset($_options["extend"]);
		}


		// get items if they have not been passed as argument
		if($items === false) {
			if(isset($_options["limit"])) {
				unset($_options["limit"]);
			}
			$items = $this->getItems($_options);
		}


		// filtering variables
		$prev_items = array();
		$item_found = false;
		$counted = 0;
		// loop backwards through all items, looking for starting point
		for($i = count($items)-1; $i >= 0; $i--) {

			// wait until we find starting point
			if($item_found) {

				// keep an eye on counter
				$counted++;

				// add to beginning of prev scope
				array_unshift($prev_items, $items[$i]);

				// end when enough items have been collected
				if($counted == $limit) {
					break;
				}
			}

			// found starting point
			else if($item_id === $items[$i]["id"]) {
				$item_found = true;
			}
		}


		// Extend previous items before returning them
		if($prev_items && $extend) {
			$prev_items = $this->extendItems($prev_items, $extend);
		}


		// return set of prev items
		return $prev_items;
	}


	/**
	 * Paginate a list of items
	 * 
	 * Splits a list of items into smaller fragments and returns information required to create meaningful pagination
	 *
	 * @param array $_options
	 * * pattern – array of options to be sent to Item::getItems, which returns the items to be paginated
	 * * limit – maximal number of items per page. Default: 5.
	 * * sindex – if passed without the direction parameter, the pagination will start with the associated item
	 * * direction – can be passed in combination with the sindex parameter
	 * * * "next" – pagination will start with the item that comes *after* the item with the specified sindex. 
	 * * * "prev" – pagination will show the items that come immediately *before* the item with the specified sindex.
	 * 
	 * 
	 * @return array
	 * * range_items (list of items in specified range)
	 * * next items
	 * * previous items
	 * * first id in range
	 * * last id in range
	 * * first s_index in range
	 * * last s_index in range
	 */
	function paginate($_options) {

		// Items selected for this pagination range
		$range_items = false;


		// Start range_items from iten - item_id or sindex - Default false
		$item_id = false;
		$sindex = false;

		// next: Next in order
		// prev: Previous in order
		// - Default next
		// Can only be used in conjunction with item_id or sindex
		$direction = "next";

		// include sindex or item_id in match
		// - Default true
		// Can only be used in conjunction with item_id or sindex
		$include = true;


		// Start range_items from page – Default false
		$page = false;



		// Search and extend pattern for range_items / pagination
		$pattern = false;

		// Limit for range_items - Default 5
		$limit = 20;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "pattern"              : $pattern         = $_value; break;

					case "limit"                : $limit           = $_value; break;

					case "sindex"               : $sindex          = $_value; break;
					case "item_id"              : $item_id         = $_value; break;

					case "page"                 : $page            = $_value; break;

					case "direction"            : $direction       = $_value; break;
					case "include"              : $include         = $_value; break;
				}
			}
		}



		
		// avoid extending all items initially
		// but save extend values to extend final range_items
		$extend = false;
		if(isset($pattern["extend"])) {
			$extend = $pattern["extend"];
			unset($pattern["extend"]);
		}



		// If invalid item_id or sindex has been passed, consider it as if no sindex or item_id was passed
		// Test item_id
		if($item_id) {

			$index_item = $this->getItem(["id" => $item_id]);
			if(!$index_item) {
				$item_id = false;
			}

		}
		// Test sindex – and convert to item_id if found
		else if($sindex) {

			$index_item = $this->getItem(["sindex" => $sindex]);
			if($index_item) {
				$item_id = $index_item["id"];
			}
			else {
				$item_id = false;
			}

		}


		// get all items sorted as base for pagination
		$items = $this->getItems($pattern);



		// if there is no item_id or page to start from beginning
		// Select first N posts
		// if(!$page && (!$item_id || !$this->getItem(["id" => $item_id])) && (!$sindex || !$this->getItem(["sindex" => $sindex]))) {
		if(!$page && !$item_id) {

			// simply add limit to items query
			$pattern["limit"] = $limit;
			$range_items = $this->getItems($pattern);

		}
		// Starting point exists
		else {

			// item_id marks pagination point
			if($item_id) {

				if($direction == "prev") {

					// Include index item (specified by passed sindex or item_id)
					if($include) {

						// Limit must be greater than one, or we already have the full range in $index_item
						if($limit > 1) {
							// Reduce limit to make room
							$range_items = $this->getPrev($item_id, ["items" => $items, "limit" => $limit-1]);
							array_push($range_items, $index_item);
						}
						else {
							$range_items[] = $index_item;
						}

					}
					// Get prev N from item_id (not including index item)
					else {

						$range_items = $this->getPrev($item_id, ["items" => $items, "limit" => $limit]);
					}

				}
				// Default direction is next
				else {

					// Include index item (specified by passed sindex or item_id)
					if($include) {

						// Limit must be greater than one, or we already have the full range in $index_item
						if($limit > 1) {
							// Reduce limit to make room
							$range_items = $this->getNext($item_id, ["items" => $items, "limit" => $limit-1]);
							array_unshift($range_items, $index_item);
						}
						else {
							$range_items[] = $index_item;
						}

					}
					// Get next N from item_id (not including index item)
					else {

						$range_items = $this->getNext($item_id, ["items" => $items, "limit" => $limit]);
					}

				}

			}
			// page marks pagination point
			else if($page) {

				$start_index = ($page-1) * $limit;
				if(count($items) >= $start_index) {

					// Find item_id of first element of page 
					$index_item = $items[$start_index];

					// Include index item (specified by passed sindex or item_id)
					// Reduce limit to make room
					// (for page based pagination it doesn't make sense to exclude item)
					$range_items = $this->getNext($index_item["id"], ["items" => $items, "limit" => $limit-1]);
					array_unshift($range_items, $index_item);

				}

			}

		}


		// Should range items be extended, then do it now
		if($range_items && $extend) {
			$range_items = $this->extendItems($range_items, $extend);
		}


		// Page information
		$total_count = count($items);

		// Include page count and current page number
		$page_count = intval(ceil($total_count / $limit));
		$current_page = false;

		$first_id = false;
		$first_sindex = false;
		$last_id = false;
		$last_sindex = false;
		$prev = false;
		$next = false;


		if($range_items) {

			if(isset($range_items[0])) {

				$first_id = $range_items[0]["id"];
				$first_sindex = $range_items[0]["sindex"];

				$prev = $this->getPrev($first_id, ["items" => $items, "limit" => 1, "extend" => $extend]);
				if($prev) {
					$prev = $prev[0];
				}

				// If there is a first id, then there must be a last id (which might be the same, though)
				$last_id = $range_items[count($range_items)-1]["id"];
				$last_sindex = $range_items[count($range_items)-1]["sindex"];

				$next = $this->getNext($last_id, ["items" => $items, "limit" => 1, "extend" => $extend]);
				if($next) {
					$next = $next[0];
				}

				// Locate first_id in page stack
				$current_position = arrayKeyValue($items, "id", $first_id);
				$current_page = intval(floor($current_position / $limit)+1);

			}

		}


		// return all pagination info
		// range_items = list of items in specified range
		// next item
		// previous item
		// first id in range
		// last id in range
		return array("range_items" => $range_items, "next" => $next, "prev" => $prev, "first_id" => $first_id, "last_id" => $last_id, "first_sindex" => $first_sindex, "last_sindex" => $last_sindex, "total" => $total_count, "page_count" => $page_count, "current_page" => $current_page);
	}



	// SEARCH

	function search($_options = false) {
		// debug(["search", $_options]);

		$pattern = false;
		$query_string = "";

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					// case "itemtype"    : $itemtype        = $_value; break;

					case "pattern"     : $pattern         = $_value; break;
					case "query"       : $query_string           = $_value; break;
				}
			}
		}

		// Valid search string
		if($query_string) {

			$query = new Query();
			$search_view_id = "search_".randomKey(8);

			$itemtype_queries = [];

			// Specific itemtype
			if($pattern && $pattern["itemtype"]) {

				// Collect data for this itemtype
				$model = $this->typeObject($pattern["itemtype"]);
				if($model) {
					$entities = $model->getModel();

					$searchable_column = [];

					foreach($entities as $name => $entity) {
						// debug([$entity]);

						if(isset($entity["searchable"]) && $entity["searchable"]) {
							$searchable_column[] = "itemtypes.".$name;
						}

					}

					if($searchable_column) {
						$itemtype_queries[] = "SELECT DISTINCT items.id as id, itemtypes.item_id as item_id, items.itemtype as itemtype, items.sindex as sindex, items.published_at as published_at, items.modified_at as modified_at, itemtypes.name as name, REGEXP_REPLACE(REGEXP_REPLACE(CONCAT_WS('###', ".implode(",", $searchable_column)."), '<[^>]+>|\\\n|\\\r',' '), '[\\\s]+', ' ') as searchable_string FROM ".SITE_DB.".item_".$pattern["itemtype"]." as itemtypes, ".UT_ITEMS." as items WHERE items.id = itemtypes.item_id";
					}

				}

			}

			// All itemtypes
			else {

				// Get used itemtypes
				$sql = "SELECT itemtype FROM ".UT_ITEMS." GROUP by itemtype";
				if($query->sql($sql)) {

					$itemtypes = $query->results("itemtype");

					// Collect data for all itemtype
					foreach($itemtypes as $itemtype) {

						$model = $this->typeObject($itemtype);
						if($model) {
							$entities = $model->getModel();

							$searchable_column = [];

							foreach($entities as $name => $entity) {
								// debug([$entity]);

								if(isset($entity["searchable"]) && $entity["searchable"]) {
									$searchable_column[] = "itemtypes.".$name;
								}

							}

							if($searchable_column) {
								$itemtype_queries[] = "SELECT DISTINCT items.id as id, itemtypes.item_id as item_id, items.itemtype as itemtype, items.sindex as sindex, items.published_at as published_at, items.modified_at as modified_at, itemtypes.name as name, REGEXP_REPLACE(REGEXP_REPLACE(CONCAT_WS('###', ".implode(",", $searchable_column)."), '<[^>]+>|\\\n|\\\r',' '), '[\\\s]+', ' ') as searchable_string FROM ".SITE_DB.".item_".$itemtype." as itemtypes, ".UT_ITEMS." as items WHERE items.id = itemtypes.item_id";
							}

						}

					}

				}

			}

			// Data queries available for view creation
			if($itemtype_queries) {

				// perf()->add("Collected data queries");

				$sql = "CREATE VIEW ".SITE_DB.".".$search_view_id." AS " . (count($itemtype_queries) > 1 ? implode(" UNION ", $itemtype_queries) : $itemtype_queries[0]);
				// debug([$sql]);
				$query->sql($sql);

				// perf()->add("View created");


				$sql = "SELECT * FROM ".SITE_DB.".".$search_view_id." WHERE searchable_string LIKE '%$query_string%' ORDER BY published_at DESC";
				// debug([$sql]);
				$query->sql($sql);
				$results = $query->results();

				// perf()->add("Search query executed");


				// Remove view after search
				$query->sql("DROP VIEW IF EXISTS ".SITE_DB.".".$search_view_id);

				// print_r($results);
				return $results;
			}

		}

	}



	// MEDIA

	// TODO: implement get media function like getTags (needs testing)
	// get mediae, optionally based on item_id
	function getMediae($_options=false) {

		$item_id = false;
		$media_id = false;
		$variant = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"    : $item_id        = $_value; break;
					case "media_id"   : $media_id       = $_value; break;
					case "variant"    : $variant        = $_value; break;
				}
			}
		}

		$query = new Query();

		// get specific media_id
		if($media_id) {
			if($query->sql("SELECT * FROM ".UT_ITEMS_MEDIAE." WHERE id = '$media_id'")) {
				return $query->result(0);
			}
		}
		else if($item_id) {

			// specific media variant?
			if($variant) {
				if($query->sql("SELECT * FROM ".UT_ITEMS_MEDIAE." WHERE variant = '$variant' AND item_id = $item_id")) {
					return $query->result(0);
				}
			}

			// all mediae (not HTML-editor media)
			else {

				$sql = "SELECT * FROM ".UT_ITEMS_MEDIAE." WHERE item_id = $item_id AND variant NOT LIKE 'HTML-%' ORDER BY position ASC, id DESC";
//				print $sql."<br>\n";

				if($query->sql($sql)) {
					$mediae = array();
					$results = $query->results();
					foreach($results as $result) {
						$mediae[$result["variant"]] = $result;
					}
					return $mediae;
				}
			}
		}

		// get all mediae
		else {

			if($query->sql("SELECT * FROM ".UT_ITEMS_MEDIAE)) {
				return $query->results();
			}
			
		}
		return false;
	}


	// find media with matching variant or simply first media (png/jpg/gif)
	// Does not modify item[mediae] array
	function getFirstMedia($item, $variant=false) {

		$media = false;

		// No variant, use first media
		if(!$variant) {
			if(isset($item["mediae"]) && $item["mediae"]) {
				foreach($item["mediae"] as $m) {
					if(preg_match("/^(png|gif|jpg)$/", $m["format"])) {
						$media = $m;
						break;
					}
				}
			}
		}
		// Find specific variant
		else if(isset($item["mediae"]) && $item["mediae"] && isset($item["mediae"][$variant])) {
			$media = $item["mediae"][$variant];
		}
		// Find first of specific variant mediae
		else if(isset($item["mediae"]) && $item["mediae"]) {
			foreach($item["mediae"] as $m) {
				if(preg_match("/^".$variant."-/", $m["variant"]) && preg_match("/^(png|gif|jpg)$/", $m["format"])) {
					$media = $m;
					break;
				}
			}
		}

		return $media;
	}

	// find media with matching variant or simply first media
	// removes media from media stack (to make it easier to loop through remaining media later)
	function sliceMediae(&$item, $variant="mediae") {

		$media = false;

		// Find specific variant
		if(isset($item["mediae"]) && $item["mediae"] && isset($item["mediae"][$variant])) {
			$media = $item["mediae"][$variant];
			unset($item["mediae"][$variant]);
		}
		// Find first of specific variant mediae
		else if(isset($item["mediae"]) && $item["mediae"]) {
			foreach($item["mediae"] as $m) {
				if(preg_match("/^".$variant."-/", $m["variant"]) && preg_match("/^(png|gif|jpg)$/", $m["format"])) {
					$media = $m;
					unset($item["mediae"][$m["variant"]]);
					break;
				}
			}
		}

		return $media;
	}

	// Filter mediae array by variants
	function filterMediae($item, $variant = "mediae") {

		$mediae = [];

		// Is variant specific
		if(isset($item["mediae"]) && $item["mediae"] && isset($item["mediae"][$variant])) {
			$media = $item["mediae"][$variant];
			return [$media];
		}
		// Look for variant collection mediae
		else if(isset($item["mediae"]) && $item["mediae"]) {
			foreach($item["mediae"] as $media) {
				if(preg_match("/^".$variant."-/", $media["variant"])) {
					array_push($mediae, $media);
				}
			}
		}

		return $mediae;
	}




	// OWNER

	// get potential owners of an itemtype, 
	// optionally based on item_id, 
	// TODO: optionally look at group access and return only mombers of allowed groups 
	function getOwners($_options=false) {

		$item_id = false;
		$itemtype = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"    : $item_id        = $_value; break;
					case "itemtype"   : $itemtype       = $_value; break;
				}
			}
		}

		$query = new Query();

		if($item_id) {
			$sql = "SELECT users.id, users.nickname FROM ".SITE_DB.".users as users, ".UT_ITEMS." as items WHERE users.id = items.user_id AND items.id = ".$item_id;
			if($query->sql($sql)) {
				return $query->result(0);
			}
		}


		// return all users
		$sql = "SELECT users.id, users.nickname FROM ".SITE_DB.".users"." as users";
		if($query->sql($sql)) {
			return $query->results();
		}

	}


	// TAGS

	// get tag, optionally based on item_id, limited to context, or just check if specific tag exists
	function getTags($_options=false) {

		$item_id = false;
		$tag_id = false;

		$tag_context = false;

		$tag_value = false;
		$order = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"    : $item_id        = $_value; break;
					case "tag_id"     : $tag_id         = $_value; break;
					case "context"    : $tag_context    = $_value; break;
					case "value"      : $tag_value      = $_value; break;
					case "order"      : $order          = $_value; break;
				}
			}
		}


		// Multiple tag contexts
		if($tag_context) {
			$tag_context = preg_split("/,|;/", $tag_context);
		}


		$query = new Query();

		// get tag information for specific item
		if($item_id) {
			// does specific tag exists?
			if($tag_context && $tag_value) {
				$sql = "SELECT * FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE";
				
				$sql .= " (tags.context = '".implode("' OR tags.context = '", $tag_context) . "')";
				// $sql .= " tags.context = '$tag_context'
					
				$sql .= " AND tags.value = '$tag_value' AND tags.id = taggings.tag_id AND taggings.item_id = $item_id";

				return $query->sql($sql);
			}
			// get all tags with context
			else if($tag_context) {
				$sql = "SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE";

				$sql .= " (tags.context = '".implode("' OR tags.context = '", $tag_context) . "')";
				// " tags.context = '$tag_context'
					
				$sql .= " AND tags.id = taggings.tag_id AND taggings.item_id = $item_id".($order ? " ORDER BY tags.$order" : "");
				if($query->sql($sql)) {
					return $query->results();
				}
			}
			// all tags
			else {
				$sql = "SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE tags.id = taggings.tag_id AND taggings.item_id = $item_id".($order ? " ORDER BY tags.$order" : "");
				if($query->sql($sql)) {
					return $query->results();
				}
			}
		}

		// Other tag relations

		// get tag and items using tag_id
		else if($tag_id) {

			$sql = "SELECT * FROM ".UT_TAG." as tags WHERE tags.id = '$tag_id'";
			$query->sql($sql);
			$tag = $query->result(0);
			
			$sql = "SELECT item_id as id, itemtype, status FROM ".UT_TAGGINGS." as taggings, ".UT_ITEMS." as items WHERE taggings.tag_id = '$tag_id' AND taggings.item_id = items.id";
			$query->sql($sql);
			$tag["items"] = $query->results();
			return $tag;
		}

		// get items using tag with context and value
		else if($tag_context && $tag_value) {

			$sql = "SELECT * FROM ".UT_TAG." as tags, ".UT_TAGGINGS." as taggings WHERE";

			$sql .= " (tags.context = '".implode("' OR tags.context = '", $tag_context) . "')";
			// " tags.context = '$tag_context'
			
			$sql .= " AND tags.value = '$tag_value' AND tags.id = taggings.tag_id";

			$query->sql($sql);
			return $query->results();
		}


		// get all tags

		// get all tags with context
		else if($tag_context) {

			$sql = "SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG." as tags WHERE";

			// Matching contexts
			$sql .= " (tags.context = '".implode("' OR tags.context = '", $tag_context) . "')";

			// Order
			$sql .=	($order ? " ORDER BY tags.$order" : "");

			// debug([$sql]);
			if($query->sql($sql)) {
				return $query->results();
			}
		}
		// all tags
		else {

			$sql = "SELECT tags.id as id, tags.context as context, tags.value as value FROM ".UT_TAG.($order ? " ORDER BY tags.$order" : " ORDER BY tags.context, tags.value");
			if($query->sql($sql)) {
				return $query->results();
			}
		}

		return false;
	}




	// COMMENTS


	// get comments, optionally based on item_id, user_id or comment_id
	function getComments($_options=false) {

		$item_id = false;
		$comment_id = false;
		$user_id = false;
		$order = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"     : $item_id        = $_value; break;
					case "comment_id"  : $comment_id     = $_value; break;
					case "user_id"     : $user_id        = $_value; break;

					case "order"       : $order          = $_value; break;
				}
			}
		}

		$query = new Query();

		// Get all comments for item_id
		if($item_id) {

			$sql = "SELECT comments.id, comments.comment, comments.created_at, users.nickname FROM ".UT_ITEMS_COMMENTS." as comments, ".SITE_DB.".users as users WHERE comments.item_id = $item_id AND comments.user_id = users.id".($order ? " ORDER BY $order" : "");
			if($query->sql($sql)) {
				return $query->results();
			}
		}
		// Get all comments by user_id and related items
		else if($user_id) {
			if($query->sql("SELECT * FROM ".UT_ITEMS_COMMENTS." as comments WHERE user_id = $user_id".($order ? " ORDER BY $order" : ""))) {
				$comments = $query->results();
				foreach($comments as $index => $comment) {
					$comments[$index]["item"] = $this->getItem(array("id" => $comment["item_id"], "extend" => true));
				}
				return $comments;
			}
		}
		// get comment using comment_id
		else if($comment_id) {
			if($query->sql("SELECT comments.id, comments.comment, comments.created_at, users.nickname FROM ".UT_ITEMS_COMMENTS." as comments, ".SITE_DB.".users as users WHERE comments.id = '$comment_id' AND comments.user_id = users.id")) {
				return $query->result(0);
			}
		}
		// get all comments
		else {
			if($query->sql("SELECT comments.id, comments.comment, comments.created_at, users.nickname FROM ".UT_ITEMS_COMMENTS." as comments, ".SITE_DB.".users as users WHERE comments.user_id = users.id" . ($order ? " ORDER BY $order" : " ORDER BY created_at"))) {
				return $query->results();
			}
		}
		return false;
	}


	// get ratings, optionally based on item_id, user_id or rating_id
	function getRatings($_options=false) {

		$item_id = false;
		$rating_id = false;
		$user_id = false;
		$order = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"     : $item_id        = $_value; break;
					case "rating_id"   : $rating_id     = $_value; break;
					case "user_id"     : $user_id        = $_value; break;

					case "order"       : $order          = $_value; break;
				}
			}
		}

		$query = new Query();
		$ratings = false;

		// Get all comments for item_id
		if($item_id !== false) {

			$sql = "SELECT ratings.id, ratings.rating, ratings.created_at, users.nickname FROM ".UT_ITEMS_RATINGS." as ratings, ".SITE_DB.".users as users WHERE ratings.item_id = $item_id AND ratings.user_id = users.id".($order ? " ORDER BY $order" : "");
			if($query->sql($sql)) {
				$results = $query->results();
				$ratings["item_id"] = $item_id;
				$ratings["ratings"] = $results;
				$total = 0;
				$lowest = false;
				$highest = false;
				foreach($results as $result) {
					$total += $result["rating"];

					if($lowest === false || intval($result["rating"]) < $lowest) {
						$lowest = $result["rating"];
					}

					if($highest === false || $result["rating"] > $highest) {
						$highest = $result["rating"];
					}
				}

				$ratings["lowest"] = intval($lowest);
				$ratings["highest"] = intval($highest);
				$ratings["average"] = round($total / count($results), 2);

				return $ratings;
			}
		}
		// Get all ratings by user_id and related items
		else if($user_id !== false) {
			if($query->sql("SELECT * FROM ".UT_ITEMS_RATINGS." as ratings WHERE user_id = $user_id".($order ? " ORDER BY $order" : ""))) {
				$ratings = $query->results();
				foreach($ratings as $index => $rating) {
					$ratings[$index]["item"] = $this->getItem(array("id" => $rating["item_id"], "extend" => true));
				}
				return $ratings;
			}
		}
		// get rating using rating_id
		else if($rating_id !== false) {
			if($query->sql("SELECT ratings.id, ratings.rating, ratings.created_at, users.nickname FROM ".UT_ITEMS_RATINGS." as ratings, ".SITE_DB.".users as users WHERE ratings.id = '$rating_id' AND ratings.user_id = users.id")) {
				return $query->result(0);
			}
		}
		// get all ratings
		else {
			if($query->sql("SELECT ratings.id, ratings.rating, ratings.created_at, users.nickname FROM ".UT_ITEMS_RATINGS." as ratings, ".SITE_DB.".users as users WHERE ratings.user_id = users.id" . ($order ? " ORDER BY $order" : " ORDER BY created_at"))) {
				$ratings = $query->results();
			}
		}

		return $ratings;
	}



	// PRICES

	// get prices, 
	// TODO: extend to be able to get items ordered by price if possible (but not here)
	// TODO: Include formatting based on selected country
	function getPrices($_options = false) {

		$price_id = false;
		$item_id = false;

		$currency = false;
//		$type = false;


		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"   : $item_id    = $_value; break;
					case "price_id"  : $price_id   = $_value; break;

					case "currency"  : $currency   = $_value; break;
//					case "type"      : $type       = $_value; break;
				}
			}
		}

		$prices = false;
		$query = new Query();


		// get specific price
		if($price_id) {
			$sql = "SELECT prices.id, prices.price, prices.currency, prices.quantity, vatrates.vatrate, price_types.name AS type, price_types.description FROM ".UT_ITEMS_PRICES." as prices, ".UT_VATRATES." as vatrates, ".UT_PRICE_TYPES." as price_types WHERE prices.id = '$price_id' AND vatrates.id = prices.vatrate_id AND price_types.id = prices.type_id";
			if($query->sql($sql)) {

				$price = $query->result(0);

				// precalculate details
				$vat = $price["price"] * (1 - (1 / (1 + ($price["vatrate"]/100))));
				$price["vat"] = $vat;
				$price["price_without_vat"] = $price["price"]-$vat;

				return $price;
			}
		}
		// get price(s) for item
		else if($item_id) {

			// if currency specified return only prices in that currency 
			if($currency) {
				$sql = "SELECT prices.id, prices.price, prices.currency, prices.quantity, vatrates.vatrate, price_types.name AS type, price_types.description FROM ".UT_ITEMS_PRICES." as prices, ".UT_VATRATES." as vatrates, ".UT_PRICE_TYPES." as price_types WHERE prices.item_id = '$item_id' AND vatrates.id = prices.vatrate_id AND price_types.id = prices.type_id AND prices.currency = '$currency' ORDER BY prices.currency ASC, price_types.name DESC, prices.quantity ASC";
				// print $sql;
					if($query->sql($sql)) {

					$prices = $query->results();

					// precalculate details
					foreach($prices as $i => $price) {
						$vat = $price["price"] * (1 - (1 / (1 + ($price["vatrate"]/100))));
						$prices[$i]["vat"] = $vat;
						$prices[$i]["price_without_vat"] = $price["price"]-$vat;
					}

					return $prices;
				}
			}
			// get all prices for item
			else {
				$sql = "SELECT prices.id, prices.price, prices.currency, prices.quantity, vatrates.vatrate, price_types.name AS type, price_types.description FROM ".UT_ITEMS_PRICES." as prices, ".UT_VATRATES." as vatrates, ".UT_PRICE_TYPES." as price_types WHERE prices.item_id = $item_id AND vatrates.id = prices.vatrate_id AND price_types.id = prices.type_id ORDER BY prices.currency ASC, price_types.name DESC, prices.quantity ASC";
				if($query->sql($sql)) {

					$prices = $query->results();

					// precalculate details
					foreach($prices as $i => $price) {
						$vat = $price["price"] * (1 - (1 / (1 + ($price["vatrate"]/100))));
						$prices[$i]["vat"] = $vat;
						$prices[$i]["price_without_vat"] = $price["price"]-$vat;
					}

					return $prices;
				}
			}

		}

		// get all prices
		else {

			$sql = "SELECT prices.id, prices.item_id, prices.price, prices.currency, prices.quantity, vatrates.vatrate, price_types.name AS type, price_types.description FROM ".UT_ITEMS_PRICES." as prices, ".UT_VATRATES." as vatrates, ".UT_PRICE_TYPES." as price_types WHERE vatrates.id = prices.vatrate_id AND price_types.id = prices.type_id ORDER BY prices.currency ASC, price_types.name DESC, prices.quantity ASC";
			if($query->sql($sql)) {

				$prices = $query->results();

				// precalculate details
				foreach($prices as $i => $price) {
					$vat = $price["price"] * (1 - (1 / (1 + ($price["vatrate"]/100))));
					$prices[$i]["vat"] = $vat;
					$prices[$i]["price_without_vat"] = $price["price"]-$vat;
				}

				return $prices;
			}				

		}

		// no matching prices found
		return false;
	}


	// get subscription method for item_id
	// maintain $_options parameter despite only one option for now (could be more in the future)
	function getSubscriptionMethod($_options=false) {

		$item_id = false;

		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {
					case "item_id"     : $item_id        = $_value; break;
				}
			}
		}

		$query = new Query();

		if($item_id) {

			$sql = "SELECT * FROM ".UT_SUBSCRIPTION_METHODS." as methods, ".UT_ITEMS_SUBSCRIPTION_METHOD." as method WHERE method.item_id = $item_id AND methods.id = method.subscription_method_id"; 
			if($query->sql($sql)) {
				return $query->result(0);
			}
			
		}

		return false;
	}

}

?>