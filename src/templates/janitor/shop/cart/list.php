<?php
global $action;
global $model;

$IC = new Items();
$carts = $model->getCarts();

?>
<div class="scene i:scene defaultList cartList">
	<h1>Carts</h1>

	<ul class="actions">
		<?= $HTML->link("New cart", "/janitor/admin/shop/cart/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<?= $HTML->link("Orders", "/janitor/admin/shop/order/list", array("class" => "button", "wrapper" => "li.orders")) ?>
		<?= $HTML->link("Payments", "/janitor/admin/shop/payment/list", array("class" => "button", "wrapper" => "li.payments")) ?>
	</ul>

	<div class="all_items i:defaultList filters">
		<? if($carts): ?>
		<ul class="items carts">
			<? foreach($carts as $cart): ?>
			<li class="item cart">
				<h3><?= $cart["cart_reference"] ?> (<?= pluralize($cart["total_items"], "item", "items" ) ?>)</h3>

				<dl class="details">
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $cart["created_at"] ?></dd>

					<dt class="price">Total price</dt>
					<dd class="price"><?= formatPrice($model->getTotalCartPrice($cart["id"]), array("vat" => true)) ?></dd>

				<? if(isset($cart["user"])): ?>
					<dt class="nickname">Nickname</dt>
					<dd class="nickname"><?= $cart["user"]["nickname"] ?></dd>

					<? if($cart["user"]["email"]): ?>
					<dt class="email">Email</dt>
					<dd class="email"><?= $cart["user"]["email"] ?></dd>
					<? endif; ?>

					<? if($cart["user"]["mobile"]): ?>
					<dt class="mobile">Mobile</dt>
					<dd class="mobile"><?= $cart["user"]["mobile"] ?></dd>
					<? endif; ?>

				<? endif; ?>
				</dl>

				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/shop/cart/edit/".$cart["id"], array("class" => "button", "wrapper" => "li.view")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No carts.</p>
<?		endif; ?>
	</div>

</div>
