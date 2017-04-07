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
<div class="scene i:scene defaultList userContentList profileContentList">
	<h1>Orders</h1>
	<h2><?= $item["nickname"] ?></h2>

	<?= $JML->profileTabs("orders") ?>


	<div class="all_items orders i:defaultList filters">
		<h2>Orders</h2>
<?		if($orders): ?>
		<ul class="items">
<?			foreach($orders as $order):
	 			$total_price = $SC->getTotalOrderPrice($order["id"]); ?>
			<li class="item">
				<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items" ) ?>)</h3>

				<dl class="details">
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $order["created_at"] ?></dd>
					<dt class="status">Status</dt>
					<dd class="status"><?= $SC->order_statuses[$order["status"]] ?></dd>
					<dt class="payment_status">Payment status</dt>
					<dd class="payment_status<?= $order["payment_status"] < 2 ? " missing" : "" ?>"><?= $SC->payment_statuses[$order["payment_status"]] ?></dd>
					<dt class="price">Total price</dt>
					<dd class="price"><?= formatPrice($total_price) ?></dd>
				</dl>

				<ul class="actions">
					<? if($order["payment_status"] < 2 && $total_price["price"] != 0): ?>
					<?= $HTML->link("Pay", "/shop/payment/".$order["order_no"], array("class" => "button", "wrapper" => "li.edit")) ?>
					<? endif; ?>
					<?= $HTML->link("View order", "/janitor/admin/shop/order/edit/".$order["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No orders.</p>
<?		endif; ?>
	</div>


	<div class="all_items cartd i:defaultList filters">
		<h2>Carts</h2>
<?		if($carts): ?>
		<ul class="items">
<?			foreach($carts as $cart): ?>
			<li class="item">
				<h3><?= $cart["cart_reference"] ?> (<?= pluralize(count($cart["items"]), "item", "items" ) ?>)</h3>

				<dl class="details">
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