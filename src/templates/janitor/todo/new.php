<?php
global $action;
global $IC;
global $model;
global $itemtype;

$return_to_todolist = "";
if(count($action) == 3 && $action[1] == "todolist") {
	$return_to_todolist = $action[2];
	session()->value("return_to_todolist", $return_to_todolist);
}
?>
<div class="scene defaultNew">
	<h1>New Task</h1>

	<ul class="actions">
		<?
		// different "back"-links depending on where you came from
		if($return_to_todolist):
			print $HTML->link("Back", "/janitor/admin/todolist/edit/".$return_to_todolist, array("class" => "button", "wrapper" => "li.todolist"));
		// standard return link
		else:
			print $JML->newList(array("label" => "Back to overview"));
		endif;
		?>
	</ul>

	<?= $model->formStart("save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("name") ?>
			<?= $model->input("description", array("class" => "autoexpand")) ?>
			<?= $model->input("priority") ?>
			<?= $model->input("deadline", array("value" => date("Y-m-d", time()+(7*24*60*60)))) ?>
		</fieldset>

		<?= $JML->newActions() ?>
	<?= $model->formEnd() ?>

</div>
