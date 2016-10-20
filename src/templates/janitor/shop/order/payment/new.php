<?php
global $action;
global $model;
$IC = new Items();

$order_id = $action[3];

$order = $model->getOrders(array("order_id" => $order_id));

?>
<div class="scene i:scene defaultNew newPayment">
	<h1>New manual payment</h1>
	<h2>Order: <?= $order["order_no"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back to order", "/janitor/admin/shop/order/edit/".$order_id, array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<?= $model->formStart("/janitor/admin/shop/addPayment", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<?= $model->input("order_id", array("type" => "hidden", "value" => $order_id)) ?>
		<fieldset>

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
			<?= $model->link("Back", "/janitor/admin/shop/order/edit/".$order_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Add payment", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>

</div>