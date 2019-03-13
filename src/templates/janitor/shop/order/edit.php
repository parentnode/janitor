<?php
global $action;
global $model;


$order_id = $action[2];
$order = $model->getOrders(array("order_id" => $action[2]));

$this->pageTitle("Order ".$order["order_no"]);

//print_r($order);

include_once("classes/users/superuser.class.php");
$UC = new SuperUser();


// get addresses for selected user
$addresses = $UC->getAddresses(array("user_id" => $order["user_id"]));
if($addresses) {
	$delivery_address_options = $model->toOptions($addresses, "id", "address_label", array("add" => array("" => "Select delivery address")));
	$billing_address_options = $model->toOptions($addresses, "id", "address_label", array("add" => array("" => "Select billing address")));
}
else {
	$delivery_address_options = array("" => "No addresses");
	$billing_address_options = array("" => "No addresses");
}


$payments = $model->getPayments(array("order_id" => $order["id"]));
$total_order_price = $model->getTotalOrderPrice($order["id"]);

$total_payments = 0;
// if order is not paid, sum up all payments
if($order["payment_status"] < 2 && $payments) {
	foreach($payments as $payment) {
		$total_payments += $payment["payment_amount"];
	}
}
// then calculate total remaining payment
$payable_amount = $total_order_price["price"]-$total_payments;


$reminders = $model->getPaymentReminders(["order_id" =>$order["id"]]);

$return_to_orderstatus = session()->value("return_to_orderstatus");

