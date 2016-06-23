<?php
global $action;
global $model;


$user_id = session()->value("user_id");
$IC = new Items();


// get current user
$item = $model->getUser();

$orders = false;

if(defined("SITE_SHOP") && SITE_SHOP) {
	$SC = new Shop();

	$orders = $SC->getOrders(array("user_id" => $user_id));
}

?>
<div class="scene i:scene defaultList userContentList profileContentList">
	<h1>Orders</h1>
	<h2><?= $item["nickname"] ?></h2>


	<ul class="tabs">
		<?= $HTML->link("Profile", "/janitor/admin/profile", array("wrapper" => "li.profile")) ?>
<?		if(defined("SITE_ITEMS") && SITE_ITEMS): ?>
		<?= $HTML->link("Content", "/janitor/admin/profile/content", array("wrapper" => "li.content")) ?>
<?		endif; ?>
<?		if(defined("SITE_SHOP") && SITE_SHOP): ?>
		<?= $HTML->link("Orders", "/janitor/admin/profile/orders", array("wrapper" => "li.orders.selected")) ?>
<?		endif; ?>
<?		if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS): ?>
		<?= $HTML->link("Subscriptions", "/janitor/admin/profile/subscriptions", array("wrapper" => "li.subscriptions")) ?>
<?		endif; ?>
	</ul>


	<div class="all_items orders i:defaultList filters">
		<h2>Orders</h2>
<?		if($orders): ?>
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