<?php

// Does auto schema unless schema is set to false

$item = false;
$media = false;

$class = "";
$variant = "";
$description = "";
$name = "";
$schema = true;

$load = false;
$size = "1200x";
$avif = true;


if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "item"              : $item                = $_value; break;

			case "media"             : $media               = $_value; break;

			case "class"             : $class               = $_value; break;
			case "variant"           : $variant             = $_value; break;
			case "description"       : $description         = $_value; break;
			case "name"              : $name                = $_value; break;
			case "schema"            : $schema              = $_value; break;

			case "load"              : $load                = $_value; break;
			case "size"              : $size                = $_value; break;
			case "avif"              : $avif                = $_value; break;
		}
	}
}


// No media passed, try standard media
if(!$media && $item) {
	$media = items()->sliceMediae($item, "main_media|single_media|mediae");
}

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


		if($load === "bg") {
			$bg_attribute = HTML()->attribute("style", "background-image(/images/".$item_id."/".$media["variant"]."/".$size.".".($avif ? "avif" : $media["format"]).")");
			
		}
		else if($load === "img") {
			$img_tag = '<img src="/images/'.$item_id.'/'.$media["variant"].'/'.$size.'.'.($avif ? "avif" : $media["format"]).'" alt="'.$description.'" />';
		}

	}
	else if($type === "video" && $media["poster"]) {
		$thumbnail_url = "/images/".$item_id."/".$media["variant"]."/1200x.".$media["poster"];
	}

	$modified_at = $media["modified_at"] ? $media["modified_at"] : $media["created_at"];


	?>
	<div class="media <?= $class ?> item_id:<?= $item_id ?> format:<?= $media["format"] ?> variant:<?= $media["variant"] ?><?= $load ? " loaded" : "" ?>"<?= $load === "bg" ? $bg_attribute : "" ?>>
<? 	if($schema): ?>
		<ul class="metadata" data-item-id="<?= $item_id ?>" data-variant="<?= $media["variant"] ?>" data-format="<?= $media["format"] ?>" data-poster="<?= $media["poster"] ?>"<?= $schema ? ' itemprop="'.$schema_prop.'" itemscope itemtype="http://schema.org/'.$schema_type.'"' : '' ?>>
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
		<?= $load === "img" ? $img_tag : "" ?>
	</div>

<?
}
