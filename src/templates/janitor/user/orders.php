<?php
global $action;
global $model;


$user_id = $action[1];
$IC = new Items();


$user = $model->getUsers(array("user_id" => $user_id));

$orders = false;

if(defined("SITE_SHOP") && SITE_SHOP) {
	$SC = new Shop();

	$orders = $SC->getOrders(array("user_id" => $user_id));
}

?>
<div class="scene i:scene defaultList userContentList">
	<h1>Orders</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->link("User groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.usergroup")) ?>
	</ul>


	<ul class="tabs">
		<?= $HTML->link("Profile", "/janitor/admin/user/edit/".$user_id, array("wrapper" => "li.profile")) ?>
<?		if(defined("SITE_ITEMS") && SITE_ITEMS): ?>
		<?= $HTML->link("Content", "/janitor/admin/user/content/".$user_id, array("wrapper" => "li.content")) ?>
<?		endif; ?>
<?		if(defined("SITE_SHOP") && SITE_SHOP): ?>
		<?= $HTML->link("Orders", "/janitor/admin/user/orders/".$user_id, array("wrapper" => "li.orders.selected")) ?>
<?		endif; ?>
<?		if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS): ?>
		<?= $HTML->link("Subscriptions", "/janitor/admin/user/subscriptions/".$user_id, array("wrapper" => "li.subscriptions")) ?>
<?		endif; ?>
	</ul>


	<div class="all_items orders i:defaultList filters">
<?		if($orders): ?>
		<h2>Orders</h2>
		<ul class="items">
<?			foreach($orders as $order): ?>
			<li class="item">
				<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items" ) ?>)</h3>

				<ul class="actions">
					<?= $HTML->link("View", "/janitor/admin/shop/order/view/".$order["id"], array("class" => "button", "wrapper" => "li.view")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No orders.</p>
<?		endif; ?>
	</div>

</div>