<?php
global $action;
global $IC;
global $model;
global $itemtype;
?>
<div class="scene defaultNew">
	<h1>New Task</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "List")) ?>
	</ul>

	<?= $model->formStart("save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<?= $model->input("status", array("type" => "hidden", "value" => 1)) ?>
		<fieldset>
			<?= $model->input("name") ?>
			<?= $model->input("description", array("class" => "autoexpand")) ?>
			<?= $model->input("priority") ?>
			<?= $model->input("deadline", array("value" => date("Y-m-d", time()+(7*24*60*60)))) ?>
		</fieldset>

		<?= $JML->newActions() ?>
	<?= $model->formEnd() ?>

</div>
