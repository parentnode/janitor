<?php
global $action;
global $model;

$orders = false;

// show specific order status
if(count($action) > 2) {
	$status = $action[2];

	if($status === "all") {
		$orders = $model->getOrders();
	}
	else {
		$orders = $model->getOrders(array("status" => $status));
	}
}
// show default = 0
else {
	$status = 0;
	$orders = $model->getOrders(array("status" => $status));

}

session()->value("return_to_orderstatus", $status);

?>
<div class="scene i:scene defaultList shopList orderList shopOrderList">
	<h1>Orders</h1>

	<ul class="actions">
		<?//= $HTML->link("New order", "/janitor/admin/shop/order/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<?= $HTML->link("Carts", "/janitor/admin/shop/cart/list", array("class" => "button", "wrapper" => "li.carts")) ?>
		<?= $HTML->link("Payments", "/janitor/admin/shop/payment/list", array("class" => "button", "wrapper" => "li.payments")) ?>
	</ul>

<? if($model->order_statuses): ?>
	<ul class="tabs">
		<? foreach($model->order_statuses as $order_status => $order_status_name): ?>
		<?= $HTML->link($order_status_name. " (<span>".$model->getOrderCount(array("status" => $order_status))."</span>)", "/janitor/admin/shop/order/list/".$order_status, array("wrapper" => "li".".".superNormalize($order_status_name).(is_numeric($status) && $order_status == $status ? ".selected" : ""))) ?>
		<? endforeach; ?>
		<?= $HTML->link("All (".$model->getOrderCount().")", "/janitor/admin/shop/order/list/all", array("wrapper" => "li.all".($status === "all" ? ".selected" : ""))) ?>
	</ul>
<? endif; ?>


	<div class="all_items i:defaultList i:orderList filters">
		<? if($orders): ?>
		<ul class="items orders">
			<? foreach($orders as $order): ?>
			<li class="item order<?= ($order["shipping_status"] < 2 && $order["status"] != 3) ? " ship" : ""?>">
				<h3>
					<?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items") ?>)
				
					<? if(!isset($order["user"]) || !$order["items"]): ?>
					- <span class="system_error">INCOMPLETE ORDER</span>
					<? endif; ?>
				</h3>

				<dl class="info">
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $order["created_at"] ?></dd>

				<? if($status === "all"): ?>
					<dt class="status">Status</dt>
					<dd class="status <?= superNormalize($model->order_statuses[$order["status"]]) ?>"><?= $model->order_statuses[$order["status"]] ?></dd>
				<? endif; ?>

				<? if($status == 1 || $status === "all"): ?>
					<dt class="payment_status">Payment status</dt>
					<dd class="payment_status <?= ["unpaid", "partial", "paid"][$order["payment_status"]] ?>"><?= $model->payment_statuses[$order["payment_status"]] ?></dd>
					<dt class="shipping_status">Shipping status</dt>
					<dd class="shipping_status <?= ["unshipped", "partial", "shipped"][$order["shipping_status"]] ?>"><?= $model->shipping_statuses[$order["shipping_status"]] ?></dd>
				<? endif; ?>

					<dt class="price">Total price</dt>
					<dd class="price"><?= formatPrice($model->getTotalOrderPrice($order["id"])) ?></dd>

				<? if(isset($order["user"])): ?>
					<dt class="nickname">Nickname</dt>
					<dd class="nickname"><?= $order["user"]["nickname"] ?></dd>

					<? if($order["user"]["email"]): ?>
					<dt class="email">Email</dt>
					<dd class="email"><?= $order["user"]["email"] ?></dd>
					<? endif; ?>

					<? if($order["user"]["mobile"]): ?>
					<dt class="mobile">Mobile</dt>
					<dd class="mobile"><?= $order["user"]["mobile"] ?></dd>
					<? endif; ?>

				<? endif; ?>
				</dl>

				<ul class="actions">
					<?= $HTML->link(($status < 2 ? "Edit" : "View"), "/janitor/admin/shop/order/edit/".$order["id"], array("class" => "button", "wrapper" => "li.view")) ?>

					<? if($order["shipping_status"] < 2 && $order["status"] != 3): ?>
					<?= $HTML->oneButtonForm("Ship order", "/janitor/admin/shop/updateShippingStatus/".$order["id"], array(
						"inputs" => array("shipped" => 1),
						"class" => "primary",
						"wrapper" => "li.ship",
						"confirm-value" => "Mark order as shipped?",

						// "success-function" => ""
						// "success-location" => "/janitor/admin/shop/order/edit/".$order["id"]

					)) ?>
					<? endif; ?>

				</ul>
			 </li>
		 	<? endforeach; ?>
		</ul>
		<? else: ?>
		<p>No orders.</p>
		<? endif; ?>
	</div>

</div>
