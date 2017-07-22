<?php
global $action;
global $model;

$order_id = $action[2];
$user_id = session()->value("user_id");
//print_r($order);
$SC = new Shop();
$order = $SC->getOrders(array("order_id" => $order_id));

if($order) {
	$user = $model->getUser();

	// Does it make sense to run any of this without the shop?
	//if(defined("SITE_SHOP") && SITE_SHOP) {
	//}


	// get addresses for selected user
	$addresses = $model->getAddresses();
	if($addresses) {
		$delivery_address_options = $model->toOptions($addresses, "id", "address_label", array("add" => array("" => "Select delivery address")));
		$billing_address_options = $model->toOptions($addresses, "id", "address_label", array("add" => array("" => "Select billing address")));
	}
	else {
		$delivery_address_options = array("" => "No addresses");
		$billing_address_options = array("" => "No addresses");
	}


	$payments = $SC->getPayments(array("order_id" => $order_id));
	$total_order_price = $SC->getTotalOrderPrice($order_id);

	$total_payments = 0;
	// if order is not paid, sum up all payments
	if($order["payment_status"] < 2 && $payments) {
		foreach($payments as $payment) {
			$total_payments += $payment["payment_amount"];
		}
	}
	// then calculate total remaining payment
	$payable_amount = $total_order_price["price"]-$total_payments;
	
}


$return_to_orderstatus = session()->value("return_to_orderstatus");

