<?php


// Make links for page or sindex 
// sindex = next/prev item
// page = load next page with specified number of elements
$type = "page";


// Default both directions
$direction = false;

// Default show total
$show_total = true;

// Default base url
$base_path = HTML()->path;

// Default class
$class = "pagination i:pagination";

$labels = [
	"next" => "Next", 
	"prev" => "Previous", 
	"total" => "Page {current_page} of {page_count} pages"
];

// overwrite defaults
if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {

			case "items"                 : $items                  = $_value; break;

			case "type"                  : $type                   = $_value; break;
			case "direction"             : $direction              = $_value; break;

			case "base_path"             : $base_path              = $_value; break;
			case "class"                 : $class                  = $_value; break;

			case "show_total"            : $show_total             = $_value; break;
			case "labels"                : $labels                 = $_value; break;

		}
	}
}


$_ = '';

// No pagination unless matching elements
if(($items["next"] && ($direction === "next" || !$direction)) || ($items["prev"] && ($direction === "prev" || !$direction))): ?>

	<div class="'<?= $class ?>">
		<ul>
<?	if(($direction === "prev" || !$direction) && $items["prev"]):
		$labels["prev"] = preg_replace("/\{name\}/", $items["prev"]["name"], $labels["prev"]);
		if($items["prev"]):
			if($type == "page" && $items["current_page"] > 0):
?>
			<li class="previous"><a href="<?= $base_path ?>/page/<?= ($items["current_page"]-1) ?>"><?= $labels["prev"] ?></a></li>
<?			else: ?>
			<li class="previous"><a href="<?= $base_path ?>/<?= $items["prev"]["sindex"] ?>"><?= $labels["prev"] ?></a></li>
<?			endif;
		else: ?>
			<li class="previous"><a class="disabled"><?= $labels["prev"] ?></a></li>
<?		endif;
	endif;


	if($show_total):

		$labels["total"] = preg_replace("/\{current_page\}/", $items["current_page"], $labels["total"]);
		$labels["total"] = preg_replace("/\{page_count\}/", $items["page_count"], $labels["total"]);
?>
			<li class="pages"><?= $labels["total"] ?></li>
<?
	endif;

	if(($direction === "next" || !$direction) && $items["next"]):

		// print_r($pitems);
		$labels["next"] = preg_replace("/\{name\}/", $items["next"]["name"], $labels["next"]);

		if($items["next"]):

			// Page based
			if($type == "page" && $items["current_page"] < $items["page_count"]):
?>
			<li class="next"><a href="<?= $base_path ?>/page/<?= ($items["current_page"]+1) ?>"><?= $labels["next"] ?></a></li>
<?
			// Sindex based
			else:
?>
			<li class="next"><a href="<?= $base_path ?>/<?= $items["next"]["sindex"] ?>"><?= $labels["next"] ?></a></li>
<?			endif;

		else:
?>
			<li class="next"><a class="disabled"><?= $labels["next"] ?></a></li>
<?
		endif;

	endif;
?>

		</ul>
	</div>

<? endif; ?>
