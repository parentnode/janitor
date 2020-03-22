<?php
global $action;
global $model;


$user_id = session()->value("user_id");
$IC = new Items();


// get current user
$item = $model->getUser();

$orders = false;

if(defined("SITE_SHOP") && SITE_SHOP) {
	$SC = new Shop();

	$orders = $SC->getOrders();
	$carts = $SC->getCarts();
}

?>
<div class="scene i:scene defaultList shopList orderList profileOrderList">
	<h1>Orders</h1>
	<h2><?= $item["nickname"] ?></h2>

	<?= $JML->profileTabs("orders") ?>


	<div class="orders item i:collapseHeader">
		<h2>Orders</h2>
		<div class="all_items i:defaultList filters">
	<?		if($orders): ?>
			<ul class="items">
	<?			foreach($orders as $order):
		 			$total_price = $SC->getTotalOrderPrice($order["id"]); ?>
				<li class="item">
					<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items" ) ?>)</h3>

					<dl class="info">
						<dt class="created_at">Created at</dt>
						<dd class="created_at"><?= $order["created_at"] ?></dd>
						<dt class="status">Status</dt>
						<dd class="status <?= superNormalize($SC->order_statuses[$order["status"]]) ?>"><?= $SC->order_statuses[$order["status"]] ?></dd>
	<?					if($order["status"] < 2): ?>
						<dt class="payment_status">Payment status</dt>
						<dd class="payment_status <?= ["unpaid", "partial", "paid"][$order["payment_status"]] ?>"><?= $SC->payment_statuses[$order["payment_status"]] ?></dd>
	<?					endif; ?>
						<dt class="price">Total price</dt>
						<dd class="price"><?= formatPrice($total_price) ?></dd>
					</dl>

					<ul class="actions">
						<?= $HTML->link("View", "/janitor/admin/profile/orders/view/".$order["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					</ul>
				 </li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No orders.</p>
	<?		endif; ?>
		</div>
	</div>

	<div class="carts item i:collapseHeader">
		<h2>Carts</h2>
		<div class="all_items carts i:defaultList filters">
	<?		if($carts): ?>
			<ul class="items">
	<?			foreach($carts as $cart): ?>
				<li class="item">
					<h3><?= $cart["cart_reference"] ?> (<?= pluralize(count($cart["items"]), "item", "items" ) ?>)</h3>

					<dl class="info">
						<dt class="created_at">Created at</dt>
						<dd class="created_at"><?= $cart["created_at"] ?></dd>
						<dt class="price">Total price</dt>
						<dd class="price"><?= formatPrice($SC->getTotalCartPrice($cart["id"])) ?></dd>
					</dl>

					<ul class="actions">
						<?= $HTML->link("View", "/shop/cart/".$cart["cart_reference"], array("class" => "button", "wrapper" => "li.edit")) ?>
					</ul>
				 </li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No carts.</p>
	<?		endif; ?>
		</div>
	</div>
</div>