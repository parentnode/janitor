<?php
/**
* This file contains HTML-element output functions for Janitor Backend
*
*/
class JanitorHTML {

	function __construct() {

		// current controller path
		$this->path = preg_replace("/\.php$/", "", $_SERVER["SCRIPT_NAME"]);

	}


	// READ THIS:

	// This is helper functions for backend templates
	// Add $_options to functions when extension is needed



	// find media with matching variant or simply first media
	// removes media from media stack (to make it easier to loop through remaining media later)
	function getMedia(&$item, $variant=false) {

		$media = false;

		if(!$variant && isset($item["mediae"]) && $item["mediae"]) {
			$media = array_shift($item["mediae"]);
		}
		else if(isset($item[$variant])) {

			$media = $item[$variant];
			unset($item[$variant]);
		}
		else if(isset($item["mediae"]) && $item["mediae"]) {
			foreach($item["mediae"] as $index => $media_item) {
				if($index == $variant) {

					$media = $item["mediae"][$variant];
					unset($item["mediae"][$variant]);
				}
			}
		}

		return $media;
	}

	// provide media info for JS
	function jsMedia(&$item, $variant=false) {

		$media = $this->getMedia($item, $variant);
		
		return $media ? (" format:".$media["format"]." variant:".$media["variant"]) : "";
	}

	// data elements for JS interaction
	// TODO: implement a filter, to avoid printing all data attributes every time
	function jsData($_options = false) {
		global $page;

		$_ = '';

		$_ .= ' data-csrf-token="'.session()->value("csrf").'"';

		if(!$_options || array_search("order", $_options) !== false) {
			$_ .= ' data-item-order="'.$page->validPath($this->path."/updateOrder").'"'; 
		}

		if(!$_options || array_search("tags", $_options) !== false) {
			$_ .= ' data-tag-get="'.$page->validPath("/janitor/admin/items/tags").'"'; 
			$_ .= ' data-tag-delete="'.$page->validPath($this->path."/deleteTag").'"';
			$_ .= ' data-tag-add="'.$page->validPath($this->path."/addTag").'"';
		}

		if(!$_options || array_search("media", $_options) !== false) {
			$_ .= ' data-media-order="'.$page->validPath($this->path."/updateMediaOrder").'"';
			$_ .= ' data-media-delete="'.$page->validPath($this->path."/deleteMedia").'"';
			$_ .= ' data-media-name="'.$page->validPath($this->path."/updateMediaName").'"';
		}

		if(!$_options || array_search("comments", $_options) !== false) {
			$_ .= ' data-comment-update="'.$page->validPath($this->path."/updateComment").'"';
			$_ .= ' data-comment-delete="'.$page->validPath($this->path."/deleteComment").'"';
		}

		if(!$_options || array_search("prices", $_options) !== false) {
			$_ .= ' data-price-update="'.$page->validPath($this->path."/updatePrice").'"';
			$_ .= ' data-price-delete="'.$page->validPath($this->path."/deletePrice").'"';
		}

		if(!$_options || array_search("qna", $_options) !== false) {
			$_ .= ' data-qna-update="'.$page->validPath($this->path."/updateQnA").'"';
			$_ .= ' data-qna-delete="'.$page->validPath($this->path."/deleteQnA").'"';
		}

		return $_;
	}



	// NEW

