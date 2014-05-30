<?php
global $action;
global $model;

$user_groups = $model->getUserGroups();

if(count($action) > 1) {
	$user_group_id = $action[1];
}
else {
	// simple users are always user_group 1
	$user_group_id = 1;
}

$users = $model->getUsers(array("user_group_id" => $user_group_id));

// print_r($carts);

?>
<div class="scene defaultList userList">
	<h1>Users</h1>

	<ul class="actions">
		<?= $HTML->link("New user", "/admin/user/new", array("class" => "button primary", "wrapper" => "li.new")) ?>
		<?= $HTML->link("User groups", "/admin/user/group/list", array("class" => "button", "wrapper" => "li.usergroup")) ?>
	</ul>

<?	if($user_groups): ?>
	<ul class="userGroups">
<?		foreach($user_groups as $user_group): ?>
		<?= $HTML->link($user_group["user_group"], "/admin/user/list/".$user_group["id"], array("wrapper" => "li.".($user_group["id"] == $user_group_id ? "selected" : ""))) ?>
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
				<h3><?= $item["nickname"] ?></h3>

				<ul class="actions">
					<?= $HTML->link("Edit", "/admin/user/edit/".$item["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					<?= $HTML->delete("Delete", "/admin/user/delete/".$item["id"]) ?>
					<?= $HTML->status("Enable", "Disable", "/admin/user/status", $item) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No users.</p>
<?		endif; ?>
	</div>

</div>