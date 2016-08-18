<?php
global $action;
global $model;

$payments = $model->getPayments();
?>
<div class="scene i:scene defaultList orderList">
	<h1>Payments</h1>

	<ul class="actions">
		<?= $HTML->link("New payment", "/janitor/admin/shop/payment/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<?= $HTML->link("Carts", "/janitor/admin/shop/cart/list", array("class" => "button", "wrapper" => "li.carts")) ?>
		<?= $HTML->link("Orders", "/janitor/admin/shop/order/list", array("class" => "button", "wrapper" => "li.orders")) ?>
	</ul>

	<div class="all_items i:defaultList filters">
		<? if($payments): ?>
		<ul class="items payments">
			<? foreach($payments as $payment):
				$order = $model->getOrders(array("order_id" => $payment["order_id"])); ?>
			<li class="item payment">
				<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items") ?>)</h3>

				<dl class="details">
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $payment["created_at"] ?></dd>
					<dt class="price">Payment</dt>
					<dd class="price"><?= formatPrice(array("price" => $payment["price"], "vat" => $payment["vat"], "currency" => $payment["currency"], "country" => $payment["country"])) ?></dd>

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
					<?= $HTML->link("Edit", "/janitor/admin/shop/payment/edit/".$payment["id"], array("class" => "button", "wrapper" => "li.view")) ?>
				</ul>
			 </li>
		 	<? endforeach; ?>
		</ul>
		<? else: ?>
		<p>No payments.</p>
		<? endif; ?>
	</div>

</div>