?>
<div class="scene i:scene defaultEdit shopView orderView">
	<h1><?= ($order["status"] < 2 ? "Edit" : "View") ?> order</h1>
	<h2><?= $order["order_no"] ?> (<?= $model->order_statuses[$order["status"]] ?>)</h2>

	<ul class="actions i:defaultEditActions">
		<?= $HTML->link("Order list", "/janitor/admin/shop/order/list/".$return_to_orderstatus, array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->link("User orders", "/janitor/admin/user/orders/".$order["user_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>

		<? if($order["status"] == 0 || $order["status"] == 1): ?>
		<?= $JML->oneButtonForm("Cancel order", "/janitor/admin/shop/cancelOrder/".$order["id"]."/".$order["user_id"], array(
			"wrapper" => "li.delete",
			"confirm-value" => "Are you sure? This will also cancel any subscriptions or memberships related to this order!",
			"class" => "secondary",
			"success-location" => "/janitor/admin/shop/order/list/3"
		)) ?>
		<? endif; ?>
		<?= $HTML->link("Invoice", "/janitor/admin/shop/order/invoice/".$order["id"], array("class" => "button primary", "wrapper" => "li.invoice", "target" => "blank")) ?>

		<? if($order["status"] == 3): ?>
		<?= $HTML->link("Credit note", "/janitor/admin/shop/order/creditnote/".$order["id"], array("class" => "button primary", "wrapper" => "li.creditnote", "target" => "blank")) ?>
		<? endif; ?>

		<? if($order["payment_status"] < 2 && $order["status"] != 3): ?>
		<?= $HTML->link("Pay order", "/janitor/admin/shop/order/payment/new/".$order["id"], array("class" => "button primary", "wrapper" => "li.pay")) ?>
		<? endif; ?>

		<? if($order["shipping_status"] < 2 && $order["status"] != 3): ?>
		<?= $JML->oneButtonForm("Ship order", "/janitor/admin/shop/updateShippingStatus/$order_id", array(
			"inputs" => array("shipped" => 1),
			"class" => "primary",
			"wrapper" => "li.ship",
			"confirm-value" => "Mark order as shipped?",
			"success-location" => "/janitor/admin/shop/order/edit/".$order["id"]
		)) ?>
		<? endif; ?>

	</ul>

	<div class="orderstatus i:collapseHeader">
		<h2 class="<?= superNormalize($model->order_statuses[$order["status"]]) ?>">Status</h2>

		<dl class="info">
			<dt class="status">Status</dt>
			<dd class="status <?= superNormalize($model->order_statuses[$order["status"]]) ?>"><?= $model->order_statuses[$order["status"]] ?></dd>
			<dt class="payment_status">Payment status</dt>
			<dd class="payment_status <?= ["unpaid", "partial", "paid"][$order["payment_status"]] ?>"><?= $model->payment_statuses[$order["payment_status"]] ?></dd>
			<dt class="shipping_status">Shipping status</dt>
			<dd class="shipping_status <?= ["unshipped", "partial", "shipped"][$order["shipping_status"]] ?>"><?= $model->shipping_statuses[$order["shipping_status"]] ?></dd>
		</dl>
	</div>

	<div class="basics i:collapseHeader">
		<h2>Details</h2>

		<? if($order["status"] == 0): ?>
		<?= $model->formStart("/janitor/admin/shop/updateOrder/".$order_id, array("class" => "i:editDataSection labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("country", array(
					"type" => "select",
					"options" => $model->toOptions($this->countries(), "id", "name"),
					"value" => $order["country"]
				)) ?>
				<?= $model->input("currency", array(
					"type" => "select",
					"options" => $model->toOptions($this->currencies(), "id", "name"),
					"value" => $order["currency"]
				)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
		<? endif; ?>

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
			<dd><?= $order["user"]["nickname"] ?></dd>
			<dt>First</dt>
			<dd><?= $order["user"]["firstname"] ?></dd>
			<dt>Lastname</dt>
			<dd><?= $order["user"]["lastname"] ?></dd>
			<dt>Email</dt>
			<dd><?= $order["user"]["email"] ?></dd>
			<dt>Mobile</dt>
			<dd><?= $order["user"]["mobile"] ?></dd>
		</dl>
	</div>

	<div class="comment i:collapseHeader">
		<h2>Comment</h2>
		<? if($order["status"] == 0): ?>
		<?= $model->formStart("/janitor/admin/shop/updateOrder/".$order_id, array("class" => "i:editDataSection labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("order_comment", array("value" => $order["comment"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
		<? endif; ?>

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
			<li class="item <?= superNormalize($model->order_statuses[$order["status"]]) ?><?= ($order_item["shipped_by"] ? " shipped" : "") ?>">
				<h3>

				<? /*if($order["status"] == 0): ?>
					<?= $model->formStart("/janitor/admin/shop/updateOrderItemQuantity/$order_id/".$order_item["id"], array("class" => "updateOrderItemQuantity labelstyle:inject")) ?>
						<fieldset>
							<?= $model->input("quantity", array(
								"type" => "integer",
								"value" =>  $order_item["quantity"],
								"hint_message" => "State the quantity of this item"
							)) ?>
						</fieldset>
						<ul class="actions">
							<?= $model->submit("Update", array("name" => "update", "wrapper" => "li.save")) ?>
						</ul>
					<?= $model->formEnd() ?>
				<? else: */?>
					<span class="quantity"><?= $order_item["quantity"] ?></span>
				<? /*endif;*/ ?>

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

				<ul class="actions">
					<? /*if($order["status"] == 0): ?>
					<?= $JML->oneButtonForm("Delete", "/janitor/admin/shop/deleteFromOrder/$order_id/".$order_item["id"], array(
						"wrapper" => "li.delete",
						"success-function" => "deletedFromOrder",
						"static" => true
					)) ?>
					<? endif; */?>

					<? /*if($order["status"] == 0 || $order["status"] == 1 || $order["status"] == 2): ?>
					<?= $JML->oneButtonForm("Mark as returned", "/janitor/admin/shop/updateShippingStatus/$order_id/".$order_item["id"], array(
						"inputs" => array("shipped" => 0),
						"wrapper" => "li.shipped",
						"static" => true,
						"confirm-value" => "Yes, the item has been returned"
					)) ?>
					<?= $JML->oneButtonForm("Mark as shipped", "/janitor/admin/shop/updateShippingStatus/$order_id/".$order_item["id"], array(
						"inputs" => array("shipped" => 1),
						"class" => "secondary",
						"wrapper" => "li.not_shipped",
						"static" => true
					)) ?>
					<? endif;*/ ?>
				</ul>
			</li>
			<? endforeach; ?>
		</ul>
		<? else: ?>
		<p>No Items in order</p>
		<? endif; ?>

		<? /*if($order["status"] == 0): ?>
		<ul class="actions">
			<?= $HTML->link("Add item", "/janitor/admin/shop/order/item/new/".$order["id"], array("class" => "button primary", "wrapper" => "li.cancel")) ?>
		</ul>
		<? endif; */?>
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
	<? if($reminders): ?>
		<p>Reminded <?= pluralize(count($reminders), "time", "times") ?>, Last on <?= date("Y-m-d H:i", strtotime($reminders[0]["created_at"])); ?></p>
	<? endif; ?>
		<ul class="actions">
			<?= $HTML->link("Pay order", "/janitor/admin/shop/order/payment/new/".$order["id"], array("class" => "button primary", "wrapper" => "li.pay")) ?>
		</ul>
<? endif; ?>

	</div>

	<div class="delivery i:collapseHeader">
		<h2>Delivery</h2>

		<? if($order["status"] == 0): ?>
		<?= $model->formStart("/janitor/admin/shop/updateOrder/".$order_id, array("class" => "i:editDataSection labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("delivery_address_id", array(
					"type" => "select",
					"options" => $delivery_address_options
				)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
		<? endif; ?>

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

		<? if($order["status"] == 0): ?>
		<?= $model->formStart("/janitor/admin/shop/updateOrder/".$order_id, array("class" => "i:editDataSection labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("billing_address_id", array(
					"type" => "select",
					"options" => $billing_address_options
				)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
		<? endif; ?>

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

</div>
