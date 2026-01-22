<?php


$SC = new Shop();

$item = false;
$url = false;
$description = false;


if($_options !== false) {
	foreach($_options as $_option => $_value) {
		switch($_option) {
			case "item"               : $item            = $_value; break;
			case "url"                : $url             = $_value; break;
			case "description"        : $description     = $_value; break;
		}
	}
}



if($item) {

	$best_price = false;


	$default_price = false;
	$default_key = arrayKeyValue($item["prices"], "type", "default");
	if($default_key !== false) {
		$default_price = $item["prices"][$default_key];
		$best_price = $default_price;
	}


	$offer_price = false;
	$offer_key = arrayKeyValue($item["prices"], "type", "offer");
	if($offer_key !== false) {
		$offer_price = $item["prices"][$offer_key];

		if(!$best_price || $offer_price["price"] < $best_price["price"]) {
			$best_price = $offer_price;
		}
	}



	if(defined("SITE_MEMBERS") && SITE_MEMBERS) {
		$MC = new Member();
		$membership = $MC->getMembership();
		if($membership && $membership["item"] && $membership["item"]["status"] == 1) {

			$price_types = page()->priceTypes();
			$membership_price_type_key = arrayKeyValue($price_types, "item_id", $membership["item"]["item_id"]);
			if($membership_price_type_key !== false) {
				$membership_price_type_id = $price_types[$membership_price_type_key]["id"];

				$membership_price_key = arrayKeyValue($item["prices"], "type_id", $membership_price_type_id);
				if($membership_price_key !== false) {
					$membership_price = $prices[$membership_price_key];

					if(!$best_price || $membership_price["price"] < $best_price["price"]) {
						$best_price = $offer_price;
					}
				}

			}

		}

	}

?>
<ul class="offer" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
	<li class="name" itemprop="name" content="<?= $item["name"] ?>"></li>
<? 

	if($best_price):
?>
	<li class="currency" itemprop="priceCurrency" content="<?= $best_price["currency"] ?>"></li>
<?

		if($default_price && $best_price !== $default_price):
?>
	<li class="price default"><?= formatPrice($default_price).(isset($item["subscription_method"]) && $item["subscription_method"] && $default_price["price"] ? ' / '.$item["subscription_method"]["name"] : '') ?></li>
	<li class="price offer" itemprop="price" content="<?= $best_price["price"] ?>"><?= formatPrice($best_price).(isset($item["subscription_method"]) && $item["subscription_method"] && $best_price ? ' / '.$item["subscription_method"]["name"] : '') ?></li>
<?
		else:
?>
	<li class="price" itemprop="price" content="<?= $best_price["price"] ?>"><?= formatPrice($best_price).(isset($item["subscription_method"]) && $item["subscription_method"] && $best_price ? ' / '.$item["subscription_method"]["name"] : '') ?></li>
<?	
		endif;

	else:

?>
	<li class="price" itemprop="price" content="0">Free</li>
<?
	endif;

?>
	<li class="url" itemprop="url" content="<?= $url ?>"></li>
<?

	if($description):
?>
	<li class="description" itemprop="description"><?= $description ?></li>
<?
	endif;

?>
</ul>
<?
}
