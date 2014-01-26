<?php

$action = $this->actions();

$model = new Shop();
$IC = new Item();

$cart = $model->getCarts(array("cart_id" => $action[2]));

?>
<div class="scene defaultView cartView">
	<h1>View cart22</h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/shop/cart/list" class="button">Back</a></li>
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
				$product = $IC->getCompleteItem($product["item_id"]);
				$price = $product["prices"][0]; ?>
			<li class="product"><?= $product["name"] ?>, <?= formatPrice($price["price_with_vat"], $price["currency"]) ?></li>
<?			endforeach; ?>
		</ul>
<?		endif; ?>

	</div>
</div>
