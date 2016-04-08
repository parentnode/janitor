<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "comments" => true)));

$return_to_todolist = session()->value("return_to_todolist");
$todo_state_view = session()->value("todo_state_view");

?>
<div class="scene i:scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit task</h1>
	<h2><?= $item["name"] ?></h2>

	<?
	// different back links depending on context

	// standard back button
	$options = false;

	// return to specific todolist
	if($return_to_todolist):
		$options = array("modify" => array(
			"list" => [
				"label" => "Back to todolist", 
				"url" => "/janitor/admin/todolist/edit/".$return_to_todolist.($todo_state_view ? "/state/".$todo_state_view : "")
			]
		));

	// return to specific state view
	elseif($todo_state_view):
		$options = array("modify" => array(
			"list" => [
				"label" => "Back", 
				"url" => "/janitor/admin/todo/list/state/".$todo_state_view
			]
		));

	endif;

	print $JML->editGlobalActions($item, $options);
	?>

	<div class="item i:defaultEdit">
		<h2>Task</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("description", array("class" => "autoexpand", "value" => $item["description"])) ?>
				<?= $model->input("user_id", array("type" => "select", "value" => $item["user_id"])) ?>
				<?= $model->input("priority", array("value" => $item["priority"])) ?>
				<?= $model->input("state", array("value" => $item["state"])) ?>
				<?= $model->input("deadline", array("value" => (strtotime($item["deadline"]) > 0 ? date("Y-m-h", strtotime($item["deadline"])) : ""))) ?>
				<?= $model->input("estimate", array("value" => $item["estimate"])) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>

	<?= $JML->editComments($item) ?>

	<?= $JML->editTags($item) ?>
</div>
