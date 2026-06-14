<?php
$item = false;
$categories = false;
$itemtype = false;

$tags = [];

$base_path = HTML()->path;
$tag_namespace = "tag";

$title = "Categories";
$see_all_label = "All";
$see_all_path = $base_path;


if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {

			case "item"                : $item                = $_value; break;
			case "categories"          : $categories          = $_value; break;
			case "itemtype"            : $itemtype            = $_value; break;

			case "tags"                : $tags                = $_value; break;

			case "title"               : $title               = $_value; break;

			case "base_path"           : $base_path           = $_value; break;
			case "tag_namespace"       : $tag_namespace       = $_value; break;

		}
	}
}

// Categories was not passed
if(!$categories):
	$category_options = ["order" => "value"];
	if($itemtype) {
		$category_options["context"] = $itemtype;
	}

	// Get categories
	$categories = items()->getTags($category_options);
endif;


if($categories):
	if($item && !$tags) {
		$tags = $item["tags"];
	}
?>
	<div class="categories">
		<h2><?= $title ?></h2>
		<ul class="tags">
		<? foreach($categories as $tag): ?>
			<li <?= ($tags && arrayKeyValue($tags, "value", $tag["value"]) !== false) ? ' class="selected"' : "" ?>><a href="<?= $base_path ?>/<?= $tag_namespace ?>/<?= urlencode($tag["value"]) ?>"><?= $tag["value"] ?></a></li>
		<? endforeach; ?>
			<li class="all<?= (!$tags ? " selected" : "") ?>"><a href="<?= $see_all_path ?>"><?= $see_all_label ?></a></li>
		</ul>
	</div>
<? endif; ?>
