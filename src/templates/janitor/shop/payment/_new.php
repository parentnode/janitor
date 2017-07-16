<?php
global $action;
global $model;
$IC = new Items();

$order_id = false;
if(count($action) > 2) {
	$order_id = $action[2];
}

$pending_orders = $model->getOrders(array("status" => 0));
$waiting_orders = $model->getOrders(array("status" => 1));
if($pending_orders && $waiting_orders) {
	$orders = array_merge($pending_orders, $waiting_orders);
}
else if($pending_orders) {
	$orders = $pending_orders;
}
else if($waiting_orders) {
	$orders = $waiting_orders;
}

$order_options = $model->toOptions($orders, "id", "order_no", array("add" => array("" => "Select order")));

?>
<div class="scene i:scene defaultNew newPayment">
	<h1>New payment</h1>

	<ul class="actions">
		<?= $HTML->link("Back to payments", "/janitor/admin/shop/payment/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<p>
		Please search and find the related order. (A payment must be related to an order.)
	</p>
	<?
	// TODO: implement order search, which redirects to shop/order/payment/new/#order_id# (the only payment form to maintain)
	// OLD MANUAL PAYMENT FORM
	?>	

	<?= $model->formStart("/janitor/admin/shop/addPayment", array("class" => "i:defaultPayment labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("order_id", array(
				"type" => "select",
				"required" => true,
				"options" => $order_options,
				"value" => $order_id,
				"hint_message" => "Select order to associate payment with.",
				"error_message" => "A payment must be associated with an order."
			)) ?>

			<?= $model->input("currency", array(
				"type" => "select",
				"options" => $model->toOptions($this->currencies(), "id", "name"),
			)) ?>
			<?= $model->input("payment_method", array(
				"type" => "select",
				"options" => $model->toOptions($this->paymentMethods(), "id", "name"),
			)) ?>

			<?= $model->input("payment_amount", array()) ?>

		</fieldset>

		<ul class="actions">
			<?= $model->link("Back", "/janitor/admin/shop/payment/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Add payment", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>