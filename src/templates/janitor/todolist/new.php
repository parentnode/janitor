<?php
global $action;
global $IC;
global $model;
global $itemtype;

$todo_state_view = session()->value("todo_state_view");
?>
<div class="scene i:scene defaultNew">
	<h1>New TODO list</h1>

	<ul class="actions">
		<?
		// return to specific state view
		if($todo_state_view):
			print $HTML->link("Back to overview", "/janitor/admin/todo/list".($todo_state_view ? "/state/".$todo_state_view : ""), array("class" => "button primary key:esc", "wrapper" => "li.back"));

		// standard return link
		else:
			print $HTML->link("Back to overview", "/janitor/admin/todo/list", array("class" => "button primary key:esc", "wrapper" => "li.back"));

		endif;
		?>
	</ul>

	<?= $model->formStart("save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("name") ?>
			<?= $model->input("classname") ?>
			<?= $model->input("description") ?>
		</fieldset>

		<?
		// different cancel links depending on context

		// return to specific state view
		if($todo_state_view):
			$options = array("modify" => array(
				"cancel" => [
					"url" => "/janitor/admin/todo/list".($todo_state_view ? "/state/".$todo_state_view : "")
				]
			));

		// default return link
		else:
			$options = array("modify" => array(
				"cancel" => [
					"url" => "/janitor/admin/todo/list"
				]
			));

		endif;

		print $JML->newActions($options); 
		?>

	<?= $model->formEnd() ?>

</div>
