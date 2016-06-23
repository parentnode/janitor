<?php
global $action;
global $model;


$user_id = $action[1];


$user = $model->getUsers(array("user_id" => $user_id));
$subscriptions = false;

if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS) {

	$subscriptions = $model->getSubscriptions(array("user_id" => $user_id));
}

?>
<div class="scene i:scene defaultList userContentList">
	<h1>Subscriptions</h1>
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
		<?= $HTML->link("Orders", "/janitor/admin/user/orders/".$user_id, array("wrapper" => "li.orders")) ?>
<?		endif; ?>
<?		if(defined("SITE_SUBSCRIPTIONS") && SITE_SUBSCRIPTIONS): ?>
		<?= $HTML->link("Subscriptions", "/janitor/admin/user/subscriptions/".$user_id, array("wrapper" => "li.subscriptions.selected")) ?>
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