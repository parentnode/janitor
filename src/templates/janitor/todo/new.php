<?php
global $action;
global $IC;
global $model;
global $itemtype;

$return_to_todolist = session()->value("return_to_todolist");
$todo_state_view = session()->value("todo_state_view");
?>
<div class="scene defaultNew">
	<h1>New Task</h1>

	<ul class="actions">
		<?
		// return to todolist view
		if($return_to_todolist):
			print $HTML->link("Back to todolist", "/janitor/admin/todolist/edit/".$return_to_todolist.($todo_state_view ? "/state/".$todo_state_view : ""), array("class" => "button", "wrapper" => "li.todolist"));

		// return to specific state view
		elseif($todo_state_view):
			print $HTML->link("Back to overview", "/janitor/admin/todo/list".($todo_state_view ? "/state/".$todo_state_view : ""), array("class" => "button", "wrapper" => "li.todolist"));

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
			<?= $model->input("deadline") ?>
		</fieldset>

		<?
		// different cancel links depending on context

		// default return link
		$options = false;

		// return to todolist view
		if($return_to_todolist):
			$options = array("modify" => array(
				"cancel" => [
					"url" => "/janitor/admin/todolist/edit/".$return_to_todolist.($todo_state_view ? "/state/".$todo_state_view : "")
				]
			));

		// return to specific state view
		elseif($todo_state_view):
			$options = array("modify" => array(
				"cancel" => [
					"url" => "/janitor/admin/todo/list".($todo_state_view ? "/state/".$todo_state_view : "")
				]
			));

		endif;

		print $JML->newActions($options); 
		?>
	<?= $model->formEnd() ?>

</div>
