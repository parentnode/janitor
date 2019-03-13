<?php
global $action;
global $model;
$IC = new Items();

$order_id = $action[3];

$order = $model->getOrders(array("order_id" => $order_id));

// calculate remaining payment
$payable_amount = 0;
if($order && $order["payment_status"] != 2) {

	$total_order_price = $model->getTotalOrderPrice($order["id"]);

	$payments = $model->getPayments(["order_id" => $order["id"]]);
	$total_payments = 0;
	if($payments) {
		foreach($payments as $payment) {
			$total_payments += $payment["payment_amount"];
		}
	}

	$payable_amount = $total_order_price["price"]-$total_payments; //formatPrice($total_order_price);

}

// Split payment methods into gateway and manual
$currency = $this->currencies($order["currency"]);
$payment_methods = $this->paymentMethods();
$payment_sources = [];
$payment_gateways = [];
foreach($payment_methods as $payment_method) {

//	if($payment_method["classname"] != "disabled") {

		if($payment_method["gateway"]) {
			$payment_gateways[] = $payment_method;
		}
		else {
			$payment_sources[] = $payment_method;
		}

//	}

}


// get previous reminders
$payment_reminders = $model->getPaymentReminders(["order_id" => $order["id"]]);

?>
<div class="scene i:scene defaultEdit shopView newPayment">
	<h1>New payment</h1>
	<h2>Order: <?= $order["order_no"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back to order", "/janitor/admin/shop/order/edit/".$order_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

<? if($payable_amount > 0): ?>
	<p>All payments to this order must be stated in <?= $currency["abbreviation"] ?> (order currency).</p>

	<div class="basics i:collapseHeader">
		<h2>Details</h2>
		<dl class="info">
			<dt>Order No.</dt>
			<dd><?= $order["order_no"] ?></dd>
			<dt>Total price</dt>
			<dd class="total_order_price"><?= formatPrice($total_order_price) ?></dd>
			<dt>Created at</dt>
			<dd><?= $order["created_at"] ?></dd>
			<dt>Remaining</dt>
			<dd class="total_order_price"><?= formatPrice(["price" => $payable_amount, "currency" => $order["currency"]]) ?></dd>
			<dt>Modified at</dt>
			<dd><?= ($order["modified_at"] ? $order["modified_at"] : "Never") ?></dd>
			<dt>Currency</dt>
			<dd><?= $order["currency"] ?></dd>
			<dt>Country</dt>
			<dd><?= $order["country"] ?></dd>
		</dl>
	</div>


	<div class="charge i:collapseHeader">
		<h2>Charge payment now</h2>
		<p>
			The payment can be charged directly from the listed payment gateways, if the buttons are active.
		</p>
		<p class="note">
			If the buttons are disabled, it means we don't have sufficient information available to charge the 
			current client. In that case, you can choose to send a payment reminder to the user (under the reminder section).
		</p>

		<ul class="actions">
		<? foreach($payment_gateways as $payment_method): ?>
			<? if($model->canBeCharged(["user_id" => $order["user_id"], "gateway" => $payment_method["gateway"]])): ?>
				<?= $JML->oneButtonForm("Charge ".formatPrice(array("price" => $payable_amount, "vat" => 0, "currency" => $order["currency"], "country" => $order["country"]))." from ".$payment_method["name"] . " (".$payment_method["gateway"].")", "/janitor/admin/shop/chargeRemainingOrderPayment", array(
					"inputs" => array("order_id" => $order["id"], "payment_method" => $payment_method["id"]),
					"confirm-value" => "Are you sure?",
					"success-location" => "/janitor/admin/shop/order/edit/".$order_id,
					"class" => "primary",
					"name" => "charge",
					"wrapper" => "li.charge.".$payment_method["classname"],
				)) ?>
			<? else: ?>
				<li class="disabled"><a class="button disabled">Charge <?= formatPrice(array(
					"price" => $payable_amount, 
					"vat" => 0, 
					"currency" => $order["currency"], 
					"country" => $order["country"]
				))." from ".$payment_method["name"] . " (".$payment_method["gateway"].")" ?></a></li>
			<? endif; ?>
		<? endforeach; ?>
		</ul>
	</div>

	<div class="register i:collapseHeader">
		<h2>Register received payment</h2>
		<p>If you have received payment, you can register it here to balance the order system.</p>
		<?= $model->formStart("/janitor/admin/shop/registerPayment", array("class" => "i:defaultNew labelstyle:inject")) ?>
			<?= $model->input("order_id", array("type" => "hidden", "value" => $order_id)) ?>
			<?= $model->input("return_to", array("type" => "hidden", "value" => "/janitor/admin/shop/order/edit/".$order_id)) ?>
			<fieldset>
				<?= $model->input("payment_method", array(
					"type" => "select",
					"options" => $model->toOptions($payment_sources, "id", "name"),
				)) ?>

				<?= $model->input("payment_amount", array("value" => $payable_amount)) ?>

				<?= $model->input("transaction_id") ?>

			</fieldset>

			<ul class="actions">
				<?= $model->submit("Add payment", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

	<div class="reminder i:collapseHeader">
		<h2>Reminders</h2>

		<? if($payment_reminders): ?>
			<p>The user has been reminded to pay this order on:</p>
			<ul class="info">
			<? foreach($payment_reminders as $payment_reminder): ?>
				<li><?= $payment_reminder["created_at"] ?></li>
			<? endforeach; ?>
			</ul>
		<? else: ?>
			<p>The user has not be reminded to pay this order yet.</p>
		<? endif; ?>


		<p>
			Send an email to remind the user to pay. The email contains a link directly to the payment
			page.
		</p>

		<ul class="actions">
			<?= $JML->oneButtonForm("Send payment reminder", "/janitor/admin/shop/sendPaymentReminder", array(
				"inputs" => array("order_id" => $order["id"]),
				"confirm-value" => "Are you sure?",
				"success-location" => "/janitor/admin/shop/order/edit/".$order_id,
				"class" => "primary",
				"name" => "send",
				"wrapper" => "li.send",
			)) ?>
		</ul>
	</div>


<? else: ?>

	<p>This order is fully paid.</p>

<? endif; ?>

</div>