<?php
global $action;
global $IC;
global $model;
global $itemtype;

$item_id = $action[1];
$item = $IC->getItem(array("id" => $item_id, "extend" => array("tags" => true, "comments" => true)));

$return_to_todolist = session()->value("return_to_todolist");
?>
<div class="scene defaultEdit <?= $itemtype ?>Edit">
	<h1>Edit task</h1>

	<?
	// return to specific todolist
	if($return_to_todolist):
		print $JML->editGlobalActions($item, array("modify" => array(
			"list" => [
				"label" => "Back", 
				"url" => "/janitor/admin/todolist/edit/".$return_to_todolist
			])));
	// standard back button
	else:
		print $JML->editGlobalActions($item);
	endif;
	?>

	<div class="item i:defaultEdit">
		<h2>Task</h2>
		<?= $model->formStart("update/".$item["id"], array("class" => "labelstyle:inject")) ?>

			<fieldset>
				<?= $model->input("name", array("value" => $item["name"])) ?>
				<?= $model->input("description", array("class" => "autoexpand", "value" => $item["description"])) ?>
				<?= $model->input("user_id", array("type" => "select", "value" => $item["user_id"])) ?>
				<?= $model->input("priority", array("value" => $item["priority"])) ?>
				<?= $model->input("deadline", array("value" => date("Y-m-h", strtotime($item["deadline"])))) ?>
			</fieldset>

			<?= $JML->editActions($item) ?>

		<?= $model->formEnd() ?>
	</div>

	<?= $JML->editComments($item) ?>

	<?= $JML->editTags($item) ?>
</div>
