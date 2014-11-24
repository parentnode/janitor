<?php
global $action;
global $model;

$user_groups_options = $model->toOptions($model->getUserGroups(), "id", "user_group");
?>
<div class="scene defaultNew">
	<h1>New user</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "List")) ?>
	</ul>

	<?= $model->formStart("save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("nickname") ?>
			<?= $model->input("user_group_id", array(
				"type" => "select", 
				"options" => $user_groups_options)
			) ?>
		</fieldset>

		<?= $JML->newActions() ?>
	<?= $model->formEnd() ?>
</div>

