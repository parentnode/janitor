<?php
global $action;
global $model;
$IC = new Items();

include_once("classes/users/superuser.class.php");
$UC = new SuperUser();

$order_id = $action[3];

$order = $model->getOrders(array("order_id" => $order_id));

// calculate remaining payment
$payment_amount = 0;
// if($order && $order["payment_status"] != 2) {
if($order) {

	$total_order_price = $model->getTotalOrderPrice($order["id"]);

	$payments = $model->getPayments(["order_id" => $order["id"]]);
	$total_payments = 0;
	if($payments) {
		foreach($payments as $payment) {
			$total_payments += $payment["payment_amount"];
		}
	}

	$payment_amount = $total_order_price["price"]-$total_payments; //formatPrice($total_order_price);

}

// Split payment methods into gateway and manual
$currency = $this->currencies($order["currency"]);
$payment_methods = $this->paymentMethods();

// $user_payment_methods = $UC->getPaymentMethods(["user_id" => $order["user_id"], "extend" => true]);

$payment_intent = payments()->canBeCaptured(["user_id" => $order["user_id"], "order_id" => $order["id"], "amount" => $payment_amount]);

// debug(["user_payment_methods", $user_payment_methods]);

$payment_sources = [];
foreach($payment_methods as $payment_method) {

	if(!$payment_method["gateway"]) {
		$payment_sources[] = $payment_method;
	}

}


// get previous reminders
$payment_reminders = $model->getPaymentReminders(["order_id" => $order["id"]]);

?>
<div class="scene i:scene defaultEdit shopView newPayment">
	<h1>New payment</h1>
	<h2>Order: <?= $order["order_no"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back to order", "/janitor/admin/shop/order/edit/".$order_id, array("class" => "button", "wrapper" => "li.back")) ?>
	</ul>

<? if($payment_amount <= 0): ?>

	<div class="notice">
		<p>This order is fully paid.</p>
	</div>

<? endif; ?>


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
			<dd class="remaining_order_price"><?= formatPrice(["price" => $payment_amount, "currency" => $order["currency"]]) ?></dd>
			<dt>Modified at</dt>
			<dd><?= ($order["modified_at"] ? $order["modified_at"] : "Never") ?></dd>
			<dt>Currency</dt>
			<dd><?= $order["currency"] ?></dd>
			<dt>Country</dt>
			<dd><?= $order["country"] ?></dd>
		</dl>
	</div>


<? if($payment_amount > 0): ?>
	<div class="capture i:collapseHeader i:capturePaymentNew">
		<h2>Capture payment now</h2>
		<p>
			The payment can be captured now.
		</p>
		<? if($payment_intent): ?>

		<ul class="actions">
			<?= $HTML->oneButtonForm(
			"Capture ".formatPrice(array("price" => $payment_amount, "vat" => 0, "currency" => $order["currency"], "country" => $order["country"]))." – from card ending in ".$payment_intent["last4"], 
			"capturePayment",
			array(
				"inputs" => array(
					"payment_intent_id" => $payment_intent["payment_intent_id"],
					"payment_amount" => $payment_amount,
				),
				"confirm-value" => "Yes, I'm serious",
				"class" => "capture",
				"name" => "delete",
				"wrapper" => "li.capture",
			)) ?>
		</ul>

		<? else: ?>
		<p class="note">
			Payment cannot be automatically captured, because we don't have sufficient information available to charge the 
			client for this order. You can choose to send a payment reminder to the user (under the reminder section).
		</p>
		<? endif; ?>

	</div>
<? endif; ?>

	<div class="register i:collapseHeader">
		<h2>Register received payment</h2>
		<p>If you have received payment, you can register it here to balance the order system.</p>
		<?= $model->formStart("/janitor/admin/shop/registerPayment", array("class" => "i:defaultNew labelstyle:inject")) ?>
			<?= $model->input("order_id", array("type" => "hidden", "value" => $order_id)) ?>
			<?= $model->input("return_to", array("type" => "hidden", "value" => "/janitor/admin/shop/order/edit/".$order_id)) ?>
			<fieldset>
				<?= $model->input("payment_method_id", array(
					"type" => "select",
					"options" => $model->toOptions($payment_sources, "id", "name"),
				)) ?>

				<?= $model->input("payment_amount", array("value" => $payment_amount)) ?>

				<?= $model->input("transaction_id") ?>

			</fieldset>

			<ul class="actions">
				<?= $model->submit("Add payment", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

<? if($payment_amount > 0): ?>
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
			<?= $HTML->oneButtonForm("Send payment reminder", "/janitor/admin/shop/sendPaymentReminder", array(
				"inputs" => array("order_id" => $order["id"]),
				"confirm-value" => "Are you sure?",
				"success-location" => "/janitor/admin/shop/order/edit/".$order_id,
				"class" => "primary",
				"name" => "send",
				"wrapper" => "li.send",
			)) ?>
		</ul>
	</div>
<? endif; ?>


</div>