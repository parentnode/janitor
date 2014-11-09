<?php
global $action;
global $model;

$order = $model->getOrders(array("order_id" => $action[2]));
?>
<div class="scene defaultView orderView">
	<h1>View order</h1>

	<ul class="actions">
		<?= $HTML->link("Order list", "/janitor/admin/shop/order/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<h2>Order</h2>
	<div class="order">
		<dl class="list">
			<dt>Order No.</dt>
			<dd><?= $order["order_no"] ?></dd>
			<dt>Created at</dt>
			<dd><?= $order["created_at"] ?></dd>
			<dt>Total price</dt>
			<dd><?= formatPrice($model->getTotalOrderPrice($order["id"]), $order["currency"]) ?></dd>
		</dl>

		<h3>Contact</h3>
		<dl class="list">
			<dt>Email</dt>
			<dd><?= $order["email"] ?></dd>
			<dt>Mobile</dt>
			<dd><?= $order["mobile"] ?></dd>
		</dl>

		<h3>Delivery</h3>
		<dl class="list">
			<dt>Name</dt>
			<dd><?= $order["delivery_name"] ?></dd>
			<dt>Address 1</dt>
			<dd><?= $order["delivery_address1"] ?></dd>
			<dt>Addresse 2</dt>
			<dd><?= $order["delivery_address2"] ?></dd>
			<dt>Postal and city</dt>
			<dd><?= $order["delivery_postal"] ?> <?= $order["delivery_city"] ?></dd>
			<dt>Country</dt>
			<dd><?= $order["delivery_country"] ?></dd>
		</dl>

		<h3>Order items</h3>
		<ul class="list">
<?		foreach($order["items"] as $order_item): ?>
			<li>
				<span class="title"><?= $order_item["name"] ?></span>
				<span class="price_piece"><?= $order_item["quantity"] ?> x <?= formatPrice($order_item["price"]+$order_item["vat"]) ?></span>
				<span class="price"><?= formatPrice($order_item["total_price"]+$order_item["total_vat"]) ?></span>
			</li>
<?		endforeach; ?>
		</ul>
	</div>

</div>
