<?php
global $action;
global $model;
$IC = new Items();


$cart_id = $action[3];
$cart = $model->getCarts(array("cart_id" => $cart_id));

$items = $IC->getItems(array("order" => "itemtype", "extend" => array("subscription_method" => true, "prices" => true)));


//print_r($items);
//print_r($subscriptions);
$item_options[""] = "Select item";

foreach($items as $item) {
	if($item["prices"]) {

		$price = $model->getPrice($item["id"], ["user_id" => $cart["user_id"]]);

		$item_options[$item["item_id"]] = strip_tags($item["name"])." (".$item["itemtype"].")";
		$item_options[$item["item_id"]] .= " – " . formatPrice($price);

		if($item["subscription_method"]) {
			$item_options[$item["item_id"]] .= " / ".$item["subscription_method"]["name"];
		}
	}
}

?>
<div class="scene i:scene defaultNew newCart">
	<h1>New cart item</h1>
	<h2><?= $cart["cart_reference"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back", "/janitor/admin/shop/cart/edit/".$cart_id, array("class" => "button", "wrapper" => "li.cart")); ?>
	</ul>

	<?= $model->formStart("/janitor/admin/shop/addToCart/".$cart["cart_reference"], array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("item_id", array(
				"label" => "Select item to add to cart",
				"type" => "select",
				"required" => true,
				"options" => $item_options,
			)) ?>
			<?= $model->input("quantity", array("value" => 1)) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->link("Cancel", "/janitor/admin/shop/cart/edit/".$cart_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Add item", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>

	<?= $model->formEnd() ?>

</div>