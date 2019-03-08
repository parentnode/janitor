<?php
global $action;
global $IC;
global $model;
global $itemtype;
?>
<div class="scene i:scene defaultNew">
	<h1>New photo collection</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "List")) ?>
	</ul>

	<?= $model->formStart("save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("name") ?>
			<?= $model->input("description", array("class" => "autoexpand short")) ?>
		</fieldset>

		<?= $JML->newActions() ?>
	<?= $model->formEnd() ?>

</div>
