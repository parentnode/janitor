<?php
global $action;
global $model;

$user_groups_options = $model->toOptions($model->getUserGroups(), "id", "user_group");
?>
<div class="scene defaultNew">
	<h1>New user</h1>

	<ul class="actions">
		<?= $model->link("List", "/janitor/admin/user/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
	</ul>

	<?= $model->formStart("/janitor/admin/user/save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("nickname") ?>
			<?= $model->input("user_group_id", array(
				"type" => "select", 
				"options" => $user_groups_options)
			) ?>
		</fieldset>

		<ul class="actions">
			<?= $model->link("Back", "/janitor/admin/user/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
			<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
		</ul>
	<?= $model->formEnd() ?>
</div>

