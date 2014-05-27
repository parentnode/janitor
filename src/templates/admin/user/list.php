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
		<li class="new"><a href="/admin/user/new" class="button primary">New user</a></li>
		<li class="usergroup"><a href="/admin/user/group/list" class="button">User groups</a></li>
	</ul>

<?	if($user_groups): ?>
	<ul class="userGroups">
<?		foreach($user_groups as $user_group): ?>
		<li class="<?= $user_group["id"] == $user_group_id ? "selected" : "" ?>"><a href="/admin/user/list/<?= $user_group["id"] ?>"><?= $user_group["user_group"] ?></a></li>
<?		endforeach; ?>
	</ul>
<?	else: ?>
	<p>You have no user groups. Create at least one user group before you continue.</p>
<?	endif; ?>


	<div class="all_items i:defaultList filters">
<?		if($users): ?>
		<ul class="items">
<?			foreach($users as $user): ?>
			<li class="item item_id:<?= $user["id"] ?>">
				<h2><?= $user["nickname"] ?></h2>
				<dl class="list">
					<?	foreach($user as $attribute => $value): ?>
					<? if($attribute != "items"): ?>
						<dt><?= $attribute ?></dt>
						<dd><?= $user[$attribute] ?></dd>
					<? endif; ?>
					<? endforeach;?>
				</dl>

				<ul class="actions">
					<li class="edit"><a href="/admin/user/edit/<?= $user["id"] ?>" class="button">Edit</a></li>
					<li class="delete">
						<form action="/admin/user/delete/<?= $user["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
							<input type="submit" value="Delete" class="button delete" />
						</form>
					</li>
					<li class="status <?= ($user["status"] == 1 ? "enabled" : "disabled") ?>">
						<form class="disable" action="/admin/user/disable/<?= $user["id"] ?>" method="post">
							<input type="submit" class="button status" value="Disable">
						</form>
						<form class="enable" action="/admin/user/enable/<?= $user["id"] ?>" method="post">
							<input type="submit" class="button status" value="Enable">
						</form>
					</li>


					<!--li class="status">
						<form action="/admin/user/<?= ($user["status"] == 1 ? "disable" : "enable") ?>/<?= $user["id"] ?>" class="i:formDefaultStatus" method="post" enctype="multipart/form-data">
							<input type="submit" value="<?= ($user["status"] == 1 ? "Disable" : "Enable") ?>" class="button status" />
						</form>
					</li-->
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No users.</p>
<?		endif; ?>
	</div>

</div>