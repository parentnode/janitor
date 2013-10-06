<?php

$action = $this->actions();

$model = new User();
// check if custom function exists on cart class
$users = $model->getUsers();

// print_r($carts);

?>
<div class="scene i:defaultList defaultList usersList">
	<h1>Users</h1>

	<ul class="actions">
		<li class="new"><a href="/admin/users/new" class="button primary">New user</a></li>
	</ul>

	<div class="all_items">
<?		if($users): ?>
		<ul class="items">
<?			foreach($users as $user): 
				//$item = $IC->getCompleteItem($item["id"]); 
				?>
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
					<li class="view"><a href="/admin/users/edit/<?= $user["id"] ?>" class="button">Edit</a></li>
					<li class="delete">
						<form action="/admin/users/delete/<?= $user["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
							<input type="submit" value="Delete" class="button delete" />
						</form>
					</li>
					<li class="status">
						<form action="/admin/users/<?= ($user["status"] == 1 ? "disable" : "enable") ?>/<?= $user["id"] ?>" class="i:formDefaultStatus" method="post" enctype="multipart/form-data">
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