?>
<div class="scene i:scene defaultEdit shopView orderView">
<? if($order): ?>
	<h1>View order</h1>
	<h2><?= $order["order_no"] ?> (<?= $SC->order_statuses[$order["status"]] ?>)</h2>

	<ul class="actions i:defaultEditActions">

		<?= $HTML->link("Your orders", "/janitor/admin/profile/orders/list", array("class" => "button", "wrapper" => "li.list")) ?>

		<? if($order["payment_status"] < 2 && $order["status"] != 3): ?>
		<?= $HTML->link("Pay now", "/shop/payment/".$order["order_no"], array("class" => "button primary", "wrapper" => "li.pay")) ?>
		<? endif; ?>

		<? if($order["status"] >= 2): ?>
		<?= $HTML->link("Invoice", "/janitor/admin/profile/orders/invoice/".$order["id"], array("class" => "button primary", "wrapper" => "li.invoice", "target" => "blank")) ?>
		<? endif; ?>

		<? if($order["status"] == 3): ?>
		<?= $HTML->link("Credit note", "/janitor/admin/profile/orders/creditnote/".$order["id"], array("class" => "button primary", "wrapper" => "li.creditnote", "target" => "blank")) ?>
		<? endif; ?>

	</ul>

	<div class="orderstatus i:collapseHeader">
		<h2 class="<?= superNormalize($SC->order_statuses[$order["status"]]) ?>">Status</h2>

		<dl class="info">
			<dt class="status">Status</dt>
			<dd class="status <?= superNormalize($SC->order_statuses[$order["status"]]) ?>"><?= $SC->order_statuses[$order["status"]] ?></dd>
			<dt class="payment_status">Payment status</dt>
			<dd class="payment_status <?= ["unpaid", "partial", "paid"][$order["payment_status"]] ?>"><?= $SC->payment_statuses[$order["payment_status"]] ?></dd>
			<dt class="shipping_status">Shipping status</dt>
			<dd class="shipping_status <?= ["unshipped", "partial", "shipped"][$order["shipping_status"]] ?>"><?= $SC->shipping_statuses[$order["shipping_status"]] ?></dd>
		</dl>
	</div>

	<div class="basics i:collapseHeader">
		<h2>Details</h2>
		<dl class="info">
			<dt>Order No.</dt>
			<dd><?= $order["order_no"] ?></dd>
			<dt>Total price</dt>
			<dd class="total_order_price"><?= formatPrice($total_order_price) ?></dd>
			<dt>Created at</dt>
			<dd><?= $order["created_at"] ?></dd>
			<dt>Modified at</dt>
			<dd><?= ($order["modified_at"] ? $order["modified_at"] : "Never") ?></dd>
			<dt>Currency</dt>
			<dd><?= $order["currency"] ?></dd>
			<dt>Country</dt>
			<dd><?= $order["country"] ?></dd>
		</dl>
	</div>

	<div class="contact i:collapseHeader">
		<h2>Contact</h2>
		<dl class="info">
			<dt>Nickname</dt>
			<dd><?= $user["nickname"] ?></dd>
			<dt>First</dt>
			<dd><?= $user["firstname"] ?></dd>
			<dt>Lastname</dt>
			<dd><?= $user["lastname"] ?></dd>
			<dt>Email</dt>
			<dd><?= $user["email"] ?></dd>
			<dt>Mobile</dt>
			<dd><?= $user["mobile"] ?></dd>
		</dl>
	</div>

	<div class="comment i:collapseHeader">
		<h2>Comment</h2>

		<? if($order["comment"]): ?>
		<p><?= nl2br($order["comment"]) ?></p>
		<? else: ?>
		<p class="note">No comment</p>
		<? endif; ?>
	</div>

	<div class="all_items i:defaultList i:orderItemsList i:collapseHeader">
		<h2>Items (<?= count($order["items"]) ?>)</h2>
		<? if($order["items"]): ?>
		<ul class="items">
			<? foreach($order["items"] as $order_item): ?>
			<li class="item <?= superNormalize($SC->order_statuses[$order["status"]]) ?><?= ($order_item["shipped_by"] ? " shipped" : "") ?>">
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
							), 
							array("vat" => true)
						) ?>
					</span>
				</h3>

			</li>
			<? endforeach; ?>
		</ul>
		<? else: ?>
		<p>No Items in order</p>
		<? endif; ?>

	</div>

	<div class="payments i:defaultList i:collapseHeader">
		<h2>Payments</h2>
		<? if($payments): ?>
		<ul class="payment items">
			<? foreach($payments as $payment):
				$payment["payment_method"] = $this->paymentMethods($payment["payment_method"]); ?>
			<li class="item">
				<dl class="info">
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $payment["created_at"] ?></dd>
					<dt class="price">Payment</dt>
					<dd class="price"><?= formatPrice(array("price" => $payment["payment_amount"], "vat" => 0, "currency" => $payment["currency"], "country" => $order["country"])) ?></dd>
					<dt class="transaction_id">Transaction id</dt>
					<dd class="transaction_id"><?= $payment["transaction_id"] ?></dd>
					<dt class="payment_method">Payment method</dt>
					<dd class="payment_method"><?= $payment["payment_method"]["name"] ?></dd>
				</dl>
			</li>
			<? endforeach; ?>
		</ul>
		<? else: ?>
		<p>No payments</p>
		<? endif; ?>

		<? if($order["payment_status"] < 2 && $order["status"] != 3): ?>
		<h3>Still to be paid: <span class="system_error"><?= formatPrice(array("price" => ($payable_amount), "vat" => 0, "currency" => $order["currency"], "country" => $order["country"])) ?></span></h3>
		<ul class="actions">
			<?= $HTML->link("Pay order", "/shop/payment/".$order["order_no"], array("class" => "button primary", "wrapper" => "li.pay")) ?>
		</ul>
		<? endif; ?>

	</div>

	<div class="delivery i:collapseHeader">
		<h2>Delivery</h2>

		<dl class="info">
			<dt>Name</dt>
			<dd><?= $order["delivery_name"] ?></dd>
			<dt>Att</dt>
			<dd><?= $order["delivery_att"] ?></dd>
			<dt>Address 1</dt>
			<dd><?= $order["delivery_address1"] ?></dd>
			<dt>Addresse 2</dt>
			<dd><?= $order["delivery_address2"] ?></dd>
			<dt>Postal and city</dt>
			<dd><?= $order["delivery_postal"] ?> <?= $order["delivery_city"] ?></dd>
			<dt>State</dt>
			<dd><?= $order["delivery_state"] ?></dd>
			<dt>Country</dt>
			<dd><?= $order["delivery_country"] ?></dd>
		</dl>
	</div>

	<div class="billing i:collapseHeader">
		<h2>Billing</h2>

		<dl class="info">
			<dt>Name</dt>
			<dd><?= $order["billing_name"] ?></dd>
			<dt>Att</dt>
			<dd><?= $order["billing_att"] ?></dd>
			<dt>Address 1</dt>
			<dd><?= $order["billing_address1"] ?></dd>
			<dt>Addresse 2</dt>
			<dd><?= $order["billing_address2"] ?></dd>
			<dt>Postal and city</dt>
			<dd><?= $order["billing_postal"] ?> <?= $order["billing_city"] ?></dd>
			<dt>State</dt>
			<dd><?= $order["billing_state"] ?></dd>
			<dt>Country</dt>
			<dd><?= $order["billing_country"] ?></dd>
		</dl>
	</div>

<? else: ?>

	<h1>What??</h1>
	<ul class="actions">
		<?= $HTML->link("Your orders", "/janitor/admin/profile/orders/list", array("class" => "button", "wrapper" => "li.list")) ?>
	</ul>

	<h1>It seems you have lost your way.</h1>
<? endif; ?>

</div>
