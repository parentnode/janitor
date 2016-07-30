<?php
global $action;
global $model;


$user_id = $action[1];
$IC = new Items();


$user = $model->getUsers(array("user_id" => $user_id));

$orders = false;

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
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>


	<?= $JML->userTabs($user_id, "orders") ?>



	<div class="all_items orders i:defaultList filters">
<?		if($orders): ?>
		<h2>Orders</h2>
		<ul class="items">
<?			foreach($orders as $order): ?>
			<li class="item">
				<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items" ) ?>)</h3>

				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/shop/order/edit/".$order["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No orders.</p>
<?		endif; ?>
	</div>

</div>