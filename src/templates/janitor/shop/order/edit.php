<?php
global $action;
global $model;

$order_id = $action[2];
$order = $model->getOrders(array("order_id" => $action[2]));

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

$return_to_orderstatus = session()->value("return_to_orderstatus");

?>
<div class="scene i:scene defaultEdit orderView">
<? if($order["status"] == 0): ?>
	<h1>Edit order</h1>
<? else: ?>
	<h1>View order</h1>
<? endif; ?>
	<h2><?= $order["order_no"] ?></h2>

	<ul class="actions i:defaultEditActions">
		<?= $HTML->link("Order list", "/janitor/admin/shop/order/list/".$return_to_orderstatus, array("class" => "button", "wrapper" => "li.cancel")) ?>
<? if($order["status"] == 0): ?>
		<?= $JML->deleteButton("Delete", "/janitor/admin/shop/deleteOrder/".$order["id"]."/".$order["user_id"]) ?>
<? endif; ?>

	</ul>

	<div class="orderstatus">
		<h2>Order status</h2>

		<?= $model->formStart("/janitor/admin/shop/updateOrderStatus/".$order_id."/".$order["user_id"], array("class" => "i:editOrder labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("order_status", array(
					"type" => "select",
					"options" => $model->order_statuses,
					"value" => $order["status"]
				)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

		<dl class="list <?= superNormalize($model->order_statuses[$order["status"]]) ?>">
			<dt class="status">Status</dt>
			<dd class="status"><?= $model->order_statuses[$order["status"]] ?></dd>
		</dl>
	</div>

	<div class="basics">
		<h2>Order</h2>

		<? if($order["status"] == 0): ?>
		<?= $model->formStart("/janitor/admin/shop/updateOrder/".$order_id, array("class" => "i:editOrder labelstyle:inject")) ?>
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

		<dl class="list">
			<dt>Order No.</dt>
			<dd><?= $order["order_no"] ?></dd>
			<dt>Total price</dt>
			<dd><?= formatPrice($model->getTotalOrderPrice($order["id"])) ?></dd>
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

	<div class="contact">
		<h2>Contact</h2>
		<dl class="list">
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

	<div class="all_items i:defaultList">
		<h2>Order items</h2>
		<? if($order["items"]): ?>
		<ul class="items">
			<? foreach($order["items"] as $order_item): ?>
			<li class="item">
				<h3><?= $order_item["quantity"] ?> x <?= $order_item["name"] ?> á 					 
					<?= formatPrice(array(
							"price" => $order_item["unit_price"], 
							"vat" => $order_item["unit_vat"], 
							"currency" => $order["currency"], 
							"country" => $order["country"]
					)) ?>
					<span class="price">
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

				<? if($order["status"] == 0): ?>
				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/shop/order/item/edit/$order_id/".$order_item["id"], array("class" => "button primary", "wrapper" => "li.users")) ?>
					<?= $JML->deleteButton("Delete", "/janitor/admin/shop/deleteFromOrder/$order_id/".$order_item["id"]) ?>
				</ul>
				<? endif; ?>
			</li>
			<? endforeach; ?>
		</ul>
		<? else: ?>
		<p>No Items in order</p>
		<? endif; ?>

		<? if($order["status"] == 0): ?>
		<ul class="actions">
			<?= $HTML->link("Add item", "/janitor/admin/shop/order/item/new/".$order["id"], array("class" => "button primary", "wrapper" => "li.cancel")) ?>
		</ul>
		<? endif; ?>
	</div>

	<div class="comment">
		<h2>Order comment</h2>
		<? if($order["status"] == 0): ?>
		<?= $model->formStart("/janitor/admin/shop/updateOrder/".$order_id, array("class" => "i:editOrder labelstyle:inject")) ?>
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

	<div class="delivery">
		<h2>Delivery</h2>

		<? if($order["status"] == 0): ?>
		<?= $model->formStart("/janitor/admin/shop/updateOrder/".$order_id, array("class" => "i:editOrder labelstyle:inject")) ?>
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

		<dl class="list">
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

	<div class="billing">
		<h2>Billing</h2>

		<? if($order["status"] == 0): ?>
		<?= $model->formStart("/janitor/admin/shop/updateOrder/".$order_id, array("class" => "i:editOrder labelstyle:inject")) ?>
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

		<dl class="list">
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
