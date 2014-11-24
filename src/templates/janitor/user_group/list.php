<?php
global $action;
global $model;

$user_groups = $model->getUserGroups();

?>
<div class="scene defaultList usergroupList">
	<h1>User groups</h1>

	<ul class="actions">
		<?= $HTML->link("New group", "/janitor/admin/user/group/new", array("class" => "button primary key:n", "wrapper" => "li.new")) ?>
		<?= $HTML->link("Users", "/janitor/admin/user/list", array("class" => "button key:n", "wrapper" => "li.users")) ?>
	</ul>

	<div class="all_items i:defaultList filters">
<?		if($user_groups): ?>
		<ul class="items">
<?			foreach($user_groups as $user_group): ?>
			<li class="item item_id:<?= $user_group["id"] ?>">
				<h3><?= $user_group["user_group"] ?></h3>

				<ul class="actions">
					<?= $HTML->link("Access", "/janitor/admin/user/access/edit/".$user_group["id"], array("class" => "button", "wrapper" => "li.access")) ?>
					<?= $HTML->link("Edit", "/janitor/admin/user/group/edit/".$user_group["id"], array("class" => "button", "wrapper" => "li.view")) ?>
					<?= $JML->deleteButton("Delete", "/janitor/admin/user/deleteUserGroup/".$user_group["id"]) ?>
				</ul>
			 </li>
<?			endforeach; ?>
		</ul>
<?		else: ?>
		<p>No user groups.</p>
<?		endif; ?>
	</div>

</div>
