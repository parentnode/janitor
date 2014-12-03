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
	function jsData() {
		global $page;

		$_ = '';

		$_ .= ' data-csrf-token="'.session()->value("csrf").'"';
		$_ .= ' data-item-order="'.$page->validPath($this->path."/updateOrder").'"'; 
		$_ .= ' data-tag-get="'.$page->validPath("/janitor/admin/items/tags").'"'; 
		$_ .= ' data-tag-delete="'.$page->validPath($this->path."/deleteTag").'"';
		$_ .= ' data-tag-add="'.$page->validPath($this->path."/addTag").'"';
		$_ .= ' data-media-order="'.$page->validPath($this->path."/updateMediaOrder").'"';
		$_ .= ' data-media-delete="'.$page->validPath($this->path."/deleteMedia").'"';
		$_ .= ' data-media-name="'.$page->validPath($this->path."/updateMediaName").'"';

		return $_;
	}



	// NEW

	// "list" button on new page
	function newList($_options = false) {
		global $model;

		$label = "List";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"           : $label            = $_value; break;
				}
			}
		}

		$_ = '';

		$_ .= $model->link($label, $this->path."/list", array("class" => "button primary key:esc", "wrapper" => "li.back"));

		return $_;
	}

	// default actions inside model form on new page
	function newActions($_options = false) {
		global $model;

		$_ = '';

		$_ .= '<ul class="actions">';
		$_ .= $model->link("Cancel", $this->path."/list", array("class" => "button key:esc", "wrapper" => "li.cancel"));
		$_ .= $model->submit("Save and continue", array("class" => "primary key:s", "wrapper" => "li.save"));
		$_ .= '</ul>';

		return $_;
	}



	// LIST

	// "new" button on list page
	function listNew($_options = false) {
		global $model;

		$label = "New";

		// overwrite defaults
		if($_options !== false) {
			foreach($_options as $_option => $_value) {
				switch($_option) {

					case "label"           : $label            = $_value; break;
				}
			}
		}

		$_ = '';

		$_ .= $model->link($label, $this->path."/new", array("class" => "button primary key:n", "wrapper" => "li.new"));

		return $_;
	}

	// default actions for list item on list page
	function listActions($item, $_options = false) {
		global $model;

		$_ = '';

		$_ .= '<ul class="actions">';
		$_ .= $model->link("Edit", $this->path."/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit"));
		$_ .= $this->deleteButton("Delete", $this->path."/delete/".$item["id"], array("js" => true));
		$_ .= $this->statusButton("Enable", "Disable", $this->path."/status", $item, array("js" => true));
		$_ .= '</ul>';

		return $_;
	}



	// EDIT

	// default back/delete/status buttons for edit page
	function editGlobalActions($item, $_options = false) {
		global $model;

		$_ = '';

		// BACK AND DELETE
		$_ .= '<ul class="actions i:defaultEditActions item_id:'.$item["id"].'" data-csrf-token="'.session()->value("csrf").'">';
		$_ .= $model->link("List", $this->path."/list", array("class" => "button", "wrapper" => "li.cancel"));
		$_ .= $this->deleteButton("Delete", $this->path."/delete/".$item["id"], array("js" => true));
		$_ .= '</ul>';

		// STATUS
		$_ .= '<div class="status i:defaultEditStatus item_id:'.$item["id"].'" data-csrf-token="'.session()->value("csrf").'">';
		$_ .= '<ul class="actions">';
		$_ .= $this->statusButton("Enable", "Disable", $this->path."/status", $item, array("js" => true));
		$_ .= '</ul>';
		$_ .= '</div>';

		return $_;
	}

	// default actions inside model form on edit page
	function editActions($item, $_options = false) {
		global $model;

		$_ = '';

		$_ .= '<ul class="actions">';
		$_ .= $model->link("Cancel", $this->path."/list", array("class" => "button key:esc", "wrapper" => "li.cancel"));
		$_ .= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save"));
		$_ .= '</ul>';

		return $_;
	}

	// edit tags form for edit page
	// TODO: implement same method in item list on list page
	function editTags($item, $_options = false) {
		global $model;

		$_ = '';

		$_ .= '<div class="tags i:defaultTags item_id:'.$item["id"].'"'.$this->jsData().'>';
		$_ .= '<h2>Tags</h2>';
		$_ .= $model->formStart($this->path."/addTag/".$item["id"], array("class" => "labelstyle:inject"));
		$_ .= '<fieldset>';
		$_ .= $model->inputTags("tags", array("id" => "tags_".$item["id"]));
		$_ .= '</fieldset>';

		$_ .= '<ul class="actions">';
		$_ .= $model->submit("Add new tag", array("class" => "primary", "wrapper" => "li.save"));
		$_ .= '</ul>';
		$_ .= $model->formEnd();

		$_ .= $this->tagList($item["tags"]);
		$_ .= '</div>';

		return $_;
	}

	// edit media form for edit page
	function editMedia($item, $_options = false) {
		global $model;


		$type = "mediae";
		$label = "Media";
		$class = "i:addMedia";

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

		$_ .= '<div class="media  '.$type.' '.$class.' sortable item_id:'.$item["id"].'"'.$this->jsData().'>';
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
		$init_class = "i:addMediaSingle";

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


	// simple tag list
	function tagList($tags) {

		$_ = '';

		$_ .= '<ul class="tags">';
		if($tags) {
			foreach($tags as $tag) {
				$_ .= '<li><span class="context">'.$tag["context"].'</span>:<span class="value">'.$tag["value"].'</span></li>';
			}
		}
		$_ .= '</ul>';

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


}

// create standalone instance to make Janitor available 
// TODO: consider instantiating in Template using this only?
$JML = new JanitorHTML();
?>