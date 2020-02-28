<?php
global $action;
global $model;


$order_id = $action[2];
$order = $model->getOrders(array("order_id" => $action[2]));

if($order) {

	$this->pageTitle("Invoice ".$order["order_no"]);
	$this->bodyClass("invoice");


	$total_order_price = $model->getTotalOrderPrice($order["id"]);
}

?>
<div class="scene i:scene invoice">
<? if($order): ?>

	<div class="status">
	<? if($order["status"] >= 2): ?>
		<h3>Paid</h3>
	<? else: ?>
		<h3>Payment due</h3>
	<? endif; ?>
	</div>

	<div class="basics">

		<? include("templates/janitor/shop/order/invoice-seller.php") ?>

		<h2>Order / Invoice: <?= $order["order_no"] ?></h2>
		<dl class="info">
			<dt>Created</dt>
			<dd><?= $order["created_at"] ?></dd>
			<? if($order["status"] == 2): ?>
			<dt>Completed</dt>
			<dd><?= $order["modified_at"] ?></dd>
			<? elseif($order["status"] == 3): ?>
			<dt>Cancelled</dt>
			<dd><?= $order["modified_at"] ?></dd>
			<? endif; ?>
		</dl>

	</div>

	<div class="buyer">
		<h2>Buyer</h2>

		<ul class="info">
			<li><?= $order["billing_name"] ?></li>
			<? if($order["billing_att"]): ?><li><?= $order["billing_att"] ?></li><? endif; ?>
			<? if($order["billing_address1"]): ?><li><?= $order["billing_address1"] ?></li><? endif; ?>
			<? if($order["billing_address2"]): ?><li><?= $order["billing_address2"] ?></li><? endif; ?>
			<? if($order["billing_postal"]): ?><li><?= $order["billing_postal"] ?> <?= $order["billing_city"] ?></li><? endif; ?>
			<? if($order["billing_state"]): ?><li><?= $order["billing_state"] ?></li><? endif; ?>
			<? if($order["billing_country"]): ?><li><?= $order["billing_country"] ?></li><? endif; ?>
		</ul>
	</div>

	<div class="all_items">
		<h2><?= pluralize(count($order["items"]), "item", "items") ?></h2>
		<? if($order["items"]): ?>
		<ul class="items">
			<? foreach($order["items"] as $order_item): ?>
			<li class="item <?= superNormalize($model->order_statuses[$order["status"]]) ?><?= ($order_item["shipped_by"] ? " shipped" : "") ?>">
				<h3>

					<span class="quantity"><?= $order_item["quantity"] ?></span>
					<span class="name">x <?= $order_item["name"] ?> á</span>
					<span class="unit_price">
						<?= formatPrice(array(
								"price" => $order_item["unit_price"], 
								"vat" => $order_item["unit_vat"], 
								"currency" => $order["currency"], 
								"country" => $order["country"]
						)) ?>
					</span>
					<span class="total_price">
						<?= formatPrice(array(
								"price" => $order_item["total_price"], 
								"vat" => $order_item["total_vat"], 
								"currency" => $order["currency"], 
								"country" => $order["country"]
							)
						) ?>
					</span>
				</h3>

			</li>
			<? endforeach; ?>
			<li class="order_total">
				<span>Total</span> <span class="amount"><?= formatPrice($total_order_price, ["vat" => false]) ?></span>
			</li>
			<li class="order_vat">
				<span>VAT</span> <span class="amount"><?= formatPrice(["price" => $total_order_price["vat"], "currency" => $total_order_price["currency"]], ["vat" => false]) ?></span>
			</li>
		</ul>
		<? else: ?>
		<p>No Items in order</p>
		<? endif; ?>

	</div>

	<div class="payment_info">
		<? include("templates/janitor/shop/order/invoice-payment-info.php") ?>
	</div>


<? else: ?>

	<h1>Invoice</h1>
	<ul class="actions i:defaultEditActions">
		<?= $HTML->link("Order", "/janitor/admin/shop/order/edit/".$order_id, array("class" => "button", "wrapper" => "li.order")) ?>
	</ul>

	<p>Invoice does not exist. Invoices can only be viewed for fully processed orders.</p>

<? endif; ?>

</div>
