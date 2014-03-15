<?php
global $action;
global $model;

$user_groups = $model->getUserGroups();

?>
<div class="scene defaultList usergroupList">
	<h1>User groups</h1>

	<ul class="actions">
		<li class="new"><a href="/admin/user/group/new" class="button primary key:n">New group</a></li>
		<li class="users"><a href="/admin/user/list" class="button key:esc">Users</a></li>
	</ul>

	<div class="all_items i:defaultList filters">
<?		if($user_groups): ?>
		<ul class="items">
<?			foreach($user_groups as $user_group): ?>
			<li class="item">
				<h2><?= $user_group["user_group"] ?>

				<ul class="actions">
					<li class="access"><a href="/admin/user/access/edit/<?= $user_group["id"] ?>" class="button">Access</a></li>
					<li class="view"><a href="/admin/user/group/edit/<?= $user_group["id"] ?>" class="button">Edit</a></li>
					<li class="delete">
						<form action="/admin/user/deleteUserGroup/<?= $user_group["id"] ?>" class="i:formDefaultDelete" method="post" enctype="multipart/form-data">
							<input type="submit" value="Delete" class="button delete" />
						</form>
					</li>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No user groups.</p>
<?		endif; ?>
	</div>

</div>
