<?php

// Does auto schema unless schema is set to false

$item = false;
$media = false;
$schema = true;
$class = "";
$name = "";
$description = "";

if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "item"              : $item                = $_value; break;

			case "media"             : $context             = $_value; break;

			case "class"             : $class               = $_value; break;
			case "variant"           : $variant             = $_value; break;
			case "description"       : $description         = $_value; break;
			case "name"              : $name                = $_value; break;
			case "schema"            : $schema              = $_value; break;
		}
	}
}


// No media passed, try standard media
if(!$media && $item) {
	$IC = new Items();
	$media = $IC->sliceMediae($item, "main_media|single_media|mediae");
}

// debug([$item, $media]);

if($media) {

	// identify type class and schema
	if(preg_match("/mp4|ogv|mov|3gp|webm/", $media["format"])) {
		$type = "video";
	}
	else {
		$type = "image";
	}

	// Add type to class
	$class .= ($class ? " " : "") . $type;

	$item_id = $media["item_id"];

	if($schema) {
		$schema_type = ucfirst($type)."Object";
		$schema_prop = $type;

		if(!$name) {
			$name = $media["name"];
		}

		if(!$description) {
			$description = $media["description"] ? $media["description"] : ($item && isset($item["description"]) ? $item["description"] : "");
		}
	}

	$content_url = "/".($type == "video" ? "videos" : "images")."/".$item_id."/".$media["variant"]."/1200x.".$media["format"];

	$thumbnail_url = false;
	if($type === "image") {
		$thumbnail_url = $content_url;
	}
	else if($type === "video" && $media["poster"]) {
		$thumbnail_url = "/images/".$item_id."/".$media["variant"]."/1200x.".$media["poster"];
	}

	$modified_at = $media["modified_at"] ? $media["modified_at"] : $media["created_at"];
	?>
	<div class="media <?= $class ?> item_id:<?= $item_id ?> format:<?= $media["format"] ?> variant:<?= $media["variant"] ?>">
<? 	if($schema): ?>
		<ul class="metadata" data-variant="<?= $media["variant"] ?>" data-format="<?= $media["format"] ?>" data-poster="<?= $media["poster"] ?><?= $schema ? ' itemprop="'.$schema_prop.'" itemscope itemtype="http://schema.org/'.$schema_type.'"' : '' ?>>
			<li itemprop="name"><?= $name ?></li>
			<li itemprop="description"><?= $description ?></li>
			<li itemprop="width"><?= $media["width"] ?></li>
			<li itemprop="height"><?= $media["height"] ?></li>
			<li itemprop="contentUrl"><?= $content_url ?></li>

<?		if($thumbnail_url): ?>
			<li itemprop="thumbnailUrl"><?= $thumbnail_url ?></li>
<?		endif; ?>
			<li itemprop="uploadDate"><?= date(DATE_ATOM, strtotime($modified_at)) ?></li>
		</ul>
<? 	endif ?>
	</div>

<?
}
