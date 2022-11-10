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

if(count($action) > 4) {
	if($action[3] === "page") {
		$options["page"] = $action[4];
	}
}


// show specific order status
if(count($action) > 2) {
	$status = $action[2];

	if($status !== "all") {

		$options["pattern"] = [
			"status" => $status,
		];

	}
}
// show default = 0
else {
	$status = 0;
	$options["pattern"] = [
		"status" => $status,
	];
	
}
$orders = $model->paginateOrders($options);

$selected_status_name = isset($model->order_statuses[$status]) ? $model->order_statuses[$status] : false;

session()->value("return_to_orderstatus", $status);

?>
<div class="scene i:scene defaultList shopList orderList shopOrderList">
	<h1>Orders</h1>

	<ul class="actions">
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


	<div class="all_items i:defaultList i:orderList filters <?= superNormalize($selected_status_name) ?>" <?= $HTML->jsData(["search"], ["filter-search" => $HTML->path."/order/list/".$status]) ?>>
		<? if($orders && $orders["range_orders"]): ?>

		<?= $HTML->pagination($orders, [
			"base_url" => "/janitor/admin/shop/order/list/".$status,
			"query" => $query,
		]) ?>

		<ul class="items orders">
			<? foreach($orders["range_orders"] as $order):
				$total_order_price = $model->getTotalOrderPrice($order["id"]);
				if($status !== "all" && $status < 2) {
					$payment_intent = payments()->canBeCaptured([
						"user_id" => $order["user_id"],
						"order_id" => $order["id"],
						"amount" => $total_order_price["price"],
						"check_validity" => false
					]);
				}
			?>
			<li class="item order<?= ($order["shipping_status"] < 2 && $order["status"] != 3) ? " ship" : ""?>">
				<h3>
					<?= $order["order_no"] ?> (<?= pluralize($order["item_count"], "item", "items") ?>)
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
					<dd class="price"><?= formatPrice($total_order_price) ?></dd>

				<? if(isset($order["nickname"])): ?>
					<dt class="nickname">Nickname</dt>
					<dd class="nickname"><?= $order["nickname"] ?></dd>

					<? if($order["email"]): ?>
					<dt class="email">Email</dt>
					<dd class="email"><?= $order["email"] ?></dd>
					<? endif; ?>

					<? /*if($order["user"]["mobile"]): ?>
					<dt class="mobile">Mobile</dt>
					<dd class="mobile"><?= $order["user"]["mobile"] ?></dd>
					<? endif;*/ ?>

				<? endif; ?>

					<dt class="order_content">Order content</dt>
					<dd class="order_content"><?= $order["order_items"]
						// $order_content = [];
						// $IC = new Items();
						// foreach($order["items"] as $order_item):
						// 	$item = $IC->getItem(["id" => $order_item["item_id"]]);
						// 	if($item && array_search($item["itemtype"], $order_content) === false) {
						// 		array_push($order_content, $item["itemtype"]);
						// 	}
						// endforeach;
						// print implode(", ", $order_content);
					?></dd>

				</dl>

				<ul class="actions">
					<?= $HTML->link((($status < 2 && $status !== "all") ? "Edit" : "View"), "/janitor/admin/shop/order/edit/".$order["id"], array("class" => "button", "wrapper" => "li.view")) ?>

					<? if(($status !== "all" && $status < 2) && $order["shipping_status"] < 2 && $order["status"] != 3): ?>
					<?= $HTML->oneButtonForm("Ship order", "/janitor/admin/shop/updateShippingStatus/".$order["id"], array(
						"inputs" => array("shipped" => 1),
						"class" => "ship primary",
						"wrapper" => "li.ship",
						"confirm-value" => "Mark order as shipped?",

						// "success-function" => ""
						// "success-location" => "/janitor/admin/shop/order/edit/".$order["id"]

					)) ?>
					<? endif; ?>
					
					<? if(($status !== "all" && $status < 2) && $order["payment_status"] < 2 && $payment_intent): ?>
					<?= $HTML->oneButtonForm(
					"Capture ".formatPrice($total_order_price),
					"capturePayment",
					array(
						"inputs" => array(
							"payment_intent_id" => $payment_intent["payment_intent_id"],
							"payment_amount" => $total_order_price["price"],
						),
						"confirm-value" => "Yes, I'm serious",
						"class" => "capture primary",
						"name" => "delete",
						"wrapper" => "li.capture",
					)); ?>
					<? endif; ?>
				</ul>
			 </li>
		 	<? endforeach; ?>
		</ul>

		<?= $HTML->pagination($orders, [
			"base_url" => "/janitor/admin/shop/order/list/".$status,
			"query" => $query,
		]) ?>

		<? else: ?>
		<p>No orders.</p>
		<? endif; ?>
	</div>

</div>
