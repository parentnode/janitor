<?php
global $action;
global $model;


$user_id = session()->value("user_id");
$IC = new Items();


// get current user
$item = $model->getUser();

$subscriptions = false;

if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS) {

	$subscriptions = $model->getSubscriptions();
}

?>
<div class="scene i:scene defaultList userContentList profileContentList">
	<h1>Subscriptions</h1>
	<h2><?= $item["nickname"] ?></h2>


	<ul class="tabs">
		<?= $HTML->link("Profile", "/janitor/admin/profile", array("wrapper" => "li.profile")) ?>
<?		if(defined("SITE_ITEMS") && SITE_ITEMS): ?>
		<?= $HTML->link("Content", "/janitor/admin/profile/content", array("wrapper" => "li.content")) ?>
<?		endif; ?>
<?		if(defined("SITE_SHOP") && SITE_SHOP): ?>
		<?= $HTML->link("Orders", "/janitor/admin/profile/orders", array("wrapper" => "li.orders")) ?>
<?		endif; ?>
<?		if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS): ?>
		<?= $HTML->link("Subscriptions", "/janitor/admin/profile/subscriptions", array("wrapper" => "li.subscriptions.selected")) ?>
<?		endif; ?>
	</ul>


	<div class="all_items subscriptions i:defaultList filters">
<?		if($subscriptions): ?>
		<h2>Subscriptions</h2>
		<ul class="items">
<?			foreach($subscriptions as $subscription): ?>
			<li class="item">
				<h3><?= $subscription["name"] ?></h3>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No subscriptions.</p>
<?		endif; ?>
	</div>

</div>