	// "list" button on new page
	function newList($_options = false) {
		global $model;

		$label = "List";
		$action = "list";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"           : $label            = $_value; break;
					case "action"          : $action           = $_value; break;
				}
			}
		}

		$_ = '';

		$_ .= $model->link($label, $this->path."/".$action, array("class" => "button primary key:esc", "wrapper" => "li.back"));

		return $_;
	}

	// default actions inside model form on new page
	function newActions($_options = false) {
		global $model;

		// standard settings
		$standard = array(
			"cancel" => array(
				"label" => "Cancel",
				"url" => $this->path."/list",
				"options" => array("class" => "button key:esc", "wrapper" => "li.cancel")
			),
			"save" => array(
				"label" => "Save and continue",
				"options" => array("class" => "primary key:s", "wrapper" => "li.save")
			)
		);

		// extend with these settings
		$modify = "";
		$extend = "";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "modify"           : $modify            = $_value; break;
					case "extend"           : $extend            = $_value; break;
				}
			}
		}

		if($modify) {
			foreach($modify as $index => $values) {
				if(isset($standard[$index])) {
					// value can be set to false to disable button
					if($values) {
						foreach($values as $attribute => $value) {
							$standard[$index][$attribute] = $value;
						}
					}
					else {
						$standard[$index] = $values;
					}
				}
			}
		}

		// TODO: implement extend options


		$_ = '';

		$_ .= '<ul class="actions">';

		// Cancel button
		if($standard["cancel"]) {
			$_ .= $model->link($standard["cancel"]["label"], $standard["cancel"]["url"], $standard["cancel"]["options"]);
		}

		// Save button
		if($standard["save"]) {
			$_ .= $model->submit($standard["save"]["label"], $standard["save"]["options"]);
		}

		$_ .= '</ul>';

		return $_;
	}



	// LIST

	// "new" button on list page
	function listNew($_options = false) {
		global $model;

		$label = "New";
		$action = "new";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"           : $label            = $_value; break;
					case "action"          : $action           = $_value; break;
				}
			}
		}

		$_ = '';

		$_ .= $model->link($label, $this->path."/".$action, array("class" => "button primary key:n", "wrapper" => "li.new"));

		return $_;
	}

	// default actions for list item on list page
	function listActions($item, $_options = false) {
		global $model;

		// standard settings
		$standard = array(
			"edit" => array(
				"label" => "Edit",
				"url" => $this->path."/edit/".$item["id"],
				"options" => array("class" => "button", "wrapper" => "li.edit")
			),
			"delete" => array(
				"label" => "Delete",
				"url" => $this->path."/delete/".$item["id"]
			),
			"status" => array(
				"label_enable" => "Enable",
				"label_disable" => "Disable",
				"url" => $this->path."/status"
			)
		);

		// extend with these settings
		$modify = "";
		$extend = "";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "modify"           : $modify            = $_value; break;
					case "extend"           : $extend            = $_value; break;
				}
			}
		}

		if($modify) {
			foreach($modify as $index => $values) {
				if(isset($standard[$index])) {
					// value can be set to false to disable button
					if($values) {
						foreach($values as $attribute => $value) {
							$standard[$index][$attribute] = $value;
						}
					}
					else {
						$standard[$index] = $values;
					}
				}
			}
		}


		// TODO: implement extend options


		$_ = '';


		$_ .= '<ul class="actions">';

		// Edit button
		if($standard["edit"]) {
			$_ .= $model->link($standard["edit"]["label"], $standard["edit"]["url"], $standard["edit"]["options"]);
		}

		// Delete button
		if($standard["delete"]) {
			$_ .= $this->deleteButton($standard["delete"]["label"], $standard["delete"]["url"], array("js" => true));
		}

		// Status button
		if($standard["status"]) {
			$_ .= $this->statusButton($standard["status"]["label_enable"], $standard["status"]["label_disable"], $standard["status"]["url"], $item, array("js" => true));
		}

		// $_ .= $model->link("Edit", $this->path."/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit"));
		// $_ .= $this->deleteButton("Delete", $this->path."/delete/".$item["id"], array("js" => true));
		// $_ .= $this->statusButton("Enable", "Disable", $this->path."/status", $item, array("js" => true));
		$_ .= '</ul>';

		return $_;
	}



	// EDIT

	// default back/delete/status buttons for edit page
	function editGlobalActions($item, $_options = false) {
		global $model;

		// standard settings
		$standard = array(
			"list" => array(
				"label" => "List",
				"url" => $this->path."/list",
				"options" => array("class" => "button", "wrapper" => "li.cancel")
			),
			"delete" => array(
				"label" => "Delete",
				"url" => $this->path."/delete/".$item["id"]
			),
			"status" => array(
				"label_enable" => "Enable",
				"label_disable" => "Disable",
				"url" => $this->path."/status"
			)
		);
		// extend with these settings
		$modify = "";
		$extend = "";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "modify"           : $modify            = $_value; break;
					case "extend"           : $extend            = $_value; break;
				}
			}
		}

		if($modify) {
			foreach($modify as $index => $values) {
				if(isset($standard[$index])) {
					// value can be set to false to disable button
					if($values) {
						foreach($values as $attribute => $value) {
							$standard[$index][$attribute] = $value;
						}
					}
					else {
						$standard[$index] = $values;
					}
				}
			}
		}



		$_ = '';

		// BACK AND DELETE
		$_ .= '<ul class="actions i:defaultEditActions item_id:'.$item["id"].'" data-csrf-token="'.session()->value("csrf").'">';
		if($standard["list"]) {
			$_ .= $model->link($standard["list"]["label"], $standard["list"]["url"], $standard["list"]["options"]);
		}
		if($standard["delete"]) {
			$_ .= $this->deleteButton($standard["delete"]["label"], $standard["delete"]["url"], array("js" => true));
		}
		$_ .= '</ul>';

		// STATUS
		if($standard["status"]) {
			$_ .= '<div class="status i:defaultEditStatus item_id:'.$item["id"].'" data-csrf-token="'.session()->value("csrf").'">';
			$_ .= '<ul class="actions">';
			$_ .= $this->statusButton($standard["status"]["label_enable"], $standard["status"]["label_disable"], $standard["status"]["url"], $item, array("js" => true));
			$_ .= '</ul>';
			$_ .= '</div>';
		}

		return $_;
	}

	// default actions inside model form on edit page
	function editActions($item, $_options = false) {
		global $model;

		$_ = '';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save"));
		$_ .= '</ul>';

		return $_;
	}

	// edit tags form for edit page
	function editTags($item, $_options = false) {
		global $model;

		$_ = '';

		$_ .= '<div class="tags i:defaultTags i:collapseHeader item_id:'.$item["id"].'"'.$this->jsData(["tags"]).'>';
		$_ .= '<h2>Tags ('.($item["tags"] ? count($item["tags"]) : 0).')</h2>';
		$_ .= $this->tagList($item["tags"]);
		$_ .= '</div>';

		return $_;
	}

	// edit media form for edit page
	function editMedia($item, $_options = false) {
		global $model;


		$type = "mediae";
		$label = "Media";
		$class = "i:addMedia i:collapseHeader";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "type"              : $type               = $_value; break;
					case "label"             : $label              = $_value; break;
					case "class"             : $class              = $_value; break;
				}
			}
		}

		$_ = '';

		$_ .= '<div class="media '.$type.' '.$class.' sortable item_id:'.$item["id"].'"'.$this->jsData().'>';
		$_ .= '<h2>'.$label.'</h2>';
		$_ .= $model->formStart($this->path."/addMedia/".$item["id"], array("class" => "upload labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= $model->input("mediae");
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Add image", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();

		$_ .= '<ul class="mediae">';

		
		if($item["mediae"]) {
			foreach($item["mediae"] as $variant => $media) {

				if($type == "mediae") {

					if(preg_match("/^(jpg|png)$/", $media["format"])) {
						$_ .= '<li class="media image variant:'.$variant.' media_id:'.$media["id"].' format:'.$media["format"].' width:'.$media["width"].' height:'.$media["height"].'">';
						$_ .= '<a href="/images/'.$item["id"].'/'.$variant.'/x150.'.$media["format"].'">'.$media["name"].'</a>';
					}
					else if(preg_match("/^(mp3|ogv)$/", $media["format"])) {
						$_ .= '<li class="media audio variant:'.$variant.' media_id:'.$media["id"].' format:'.$media["format"].'">';
						$_ .= '<a href="/audios/'.$item["id"].'/'.$variant.'/128.'.$media["format"].'">'.$media["name"].'</a>';
					}
					else if(preg_match("/^(mp4|mov)$/", $media["format"])) {
						$_ .= '<li class="media video variant:'.$variant.' media_id:'.$media["id"].' format:'.$media["format"].' width:'.$media["width"].' height:'.$media["height"].'">';
						$_ .= '<a href="/videos/'.$item["id"].'/'.$variant.'/x150.'.$media["format"].'">'.$media["name"].'</a>';
					}
					$_ .= '<p>'.$media["name"].'</p>';
					$_ .= '</li>';

				}
				else if($type == "variant") {

					if(preg_match("/^(jpg|png)$/", $media["format"])) {
						$_ .= '<li class="media image variant:'.$variant.' media_id:'.$media["id"].' format:'.$media["format"].' width:'.$media["width"].' height:'.$media["height"].'">';
						$_ .= '<a href="/images/'.$item["id"].'/'.$variant.'/x150.'.$media["format"].'">'.$variant.'</a>';
					}
					else if(preg_match("/^(mp3|ogv)$/", $media["format"])) {
						$_ .= '<li class="media audio variant:'.$variant.' media_id:'.$media["id"].' format:'.$media["format"].'">';
						$_ .= '<a href="/audios/'.$item["id"].'/'.$variant.'/128.'.$media["format"].'">'.$variant.'</a>';
					}
					else if(preg_match("/^(mp4|mov)$/", $media["format"])) {
						$_ .= '<li class="media video variant:'.$variant.' media_id:'.$media["id"].' format:'.$media["format"].' width:'.$media["width"].' height:'.$media["height"].'">';
						$_ .= '<a href="/videos/'.$item["id"].'/'.$variant.'/x150.'.$media["format"].'">'.$variant.'</a>';
					}
					$_ .= '<p>'.$variant.'</p>';
					$_ .= '</li>';
				}
			}
		}

		$_ .= '</ul>';
		$_ .= '</div>';

		return $_;
	}

	// edit single media form for edit page
	function editSingleMedia(&$item, $_options = false) {
		global $model;

		$variant = "single_media";
		$label = "Single media";
		$class = "";
		$init_class = "i:addMediaSingle i:collapseHeader";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "variant"           : $variant            = $_value; break;
					case "label"             : $label              = $_value; break;
					case "class"             : $class              = $_value; break;
					case "init_class"        : $init_class         = $_value; break;
				}
			}
		}

		if(!preg_match("/i:[a-z]+/", $class)) {
			$class = $class." ".$init_class;
		}

		// get media
		$media = $this->getMedia($item, $variant);
		// if($media) {
		// 	$class_input = "uploaded";
		// }

		$_ = '';

		// default view
		$_ .= '<div class="media single_media '.$variant.' '.$class.' variant:'.$variant.' item_id:'.$item["id"].' format:'.($media ? $media["format"] : "").'"'.$this->jsData().'>';
		$_ .= '<h2>'.$label.'</h2>';

		$_ .= $model->formStart($this->path."/addSingleMedia/".$item["id"]."/".$variant, array("class" => "upload labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= $model->input($variant, array("value" => $media));
		$_ .= '</fieldset>';
		$_ .= $model->formEnd();

		if($media) {
			$_ .= '<div class="file">';
			if(preg_match("/^(jpg|png)$/", $media["format"])) {
				$_ .= '<a href="/images/'.$item["id"].'/'.$variant.'/480x.'.$media["format"].'">'.$media["name"].'</a>';
			}
			else if(preg_match("/^(mp3|ogv)$/", $media["format"])) {
				$_ .= '<a href="/audios/'.$item["id"].'/'.$variant.'/128.'.$media["format"].'">'.$media["name"].'</a>';
			}
			else if(preg_match("/^(mp4|mov)$/", $media["format"])) {
				$_ .= '<a href="/videos/'.$item["id"].'/'.$variant.'/480x.'.$media["format"].'">'.$media["name"].'</a>';
			}
			$_ .= '<p>'.$media["name"].'</p>';
			$_ .= '</div>';
		}

		$_ .= '</div>';

		return $_;
	}


	// edit Comments form for edit page
	function editComments($item, $_options = false) {
		global $model;

		$_ = '';

		$_ .= '<div class="comments i:defaultComments i:collapseHeader item_id:'.$item["id"].'"'.$this->jsData(["comments"]).'>';
		$_ .= '<h2>Comments ('.($item["comments"] ? count($item["comments"]) : 0).')</h2>';

		$_ .= $this->commentList($item["comments"]);

		$_ .= $model->formStart($this->path."/addComment/".$item["id"], array("class" => "labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= $model->input("comment", array("id" => "comment_".$item["id"]));
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Add new comment", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();
		$_ .= '</div>';

		return $_;
	}

	// edit Prices form for edit page
	function editPrices($item, $_options = false) {
		global $model;


		$_ = '';

		$_ .= '<div class="prices i:defaultPrices i:collapseHeader item_id:'.$item["id"].'"'.$this->jsData(["prices"]).'>';
		$_ .= '<h2>Prices</h2>';

		$_ .= $this->priceList($item["prices"]);

		$_ .= $model->formStart($this->path."/addPrice/".$item["id"], array("class" => "labelstyle:inject"));
		// Use default vatrate for now
		$_ .= $model->input("currency", array("type" => "hidden", "value" => "DKK"));
		$_ .= $model->input("vatrate_id", array("type" => "hidden", "value" => "1"));
		$_ .= '<fieldset>';
		$_ .= $model->input("price");
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Add new price", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();
		$_ .= '</div>';

		return $_;
	}


	// edit Subscription method form for edit page
	// TODO: should also list subscribers if current user has user listing permissions
	function editSubscriptionMethod($item, $_options = false) {
		global $model;
		global $page;


		$subscription_options = $model->toOptions($page->subscriptionMethods(), "id", "name", array("add" => array("" => " - ")));

		$_ = '';

		$_ .= '<div class="subscription_method i:defaultSubscriptionmethod i:collapseHeader item_id:'.$item["id"].'"'.$this->jsData(["subscriptions"]).'>';
		$_ .= '<h2>Subscription method</h2>';
		$_ .= '<dl class="settings">';
			$_ .= '<dt class="subscription_method">Subscription renewal:</dt>';
			$_ .= '<dd class="subscription_method">'.($item["subscription_method"] ? $item["subscription_method"]["name"] : "No renewal").'</dd>';
		$_ .= '</dl>';

		
		$_ .= '<div class="change_subscription_method">';
			$_ .= $model->formStart($this->path."/updateSubscriptionMethod/".$item["id"], array("class" => "labelstyle:inject"));
			$_ .= '<fieldset>';
				$_ .= $model->input("subscription_method", array("type" => "select", "options" => $subscription_options, "value" => ""));
			$_ .= '</fieldset>';

			$_ .= '<ul class="actions">';
				$_ .= $model->submit("Update", array("class" => "primary", "wrapper" => "li.save"));
			$_ .= '</ul>';
			$_ .= $model->formEnd();
		$_ .= '</div>';


		// // does current user have global user privileged
		// // then ok to list subscriber info
		// if($page->validatePath("/janitor/admin/user/list")) {
		//
		// 	include_once("classes/users/superuser.class.php");
		// 	$UC = new SuperUser();
		// 	$subscribers = $UC->getSubscriptions(array("item_id" => $item["id"]));
		//
		// 	$_ .= $this->subscriberList($subscribers);
		//
		// }

		$_ .= '</div>';

		return $_;
	}



	// // edit Comments form for edit page
	// function editQnA($item, $_options = false) {
	// 	global $model;
	//
	// 	$_ = '';
	//
	// 	$_ .= '<div class="qna i:defaultQnA item_id:'.$item["id"].'"'.$this->jsData().'>';
	// 	$_ .= '<h2>Questions and Answers</h2>';
	//
	// 	$_ .= $this->qnaList($item["qna"]);
	//
	// 	$_ .= $model->formStart($this->path."/addQuestion/".$item["id"], array("class" => "labelstyle:inject"));
	// 	$_ .= '<fieldset>';
	// 	$_ .= $model->input("question", array("id" => "question_".$item["id"]));
	// 	$_ .= '</fieldset>';
	//
	// 	$_ .= '<ul class="actions">';
	// 	$_ .= $model->submit("Add new question", array("class" => "primary", "wrapper" => "li.save"));
	// 	$_ .= '</ul>';
	// 	$_ .= $model->formEnd();
	// 	$_ .= '</div>';
	//
	// 	return $_;
	// }


	// simple tag list
	function tagList($tags) {

		$_ = '';

		$_ .= '<ul class="tags">';
		if($tags) {
			foreach($tags as $tag) {
				$_ .= '<li class="tag '.$tag["context"].'"><span class="context">'.$tag["context"].'</span>:<span class="value">'.$tag["value"].'</span></li>';
			}
		}
		$_ .= '</ul>';

		return $_;
	}


	// simple comment list
	function commentList($comments) {

		$_ = '';

		$_ .= '<ul class="comments">';
		if($comments) {
			foreach($comments as $comment) {
				$_ .= '<li class="comment comment_id:'.$comment["id"].'">';
					$_ .= '<ul class="info">';
						$_ .= '<li class="user">'.$comment["nickname"].'</li>';
						$_ .= '<li class="created_at">'. date("Y-m-d, H:i", strtotime($comment["created_at"])).'</li>';
					$_ .= '</ul>';
					$_ .= '<p class="comment">'.$comment["comment"].'</p>';
				$_ .= '</li>';
			}
		}
		$_ .= '</ul>';

		return $_;
	}


	// simple price list
	function priceList($prices) {

		$_ = '';

		$_ .= '<ul class="prices">';
		if($prices) {
			foreach($prices as $price) {
				$_ .= '<li class="pricedetails price_id:'.$price["id"].'">';
					$_ .= '<ul class="info">';
						$_ .= '<li class="price">'.$price["formatted_price"].'</li>';
						$_ .= '<li class="currency">'. $price["currency"].'</li>';
						$_ .= '<li class="vatrate">('. $price["vatrate"].'%)</li>';
					$_ .= '</ul>';
				$_ .= '</li>';
			}
		}
		$_ .= '</ul>';

		return $_;
	}







	function listTodos($item) {
		global $model;

		$IC = new Items();

		$_ = '';
		$_ .= '<div class="todos i:defaultTodos item_id:'.$item["id"].'"'.$this->jsData().'>';
		$_ .= '<h2>TODOs</h2>';

		$todo_tag = $IC->getTags(array("item_id" => $item["item_id"], "context" => "todo"));
		if($todo_tag) {
			$todos = $IC->getItems(array("itemtype" => "todo", "status" => 1, "tags" => $todo_tag[0]["context"].":".$todo_tag[0]["value"], "extend" => array("user" => true)));

			if($todos) {
			$_ .= '<ul class="todos">';
				foreach($todos as $todo) {
					$_ .= '<li class="todo todo_id:'.$todo["id"].'">';
						$_ .= stringOr($model->link($todo["name"], "/janitor/admin/todo/edit/".$todo["id"], array("target" => "_blank")), $todo["name"]);
						$_ .= ", Assigned to: ".$todo["user_nickname"];
					$_ .= '</li>';
				}
			$_ .= '</ul>';
			}
			else {
				$_ .= '<p>No TODOs</p>';
			}
			
		}
		else {
			$_ .= '<p>No TODOs</p>';
		}

		$_ .= '</div>';

		return $_;
	}


	// simple QnA list
	// QnA list is different because it links to separate item
	function listQnas($item) {
		global $model;

		// look for QnA tag on item
		// if QnA tag exists, find QnA items and list them
		$IC = new Items();

		$_ = '';
		$_ .= '<div class="qnas i:defaultQnas item_id:'.$item["id"].'"'.$this->jsData().'>';
		$_ .= '<h2>Questions and Answers</h2>';


		$qna_tag = $IC->getTags(array("item_id" => $item["item_id"], "context" => "qna"));
		if($qna_tag) {
			$qnas = $IC->getItems(array("itemtype" => "qna", "status" => 1, "tags" => $qna_tag[0]["context"].":".$qna_tag[0]["value"], "extend" => array("tags" => true, "user" => true)));

			if($qnas) {
			$_ .= '<ul class="qnas">';
				foreach($qnas as $qna) {
					$_ .= '<li class="qna qna_id:'.$qna["id"].'">';
						$_ .= '<ul class="info">';
							$_ .= '<li class="user">'.$qna["user_nickname"].'</li>';
							$_ .= '<li class="created_at">'. date("Y-m-d, H:i", strtotime($qna["created_at"])).'</li>';
						$_ .= '</ul>';
						$_ .= '<p class="question">'.stringOr($model->link($qna["name"], "/janitor/admin/qna/edit/".$qna["id"], array("target" => "_blank")), $qna["name"]).'</p>';

						// is answer available
						if($qna["answer"]) {
							$_ .= '<p class="answer">'.$qna["answer"].'</p>';
						}
						else {
							$_ .= '<p class="answer">No answer yet</p>';
						}
					$_ .= '</li>';
				}
			$_ .= '</ul>';
			}
			else {
				$_ .= '<p>No questions</p>';
			}
		}
		else {
			$_ .= '<p>No questions</p>';
		}

		$_ .= '</div>';

		return $_;
	}



	/**
	* Delete item
	*/
	function deleteButton($name, $action, $_options = false) {
		global $page;
		global $HTML;

		if(!$page->validatePath($action)) {
			return "";
		}

		$js = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "js"           : $js            = $_value; break;

				}
			}
		}

		if($js) {
			$_ = '<li class="delete" data-item-delete="'.$action.'">';
		}
		else {
			$_ = '<li class="delete">';

			$_ .= $HTML->formStart($action);
			$_ .= '<input type="submit" value="'.$name.'" name="delete" class="button delete" />';
			$_ .= $HTML->formEnd();
		}

		$_ .= '</li>';

		return $_;
	}


	/**
	* Change status of item
	*/
	function statusButton($enable_label, $disable_label, $action, $item, $_options = false) {

		global $page;
		global $HTML;

		if(!$page->validatePath($action)) {
			return "";
		}

		$status_states = array(
			0 => "disabled",
			1 => "enabled"
		);

		$js = false;
		$_ = '';

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "js"                     : $js                      = $_value; break;

				}
			}
		}

		if($item && $item["id"] && isset($item["status"])) {

			$state_class = $status_states[$item["status"]];
			$change_to = ($item["status"]+1)%2;

			if($js) {
				$_ .= '<li class="status '.$state_class.'" data-item-status="'.$action.'">';
			}
			else {
				$_ .= '<li class="status '.$state_class.'">';

				$_ .= $HTML->formStart($action.'/'.$item["id"].'/0', array("class" => "disable"));
				$_ .= '<input type="submit" value="'.$disable_label.'" name="disable" class="button status" />';
				$_ .= $HTML->formEnd();

				$_ .= $HTML->formStart($action.'/'.$item["id"].'/1', array("class" => "enable"));
				$_ .= '<input type="submit" value="'.$enable_label.'" name="enable" class="button status" />';
				$_ .= $HTML->formEnd();
			}

			$_ .= '</li>';

		}

		return $_;
	}



	// USER DASHBOARDS


	// Current user TODOs dashboard
	function listUserTodos() {
		global $HTML;
		global $page;

		$_ = '';

		// only show todos if user has access
		if($page->validatePath("/janitor/admin/todo")) {
			
			$IC = new Items();
			$model = $IC->typeObject("todo");
			$todos = $IC->getItems(array("itemtype" => "todo", "where" => "todo.priority = 20", "user_id" => session()->value("user_id"), "extend" => array("tags" => true)));

			$_ .= '<div class="todos">';
			$_ .= '<h2>TODOs</h2>';

			if($todos) {
				$_ .= '<ul class="todos">';
				foreach($todos as $todo) {
					$_ .= '<li class="todo todo_id:'.$todo["id"].'">';
						$_ .= '<h3>'.stringOr($HTML->link($todo["name"], "/janitor/admin/todo/edit/".$todo["id"], array("target" => "_blank")), $todo["name"]).'</h3>';
						$_ .= $this->tagList($todo["tags"]);
					$_ .= '</li>';
				}
				$_ .= '</ul>';
			}
			else {
				$_ .= '<p>No TODOs</p>';
			}

			$_ .= '</div>';
		}

		return $_;
	}


	// Current open questions dashboard
	function listOpenQuestions() {
		global $HTML;
		global $page;


		$_ = '';

		// only show todos if user has access
		if($page->validatePath("/janitor/admin/qna")) {

			$IC = new Items();
			$qnas = $IC->getItems(array("itemtype" => "qna", "user_id" => session()->value("user_id"), "extend" => array("tags" => true)));

			$_ .= '<div class="qnas">';
			$_ .= '<h2>Unanswered questions</h2>';

			if($qnas) {
				$_ .= '<ul class="qnas">';
				foreach($qnas as $qna) {
					if(!$qna["answer"]) {
					$_ .= '<li class="qna qna_id:'.$qna["id"].'">';
						$_ .= '<h3>'.stringOr($HTML->link($qna["name"], "/janitor/admin/qna/edit/".$qna["id"], array("target" => "_blank")), $qna["name"]).'</h3>';
						$_ .= $this->tagList($qna["tags"]);
					$_ .= '</li>';
					}
				}
				$_ .= '</ul>';
			}
			else {
				$_ .= '<p>No questions</p>';
			}

			$_ .= '</div>';

		}

		return $_;
	}


}

// create standalone instance to make Janitor available 
// TODO: consider instantiating in Template using this only?
$JML = new JanitorHTML();
?>