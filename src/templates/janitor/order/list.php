<?php
global $action;
global $model;

$orders = $model->getOrders();
?>
<div class="scene defaultList orderList">
	<h1>Orders</h1>

	<ul class="actions">
		<?= $HTML->link("Carts", "/janitor/admin/shop/cart/list", array("class" => "button", "wrapper" => "li.cart")) ?>
	</ul>

	<div class="all_items i:defaultList filters">
<?		if($orders): ?>
		<ul class="items">
<?			foreach($orders as $order): ?>
			<li class="item">
				<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items") ?>)</h3>
				<dl class="details">
					<dt class="name">Name</dt>
					<dd class="name"><?= $order["delivery_name"] ?></dd>
					<dt class="price">Total price</dt>
					<dd class="price"><?= formatPrice($model->getTotalOrderPrice($order["id"]), $order["currency"]) ?></dd>
					<dt class="email">Email</dt>
					<dd class="email"><?= $order["email"] ?></dd>
					<dt class="mobile">Mobile</dt>
					<dd class="mobile"><?= $order["mobile"] ?></dd>
				</dl>

				<ul class="actions">
					<?= $HTML->link("View", "/janitor/admin/shop/order/view/".$order["id"], array("class" => "button", "wrapper" => "li.view")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No orders.</p>
<?		endif; ?>
	</div>

</div>
