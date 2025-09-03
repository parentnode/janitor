<?php
global $action;
global $IC;
global $model;
global $itemtype;
?>
<div class="scene i:scene defaultNew">
	<h1>New tag</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "List")) ?>
	</ul>

	<?= $model->formStart("addTag", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<?= $model->input("return_to", ["type" => "hidden", "value" => "/janitor/admin/tag/list"]) ?>
		<fieldset>
			<?= $model->input("context") ?>
			<?= $model->input("value") ?>
			<?= $model->input("description") ?>
		</fieldset>

		<?= $JML->newActions([
			"modify" => [
				"save" => [
					"label" => "Save"
				]
			]
		]) ?>
	<?= $model->formEnd() ?>

</div>
