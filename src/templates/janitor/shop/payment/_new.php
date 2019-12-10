<?php
global $action;
global $model;
$IC = new Items();

$orders = $model->getOrders(array("status" => $status));

?>
<div class="scene i:scene defaultEdit shopView newPayment">
	<h1>New payment</h1>

	<ul class="actions">
		<?= $HTML->link("Back to payments", "/janitor/admin/shop/payment/".$order_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

<? if($payable_amount <= 0): ?>

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
			<dd class="total_order_price"><?= formatPrice(["price" => $payable_amount, "currency" => $order["currency"]]) ?></dd>
			<dt>Modified at</dt>
			<dd><?= ($order["modified_at"] ? $order["modified_at"] : "Never") ?></dd>
			<dt>Currency</dt>
			<dd><?= $order["currency"] ?></dd>
			<dt>Country</dt>
			<dd><?= $order["country"] ?></dd>
		</dl>
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

<? if($payable_amount > 0): ?>
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