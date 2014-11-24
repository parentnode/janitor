<?php
global $action;
global $model;

?>
<div class="scene defaultNew">
	<h1>New navigation</h1>

	<ul class="actions">
		<?= $JML->newList(array("label" => "List")) ?>
	</ul>

	<?= $model->formStart("/janitor/admin/navigation/save", array("class" => "i:defaultNew labelstyle:inject")) ?>
		<fieldset>
			<h2>Create a new navigation</h2>
			<p>A navigation is a structured link list, which can be used in your templates.</p>

			<?= $model->input("name") ?>
		</fieldset>

		<?= $JML->newActions() ?>
	<?= $model->formEnd() ?>
</div>
