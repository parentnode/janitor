<?php
global $action;
global $IC;
global $model;
global $itemtype;
?>
<div class="scene i:scene defaultNew">
	<h1>New message</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "Messages", "action" => "/janitor/admin/message")) ?>
	</ul>

	<?= $model->formStart("save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<?= $model->input("published_at", array("value" => date("Y-m-d H:i", time()))) ?>
			<?= $model->input("name") ?>
		</fieldset>

		<?= $JML->newActions(["modify" => [
			"cancel" => [
				"url" => "/janitor/admin/message"
			]
		]]) ?>
	<?= $model->formEnd() ?>
</div>
