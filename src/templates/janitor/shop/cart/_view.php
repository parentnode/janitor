<?php
global $action;
global $model;

$IC = new Items();

$cart = $model->getCarts(array("cart_id" => $action[2]));

?>
<div class="scene i:scene defaultView cartView">
	<h1>View cart</h1>

	<ul class="actions">
		<?= $HTML->link("Cart list", "/janitor/admin/shop/cart/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $JML->oneButtonForm("Delete", "/janitor/admin/shop/deleteCart/".$cart["id"], array(
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/shop/cart/list"
		)) ?>
	</ul>

	<h2>Cart</h2>
	<div class="cart">
		<dl class="list">
<?		foreach($cart as $attribute => $value): ?>
<? 			if($attribute != "items"): ?>
				<dt><?= $attribute ?></dt>
				<dd><?= $cart[$attribute] ?></dd>
<? 			endif; ?>
<? 		endforeach;?>
		</dl>

	</div>

	<h2>Products in cart</h2>
	<div class="products">

<?		if($cart["items"]): ?>
		<ul class="products">
<?			foreach($cart["items"] as $product): 
				$product = $IC->getCompleteItem(array("id" => $product["item_id"]));
				$price = $product["prices"][0]; ?>
			<li class="product"><?= $product["name"] ?>, <?= formatPrice($price["price_with_vat"], $price["currency"]) ?></li>
<?			endforeach; ?>
		</ul>
<?		endif; ?>

	</div>
</div>
