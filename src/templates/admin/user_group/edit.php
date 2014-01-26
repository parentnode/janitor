<?php

$action = $this->actions();

$model = new User();
// check if custom function exists on cart class
$item = $model->getUserGroups(array("user_group_id" => $action[2]));

?>

<div class="scene defaultEdit usergroupEdit">
	<h1>Edit user group</h1>

	<ul class="actions">
		<li class="cancel"><a href="/admin/user/group/list" class="button">Back</a></li>
	</ul>

	<div class="item i:defaultEdit">
		<form action="/admin/user/updateUserGroup/<?= $action[2] ?>" class="labelstyle:inject" method="post" enctype="multipart/form-data">
			<fieldset>
				<?= $model->input("user_group", array("value" => $item["user_group"])) ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/user/group/list" class="button key:esc">Back</a></li>
				<li class="save"><input type="submit" value="Update" class="button primary key:s" /></li>
			</ul>
		</form>
	</div>

</div>