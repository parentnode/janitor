<?php
global $action;
global $model;


$user_id = $action[1];
$IC = new Items();


$user = $model->getUsers(array("user_id" => $user_id));

$orders = false;
$carts = false;

if(defined("SITE_SHOP") && SITE_SHOP) {
	include_once("classes/shop/supershop.class.php");
	$SC = new SuperShop();

	$orders = $SC->getOrders(array("user_id" => $user_id));
	$carts = $SC->getCarts(array("user_id" => $user_id));
}

?>
<div class="scene i:scene defaultList userContentList">
	<h1>Orders</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.list")) ?>
	</ul>


	<?= $JML->userTabs($user_id, "orders") ?>



	<div class="all_items orders i:defaultList filters">
		<h2>Orders</h2>
<?		if($orders): ?>
		<ul class="items">
<?			foreach($orders as $order): ?>
			<li class="item">
				<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items" ) ?>)</h3>

				<dl class="details">
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $order["created_at"] ?></dd>
					<dt class="status">Status</dt>
					<dd class="status"><?= $SC->order_statuses[$order["status"]] ?></dd>
					<dt class="payment_status">Payment status</dt>
					<dd class="payment_status<?= $order["payment_status"] < 2 ? " missing" : "" ?>"><?= $SC->payment_statuses[$order["payment_status"]] ?></dd>
					<dt class="price">Total price</dt>
					<dd class="price"><?= formatPrice($SC->getTotalOrderPrice($order["id"])) ?></dd>
				</dl>

				<ul class="actions">
					<?= $HTML->link("View", "/janitor/admin/shop/order/edit/".$order["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No orders.</p>
<?		endif; ?>
	</div>


	<div class="all_items cartd i:defaultList filters">
		<h2>Carts</h2>
<?		if($carts): ?>
		<ul class="items">
<?			foreach($carts as $cart): ?>
			<li class="item">
				<h3><?= $cart["cart_reference"] ?> (<?= pluralize(count($cart["items"]), "item", "items" ) ?>)</h3>

				<dl class="details">
					<dt class="created_at">Created at</dt>
					<dd class="created_at"><?= $cart["created_at"] ?></dd>
					<dt class="price">Total price</dt>
					<dd class="price"><?= formatPrice($SC->getTotalCartPrice($cart["id"])) ?></dd>
				</dl>

				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/shop/cart/edit/".$cart["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No carts.</p>
<?		endif; ?>
	</div>


</div>