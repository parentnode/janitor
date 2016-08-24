<?php
global $action;
global $model;

$cart_id = $action[2];
$cart = $model->getCarts(array("cart_id" => $action[2]));

include_once("classes/users/superuser.class.php");
$UC = new SuperUser();

$users = $UC->getUsers();
$user_options = $model->toOptions($users, "id", "nickname", array("add" => array("0" => "Select user")));

$IC = new Items();

?>
<div class="scene i:scene defaultEdit shopView cartView">
	<h1>Edit cart</h1>
	<h2><?= $cart["cart_reference"] ?></h2>

	<ul class="actions i:defaultEditActions">
		<?= $HTML->link("Cart list", "/janitor/admin/shop/cart/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $JML->oneButtonForm("Delete cart", "/janitor/admin/shop/deleteCart/".$cart["id"]."/".$cart["cart_reference"], array(
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/shop/cart/list"
		)) ?>
	</ul>

	<div class="basics">
		<h2>Cart</h2>

		<?= $model->formStart("/janitor/admin/shop/updateCart/".$cart["cart_reference"], array("class" => "i:editDataSection labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("country", array(
					"type" => "select",
					"options" => $model->toOptions($this->countries(), "id", "name"),
					"value" => $cart["country"]
				)) ?>
				<?= $model->input("currency", array(
					"type" => "select",
					"options" => $model->toOptions($this->currencies(), "id", "name"),
					"value" => $cart["currency"]
				)) ?>
			</fieldset>
			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

		<dl class="list">
			<dt>Cart reference</dt>
			<dd><?= $cart["cart_reference"] ?></dd>
			<dt>Total price</dt>
			<dd class="total_cart_price"><?= formatPrice($model->getTotalCartPrice($cart["id"])) ?></dd>
			<dt>Created at</dt>
			<dd><?= $cart["created_at"] ?></dd>
			<dt>Modified at</dt>
			<dd><?= ($cart["modified_at"] ? $cart["modified_at"] : "Never") ?></dd>
			<dt>Currency</dt>
			<dd><?= $cart["currency"] ?></dd>
			<dt>Country</dt>
			<dd><?= $cart["country"] ?></dd>
		</dl>
	</div>

	<div class="contact">
		<h2>Contact</h2>

		<?= $model->formStart("/janitor/admin/shop/updateCart/".$cart["cart_reference"], array("class" => "i:editDataSection labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("user_id", array(
					"type" => "select",
					"options" => $user_options,
					"value" => $cart["user_id"],
					"hint_message" => "Select user for this cart"
				)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->submit("Update", array("class" => "primary", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>

		<? if(isset($cart["user"])): ?>
		<dl class="list">
			<dt>Nickname</dt>
			<dd><?= $cart["user"]["nickname"] ?></dd>
			<dt>First</dt>
			<dd><?= $cart["user"]["firstname"] ?></dd>
			<dt>Lastname</dt>
			<dd><?= $cart["user"]["lastname"] ?></dd>
			<dt>Email</dt>
			<dd><?= $cart["user"]["email"] ?></dd>
			<dt>Mobile</dt>
			<dd><?= $cart["user"]["mobile"] ?></dd>
		</dl>
		<? endif; ?>
	</div>

	<div class="all_items i:defaultList i:cartItemsList">
		<h2>Cart items</h2>
		<? if($cart["items"]): ?>
		<ul class="items">
			<? foreach($cart["items"] as $cart_item):
				$item = $IC->getItem(array("id" => $cart_item["item_id"], "extend" => true)); 
				$price = $model->getPrice($cart_item["item_id"], array("quantity" => $cart_item["quantity"], "currency" => $cart["currency"], "country" => $cart["country"]));
			?>
			<li class="item">
				<h3>
					<?= $model->formStart("/janitor/admin/shop/updateCartItemQuantity/".$cart["cart_reference"]."/".$cart_item["id"], array("class" => "updateCartItemQuantity labelstyle:inject")) ?>
						<fieldset>
							<?= $model->input("quantity", array(
								"type" => "integer",
								"value" =>  $cart_item["quantity"],
								"hint_message" => "State the quantity of this item"
							)) ?>
						</fieldset>
						<ul class="actions">
							<?= $model->submit("Update", array("name" => "update", "wrapper" => "li.save")) ?>
						</ul>
					<?= $model->formEnd() ?>
					<span class="name">x <?= $item["name"] ?> á</span>
					<span class="unit_price"><?= formatPrice($price) ?></span>
					<span class="total_price">
						<?= formatPrice(array(
								"price" => $price["price"]*$cart_item["quantity"], 
								"vat" => $price["vat"]*$cart_item["quantity"], 
								"currency" => $cart["currency"], 
								"country" => $cart["country"]
							), 
							array("vat" => true)
						) ?>
					</span>
				</h3>

				<ul class="actions">
					<?= $JML->oneButtonForm("Delete", "/janitor/admin/shop/deleteFromCart/".$cart["cart_reference"]."/".$cart_item["id"], array(
						"wrapper" => "li.delete",
						"success-function" => "deletedFromCart",
						"static" => true
					)) ?>
				</ul>
			</li>
			<? endforeach; ?>
		</ul>
		<? else: ?>
		<p>No Items in cart</p>
		<? endif; ?>

		<ul class="actions">
			<?= $HTML->link("Add item", "/janitor/admin/shop/cart/item/new/".$cart["id"], array("class" => "button primary", "wrapper" => "li.cancel")) ?>
		</ul>
	</div>

<? if($cart["items"] && $cart["user_id"]) :?>
	<div class="order i:newOrderFromCart">
		<h2>Checkout</h2>
		<p>Start checkout process by converting this cart into an order.</p>

		<ul class="actions">
			<?= $JML->oneButtonForm("Start checkout process", "/janitor/admin/shop/newOrderFromCart/".$cart["id"]."/".$cart["cart_reference"], array(
				"inputs" => array("user_id" => $cart["user_id"], "currency" => $cart["currency"], "country" => $cart["country"], "order_comment" => "Created by admin"),
				"confirm-value" => "Create new order from this cart?",
				"class" => "primary",
				"name" => "convert",
				"wrapper" => "li.convert",
			)) ?>
		</ul>
	</div>
<? endif; ?>
</div>
