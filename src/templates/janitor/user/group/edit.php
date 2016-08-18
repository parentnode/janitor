<?php
global $action;
global $model;

$user_group_id = $action[2];
$item = $model->getUserGroups(array("user_group_id" => $user_group_id));
?>
<div class="scene i:scene defaultEdit usergroupEdit">
	<h1>Edit user group</h1>

	<ul class="actions i:defaultEditActions">
		<?= $HTML->link("Groups", "/janitor/admin/user/group/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
		<?= $JML->oneButtonForm("Delete", "/janitor/admin/user/deleteUserGroup/".$user_group_id, array(
			"wrapper" => "li.delete",
			"success-location" => "/janitor/admin/user/group/list"
		)) ?>
		<?= $HTML->link("Access", "/janitor/admin/user/access/edit/".$user_group_id, array("class" => "button primary", "wrapper" => "li.access")) ?>
	</ul>

	<div class="item i:defaultEdit">
		<h2>User group</h2>
		<?= $model->formStart("/janitor/admin/user/updateUserGroup/".$user_group_id, array("class" => "labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("user_group", array("value" => $item["user_group"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $HTML->link("Back", "/janitor/admin/user/group/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>