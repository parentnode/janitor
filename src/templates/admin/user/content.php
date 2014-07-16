<?php
global $action;
global $model;


$user_id = $action[1];
$IC = new Item();


$user = $model->getUsers(array("user_id" => $user_id));
$items = $IC->getItems(array("user_id" => $user_id));

if(defined("SITE_SHOP") && SITE_SHOP) {
	$SC = new Shop();

	$orders = $SC->getOrders(array("user_id" => $user_id));
}

?>
<div class="scene defaultList userContentList">
	<h1>Content for <?= $user["nickname"] ?></h1>

	<ul class="actions">
		<?= $HTML->link("All users", "/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>


	<ul class="views">
		<?= $HTML->link("Profile", "/admin/user/edit/".$user["id"], array("wrapper" => "li.profile")) ?>
<?		if(defined("SITE_SHOP") && SITE_SHOP): ?>
		<?= $HTML->link("Content and orders", "/admin/user/content/".$user["id"], array("wrapper" => "li.content.selected")) ?>
<?		else: ?>
		<?= $HTML->link("Content", "/admin/user/content/".$user["id"], array("wrapper" => "li.content.selected")) ?>
<?		endif; ?>
	</ul>


	<h2>Orders</h2>
	<div class="all_items orders i:defaultList filters">
<?		if($orders): ?>
		<ul class="items">
<?			foreach($orders as $order): ?>
			<li class="item">
				<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items" ) ?>)</h3>

				<ul class="actions">
					<?= $HTML->link("View", "/admin/shop/order/view/".$order["id"], array("class" => "button", "wrapper" => "li.view")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No orders.</p>
<?		endif; ?>
	</div>


	<h2>Items</h2>
	<div class="all_items content i:defaultList filters">
<? 		if($items): ?>
		<ul class="items">
<? 			foreach($items as $item):
				$item = $IC->extendItem($item); ?>
			<li class="item item_id:<?= $item["item_id"] ?>">
				<h3><?= $item["name"] ?> (<?= $item["itemtype"] ?>)</h3>

				<ul class="actions">
					<?= $HTML->link("Edit", "/admin/".$item["itemtype"]."/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					<?= $HTML->statusButton("Enable", "Disable", "/admin/cms/status", $item, array("js" => true)) ?>
				</ul>
			</li>
<? 			endforeach; ?>
		</ul>
<? 		else: ?>
		<p>No items.</p>
<? 		endif; ?>
	</div>



</div>