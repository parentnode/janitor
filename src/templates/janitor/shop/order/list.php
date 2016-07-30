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
<div class="scene i:scene defaultList orderList">
	<h1>Orders</h1>

	<ul class="actions">
		<?= $HTML->link("New order", "/janitor/admin/shop/order/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<?= $HTML->link("Carts", "/janitor/admin/shop/cart/list", array("class" => "button", "wrapper" => "li.cart")) ?>
		<?= $HTML->link("Payments", "/janitor/admin/shop/payment/list", array("class" => "button", "wrapper" => "li.payment")) ?>
	</ul>

<? if($model->order_statuses): ?>
	<ul class="tabs">
		<? foreach($model->order_statuses as $order_status => $order_status_name): ?>
		<?
		print_r($model->getOrders(array("status" => $order_status)));
		?>
		<?= $HTML->link($order_status_name. "(".count($model->getOrders(array("status" => $order_status))).")", "/janitor/admin/shop/order/list/".$order_status, array("wrapper" => "li".".".superNormalize($order_status_name).(is_numeric($status) && $order_status == $status ? ".selected" : ""))) ?>
		<? endforeach; ?>
		<?= $HTML->link("All", "/janitor/admin/shop/order/list/all", array("wrapper" => "li".($status === "all" ? ".selected" : ""))) ?>
	</ul>
<? endif; ?>


	<div class="all_items i:defaultList filters">
		<? if($orders): ?>
		<ul class="items orders">
			<? foreach($orders as $order): ?>
			<li class="item order">
				<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items") ?>)</h3>

				<dl class="details">
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $order["created_at"] ?></dd>
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

				<? if(!isset($order["user"])): ?>
					<p class="error">INCOMPLETE ORDER</p>
				<? endif; ?>

				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/shop/order/edit/".$order["id"], array("class" => "button", "wrapper" => "li.view")) ?>
				</ul>
			 </li>
		 	<? endforeach; ?>
		</ul>
		<? else: ?>
		<p>No orders.</p>
		<? endif; ?>
	</div>

</div>
