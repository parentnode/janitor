<?php
global $action;
global $model;



$options = [
	"limit" => 200,
	"pattern" => false
];


$query = getVar("query");
if($query) {
	$options["query"] = $query;
}

if(count($action) > 3) {
	if($action[2] === "page") {
		$options["page"] = $action[3];
	}
}


$payments = $model->paginatePayments($options);
// debug([$payments])


?>
<div class="scene i:scene defaultList shopList paymentList">
	<h1>Payments</h1>

	<ul class="actions">
		<?= $HTML->link("Carts", "/janitor/admin/shop/cart/list", array("class" => "button", "wrapper" => "li.carts")) ?>
		<?= $HTML->link("Orders", "/janitor/admin/shop/order/list", array("class" => "button", "wrapper" => "li.orders")) ?>
	</ul>

	<div class="all_items i:defaultList filters" <?= $HTML->jsData(["search"], ["filter-search" => $HTML->path."/payment/list"]) ?>>
		<? if($payments && $payments["range_payments"]): ?>

		<?= $HTML->pagination($payments, [
			"base_url" => "/janitor/admin/shop/payment/list",
			"query" => $query,
		]) ?>

		<ul class="items payments">
			<? foreach($payments["range_payments"] as $payment): ?>
			<li class="item payment">
				<h3><?= $payment["order_no"] ?> (<?= pluralize($payment["item_count"], "item", "items") ?>)</h3>

				<dl class="info">
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $payment["created_at"] ?></dd>
					<dt class="price">Payment</dt>
					<dd class="price"><?= formatPrice(array("price" => $payment["payment_amount"], "vat" => 0, "currency" => $payment["currency"], "country" => $payment["country"])) ?></dd>
					<dt class="transaction_id">Transaction id</dt>
					<dd class="transaction_id"><?= $payment["transaction_id"] ?></dd>
					<dt class="payment_method">Payment method</dt>
					<dd class="payment_method"><?= $payment["payment_method"] ?></dd>
					<dt class="currency">Currency</dt>
					<dd class="currency"><?= $payment["currency"] ?></dd>

				<? if(isset($payment["nickname"])): ?>
					<dt class="nickname">Nickname</dt>
					<dd class="nickname"><?= $payment["nickname"] ?></dd>
				<? endif; ?>

				<? if($payment["usernames"]): ?>
					<dt class="email">Usernames</dt>
					<dd class="email"><?= $payment["usernames"] ?></dd>
				<? endif; ?>

				</dl>

				<ul class="actions">
					<?= $HTML->link("View order", "/janitor/admin/shop/order/edit/".$payment["order_id"], array("class" => "button", "wrapper" => "li.view")) ?>
				</ul>
			 </li>
		 	<? endforeach; ?>
		</ul>

		<?= $HTML->pagination($payments, [
			"base_url" => "/janitor/admin/shop/payment/list",
			"query" => $query,
		]) ?>

		<? else: ?>
		<p>No payments.</p>
		<? endif; ?>
	</div>

</div>
