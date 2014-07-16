<?php
global $action;
global $model;

$item = $model->getUserGroups(array("user_group_id" => $action[2]));

?>

<div class="scene defaultEdit usergroupEdit">
	<h1>Edit user group</h1>

	<ul class="actions">
		<?= $HTML->link("Groups", "/admin/user/group/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<div class="item i:defaultEdit">
		<?= $model->formStart("/admin/user/updateUserGroup/".$action[2], array("class" => "i:formDefaultNew labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("user_group", array("value" => $item["user_group"])) ?>
			</fieldset>

			<ul class="actions">
				<?= $HTML->link("Back", "/admin/user/group/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Update", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>