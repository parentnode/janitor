<?php
global $action;
global $model;
$IC = new Items();


$order_id = $action[3];
$order_item_id = $action[4];
$order = $model->getOrders(array("order_id" => $order_id));

if($order["items"]) {

	$order_item_index = arrayKeyValue($order["items"], "id", $order_item_id);
	if($order_item_index !== false) {
		$order_item = $order["items"][$order_item_index];
	}
	
}

?>
<div class="scene i:scene defaultEdit editOrder">
	<h1>Edit order item quantity</h1>
	<h2><?= $order["order_no"] ?> / <?= $order_item["name"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back", "/janitor/admin/shop/order/edit/".$order_id, array("class" => "button", "wrapper" => "li.orders")); ?>
	</ul>

	<div class="item i:editOrderItem">
		<h2>Order item</h2>
		<?= $model->formStart("/janitor/admin/shop/updateOrderItemQuantity/$order_id/".$order_item_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("quantity", array("value" => $order_item["quantity"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Cancel", "/janitor/admin/shop/order/edit/".$order_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update quantity", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>

		<?= $model->formEnd() ?>
	</div>

</div>