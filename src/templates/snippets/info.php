<?php

// $item should be the item represented in the schema
// $url should be the cannonical url for this item
// $media should be the main media associated with the schema item
// $sharing defines whether to add share class to main entity


$item = false;

$url = false;

$media = false;
$sharing = false;


if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "item"              : $item                = $_value; break;

			case "url"               : $url                 = $_value; break;

			case "media"             : $media               = $_value; break;
			case "sharing"           : $sharing             = $_value; break;
		}
	}
}


if($item && $url): ?>
<ul class="info">
	<li class="published_at" itemprop="datePublished" content="<?= date("Y-m-d H:i:s T", strtotime($item["published_at"])) ?>"><?= date("Y-m-d, H:i", strtotime($item["published_at"])) ?></li>
	<li class="modified_at" itemprop="dateModified" content="<?= date("Y-m-d H:i:s T", strtotime($item["modified_at"])) ?>"></li>
	<li class="author" itemprop="author"><?= (isset($item["user_nickname"]) ? $item["user_nickname"] : SITE_NAME) ?></li>
	<li class="main_entity<?= ($sharing ? ' share' : '') ?>" itemprop="mainEntityOfPage" content="<?= SITE_URL.$url ?>"></li>
	<li class="publisher" itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
		<ul class="publisher_info">
			<li class="name" itemprop="name"><?= SITE_NAME ?></li>
			<li class="logo" itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
				<span class="image_url" itemprop="url" content="<?= SITE_URL ?>/img/logo-large.png"></span>
				<span class="image_width" itemprop="width" content="1200"></span>
				<span class="image_height" itemprop="height" content="675"></span>
			</li>
		</ul>
	</li>
	<li class="image_info" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
<?	if($media): ?>
		<span class="image_url" itemprop="url" content="<?= SITE_URL ?>/images/<?= $item["item_id"] ?>/<?= $media["variant"] ?>/1200x.<?= $media["format"] ?>"></span>
		<span class="image_width" itemprop="width" content="1200"></span>
		<span class="image_height" itemprop="height" content="<?= floor(1200 / ($media["width"] / $media["height"])) ?>"></span>
<?	else: ?>
		<span class="image_url" itemprop="url" content="<?= SITE_URL ?>/img/logo-large.png"></span>
		<span class="image_width" itemprop="width" content="1200"></span>
		<span class="image_height" itemprop="height" content="675"></span>
<?	endif; ?>
	</li>
<?	if(isset($item["location"]) && $item["location"] && isset($item["latitude"]) && $item["latitude"] && isset($item["longitude"]) && $item["longitude"]): ?>
	<li class="place" itemprop="contentLocation" itemscope itemtype="http://schema.org/Place">
		<ul class="geo" itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates">
			<li class="name" itemprop="name"><?= $item["location"] ?></li>
			<li class="latitude" itemprop="latitude" content="<?= round($item["latitude"], 5) ?>"></li>
			<li class="longitude" itemprop="longitude" content="<?= round($item["longitude"], 5) ?>"></li>
		</ul>
	</li>
<?	endif; ?>
</ul>
<? endif;
