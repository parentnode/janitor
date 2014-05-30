<?php
global $action;
global $model;

// TODO: Create global function for this
$user_groups = $model->getUserGroups();
$user_groups_options = array();
foreach($user_groups as $user_group) {
	$user_groups_options[$user_group["id"]] = $user_group["user_group"];
}

?>

	<div class="scene defaultNew">
		<h1>New user</h1>

		<ul class="actions">
			<?= $model->link("List", "/admin/user/list", array("class" => "button", "wrapper" => "li.cancel")) ?>
		</ul>

		<h2>Let's start with some very basic information</h2>


		<?= $model->formStart("/admin/user/save", array("class" => "i:formDefaultNew labelstyle:inject")) ?>
			<h3>Name and user group</h3>
			<fieldset>
				<?= $model->input("nickname") ?>
				<?= $model->input("user_group_id", array("type" => "select", "options" => $user_groups_options)) ?>
			</fieldset>

			<ul class="actions">
				<?= $model->link("Back", "/admin/user/list", array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Save", array("class" => "primary key:s", "wrapper" => "li.save")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

