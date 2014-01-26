<?php

$action = $this->actions();

$model = new User();
// check if custom function exists on cart class
$users = $model->getUsers();

// print_r($carts);

?>
<div class="scene defaultList userList">
	<h1>Users</h1>

	<ul class="actions">
		<li class="new"><a href="/admin/user/new" class="button primary">New user</a></li>
		<li class="usergroup"><a href="/admin/user/group/list" class="button">User groups</a></li>
	</ul>

	<div class="all_items i:defaultList filters">
<?		if($users): ?>
		<ul class="items">
<?			foreach($users as $user): ?>
			<li class="item">
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
					<li class="view"><a href="/admin/user/edit/<?= $user["id"] ?>" class="button">Edit</a></li>
					<li class="delete">
						<form action="/admin/user/delete/<?= $user["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
							<input type="submit" value="Delete" class="button delete" />
						</form>
					</li>
					<li class="status">
						<form action="/admin/user/<?= ($user["status"] == 1 ? "disable" : "enable") ?>/<?= $user["id"] ?>" class="i:formDefaultStatus" method="post" enctype="multipart/form-data">
							<input type="submit" value="<?= ($user["status"] == 1 ? "Disable" : "Enable") ?>" class="button status" />
						</form>
					</li>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No users.</p>
<?		endif; ?>
	</div>

</div>