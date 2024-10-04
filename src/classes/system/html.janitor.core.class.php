<?php
/**
* This file contains HTML-element output functions for Janitor Backend
*
*/
class JanitorHTMLCore {


	public $path;


	function __construct() {

		// current controller path
		$this->path = preg_replace("/\.php$/", "", $_SERVER["SCRIPT_NAME"]);

	}


	// READ THIS:

	// This is helper functions for backend templates
	// Add $_options to functions when extension is needed



	// NEW

	// "list" button on new page
	function newList($_options = false) {
		global $model;

		$label = "List";
		$action = $this->path."/list";

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

		$_ .= $model->link($label, $action, array("class" => "button primary key:esc", "wrapper" => "li.back"));

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
				"wrapper" => "li.delete",
				"static" => true,
				"url" => $this->path."/delete/".$item["id"],
				"success-location" => false,
				"class" => "",
			),
			"status" => array(
				"label_enable" => "Enable",
				"label_disable" => "Disable",
				"url" => $this->path."/status"
			)
		);

		if(isset($item["dependencies"]) && $item["dependencies"]) {
			$standard["delete"]["class"] = "disabled";
			$standard["delete"]["wrapper"] = "li.delete.has_dependencies";
		}

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
		if($extend) {
			foreach($extend as $index => $values) {
				if(!isset($standard[$index])) {
					$standard[$index] = $values;
				}
			}
		}

		$_ = '';


		$_ .= '<ul class="actions">';


		foreach($standard as $button => $data) {
			if(!preg_match("/edit|delete|status/", $button)) {
				if(isset($data["type"]) && $data["type"] == "onebuttonform") {
					$_ .= $model->oneButtonForm($data["label"], $data["url"], array(
						"wrapper" => $data["wrapper"],
						"success-function" => $data["success-function"]
					));
				}
				else {
					$_ .= $model->link($data["label"], $data["url"], $data["options"]);
				}
			}
		}

		// Edit button
		if($standard["edit"]) {
			$_ .= $model->link($standard["edit"]["label"], $standard["edit"]["url"], $standard["edit"]["options"]);
		}

		// Delete button
		if($standard["delete"]) {
			$_ .= $model->oneButtonForm($standard["delete"]["label"], $standard["delete"]["url"], array(
				"js" => true,
				"wrapper" => $standard["delete"]["wrapper"],
				"static" => $standard["delete"]["static"],
				"success-location" => $standard["delete"]["success-location"],
				"class" => $standard["delete"]["class"],
			));
		}

		// Status button
		if($standard["status"]) {
			$_ .= $this->statusButton($standard["status"]["label_enable"], $standard["status"]["label_disable"], $standard["status"]["url"], $item, array("js" => true));
		}


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
			"duplicate" => array(
				"label" => "Duplicate",
				"wrapper" => "li.duplicate",
				"url" => $this->path."/duplicate/".$item["id"],
				"success-function" => "duplicated"
			),
			"delete" => array(
				"label" => "Delete",
				"wrapper" => "li.delete",
				"url" => $this->path."/delete/".$item["id"],
				"success-location" => $this->path."/list",
				"class" => ""
			),
			"status" => array(
				"label_enable" => "Enable",
				"label_disable" => "Disable",
				"url" => $this->path."/status"
			)
		);

		if(isset($item["dependencies"]) && $item["dependencies"]) {
			$standard["delete"]["class"] = "disabled";
			$standard["delete"]["wrapper"] = "li.delete.has_dependencies";
		}

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


		if($extend) {
			foreach($extend as $index => $values) {
				if(!isset($standard[$index])) {
					$standard[$index] = $values;
				}
			}
		}


		$_ = '';

		// BACK AND DELETE
		$_ .= '<ul class="actions i:defaultEditActions">';
		if($standard["list"]) {
			$_ .= $model->link($standard["list"]["label"], $standard["list"]["url"], $standard["list"]["options"]);
		}

		if($standard["delete"]) {
			$_ .= $model->oneButtonForm($standard["delete"]["label"], $standard["delete"]["url"], array(
				"wrapper" => $standard["delete"]["wrapper"],
				"success-location" => $standard["delete"]["success-location"],
				"class" => $standard["delete"]["class"]
			));
		}

		if($standard["duplicate"]) {
			$_ .= $model->oneButtonForm($standard["duplicate"]["label"], $standard["duplicate"]["url"], array(
				"wrapper" => $standard["duplicate"]["wrapper"],
				"success-function" => $standard["duplicate"]["success-function"]
			));
		}

		foreach($standard as $button => $data) {
			if(!preg_match("/list|delete|duplicate|status/", $button)) {
				if(isset($data["type"]) && $data["type"] == "onebuttonform") {
					$_ .= $model->oneButtonForm($data["label"], $data["url"], array(
						"wrapper" => $data["wrapper"],
						"success-function" => $data["success-function"]
					));
				}
				else {
					$_ .= $model->link($data["label"], $data["url"], $data["options"]);
				}
			}
		}

		$_ .= '</ul>';

		// STATUS
		if($standard["status"]) {
			$_ .= '<div class="status i:defaultEditStatus item_id:'.$item["id"].'" data-csrf-token="'.security()->getValue("csrf").'">';
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

	// edit owner form for edit page
	function editOwner($item, $_options = false) {
		global $model;

		$_ = '';


		$IC = new Items();
		$owner = $IC->getOwners(["item_id" => $item["id"]]);
		$_ .= '<div class="owner i:defaultOwner i:collapseHeader item_id:'.$item["id"].'">';
		$_ .= '<h2>Owner</h2>';

		$_ .= '<fieldset>';
			$_ .= '<h3>Current owner</h3>';
			$_ .= '<p class="current_owner">'.$owner["nickname"]."</p>";
		$_ .= '</fieldset>';

		if(security()->validPath($this->path."/updateOwner")) {
			$owner_options = $model->toOptions($IC->getOwners(), "id", "nickname");

			$_ .= '<div class="change_ownership">';
			
				$_ .= $model->formStart($this->path."/updateOwner/".$item["id"], array("class" => "labelstyle:inject"));
					$_ .= $model->input("return_to", array("type" => "hidden", "value" => $this->path."/edit/".$item["id"]));
				$_ .= '<fieldset>';
					$_ .= '<h3>Change owner</h3>';
					$_ .= $model->input("item_ownership", array("type" => "select", "options" => $owner_options, "value" => $item["user_id"]));
				$_ .= '</fieldset>';

				$_ .= '<ul class="actions">';
					$_ .= $model->submit("Update", array("class" => "primary", "wrapper" => "li.save"));
				$_ .= '</ul>';
				$_ .= $model->formEnd();
			$_ .= '</div>';		

		}

		$_ .= '</div>';

		return $_;
	}

	// edit sindex form for edit page (Currently only showing sindex)
	function editSindex($item, $_options = false) {
		global $model;

		$_ = '';


		$_ .= '<div class="sindex i:defaultSindex i:collapseHeader item_id:'.$item["id"].'" data-check-sindex="'.security()->validPath($this->path."/checkSindex/".$item["id"]).'">';
		$_ .= '<h2>sindex</h2>';
		$_ .= '<p>The <em>sindex</em> is a Search engine optimized item identified, typically used in URL\'s.</p>';
		$_ .= '<p>The <em>sindex</em> value is deducted from the item <em>name</em> when you create a new item in Janitor. While the name of the item may be updated later, the <em>sindex</em> value remains the same to avoid breaking links.</p>';
		$_ .= '<p>However, you can manually request an update of the <em>sindex</em> value – or specify a custom value for this item.</p>';


		// Update sindex
		$_ .= '<fieldset>';
			$_ .= '<h3>Current sindex value</h3>';
			$_ .= '<p class="current_sindex">'.$item["sindex"]."</p>";
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->oneButtonForm("Update sindex", "updateSindex/".$item["id"], array(
				"class" => "primary",
				 "wrapper" => "li.update", 
				 "confirm-value" => false)
			 );
		$_ .= '</ul>';


		// Set sindex manually
		$_ .= $model->formStart($this->path."/updateSindex/".$item["id"], array("class" => "manual_sindex labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= '<h3>Set sindex manually</h3>';

		$_ .= $model->input("item_sindex", array("value" => $item["sindex"]));
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Set custom sindex", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();
		
		$_ .= '</div>';

		return $_;
	}

	// edit sindex form for edit page (Currently only showing sindex)
	function editDeveloperSettings($item, $_options = false) {
		global $model;

		$title = "Developer settings";
		$paragraph = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "title"             : $title              = $_value; break;
					case "paragraph"         : $paragraph          = $_value; break;

				}
			}
		}

		$_ = '';

		$_ .= '<div class="developer i:defaultDeveloper i:collapseHeader item_id:'.$item["id"].'"'.$model->jsData(["comments"]).'>';
		$_ .= '<h2>'.$title.'</h2>';

		if($paragraph) {
			$_ .= '<p>'.$paragraph.'</p>';
		}

		$_ .= $model->formStart($this->path."/update/".$item["id"], array("class" => "labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= $model->input("classname", array("value" => $item["classname"]));
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Save", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();
		$_ .= '</div>';

		return $_;

	}


	// edit tags form for edit page
	function editTags($item, $_options = false) {
		global $HTML;

		$title = "Tags";
		$class = "i:defaultTags i:collapseHeader";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "class"             : $class              = $_value; break;
					case "title"             : $title              = $_value; break;

				}
			}
		}

		$_ = '';
		$_ .= '<div class="tags item_id:'.$item["id"].' '.$class.'"'.$HTML->jsData(["tags"]).'>';
		$_ .= '<h2>'.$title.' ('.($item["tags"] ? count($item["tags"]) : 0).')</h2>';
		$_ .= $this->tagList($item["tags"], $_options);
		$_ .= '</div>';

		return $_;
	}

	// edit mediae (multiple media) form for edit page
	function editMediae($item, $_options = false) {
		global $model;

		$variant = "mediae";
		$label = "Mediae";
		$class = "media i:addMedia i:collapseHeader";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "variant"           : $variant            = $_value; break;
					case "label"             : $label              = $_value; break;
					case "class"             : $class              = $_value; break;
				}
			}
		}

		$IC = new Items();
		$file_input_value = $IC->filterMediae($item, $variant);


		$_ = '';

		$_ .= '<div class="'.$variant.' '.$class.' variant:'.$variant.' sortable item_id:'.$item["id"].'"'.$model->jsData(["media"]).'>';

		$_ .= '<h2>'.$label.' ('.count($file_input_value).')</h2>';
		$_ .= $model->formStart($this->path."/addMedia/".$item["id"]."/".$variant, array("class" => "upload labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= $model->input($variant, ["value" => $file_input_value]);
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Add mediae", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();

		$_ .= '</div>';

		return $_;
	}

	// edit single media form for edit page
	function editSingleMedia($item, $_options = false) {
		global $model;

		$variant = "single_media";
		$label = "Single media";
		$class = "media single_media i:addMediaSingle i:collapseHeader";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "variant"           : $variant            = $_value; break;
					case "label"             : $label              = $_value; break;
					case "class"             : $class              = $_value; break;
				}
			}
		}

		// check if value exists
		$IC = new Items();
		$file_input_value = $IC->filterMediae($item, $variant);


		$_ = "";

		// default view
		$_ .= '<div class="'.$variant.' '.$class.' variant:'.$variant.' item_id:'.$item["id"].'"'.$model->jsData(["media"]).'>';
		$_ .= '<h2>'.$label.'</h2>';

		$_ .= $model->formStart($this->path."/addSingleMedia/".$item["id"]."/".$variant, array("class" => "upload labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= $model->input($variant, array("value" => $file_input_value));
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Add media", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();

		$_ .= '</div>';

		return $_;
	}


	// edit Comments form for edit page
	function editComments($item, $_options = false) {
		global $model;

		$_ = '';

		$_ .= '<div class="comments i:defaultComments i:collapseHeader item_id:'.$item["id"].'"'.$model->jsData(["comments"]).'>';
		$_ .= '<h2>Comments ('.($item["comments"] ? count($item["comments"]) : 0).')</h2>';

		$_ .= $this->commentList($item["comments"]);

		$_ .= $model->formStart($this->path."/addComment/".$item["id"], array("class" => "labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= $model->input("item_comment", array("id" => "comment_".$item["id"]));
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
		global $page;
		$query = new Query();


		$currency_options = $model->toOptions($page->currencies(), "id", "id");
		$default_currency = $page->currency();

		$vatrate_options = $model->toOptions($page->vatrates(), "id", "name");

		$type_options = $model->toOptions($page->price_types(["exclude_id" => $item["id"]]), "id", "description");

		


		$_ = '';

		$_ .= '<div class="prices i:defaultPrices i:collapseHeader item_id:'.$item["id"].'"'.$model->jsData(["prices"]).'>';
		$_ .= '<h2>Prices (' . ((isset($item["prices"]) && $item["prices"]) ? count($item["prices"]) : 0) .')</h2>';

		$_ .= $this->priceList($item["item_id"]);

		$_ .= $model->formStart($this->path."/addPrice/".$item["id"], array("class" => "labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= $model->input("item_price");
		$_ .= $model->input("item_price_currency", array("type" => "select", "options" => $currency_options, "value" => $default_currency));
		$_ .= $model->input("item_price_vatrate", array("type" => "select", "options" => $vatrate_options));
		$_ .= $model->input("item_price_type", array("type" => "select", "options" => $type_options));
		$_ .= $model->input("item_price_quantity");
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Add new price", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();
		$_ .= '</div>';

		return $_;
	}


	// edit Subscription method form for edit page
	// Only outputs if SITE_SUBSCRIPTIONS are on
	// TODO: should also list subscribers if current user has user listing permissions
	function editSubscriptionMethod($item, $_options = false) {

		$_ = '';

		if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS) {
			global $model;
			global $page;


			$subscription_options = $model->toOptions($page->subscriptionMethods(), "id", "name", array("add" => array("" => " - ")));

			$_ .= '<div class="subscription_method i:defaultSubscriptionmethod i:collapseHeader item_id:'.$item["id"].'"'.$model->jsData(["subscriptions"]).'>';
			$_ .= '<h2>Subscription settings</h2>';
			$_ .= '<dl class="info">';
				$_ .= '<dt class="subscription_method">Subscription period</dt>';
				$_ .= '<dd class="subscription_method">'.($item["subscription_method"] ? $item["subscription_method"]["name"] : "No renewal").'</dd>';
			$_ .= '</dl>';

		
			$_ .= '<div class="change_subscription_method">';
				$_ .= $model->formStart($this->path."/updateSubscriptionMethod/".$item["id"], array("class" => "labelstyle:inject"));
				$_ .= '<fieldset>';
					$_ .= $model->input("item_subscription_method", array("type" => "select", "options" => $subscription_options, "value" => ($item["subscription_method"] ? $item["subscription_method"]["id"] : "")));
				$_ .= '</fieldset>';

				$_ .= '<ul class="actions">';
					$_ .= $model->submit("Update", array("class" => "primary", "wrapper" => "li.save"));
				$_ .= '</ul>';
				$_ .= $model->formEnd();
			$_ .= '</div>';


			// // does current user have global user privileged
			// // then ok to list subscriber info
			// if($security()->validatePath("/janitor/admin/user/list")) {
			//
			// 	include_once("classes/users/superuser.class.php");
			// 	$UC = new SuperUser();
			// 	$subscribers = $UC->getSubscriptions(array("item_id" => $item["id"]));
			//
			// 	$_ .= $this->subscriberList($subscribers);
			//
			// }

			$_ .= '</div>';
		}

		return $_;
	}

	// edit Editors form for edit page
	function editEditors($item, $_options = false) {
		global $IC;
		global $model;

		$class = "all_items editors i:defaultEditors i:collapseHeader";

		$users = false;
		$users_options = false;


		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "users"                   : $users                   = $_value; break;
					case "users_options"           : $users_options           = $_value; break;

					case "class"                   : $class                   = $_value; break;
				}
			}
		}

		if(!isset($item["editors"])) {
			$item["editors"] = $IC->getEditors(["item_id" => $item["id"]]);
		}

		if(!$users_options) {

			if(!$users) {
				$UC = new User();

				$users = $UC->getUsers();
			}

			$users_options = $model->toOptions($users, "id", "nickname", ["add" => ["" => "Select editor"]]);
		}


		$_ = '';

		$_ .= '<div class="'.$class.' item_id:'.$item["id"].'" data-csrf-token="'.security()->getValue("csrf").'" data-editor-remove="'.$this->path.'/removeEditor">';
		$_ .= '<h2>Editors ('.($item["editors"] ? count($item["editors"]) : 0).')</h2>';

		$_ .= '<fieldset>';
		$_ .= '<h3>Existing editors</h3>';
		$_ .= '</fieldset>';

		if($item["editors"]):

			$_ .= '<ul class="items editors">';

			foreach($item["editors"] as $editor) {
				$_ .= '<li class="item editor editor_id:'.$editor["id"].'"><h3>'.$editor["nickname"].'</h3></li>';
			}
			$_ .= '</ul>';

		else:

		$_ .= '<p>No editors defined yet.</p>';

		endif;

		$_ .= $model->formStart($this->path."/addEditor/".$item["id"], array("class" => "editors labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= '<h3>Add editor</h3>';
		$_ .= $model->input("item_editor", array("type" => "select", "options" => $users_options));
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Add editor", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();
		$_ .= '</div>';

		return $_;
	}


	// edit event for ticket
	function editTicketEvent($item, $_options = false) {
		global $model;

		$class = "all_items ticketEvent i:ticketEvent i:collapseHeader";


		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "class"                   : $class                   = $_value; break;
				}
			}
		}

		$IC = new Items();

		$event_model = $IC->typeObject("event");
		$ticket_model = $IC->typeObject("ticket");

		$event_items = $IC->getItems(array("itemtype" => "event", "where" => "event.starting_at > (NOW() - INTERVAL 5 HOUR)", "order" => "event.starting_at ASC", "extend" => true));
		$event_options = ["" => "Choose event for this ticket"];
		foreach($event_items as $event_item) {
			$event_options[$event_item["item_id"]] = $event_item["name"]." (".date("Y-m-d", strtotime($event_item["starting_at"])).")";
		}

		$ticket_event = $ticket_model->getTicketEvent(["item_id" => $item["id"]]);

		$_ = '';

		$_ .= '<div class="'.$class.' item_id:'.$item["id"].'" data-csrf-token="'.security()->getValue("csrf").'" data-event-remove="'.$this->path.'/removeTicketEvent/'.$item["item_id"].'">';
		$_ .= '<h2>Related event</h2>';

		if($ticket_event):

			$_ .= '<ul class="items events">';
				$_ .= '<li class="item event event_id:'.$ticket_event["id"].'"><h3>'.$ticket_event["name"].' ('.date("Y-m-d", strtotime($event_item["starting_at"])).')</h3></li>';
			$_ .= '</ul>';

		else:

			$_ .= '<p>This ticket is not attached to any event.</p>';

		endif;


		$_ .= $model->formStart($this->path."/addTicketEvent/".$item["id"], array("class" => "event labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= '<h3>Choose event</h3>';
		$_ .= $model->input("item_event", array("type" => "select", "options" => $event_options));
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Add ticket to event", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();
		$_ .= '</div>';

		return $_;
	}

	// edit event for ticket
	function editEventTickets($item, $_options = false) {
		global $model;

		$class = "all_items eventTickets i:eventTickets i:collapseHeader";


		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "class"                   : $class                   = $_value; break;
				}
			}
		}

		$IC = new Items();
		$query = new Query();

		$event_model = $IC->typeObject("event");
		$ticket_model = $IC->typeObject("ticket");


		// Find available tickets
		$ticket_query = [
			"itemtype" => "ticket", 
			"where" => "ticket.sale_closes > NOW()", 
			"order" => "sale_opens ASC", 
			"extend" => true,
		];

		// Find only unused tickets
		$sql = "SELECT ticket_id FROM ".$event_model->db_tickets;
		if($query->sql($sql)) {
			$used_tickets = $query->results("ticket_id");
			$ticket_query["exclude"] = implode(";", $used_tickets);

		}

		$ticket_items = $IC->getItems($ticket_query);

		// debug(["u",$used_tickets, "t", $ticket_items]);
		$ticket_options = ["" => "Choose ticket for this event"];
		foreach($ticket_items as $ticket_item) {
			$ticket_options[$ticket_item["item_id"]] = $ticket_item["name"];
		}


		// Tickets for this event
		$event_tickets = $event_model->getEventTickets(["item_id" => $item["id"]]);



		$_ = '';

		$_ .= '<div class="'.$class.' item_id:'.$item["id"].'" data-csrf-token="'.security()->getValue("csrf").'" data-ticket-remove="'.$this->path.'/removeEventTicket/'.$item["item_id"].'">';
		$_ .= '<h2>Tickets (' . ($event_tickets ? count($event_tickets) : 0) . ')</h2>';

		if($event_tickets):

			$_ .= '<ul class="items tickets">';
			foreach($event_tickets as $event_ticket) {
				$_ .= '<li class="item ticket ticket_id:'.$event_ticket["id"].'"><h3>'.$event_ticket["name"].'</h3></li>';
			}
			$_ .= '</ul>';

		else:

			$_ .= '<p>This event does not have any tickets.</p>';

		endif;


		$_ .= $model->formStart($this->path."/addEventTicket/".$item["id"], array("class" => "ticket labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= '<h3>Choose ticket</h3>';
		$_ .= $model->input("item_ticket", array("type" => "select", "options" => $ticket_options));
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Add ticket to event", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();
		$_ .= '</div>';

		return $_;
	}

	// simple tag list
	function tagList($tags, $_options = false) {

		$_ = '';


		$context = false;

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "context"              : $context               = $_value; break;

				}
			}
		}

		$_ .= '<ul class="tags" data-context="'.$context.'">';
		if($tags) {
			foreach($tags as $tag) {
				if(!$context || (array_search($tag["context"], preg_split("/,|;/", $context)) !== false)) {

					$_ .= '<li class="tag '.$tag["context"].'"><span class="context">'.$tag["context"].'</span>:<span class="value">'.$tag["value"].'</span></li>';

				}
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
	function priceList($item_id) {

		$IC = new Items();
		$prices = $IC->getPrices(array("item_id" => $item_id));

		$_ = '';

		$_ .= '<ul class="prices">';
		if($prices) {
			foreach($prices as $price) {
				$_ .= '<li class="pricedetails price_id:'.$price["id"].'">';
					$_ .= '<ul class="info">';
						$_ .= '<li class="price">'.formatPrice($price, array("vat" => true)).'</li>';
//						$_ .= '<li class="currency">'. $price["currency"].'</li>';
						$_ .= '<li class="vatrate">'.$price["vatrate"].'% VAT</li>';
						if($price["type"] == "offer"):
							$_ .= '<li class="offer">Special offer</li>';
						elseif($price["type"] == "bulk"):
							$_ .= '<li class="bulk">Bulk price for '.$price["quantity"].' items</li>';
						elseif($price["type"] != "default"):
							$_ .= '<li class="custom_price">'.$price["description"].'</li>';
						endif;
					$_ .= '</ul>';
				$_ .= '</li>';
			}
		}
		$_ .= '</ul>';

		return $_;
	}





	// DEPRECATED: used to be possible to map todos to items via tags – but it doesn't really make sense
	// function listTodos($item) {
	// 	global $model;
	//
	// 	$IC = new Items();
	//
	// 	$_ = '';
	// 	$_ .= '<div class="todos i:defaultTodos item_id:'.$item["id"].'"'.$model->jsData(["todos]).'>';
	// 	$_ .= '<h2>TODOs</h2>';
	//
	// 	$todo_tag = $IC->getTags(array("item_id" => $item["item_id"], "context" => "todo"));
	// 	if($todo_tag) {
	// 		$todos = $IC->getItems(array("itemtype" => "todo", "status" => 1, "tags" => $todo_tag[0]["context"].":".$todo_tag[0]["value"], "extend" => array("user" => true)));
	//
	// 		if($todos) {
	// 		$_ .= '<ul class="todos">';
	// 			foreach($todos as $todo) {
	// 				$_ .= '<li class="todo todo_id:'.$todo["id"].'">';
	// 					$_ .= stringOr($model->link($todo["name"], "/janitor/admin/todo/edit/".$todo["id"], array("target" => "_blank")), $todo["name"]);
	// 					$_ .= ", Assigned to: ".$todo["user_nickname"];
	// 				$_ .= '</li>';
	// 			}
	// 		$_ .= '</ul>';
	// 		}
	// 		else {
	// 			$_ .= '<p>No TODOs</p>';
	// 		}
	//
	// 	}
	// 	else {
	// 		$_ .= '<p>No TODOs</p>';
	// 	}
	//
	// 	$_ .= '</div>';
	//
	// 	return $_;
	// }


	// simple QnA list
	// QnA list is different because it links to separate item
	function listQnas($item) {
		global $model;

		// look for QnA tag on item
		// if QnA tag exists, find QnA items and list them
		$IC = new Items();

		$_ = '';
		$_ .= '<div class="qnas i:defaultQnas item_id:'.$item["id"].'"'.$model->jsData(["qna"]).'>';
		$_ .= '<h2>Questions and Answers</h2>';


		$qnas = $IC->getItems(array("itemtype" => "qna", "status" => 1, "where" => "qna.about_item_id = ".$item["id"], "extend" => array("user" => true)));

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

		$_ .= '</div>';

		return $_;
	}




	/**
	* Change status button
	*/
	function statusButton($enable_label, $disable_label, $action, $item, $_options = false) {

		global $HTML;

		if(!security()->validatePath($action)) {
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



	// TABSET FOR USER AND PROFILE PAGE

	// HTML for tabs for user templates
	function userTabs($user_id, $selected) {
		global $HTML;

		$_ = '';
		
		$_ .= '<ul class="tabs">';
			$_ .= $HTML->link("Profile", "/janitor/admin/user/edit/".$user_id, array("wrapper" => "li.profile".($selected == "profile" ? ".selected" : "")));

			if(defined("SITE_ITEMS") && SITE_ITEMS):
				$_ .= $HTML->link("Content", "/janitor/admin/user/content/".$user_id, array("wrapper" => "li.content".($selected == "content" ? ".selected" : "")));

				// readstates not available for guest user
				// if($user_id != 1):
				// 	$_ .= $HTML->link("Readstates", "/janitor/admin/user/readstates/".$user_id, array("wrapper" => "li.readstates".($selected == "readstates" ? ".selected" : "")));
				// endif;

			endif;

			// maillist not available for guest user
			if(defined("SITE_SIGNUP") && SITE_SIGNUP && $user_id != 1):
				$_ .= $HTML->link("Maillists", "/janitor/admin/user/maillists/".$user_id, array("wrapper" => "li.maillist".($selected == "maillists" ? ".selected" : "")));
			endif;
	
			// orders not available for guest user
			if(defined("SITE_SHOP") && SITE_SHOP && $user_id != 1):
				$_ .= $HTML->link("Orders", "/janitor/admin/user/orders/".$user_id, array("wrapper" => "li.orders".($selected == "orders" ? ".selected" : "")));
			endif;

			// subscriptions not available for guest user
			if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS && $user_id != 1):
				$_ .= $HTML->link("Subscriptions", "/janitor/admin/user/subscription/list/".$user_id, array("wrapper" => "li.subscriptions".($selected == "subscriptions" ? ".selected" : "")));
			endif;

			// membership not available for guest user
			if(defined("SITE_MEMBERS") && SITE_MEMBERS && $user_id != 1):
				$_ .= $HTML->link("Membership", "/janitor/admin/member/view/".$user_id, array("wrapper" => "li.membership".($selected == "membership" ? ".selected" : "")));
			endif;

		$_ .= '</ul>';
		
		return $_;
	}



	// HTML for tabs for profile templates
	function profileTabs($selected) {
		global $HTML;

		$_ = '';
		
		$_ .= '<ul class="tabs">';
			$_ .= $HTML->link("Profile", "/janitor/admin/profile", array("wrapper" => "li.profile".($selected == "profile" ? ".selected" : "")));

			if(defined("SITE_ITEMS") && SITE_ITEMS):
				$_ .= $HTML->link("Content", "/janitor/admin/profile/content", array("wrapper" => "li.content".($selected == "content" ? ".selected" : "")));

				// readstates not available for guest user
				// $_ .= $HTML->link("Readstates", "/janitor/admin/profile/readstates", array("wrapper" => "li.readstates".($selected == "readstates" ? ".selected" : "")));

			endif;

			// maillist not available for guest user
			if(defined("SITE_SIGNUP") && SITE_SIGNUP):
				$_ .= $HTML->link("Maillists", "/janitor/admin/profile/maillists", array("wrapper" => "li.maillist".($selected == "maillists" ? ".selected" : "")));
			endif;

			// orders not available for guest user
			if(defined("SITE_SHOP") && SITE_SHOP):
				$_ .= $HTML->link("Orders", "/janitor/admin/profile/orders/list", array("wrapper" => "li.orders".($selected == "orders" ? ".selected" : "")));
			endif;

			// subscriptions not available for guest user
			if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS):
				$_ .= $HTML->link("Subscriptions", "/janitor/admin/profile/subscription/list", array("wrapper" => "li.subscriptions".($selected == "subscriptions" ? ".selected" : "")));
			endif;

			// membership not available for guest user
			if(defined("SITE_MEMBERS") && SITE_MEMBERS):
				$_ .= $HTML->link("Membership", "/janitor/admin/profile/membership/view", array("wrapper" => "li.membership".($selected == "membership" ? ".selected" : "")));
			endif;

		$_ .= '</ul>';
		
		return $_;
	}




	// USER DASHBOARDS (FOR FRONTPAGE)


	// Current user TODOs dashboard
	function listUserTodos() {
		global $HTML;

		$_ = '';

		// only show todos if user has access
		if(security()->validatePath("/janitor/admin/todo")) {
			
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


		$_ = '';

		// only show todos if user has access
		if(security()->validatePath("/janitor/admin/qna")) {

			$IC = new Items();
			$qnas = $IC->getItems(array("itemtype" => "qna", "where" => "qna.answer IS NULL", "extend" => true));

			$_ .= '<div class="qnas">';
			$_ .= '<h2>Unanswered questions</h2>';

			if($qnas):
				$_ .= '<ul class="qnas">';
				foreach($qnas as $qna):
//					if(!$qna["answer"]):
					$_ .= '<li class="qna qna_id:'.$qna["id"].'">';
						$_ .= '<h3>'.stringOr($HTML->link($qna["name"], "/janitor/admin/qna/edit/".$qna["id"], array("target" => "_blank")), $qna["name"]).'</h3>';
						if($qna["about_item_id"]):
							$related_item = $IC->getItem(array("id" => $qna["about_item_id"], "extend" => true));
							$_ .= '<p>Asked about: '. strip_tags($related_item["name"]).'</p>';
						endif;
					$_ .= '</li>';
//					endif;
				endforeach;
				$_ .= '</ul>';
			
			else:

				$_ .= '<p>No questions</p>';

			endif;

			$_ .= '</div>';

		}

		return $_;
	}


	// Current ORDER STATUS dashboard
	function listOrderStatus() {
		global $HTML;

		$_ = '';

		// only show orders if user has access
		if(security()->validatePath("/janitor/admin/shop/order/list")) {

			include_once("classes/shop/supershop.class.php");
			$model = new SuperShop();

			$_ .= '<div class="orders">';
			$_ .= '<h2>Order status</h2>';


			if($model->order_statuses) {
				$_ .= '<ul class="orders">';
				foreach($model->order_statuses as $order_status => $order_status_name) {
					$_ .= '<li class="'.superNormalize($order_status_name).'">';
					$_ .= '<h3>';
					$_ .= '<a href="/janitor/admin/shop/order/list/'.$order_status.'">'.$order_status_name.'</a> ';
					$_ .= '<span class="count">'.$model->getOrderCount(array("status" => $order_status)).'</span>';
					$_ .= '</h3>';

					$_ .= '</li>';
				}
				$_ .= '</ul>';
			}

			$_ .= '</div>';
		}

		return $_;
	}


	// Current ORDER STATUS dashboard
	function listMemberStatus() {

		$_ = '';


		// only show orders if user has access
		if(security()->validatePath("/janitor/admin/member/list")) {

			include_once("classes/users/superuser.class.php");
			include_once("classes/users/supermember.class.php");
			$model = new SuperUser();
			$IC = new Items();
			$MC = new SuperMember();

			$memberships = $IC->getItems(array("itemtype" => "membership", "status" => 1, "extend" => true));

			$_ .= '<div class="members">';
			$_ .= '<h2>Member status</h2>';

			if($memberships) {
			$_ .= '<ul class="members">';
				foreach($memberships as $membership) {
			
					$_ .= '<li class="'.superNormalize($membership["name"]).'">';
					$_ .= '<h3>';
					$_ .= '<a href="/janitor/admin/member/list/'.$membership["id"].'">'.$membership["name"].'</a> ';
					$_ .= '<span class="count">'.$MC->getMemberCount(array("item_id" => $membership["id"])).'</span>';
					$_ .= '</h3>';
					$_ .= '</li>';
				}
				$_ .= '</ul>';
			}

			$_ .= '</div>';

		}

		return $_;
	}

}

?>