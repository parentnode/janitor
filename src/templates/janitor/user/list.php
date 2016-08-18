<?php
global $action;
global $model;

$user_groups = $model->getUserGroups();

// show user_group users
if(count($action) > 1 && $action[1]) {
	$user_group_id = $action[1];
}
else {
	// Simple users (clients/customers/guest) are always user_group 99 (but such group might not always exist)
	if(arrayKeyValue($user_groups, "id", 99) !== false) {
		$user_group_id = 99;
	}
	// Developers are always user_group 1 (and they always exist)
	else {
		$user_group_id = 1;
	}
}

$users = $model->getUsers(array("user_group_id" => $user_group_id));
?>
<div class="scene i:scene defaultList userList">
	<h1>Users</h1>

	<ul class="actions">
		<?= $HTML->link("New user", "/janitor/admin/user/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<?= $HTML->link("User groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.usergroup")) ?>
		<?= $HTML->link("Members", "/janitor/admin/user/member/list", array("class" => "button", "wrapper" => "li.member")) ?>
		<?= $HTML->link("Online users", "/janitor/admin/user/online", array("class" => "button", "wrapper" => "li.online")) ?>
	</ul>

<?	if($user_groups): ?>
	<ul class="tabs">
<?		foreach($user_groups as $user_group): ?>
		<?= $HTML->link($user_group["user_group"], "/janitor/admin/user/list/".$user_group["id"], array("wrapper" => "li".($user_group["id"] == $user_group_id ? ".selected" : ""))) ?>
<?		endforeach; ?>
	</ul>
<?	else: ?>
	<p>You have no user groups. Create at least one user group before you continue.</p>
<?	endif; ?>


	<div class="all_items i:defaultList filters">
<?		if($users): ?>
		<ul class="items">
<?			foreach($users as $item): ?>
			<li class="item item_id:<?= $item["id"] ?>">
				<h3><?= $item["nickname"] ?><?= $item["id"] == session()->value("user_id") ? " (YOU)" : "" ?></h3>

				<ul class="actions">
					<?= $HTML->link("Edit", "/janitor/admin/user/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) ?>

<? if($item["id"] != 1): ?>
					<?= $JML->oneButtonForm("Delete", "/janitor/admin/user/delete/".$item["id"], array(
						"js" => true,
						"wrapper" => "li.delete",
						"static" => true
					)) ?>
					<?= $JML->statusButton("Enable", "Disable", "/janitor/admin/user/status", $item) ?>
<? endif; ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No users.</p>
<?		endif; ?>
	</div>

</div>