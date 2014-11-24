<?php
global $action;
global $IC;
global $model;
global $itemtype;
?>
<div class="scene defaultNew">
	<h1>New post</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "List")) ?>
	</ul>

	<?= $model->formStart("save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("published_at", array("value" => date("Y-m-d H:i", time()))) ?>
			<?= $model->input("name") ?>
			<?= $model->input("description", array("class" => "autoexpand short")) ?>
			<?= $model->inputHTML("html") ?>
		</fieldset>

		<?= $JML->newActions() ?>
	<?= $model->formEnd() ?>
</div>
