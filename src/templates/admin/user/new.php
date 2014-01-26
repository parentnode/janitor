<?php

$model = new User();

// TODO: Create global function for this
$user_groups = $model->getUserGroups();
$user_groups_options = array();
foreach($user_groups as $user_group) {
	$option = array();
	$option[0] = $user_group["id"];
	$option[1] = $user_group["user_group"];
	$user_groups_options[] = $option;
}

?>

	<div class="scene defaultNew">
		<h1>New user</h1>

		<ul class="actions">
			<li class="cancel"><a href="/admin/user/list" class="button">Back</a></li>
		</ul>

		<form action="/admin/user/save" class="i:formDefaultNew labelstyle:inject" method="post" enctype="multipart/form-data">

			<fieldset>
				<?= $model->input("nickname") ?>
				<?= $model->input("user_group_id", array("type" => "select", "options" => $user_groups_options)) ?>
			</fieldset>

			<ul class="actions">
				<li class="cancel"><a href="/admin/user/list" class="button key:esc">Back</a></li>
				<li class="save"><input type="submit" value="Save" class="button primary key:s" /></li>
			</ul>

		</form>
	</div>

