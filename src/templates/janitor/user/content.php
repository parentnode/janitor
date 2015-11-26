<?php
global $action;
global $model;


$user_id = $action[1];
$IC = new Items();


$user = $model->getUsers(array("user_id" => $user_id));
$items = $IC->getItems(array("user_id" => $user_id, "extend" => true));
$comments = $IC->getComments(array("user_id" => $user_id));

$orders = false;

if(defined("SITE_SHOP") && SITE_SHOP) {
	$SC = new Shop();

	$orders = $SC->getOrders(array("user_id" => $user_id));
}

?>
<div class="scene defaultList userContentList">
	<h1>Content for <?= $user["nickname"] ?></h1>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $HTML->link("User groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.usergroup")) ?>
	</ul>


	<ul class="views">
		<?= $HTML->link("Profile", "/janitor/admin/user/edit/".$user["id"], array("wrapper" => "li.profile")) ?>
<?		if(defined("SITE_SHOP") && SITE_SHOP): ?>
		<?= $HTML->link("Content and orders", "/janitor/admin/user/content/".$user["id"], array("wrapper" => "li.content.selected")) ?>
<?		else: ?>
		<?= $HTML->link("Content", "/janitor/admin/user/content/".$user["id"], array("wrapper" => "li.content.selected")) ?>
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
					<?= $HTML->link("View", "/janitor/admin/shop/order/view/".$order["id"], array("class" => "button", "wrapper" => "li.view")) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No orders.</p>
<?		endif; ?>
	</div>


	<h2>Items</h2>
	<div class="all_items content i:defaultList filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
<? 		if($items): ?>
		<ul class="items">
<? 			foreach($items as $item):

				// find path to itemtype
				// We don know whether it is an inherited controller or a local one
				// - look in the two most obvious places
				if(file_exists(LOCAL_PATH."/www/janitor/".$item["itemtype"].".php")) {
					$path = "/janitor/".$item["itemtype"];
				}
				else if(file_exists(FRAMEWORK_PATH."/www/".$item["itemtype"].".php")) {
					$path = "/janitor/admin/".$item["itemtype"];
				}
				else {
					$path = false;
				}
?>
			<li class="item item_id:<?= $item["item_id"] ?>">
				<h3><?= $item["name"] ?> (<?= $item["itemtype"] ?>)</h3>

				<ul class="actions">
					<?= $path ? $HTML->link("Edit", $path."/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) : "" ?>
					<?= $path ? $JML->statusButton("Enable", "Disable", $path."/status", $item, array("js" => true)) : "" ?>
				</ul>
			</li>
<? 			endforeach; ?>
		</ul>
<? 		else: ?>
		<p>No items.</p>
<? 		endif; ?>
	</div>


	<h2>Comments</h2>
	<div class="all_items comments i:defaultList filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
<? 		if($comments): ?>
		<ul class="items">
<? 			foreach($comments as $comment): 

				// find path to itemtype
				// We don know whether it is an inherited controller or a local one
				// - look in the two most obvious places
				if(file_exists(LOCAL_PATH."/www/janitor/".$comment["item"]["itemtype"].".php")) {
					$path = "/janitor/".$comment["item"]["itemtype"];
				}
				else if(file_exists(FRAMEWORK_PATH."/www/".$comment["item"]["itemtype"].".php")) {
					$path = "/janitor/admin/".$comment["item"]["itemtype"];
				}
				else {
					$path = false;
				}
?>
			<li class="item comment_id:<?= $comment["item_id"] ?>">
				<h3>Comment for: <?= $comment["item"]["name"] ?></h3>
				<ul class="info">
					<li class="created_at"><?= date("Y-m-d, H:i", strtotime($comment["created_at"])) ?></li>
				</ul>
				<p class="comment"><?= $comment["comment"] ?></p>

				<ul class="actions">
					<?= $path ? $HTML->link("Edit", $path."/edit/".$comment["item"]["id"], array("class" => "button", "wrapper" => "li.edit")) : "" ?>
				</ul>
			</li>
<? 			endforeach; ?>
		</ul>
<? 		else: ?>
		<p>No comments.</p>
<? 		endif; ?>
	</div>


</div>