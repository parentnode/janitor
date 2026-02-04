<?php

// $context should be array of allowed contexts
// - if $context is false, no tags are shown (except editing and default tag)
// – if $context is omitted (or empty array), then all tags are shown
// $default should be array with url and text
// $url should be url to prefix tag links
// $editing defines if editing link is shown


$item = false;

$context = [];
$default = false;
$url = false;

$editing = true;
$editing_text = "Work in progress";
$editing_title_text = $editing_text;

$schema = "articleSection";


if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "item"                  : $item                   = $_value; break;

			case "context"               : $context                = $_value; break;
			case "default"               : $default                = $_value; break;

			case "url"                   : $url                    = $_value; break;
			
			case "editing"               : $editing                = $_value; break;
			case "editing_text"          : $editing_text           = $_value; break;
			case "editing_title_text"    : $editing_title_text     = $_value; break;

			case "schema"                : $schema                 = $_value; break;

		}
	}
}


$editing_tag = false;
$default_tag = false;
$item_tags = [];


// Has editing tag
if($item["tags"] && $editing):
	$editing_tag_key = arrayKeyValue($item["tags"], "context", "editing");
	if($editing_tag_key !== false):
		$editing_tag = $item["tags"][$editing_tag_key];
		unset($item["tags"][$editing_tag_key]);
	endif;
endif;

// Has default tag
if(is_array($default)):
	$default_tag = $default;
endif;

// item tag list
// context is specified – filter by context
if($item["tags"] && $context):
	foreach($item["tags"] as $item_tag):
		if(array_search($item_tag["context"], $context) !== false):
			$item_tags[] = $item_tag;
		endif;
	endforeach;
// context is empty array – show all tags
elseif(is_array($context) && !$context):
	$item_tags = $item["tags"];
endif;


if($editing_tag || $default_tag || $item_tags):
?>
<ul class="tags">
<?	if($editing_tag): ?>
	<li class="editing" title="<?= $editing_title_text ?>"><?= ($editing_tag["value"] == "true" ? $editing_text : $editing_tag["value"]) ?></li>
<?
	endif;

	if($default_tag):
?>
	<li class="default"><a href="<?= $default_tag[0] ?>"><?= $default[1] ?></a></li>
<?
	endif;

	if($item_tags):
		foreach($item_tags as $item_tag):
?>
	<li<?= ($schema ? ' itemprop="'.$schema.'"' : '') ?>>
<?			if($url): ?>
		<a href="<?= $url ?>/<?= urlencode($item_tag["value"]) ?>"><?= $item_tag["value"] ?></a>
<?			else: ?>
		<?= $item_tag["value"] ?>
<?			endif; ?>
	</li>
<?
		endforeach;
	endif;
?>
</ul>
<?
endif;
