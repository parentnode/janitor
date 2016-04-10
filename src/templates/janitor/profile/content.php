<?php
global $action;
global $model;


$user_id = session()->value("user_id");
$IC = new Items();


// get current user
$item = $model->getUser();

//$user = $model->getUsers(array("user_id" => $user_id));
$items = $IC->getItems(array("user_id" => $user_id, "extend" => true));
$comments = $IC->getComments(array("user_id" => $user_id));

$orders = false;

if(defined("SITE_SHOP") && SITE_SHOP) {
	$SC = new Shop();

	$orders = $SC->getOrders(array("user_id" => $user_id));
}

?>
<div class="scene i:scene defaultList userContentList profileContentList">
	<h1>Content</h1>
	<h2><?= $item["nickname"] ?></h2>


	<ul class="tabs">
		<?= $HTML->link("Profile", "/janitor/admin/profile", array("wrapper" => "li.profile")) ?>
<?		if(defined("SITE_SHOP") && SITE_SHOP): ?>
		<?= $HTML->link("Content and orders", "/janitor/admin/profile/content", array("wrapper" => "li.content.selected")) ?>
<?		else: ?>
		<?= $HTML->link("Content", "/janitor/admin/profile/content", array("wrapper" => "li.content.selected")) ?>
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


	<div class="all_items content i:defaultList filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<h2>Items</h2>
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


	<div class="all_items comments i:defaultList filters"
		data-csrf-token="<?= session()->value("csrf") ?>"
		>
		<h2>Comments</h2>
<? 		if($comments): ?>
		<ul class="items">
<? 			foreach($comments as $comment): ?>
			<li class="item comment comment_id:<?= $comment["item_id"] ?>">
				<h3>Comment for: <?= $comment["item"]["name"] ?></h3>
				<ul class="info">
					<li class="created_at"><?= date("Y-m-d, H:i", strtotime($comment["created_at"])) ?></li>
				</ul>
				<p class="comment"><?= $comment["comment"] ?></p>
			</li>
<? 			endforeach; ?>
		</ul>
<? 		else: ?>
		<p>No comments.</p>
<? 		endif; ?>
	</div>

</div>