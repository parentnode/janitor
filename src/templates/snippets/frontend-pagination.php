<?php

// Make links for page or sindex
$type = "page";


// Default both directions
$direction = false;

// Default show total
$show_total = true;

// Default base url
$base_url = $this->path;

// Default class
$class = "pagination";

$labels = [
	"next" => "Next", 
	"prev" => "Previous", 
	"total" => "Page {current_page} of {page_count} pages"
];

// overwrite defaults
if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {

			case "type"              : $type               = $_value; break;

			case "direction"         : $direction          = $_value; break;

			case "show_total"        : $show_total         = $_value; break;

			case "base_url"          : $base_url           = $_value; break;

			case "class"             : $class              = $_value; break;

			case "labels"            : $labels             = $_value; break;

		}
	}
}



// No pagination unless matching elements
if(($pagination_items["next"] && ($direction === "next" || !$direction)) || ($pagination_items["prev"] && ($direction === "prev" || !$direction))) {
?>

<div class="<? $class ?>">
	<ul>


<?	if(($direction === "prev" || !$direction) && $pagination_items["prev"]) {

		$labels["prev"] = preg_replace("/\{name\}/", $pagination_items["prev"]["name"], $labels["prev"]);

		if($type == "page" && $pagination_items["current_page"] > 0) { ?>
			<li class="previous"><a href="<? $base_url ?>/page/<? ($pagination_items["current_page"]-1) ?>"><? strip_tags($labels["prev"]) ?></a></li>
		<?}
		else { ?>
			<li class="previous"><a href="<? $base_url ?>/<? $pagination_items["prev"]["sindex"] ?>"><? strip_tags($labels["prev"]) ?></a></li>
		<? }

	}


	if($show_total) {

		$labels["total"] = preg_replace("/\{current_page\}/", $pagination_items["current_page"], $labels["total"]);
		$labels["total"] = preg_replace("/\{page_count\}/", $pagination_items["page_count"], $labels["total"]);
?>
		<li class="pages"><? $labels["total"] ?></li>
<?	}


	if(($direction === "next" || !$direction) && $pagination_items["next"]) {

		// print_r($pagination_items);
		$labels["next"] = preg_replace("/\{name\}/", $pagination_items["next"]["name"], $labels["next"]);

		// Page based
		if($type == "page" && $pagination_items["current_page"] < $pagination_items["page_count"]) {
?>
			<li class="next"><a href="<? $base_url ?>/page/<? ($pagination_items["current_page"]+1) ?>"><? strip_tags($labels["next"]) ?></a></li>
<?		}
		// Sindex based
		else {
?>			<li class="next"><a href="<? $base_url ?>/<? $pagination_items["next"]["sindex"] ?>"><? strip_tags($labels["next"]) ?></a></li>
<?
}

	}

?>
	</ul>
	</div>
<? }